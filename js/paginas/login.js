/* ==========================================================================
   login.js — valida e-mail e senha antes de enviar e alterna "Mostrar/Ocultar"
   senha. O login.php confere a senha (password_verify), abre a $_SESSION e,
   em caso de erro, devolve a página com a mensagem e o e-mail digitado.
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

  // Sem aoEnviar: com os campos válidos, o formulário segue por POST para o login.php
  prepararFormulario(form);
}

