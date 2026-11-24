/* ==========================================================================
   login.js — login autenticado pelo PHP usando $_SESSION.
   ========================================================================== */

import { prepararFormulario } from '../componentes/validacao.js';

function prepararMostrarSenha() {
  const botao = document.getElementById('mostrar-senha');
  const senha = document.getElementById('login-senha');

  botao.addEventListener('click', () => {
    const mostrando = senha.type === 'text';

    senha.type = mostrando ? 'password' : 'text';
    botao.textContent = mostrando ? 'Mostrar' : 'Ocultar';
    botao.setAttribute('aria-pressed', String(!mostrando));
  });
}

export async function iniciar() {
  prepararMostrarSenha();

  const form = document.getElementById('form-login');
  const alerta = document.getElementById('alerta-login');

  form.addEventListener('input', () => {
    alerta.hidden = true;
  });

  prepararFormulario(form);
  
  if (new URLSearchParams(window.location.search).has('erro')) {
    alerta.hidden = false;
  }
}

