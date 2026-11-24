/* ==========================================================================
   busca.js — P2: lê os filtros da URL (formulário GET), filtra e ordena os
   títulos, mostra a lista com paginação (desktop) ou "Carregar mais" (mobile)
   e alterna entre visualização em grade e em lista.
   ========================================================================== */

import { config } from '../nucleo/config.js';
import { carregarDados } from '../nucleo/dados.js';
import { comEstatisticas } from '../nucleo/regras.js';
import { escaparHtml, normalizar, parametros, plural } from '../nucleo/util.js';
import { itemLista } from '../componentes/card.js';
import { prepararFormulario, mostrarErro } from '../componentes/validacao.js';

// Filtros escolhidos, lidos de ?q=&genero=&ano_de=&ano_ate=&nota_min=&tipo=&ordem=&pagina=
function lerFiltros() {
  const p = parametros();
  return {
    q: (p.get('q') || '').trim(),
    generos: p.getAll('genero').map(normalizar).filter(Boolean),
    anoDe: Number(p.get('ano_de')) || null,
    anoAte: Number(p.get('ano_ate')) || null,
    notaMin: Number(p.get('nota_min')) || null,
    tipo: p.get('tipo') || '',
    ordem: p.get('ordem') || 'popularidade',
    pagina: Math.max(1, Number(p.get('pagina')) || 1)
  };
}

function filtrar(titulos, f) {
  const termo = normalizar(f.q);
  return titulos.filter((t) => {
    // Busca no título, direção, elenco, gêneros e sinopse
    const texto = normalizar([t.titulo, t.direcao, t.elenco.join(' '), t.generos.join(' '), t.sinopse].join(' '));
    if (termo && !texto.includes(termo)) return false;
    if (f.generos.length && !t.generos.some((g) => f.generos.includes(normalizar(g)))) return false;
    if (f.anoDe && t.ano < f.anoDe) return false;
    if (f.anoAte && t.ano > f.anoAte) return false;
    if (f.notaMin && (t.media === null || t.media < f.notaMin)) return false;
    if (f.tipo && t.tipo !== f.tipo) return false;
    return true;
  });
}

const ORDENACOES = {
  popularidade: (a, b) => b.popularidade - a.popularidade,
  nota: (a, b) => (b.media ?? -1) - (a.media ?? -1) || b.votos - a.votos,
  ano: (a, b) => b.ano - a.ano,
  titulo: (a, b) => a.titulo.localeCompare(b.titulo, 'pt-BR')
};

/* ---- Formulário de filtros: gêneros do site.json e valores atuais da URL ---- */
function preencherFormulario(f) {
  const form = document.getElementById('form-filtros');
  form.querySelector('input[name="q"]').value = f.q;

  form.querySelector('.opcoes-coluna').innerHTML = config().catalogo.generos.map((genero) => `
    <label class="opcao"><input type="checkbox" name="genero" value="${escaparHtml(genero)}"
      ${f.generos.includes(normalizar(genero)) ? 'checked' : ''}> ${escaparHtml(genero)}</label>`).join('');

  document.getElementById('filtro-ano-de').value = f.anoDe || '';
  document.getElementById('filtro-ano-ate').value = f.anoAte || '';
  document.getElementById('filtro-nota').value = f.notaMin ? String(f.notaMin) : '';
  form.querySelectorAll('input[name="tipo"]').forEach((radio) => (radio.checked = radio.value === f.tipo));
  document.getElementById('ordenar-busca').value = f.ordem in ORDENACOES ? f.ordem : 'popularidade';

  // Quantos filtros estão ativos, para o botão "Filtros (n)" do mobile
  const ativos = f.generos.length + (f.anoDe || f.anoAte ? 1 : 0) + (f.notaMin ? 1 : 0) + (f.tipo ? 1 : 0);
  document.getElementById('abrir-filtros').innerHTML =
    `<i class="bi bi-funnel" aria-hidden="true"></i> Filtros${ativos ? ` (${ativos})` : ''}`;

  // Regra que o HTML não cobre: ano inicial ≤ ano final
  prepararFormulario(form, {
    validarExtra() {
      const de = document.getElementById('filtro-ano-de');
      const ate = document.getElementById('filtro-ano-ate');
      if (de.value && ate.value && Number(de.value) > Number(ate.value)) {
        mostrarErro(de, config().validacao.mensagens.ano_intervalo);
        ate.setAttribute('aria-invalid', 'true');
        return false;
      }
      return true;
    }
  });

  // Mudar a ordenação já reenvia o formulário
  document.getElementById('ordenar-busca').addEventListener('change', () => form.requestSubmit());
}

/* ---- Painel de filtros no mobile (abre/fecha) ---- */
function prepararPainelFiltros() {
  const painel = document.getElementById('painel-filtros');
  const abrir = document.getElementById('abrir-filtros');
  const fechar = document.getElementById('fechar-filtros');

  abrir.addEventListener('click', () => {
    const aberto = painel.classList.toggle('aberto');
    abrir.setAttribute('aria-expanded', String(aberto));
    if (aberto) painel.querySelector('input, select').focus();
  });
  fechar.addEventListener('click', () => {
    painel.classList.remove('aberto');
    abrir.setAttribute('aria-expanded', 'false');
    abrir.focus();
  });
}

/* ---- Alternância grade/lista ---- */
function prepararVisualizacao() {
  const lista = document.getElementById('lista-resultados');
  const botoes = document.querySelectorAll('.alternar-visualizacao [data-visualizacao]');
  botoes.forEach((botao) => {
    botao.addEventListener('click', () => {
      lista.dataset.visualizacao = botao.dataset.visualizacao;
      botoes.forEach((b) => b.setAttribute('aria-pressed', String(b === botao)));
    });
  });
}

/* ---- Paginação: links que mantêm os filtros e só trocam ?pagina= ---- */
function linkPagina(numero) {
  const p = parametros();
  p.set('pagina', numero);
  return `busca.php?${escaparHtml(p.toString())}`;
}

function montarPaginacao(paginaAtual, totalPaginas) {
  const nav = document.querySelector('.paginacao');
  nav.hidden = totalPaginas <= 1;
  if (nav.hidden) return;

  let itens = '';
  if (paginaAtual > 1) {
    itens += `<li><a href="${linkPagina(paginaAtual - 1)}" aria-label="Página anterior"><i class="bi bi-chevron-left" aria-hidden="true"></i></a></li>`;
  }
  for (let n = 1; n <= totalPaginas; n++) {
    itens += `<li><a href="${linkPagina(n)}"${n === paginaAtual ? ' aria-current="page"' : ''}>${n}</a></li>`;
  }
  if (paginaAtual < totalPaginas) {
    itens += `<li><a href="${linkPagina(paginaAtual + 1)}" aria-label="Próxima página"><i class="bi bi-chevron-right" aria-hidden="true"></i></a></li>`;
  }
  nav.querySelector('ol').innerHTML = itens;
}

export async function iniciar() {
  const { titulos, avaliacoes } = await carregarDados();
  const f = lerFiltros();
  const porPagina = config().catalogo.resultados_por_pagina;

  preencherFormulario(f);
  prepararPainelFiltros();
  prepararVisualizacao();

  const resultados = filtrar(comEstatisticas(titulos, avaliacoes), f)
    .sort(ORDENACOES[f.ordem] || ORDENACOES.popularidade);

  // Cabeçalho: termo buscado e quantidade
  const h1 = document.querySelector('.cabecalho-pagina h1');
  h1.innerHTML = f.q
    ? `Resultados da busca: <span id="termo-busca">“${escaparHtml(f.q)}”</span>`
    : 'Catálogo de títulos';
  document.title = `${f.q ? `Busca: ${f.q}` : 'Catálogo'} · IMDb Clone`;
  document.getElementById('contador-resultados').textContent = resultados.length
    ? `${plural(resultados.length, 'título encontrado', 'títulos encontrados')}`
    : 'Nenhum título encontrado';

  const lista = document.getElementById('lista-resultados');
  const totalPaginas = Math.max(1, Math.ceil(resultados.length / porPagina));
  const pagina = Math.min(f.pagina, totalPaginas);
  let exibidos = pagina * porPagina; // até onde a lista já foi mostrada

  // Mostra os itens de "inicio" até "fim" (numeração continua entre páginas)
  const htmlItens = (inicio, fim) => resultados.slice(inicio, fim)
    .map((t, i) => itemLista(t, inicio + i + 1)).join('');

  if (resultados.length === 0) {
    lista.innerHTML = `<li class="estado-vazio"><i class="bi bi-search" aria-hidden="true"></i>
      <h2>Nada por aqui</h2><p>Tente outro termo ou <a href="busca.php">limpe os filtros</a>.</p></li>`;
  } else {
    lista.innerHTML = htmlItens((pagina - 1) * porPagina, exibidos);
  }
  montarPaginacao(pagina, totalPaginas);

  // Mobile: "Carregar mais" acrescenta a próxima página no fim da lista
  const carregarMais = document.getElementById('carregar-mais');
  carregarMais.hidden = exibidos >= resultados.length;
  carregarMais.addEventListener('click', () => {
    lista.insertAdjacentHTML('beforeend', htmlItens(exibidos, exibidos + porPagina));
    exibidos += porPagina;
    carregarMais.hidden = exibidos >= resultados.length;
  });
}
