/* ==========================================================================
   tema.js — alterna entre tema claro (padrão) e escuro.
   O CSS já tem as duas paletas em variaveis.css; aqui só trocamos o atributo
   html[data-tema] e guardamos a escolha no localStorage.
   ========================================================================== */

import { ler, salvar } from '../nucleo/armazenamento.js';

// Aplica o tema salvo; roda antes de carregar os dados para a tela não "piscar"
export function aplicarTemaSalvo() {
  const tema = ler('tema', null);
  if (tema === 'escuro') {
    document.documentElement.dataset.tema = 'escuro';
  }
}

// Atualiza ícone (lua/sol), aria-pressed e texto do botão de tema
function atualizarBotao(botao, escuro) {
  botao.setAttribute('aria-pressed', String(escuro));
  const icone = botao.querySelector('.bi');
  icone.classList.toggle('bi-moon', !escuro);
  icone.classList.toggle('bi-sun', escuro);
  botao.querySelector('.visualmente-oculto').textContent = escuro ? 'Ativar tema claro' : 'Ativar tema escuro';
}

export function iniciarTema() {
  aplicarTemaSalvo();
  const botao = document.getElementById('botao-tema');
  if (!botao) return;

  atualizarBotao(botao, document.documentElement.dataset.tema === 'escuro');

  botao.addEventListener('click', () => {
    const escuro = document.documentElement.dataset.tema !== 'escuro';
    if (escuro) document.documentElement.dataset.tema = 'escuro';
    else delete document.documentElement.dataset.tema;
    salvar('tema', escuro ? 'escuro' : 'claro');
    atualizarBotao(botao, escuro);
  });
}
