/* ==========================================================================
   login.js — P6: valida e-mail e senha, alterna "Mostrar/Ocultar" senha e
   simula o login com o usuário de demonstração do site.json.
   Na Parte 3 o PHP confere a senha com password_verify e abre a $_SESSION.
   ========================================================================== */

import { config } from '../nucleo/config.js';
import { normalizar } from '../nucleo/util.js';
import { iniciarSessao, lerSessao } from '../nucleo/sessao.js';
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
  // Já logado neste navegador: vai direto para o painel
  if (lerSessao()) {
    window.location.replace('painel.html');
    return;
  }

  prepararMostrarSenha();
  const form = document.getElementById('form-login');
  const alerta = document.getElementById('alerta-login');
  const admin = config().admin_demo;

  // Some o alerta assim que o usuário volta a digitar
  form.addEventListener('input', () => (alerta.hidden = true));

  prepararFormulario(form, {
    aoEnviar() {
      const email = form.elements.email.value;
      const senha = form.elements.senha.value;
      const confere = normalizar(email) === normalizar(admin.email) && senha === admin.senha;
      if (!confere) {
        // Mensagem genérica: não diz se o erro foi no e-mail ou na senha
        alerta.querySelector('span').textContent = config().validacao.mensagens.login_invalido;
        alerta.hidden = false;
        form.elements.senha.value = '';
        form.elements.senha.focus();
        return;
      }
      iniciarSessao(admin);
      window.location.href = 'painel.html';
    }
  });
}
