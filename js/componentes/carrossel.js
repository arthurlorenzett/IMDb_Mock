/* ==========================================================================
   carrossel.js — carrossel de destaques da página inicial.
   Move .carrossel-trilho com translateX, marca o indicador atual e troca
   sozinho a cada N ms (pausa com o mouse em cima ou com o foco dentro).
   ========================================================================== */

export function iniciarCarrossel(carrossel, intervaloMs) {
  const trilho = carrossel.querySelector('.carrossel-trilho');
  const slides = [...trilho.children];
  const indicadores = [...carrossel.querySelectorAll('[data-ir-slide]')];
  const anterior = carrossel.querySelector('.carrossel-anterior');
  const proximo = carrossel.querySelector('.carrossel-proximo');
  let atual = 0;
  let temporizador = null;

  function irPara(indice) {
    atual = (indice + slides.length) % slides.length; // volta ao início depois do último
    trilho.style.transform = `translateX(-${atual * 100}%)`;
    slides.forEach((slide, i) => {
      const visivel = i === atual;
      slide.setAttribute('aria-hidden', String(!visivel));
      // Links dos slides escondidos saem da ordem do Tab
      slide.querySelectorAll('a').forEach((link) => (link.tabIndex = visivel ? 0 : -1));
    });
    indicadores.forEach((botao, i) => {
      if (i === atual) botao.setAttribute('aria-current', 'true');
      else botao.removeAttribute('aria-current');
    });
  }

  // Quem prefere menos movimento (configuração do sistema) não tem troca automática
  const reduzirMovimento = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function iniciarAutomatico() {
    if (reduzirMovimento || slides.length < 2) return;
    pararAutomatico();
    temporizador = setInterval(() => irPara(atual + 1), intervaloMs);
  }

  function pararAutomatico() {
    clearInterval(temporizador);
  }

  anterior.addEventListener('click', () => irPara(atual - 1));
  proximo.addEventListener('click', () => irPara(atual + 1));
  indicadores.forEach((botao) => {
    botao.addEventListener('click', () => irPara(Number(botao.dataset.irSlide)));
  });

  // Setas do teclado quando o foco está no carrossel
  carrossel.addEventListener('keydown', (evento) => {
    if (evento.key === 'ArrowLeft') irPara(atual - 1);
    if (evento.key === 'ArrowRight') irPara(atual + 1);
  });

  carrossel.addEventListener('mouseenter', pararAutomatico);
  carrossel.addEventListener('mouseleave', iniciarAutomatico);
  carrossel.addEventListener('focusin', pararAutomatico);
  carrossel.addEventListener('focusout', iniciarAutomatico);

  irPara(0);
  iniciarAutomatico();
}
