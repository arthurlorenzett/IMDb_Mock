/* ==========================================================================
   layout.js — comportamento do cabeçalho comum a todas as páginas:
   gaveta do menu (☰), contador da Watchlist e busca do cabeçalho.
   O HTML do cabeçalho e do rodapé já vem pronto em cada página.
   ========================================================================== */

import { lerWatchlist } from '../nucleo/armazenamento.js';
import { parametros } from '../nucleo/util.js';

/* ---- Gaveta do menu: abre/fecha no ☰, fecha no ✕, no Esc e ao clicar fora ---- */
function prepararMenu() {
  const botao = document.getElementById('botao-menu');
  if (!botao) return;
  const gaveta = document.getElementById(botao.getAttribute('aria-controls'));
  const botaoFechar = document.getElementById('fechar-menu');

  const estaAberta = () => gaveta.classList.contains('aberta');

  function abrir() {
    gaveta.classList.add('aberta');
    botao.setAttribute('aria-expanded', 'true');
    // Leva o foco para dentro da gaveta (navegação por teclado). Espera um instante
    // porque, enquanto a gaveta ainda está com visibility: hidden, o foco não entra
    setTimeout(() => (botaoFechar || gaveta.querySelector('a')).focus(), 50);
  }

  function fechar(devolverFoco = true) {
    gaveta.classList.remove('aberta');
    botao.setAttribute('aria-expanded', 'false');
    if (devolverFoco) botao.focus();
  }

  botao.addEventListener('click', () => (estaAberta() ? fechar() : abrir()));
  botaoFechar?.addEventListener('click', () => fechar());

  document.addEventListener('keydown', (evento) => {
    if (evento.key === 'Escape' && estaAberta()) fechar();
  });

  // Clique fora da gaveta (ou num link dela) fecha sem roubar o foco
  document.addEventListener('click', (evento) => {
    if (!estaAberta() || botao.contains(evento.target)) return;
    if (!gaveta.contains(evento.target) || evento.target.closest('a')) fechar(false);
  });
}

/* ---- Contador "Watchlist (n)" do cabeçalho e da gaveta ---- */
function atualizarContador() {
  const total = lerWatchlist().length;
  document.querySelectorAll('[data-contador-watchlist]').forEach((contador) => {
    contador.textContent = total;
  });
}

/* ---- Busca do cabeçalho: mantém o termo digitado ao chegar na página de busca ---- */
function prepararBuscaCabecalho() {
  const termo = document.getElementById('busca-termo');
  const tipo = document.getElementById('busca-tipo');
  if (!termo) return;
  const params = parametros();
  termo.value = params.get('q') || '';
  if (tipo) tipo.value = params.get('tipo') || '';
}

export function iniciarLayout() {
  prepararMenu();
  prepararBuscaCabecalho();
  atualizarContador();
  document.addEventListener('watchlist-alterada', atualizarContador);
}
