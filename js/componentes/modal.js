/* ==========================================================================
   modal.js — abre e fecha os <dialog class="modal"> das páginas.
   O <dialog> nativo já prende o foco e fecha com Esc; aqui ligamos os
   botões [data-abrir-modal="id"] e [data-fechar-modal] e o clique no fundo.
   ========================================================================== */

export function abrirModal(id) {
  const modal = document.getElementById(id);
  if (modal && !modal.open) modal.showModal();
  return modal;
}

export function fecharModal(id) {
  const modal = typeof id === 'string' ? document.getElementById(id) : id;
  if (modal?.open) modal.close();
}

export function iniciarModais() {
  // Delegação: funciona também para botões criados depois pelo JS
  document.addEventListener('click', (evento) => {
    const abrir = evento.target.closest('[data-abrir-modal]');
    if (abrir) {
      abrirModal(abrir.dataset.abrirModal);
      return;
    }
    const fechar = evento.target.closest('[data-fechar-modal]');
    if (fechar) fecharModal(fechar.closest('dialog'));
  });

  // Clique no fundo escuro (fora da caixa) fecha o modal
  document.querySelectorAll('dialog.modal').forEach((modal) => {
    modal.addEventListener('click', (evento) => {
      if (evento.target !== modal) return;
      const caixa = modal.getBoundingClientRect();
      const fora = evento.clientX < caixa.left || evento.clientX > caixa.right ||
        evento.clientY < caixa.top || evento.clientY > caixa.bottom;
      if (fora) modal.close();
    });
  });
}
