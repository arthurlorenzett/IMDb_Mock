/* ==========================================================================
   painel.js — P7: área restrita. Lista os títulos (com filtro, editar e excluir
   com confirmação) e modera as avaliações pendentes (RN02: aprovar/rejeitar
   recalcula a média). Na Parte 2 as alterações ficam no localStorage.
   ========================================================================== */

import { config } from '../nucleo/config.js';
import { carregarDados, excluirTitulo, moderarAvaliacao } from '../nucleo/dados.js';
import { comEstatisticas } from '../nucleo/regras.js';
import { exigirLogin } from '../nucleo/sessao.js';
import { escaparHtml, formatarNota, normalizar, parametros } from '../nucleo/util.js';
import { urlPoster } from '../componentes/card.js';
import { abrirModal, fecharModal } from '../componentes/modal.js';

// Mensagens de retorno (?msg=salvo depois do formulário de título)
const MENSAGENS = {
  salvo: 'Título salvo com sucesso.',
  excluido: 'Título excluído, junto com as avaliações dele.',
  aprovada: 'Avaliação aprovada. A nota média do título foi recalculada.',
  rejeitada: 'Avaliação rejeitada. Ela não aparecerá no site.'
};

let temporizadorAlerta = null;

function mostrarAlerta(chave) {
  const alerta = document.getElementById('alerta-painel');
  alerta.querySelector('span').textContent = MENSAGENS[chave];
  alerta.hidden = false;
  // Esconde sozinho depois de alguns segundos
  clearTimeout(temporizadorAlerta);
  temporizadorAlerta = setTimeout(() => (alerta.hidden = true), config().interface.alerta_some_ms);
}

function linhaTitulo(t) {
  const nome = escaparHtml(t.titulo);
  return `
    <tr data-id="${t.id}">
      <td class="numero">${t.id}</td>
      <td><img class="poster poster-mini" src="${escaparHtml(urlPoster(t))}" alt="Pôster de ${nome}" width="32" height="48" loading="lazy"></td>
      <th scope="row">${nome}</th>
      <td class="numero">${t.ano}</td>
      <td class="numero">${formatarNota(t.media)}</td>
      <td class="numero">${t.votos}</td>
      <td>
        <div class="acoes-tabela">
          <a class="botao botao-contorno botao-pequeno" href="titulo-form.html?id=${t.id}"><i class="bi bi-pencil-square" aria-hidden="true"></i> Editar<span class="visualmente-oculto"> ${nome}</span></a>
          <button class="botao botao-contorno botao-pequeno botao-excluir" type="button" data-id="${t.id}"><i class="bi bi-trash" aria-hidden="true"></i> Excluir<span class="visualmente-oculto"> ${nome}</span></button>
        </div>
      </td>
    </tr>`;
}

function linhaPendente(a, nomeTitulo) {
  return `
    <tr data-id-avaliacao="${a.id}">
      <th scope="row">${escaparHtml(nomeTitulo)}</th>
      <td>${escaparHtml(a.email)}</td>
      <td class="numero">${a.nota}</td>
      <td class="comentario-celula">${escaparHtml(a.comentario)}</td>
      <td>
        <div class="acoes-tabela">
          <button class="botao botao-contorno botao-pequeno botao-aprovar" type="button" data-id-avaliacao="${a.id}" data-status="aprovada"><i class="bi bi-check-lg" aria-hidden="true"></i> Aprovar</button>
          <button class="botao botao-contorno botao-pequeno botao-rejeitar" type="button" data-id-avaliacao="${a.id}" data-status="rejeitada"><i class="bi bi-x-lg" aria-hidden="true"></i> Rejeitar</button>
        </div>
      </td>
    </tr>`;
}

export async function iniciar() {
  if (!exigirLogin()) return;

  const dados = await carregarDados();
  const filtro = document.getElementById('filtro-titulos');
  const corpoTitulos = document.querySelector('#tabela-titulos tbody');
  const corpoPendentes = document.querySelector('#tabela-pendentes tbody');
  let idParaExcluir = null;

  function desenharTitulos() {
    const termo = normalizar(filtro.value);
    const lista = comEstatisticas(dados.titulos, dados.avaliacoes)
      .filter((t) => normalizar(t.titulo).includes(termo))
      .sort((a, b) => a.id - b.id);
    corpoTitulos.innerHTML = lista.length
      ? lista.map(linhaTitulo).join('')
      : '<tr><td colspan="7" class="centro">Nenhum título encontrado.</td></tr>';
  }

  function desenharPendentes() {
    const nomes = new Map(dados.titulos.map((t) => [t.id, t.titulo]));
    const pendentes = dados.avaliacoes
      .filter((a) => a.status === config().regras.RN02.status_inicial)
      .sort((a, b) => a.criada_em.localeCompare(b.criada_em)); // mais antigas primeiro
    corpoPendentes.innerHTML = pendentes.length
      ? pendentes.map((a) => linhaPendente(a, nomes.get(a.titulo_id))).join('')
      : '<tr><td colspan="5" class="centro">Nenhuma avaliação aguardando moderação.</td></tr>';

    // Contadores "(n)" no título da seção e no menu do painel
    document.querySelector('#titulo-pendentes .nota-votos').textContent = `(${pendentes.length})`;
    const linkMenu = document.querySelector('#menu-admin a[href$="#avaliacoes"]');
    if (linkMenu) {
      linkMenu.innerHTML = `<i class="bi bi-chat-square-text" aria-hidden="true"></i> Avaliações (${pendentes.length})`;
    }
  }

  // Alerta só aparece quando há mensagem na URL (?msg=salvo)
  const msg = parametros().get('msg');
  document.getElementById('alerta-painel').hidden = true;
  if (MENSAGENS[msg]) mostrarAlerta(msg);

  filtro.addEventListener('input', desenharTitulos);

  // Excluir: guarda o id e pede confirmação no modal
  corpoTitulos.addEventListener('click', (evento) => {
    const botao = evento.target.closest('.botao-excluir');
    if (!botao) return;
    idParaExcluir = Number(botao.dataset.id);
    document.getElementById('nome-excluir').textContent =
      dados.titulos.find((t) => t.id === idParaExcluir).titulo;
    abrirModal('modal-excluir');
  });

  document.getElementById('confirmar-excluir').addEventListener('click', () => {
    excluirTitulo(idParaExcluir);
    fecharModal('modal-excluir');
    desenharTitulos();
    desenharPendentes();
    mostrarAlerta('excluido');
  });

  // Aprovar/Rejeitar: muda o status e redesenha as duas tabelas (média recalculada)
  corpoPendentes.addEventListener('click', (evento) => {
    const botao = evento.target.closest('[data-status]');
    if (!botao) return;
    moderarAvaliacao(botao.dataset.idAvaliacao, botao.dataset.status);
    desenharPendentes();
    desenharTitulos();
    mostrarAlerta(botao.dataset.status);
    corpoPendentes.querySelector('button')?.focus(); // o foco não se perde ao sumir a linha
  });

  desenharTitulos();
  desenharPendentes();
}
