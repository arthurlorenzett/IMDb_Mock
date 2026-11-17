/* ==========================================================================
   tema-inicial.js — carregado no <head> (sem type="module", então roda antes
   de a página aparecer). Aplica o tema escuro salvo logo de início, para a
   tela não "piscar" em branco ao trocar de página. O botão fica em tema.js.
   ========================================================================== */

try {
  if (JSON.parse(localStorage.getItem('imdbclone:tema')) === 'escuro') {
    document.documentElement.dataset.tema = 'escuro';
  }
} catch (erro) {
  // localStorage bloqueado: fica o tema claro padrão
}
