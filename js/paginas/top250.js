/* ==========================================================================
   top250.js — P4: ranking da RN03 (mínimo de avaliações aprovadas, ordem por
   média e votos). Permite reordenar pelas colunas, filtrar por gênero,
   inverter a ordem e mostrar mais linhas aos poucos.
   ========================================================================== */

import { config } from '../nucleo/config.js';
import { carregarDados } from '../nucleo/dados.js';
import { comEstatisticas, rankingTop250 } from '../nucleo/regras.js';
import { escaparHtml, formatarNota, formatarDuracao, normalizar, plural } from '../nucleo/util.js';
import { urlPoster, linkTitulo, botaoWatchlist } from '../componentes/card.js';

// Ordem "natural" de cada critério (nota e votos: maior primeiro; título: A–Z)
const ORDENACOES = {
  posicao: (a, b) => a.posicao - b.posicao,
  nota: (a, b) => b.media - a.media || b.votos - a.votos,
  votos: (a, b) => b.votos - a.votos,
  ano: (a, b) => b.ano - a.ano,
  titulo: (a, b) => a.titulo.localeCompare(b.titulo, 'pt-BR')
};

function linhaTabela(t) {
  const nome = escaparHtml(t.titulo);
  return `
    <tr data-id="${t.id}">
      <td class="numero top-posicao">${t.posicao}</td>
      <th scope="row" class="top-titulo">
        <img class="poster" src="${escaparHtml(urlPoster(t))}" alt="Pôster de ${nome}" width="32" height="48" loading="lazy">
        <span>
          <a href="${linkTitulo(t.id)}">${nome}</a>
          <span class="top-meta">${t.ano} · ${escaparHtml(formatarDuracao(t))}</span>
        </span>
      </th>
      <td class="numero top-ano">${t.ano}</td>
      <td class="numero top-nota"><span class="nota"><i class="bi bi-star-fill" aria-hidden="true"></i> <span class="nota-valor">${formatarNota(t.media)}</span></span></td>
      <td class="numero top-votos">${t.votos}</td>
      <td class="centro top-acao">
        <a class="botao botao-contorno botao-icone botao-pequeno" href="${linkTitulo(t.id, '#avaliar')}">
          <i class="bi bi-star" aria-hidden="true"></i><span class="visualmente-oculto">Avaliar ${nome}</span>
        </a>
      </td>
      <td class="centro top-acao">${botaoWatchlist(t, 'icone', 'botao-contorno botao-icone botao-pequeno')}</td>
    </tr>`;
}

export async function iniciar() {
  const { titulos, avaliacoes } = await carregarDados();
  const site = config();
  const porVez = site.catalogo.top250_por_vez;

  // Posição fixa do ranking oficial (não muda quando o usuário reordena)
  const ranking = rankingTop250(comEstatisticas(titulos, avaliacoes))
    .map((t, i) => ({ ...t, posicao: i + 1 }));

  const corpo = document.getElementById('corpo-top250');
  const seletorOrdem = document.getElementById('ordenar-top');
  const seletorGenero = document.getElementById('filtro-genero-top');
  const botaoMais = document.getElementById('mostrar-mais-top');
  const contador = document.getElementById('contador-top');

  // Texto da regra com o número vindo do site.json
  document.querySelector('.cabecalho-pagina .subtitulo').textContent =
    `Classificados pela nota média das avaliações aprovadas; exige no mínimo ${site.regras.RN03.minimo_aprovadas} avaliações (RN03).`;

  // Opções de gênero a partir do site.json
  seletorGenero.innerHTML = '<option value="">Gênero: todos</option>' +
    site.catalogo.generos.map((g) => `<option value="${escaparHtml(g)}">${escaparHtml(g)}</option>`).join('');

  const estado = { ordem: 'posicao', invertido: false, genero: '', mostrando: porVez };

  function desenhar() {
    let linhas = ranking.filter((t) =>
      !estado.genero || t.generos.some((g) => normalizar(g) === normalizar(estado.genero)));
    linhas.sort(ORDENACOES[estado.ordem]);
    if (estado.invertido) linhas.reverse();

    contador.textContent = plural(linhas.length, 'título', 'títulos');
    corpo.innerHTML = linhas.length
      ? linhas.slice(0, estado.mostrando).map(linhaTabela).join('')
      : `<tr><td colspan="7" class="centro">Nenhum título com ${site.regras.RN03.minimo_aprovadas} ou mais avaliações aprovadas neste filtro.</td></tr>`;
    botaoMais.hidden = linhas.length <= estado.mostrando;

    // aria-sort no cabeçalho da coluna ordenada (o CSS gira a setinha)
    document.querySelectorAll('#tabela-top250 thead th').forEach((th) => {
      const botao = th.querySelector('[data-ordenar]');
      if (botao && botao.dataset.ordenar === estado.ordem) {
        th.setAttribute('aria-sort', estado.invertido ? 'ascending' : 'descending');
      } else {
        th.removeAttribute('aria-sort');
      }
    });
  }

  seletorOrdem.addEventListener('change', () => {
    estado.ordem = seletorOrdem.value;
    estado.invertido = false;
    desenhar();
  });

  seletorGenero.addEventListener('change', () => {
    estado.genero = seletorGenero.value;
    estado.mostrando = porVez;
    desenhar();
  });

  document.getElementById('inverter-ordem').addEventListener('click', () => {
    estado.invertido = !estado.invertido;
    desenhar();
  });

  // Clique no cabeçalho: ordena pela coluna; clicar de novo inverte
  document.querySelectorAll('#tabela-top250 [data-ordenar]').forEach((botao) => {
    botao.addEventListener('click', () => {
      const coluna = botao.dataset.ordenar;
      estado.invertido = estado.ordem === coluna ? !estado.invertido : false;
      estado.ordem = coluna;
      seletorOrdem.value = coluna;
      desenhar();
    });
  });

  botaoMais.addEventListener('click', () => {
    estado.mostrando += porVez;
    desenhar();
  });

  desenhar();
}
