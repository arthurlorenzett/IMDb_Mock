/* ==========================================================================
   validacao.js — validação no cliente com mensagens na própria página (sem alert).
   Os limites vêm dos atributos do HTML (required, minlength, min, max, pattern…)
   e os textos das mensagens vêm do site.json → validacao.mensagens.
   ========================================================================== */

import { config } from '../nucleo/config.js';
import { preencher } from '../nucleo/util.js';

// Campos que participam da validação (botões e hidden ficam de fora)
function camposDo(formulario) {
  return [...formulario.elements].filter((campo) =>
    campo.willValidate && !['submit', 'button', 'hidden'].includes(campo.type)
  );
}

// Acha o <p class="mensagem-erro"> do campo: pelo aria-describedby ou subindo no HTML
function elementoErro(campo) {
  const ids = (campo.getAttribute('aria-describedby') || '').split(' ');
  for (const id of ids) {
    const elemento = id ? document.getElementById(id) : null;
    if (elemento?.classList.contains('mensagem-erro')) return elemento;
  }
  let pai = campo.parentElement;
  while (pai && pai !== campo.form) {
    const erro = pai.querySelector(':scope > .mensagem-erro');
    if (erro) return erro;
    pai = pai.parentElement;
  }
  return null;
}

// Traduz o estado de validade do navegador para uma mensagem do site.json
function mensagemPara(campo) {
  const m = config().validacao.mensagens;
  const v = campo.validity;
  const valor = campo.value.trim();

  if (v.valueMissing || (campo.required && valor === '' && campo.type !== 'radio' && campo.type !== 'checkbox')) {
    return ['radio', 'checkbox', 'select-one'].includes(campo.type) ? m.obrigatorio_opcao : m.obrigatorio;
  }
  if (v.badInput) return m.inteiro;   // texto digitado num campo number
  if (valor === '') return '';        // campo opcional vazio
  if (v.typeMismatch) return campo.type === 'email' ? m.email : m.url;
  // minlength/maxlength conferidos aqui porque o navegador só checa depois de digitar
  if (campo.minLength > 0 && valor.length < campo.minLength) return preencher(m.curto, { min: campo.minLength });
  if (campo.maxLength > 0 && valor.length > campo.maxLength) return preencher(m.longo, { max: campo.maxLength });
  if (v.stepMismatch) return m.inteiro;
  if (v.rangeUnderflow) return preencher(m.menor, { min: campo.min });
  if (v.rangeOverflow) return preencher(m.maior, { max: campo.max });
  if (v.patternMismatch) return campo.type === 'url' ? m.url : m.formato;
  if (v.customError) return campo.validationMessage;
  return '';
}

// Mostra (ou limpa) o erro de um campo e marca aria-invalid para o CSS (borda vermelha)
export function mostrarErro(campo, mensagem) {
  const erro = elementoErro(campo);
  if (erro) erro.textContent = mensagem;
  // Num grupo de radios/checkbox, marca todos do mesmo nome
  const grupo = campo.name && campo.form && ['radio', 'checkbox'].includes(campo.type)
    ? campo.form.querySelectorAll(`[name="${campo.name}"]`)
    : [campo];
  grupo.forEach((item) => {
    if (mensagem) item.setAttribute('aria-invalid', 'true');
    else item.removeAttribute('aria-invalid');
  });
}

export function validarCampo(campo) {
  const mensagem = mensagemPara(campo);
  mostrarErro(campo, mensagem);
  return mensagem === '';
}

/* Liga a validação a um formulário.
   - validarExtra(form): regras que o HTML não cobre; devolve false se houver erro
   - aoEnviar(form): chamado só quando tudo está válido */
export function prepararFormulario(formulario, { validarExtra, aoEnviar } = {}) {
  formulario.noValidate = true; // desliga os balões do navegador; usamos as nossas mensagens

  // Valida ao sair do campo e, depois do primeiro erro, a cada tecla
  formulario.addEventListener('focusout', (evento) => {
    if (evento.target.willValidate && evento.target.type !== 'radio') validarCampo(evento.target);
  });
  formulario.addEventListener('input', (evento) => {
    if (evento.target.getAttribute('aria-invalid') === 'true') validarCampo(evento.target);
  });
  formulario.addEventListener('change', (evento) => {
    if (['radio', 'checkbox'].includes(evento.target.type)) validarCampo(evento.target);
  });

  formulario.addEventListener('submit', (evento) => {
    const campos = camposDo(formulario);
    let valido = campos.map(validarCampo).every(Boolean);
    if (validarExtra && validarExtra(formulario) === false) valido = false;

    if (!valido) {
      evento.preventDefault();
      // Leva o foco ao primeiro campo com erro
      formulario.querySelector('[aria-invalid="true"]')?.focus();
      return;
    }
    if (aoEnviar) {
      evento.preventDefault(); // na Parte 2 o JS trata o envio; na Parte 3 o PHP recebe o POST
      aoEnviar(formulario);
    }
  });
}
