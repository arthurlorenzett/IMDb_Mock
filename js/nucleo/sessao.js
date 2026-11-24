/* ==========================================================================
   sessao.js — autenticação administrada pelo PHP.
   A sessão real fica no servidor; o JavaScript não simula autenticação.
   ========================================================================== */

export function exigirLogin() {
  const saudacao = document.querySelector('.saudacao');

  if (saudacao) {
    saudacao.textContent = 'Olá, Administrador';
  }

  document.querySelectorAll('#botao-sair, #menu-admin a[href$="login.html"]')
    .forEach((link) => {
      link.href = 'sair.php';
      link.addEventListener('click', (evento) => {
        evento.preventDefault();
        window.location.href = 'sair.php';
      });
    });

  return true;
}

