/* ==========================================================================
   sessao.js — simula o login do administrador na Parte 2.
   A "sessão" fica no localStorage; na Parte 3 vira $_SESSION no PHP e as
   páginas do admin passam a redirecionar com header('Location: login.php').
   ========================================================================== */

import { ler, salvar, remover } from './armazenamento.js';

export function lerSessao() {
  return ler('sessao-admin', null);
}

export function iniciarSessao(admin) {
  salvar('sessao-admin', { nome: admin.nome, email: admin.email, desde: new Date().toISOString() });
}

export function encerrarSessao() {
  remover('sessao-admin');
}

// Páginas restritas chamam isto primeiro: sem sessão, volta para o login
export function exigirLogin() {
  const sessao = lerSessao();
  if (!sessao) {
    window.location.replace('login.html');
    return null;
  }
  // "Olá, Administrador" e botões Sair (cabeçalho e menu do painel)
  const saudacao = document.querySelector('.saudacao');
  if (saudacao) saudacao.textContent = `Olá, ${sessao.nome}`;
  document.querySelectorAll('#botao-sair, #menu-admin a[href$="login.html"]').forEach((link) => {
    link.addEventListener('click', encerrarSessao);
  });
  return sessao;
}
