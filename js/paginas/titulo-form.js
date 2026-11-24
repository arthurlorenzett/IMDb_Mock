/* ==========================================================================
   titulo-form.js — P8: cadastro (sem ?id) e edição (?id=) de título.
   Gera os gêneros a partir do site.json, mostra a prévia do pôster, valida
   (incluindo "pelo menos um gênero") e salva; depois volta ao painel.
   ========================================================================== */

import { config, caminho } from '../nucleo/config.js';
import { carregarDados } from '../nucleo/dados.js';
import { exigirLogin } from '../nucleo/sessao.js';
import { escaparHtml, parametros } from '../nucleo/util.js';
import { prepararFormulario, mostrarErro } from '../componentes/validacao.js';

const $ = (id) => document.getElementById(id);

function montarGeneros(selecionados = []) {
  document.querySelector('.area-generos .opcoes').innerHTML = config().catalogo.generos.map((g) => `
    <label class="opcao"><input type="checkbox" name="generos[]" value="${escaparHtml(g)}"
      ${selecionados.includes(g) ? 'checked' : ''}> ${escaparHtml(g)}</label>`).join('');
}

// Limite do ano: 1888 (primeiro filme) até o ano atual + 5
function ajustarLimitesDeData() {
  const anoMaximo = new Date().getFullYear() + 5;
  $('titulo-ano').max = anoMaximo;
  $('titulo-lancamento').max = `${anoMaximo}-12-31`;
}

// Prévia: mostra a imagem da URL digitada ou a imagem padrão
function prepararPrevia() {
  const campo = $('titulo-poster');
  const previa = $('previa-poster');
  const padrao = caminho(config().interface.imagem_padrao);

  function atualizar() {
    previa.src = campo.value && campo.checkValidity() ? campo.value : padrao;
  }
  previa.addEventListener('error', () => {
    if (!previa.src.endsWith('sem-poster.svg')) previa.src = padrao;
  });
  campo.addEventListener('change', atualizar);
  campo.addEventListener('input', atualizar);
  atualizar();
}

// Edição: preenche o formulário com o título existente
function preencher(form, t) {
  document.title = `Editar título · IMDb Clone`;
  $('titulo-form-cabecalho').textContent = 'Editar título';
  document.querySelector('.trilha li[aria-current]').textContent = 'Editar título';

  form.elements.id.value = t.id;
  form.elements.titulo.value = t.titulo;
  form.querySelector(`input[name="tipo"][value="${t.tipo}"]`).checked = true;
  form.elements.ano.value = t.ano;
  form.elements.lancamento.value = t.lancamento || '';
  form.elements.duracao.value = t.duracao_min || '';
  form.elements.classificacao.value = t.classificacao;
  form.elements.poster.value = t.poster || '';
  form.elements.trailer.value = t.trailer || '';
  form.elements.direcao.value = t.direcao || '';
  form.elements.elenco.value = t.elenco.join(', ');
  form.elements.sinopse.value = t.sinopse;
}

export async function iniciar() {
  if (!exigirLogin()) return;

  const { titulos } = await carregarDados();
  const form = $('form-titulo');
  const id = Number(parametros().get('id'));
  const existente = id ? titulos.find((t) => t.id === id) : null;

  // ?id= de um título que não existe mais: volta ao painel
  if (id && !existente) {
    window.location.replace('painel.html');
    return;
  }

  montarGeneros(existente?.generos);
  ajustarLimitesDeData();
  if (existente) preencher(form, existente);
  prepararPrevia();

  const erroGeneros = () => form.querySelector('input[name="generos[]"]');

  prepararFormulario(form, {
    // Regra que o HTML não cobre: pelo menos um gênero marcado
    validarExtra() {
      const algum = form.querySelectorAll('input[name="generos[]"]:checked').length > 0;
      mostrarErro(erroGeneros(), algum ? '' : config().validacao.mensagens.generos);
      return algum;
    },

async aoEnviar() {
  const f = form.elements;

  const generos = [
    ...form.querySelectorAll('input[name="generos[]"]:checked')
  ].map(c => c.value);

  const tipo = form.querySelector('input[name="tipo"]:checked').value;

  const titulo = {
    id: existente ? existente.id : null,

    titulo: f.titulo.value.trim(),
    tipo,
    ano: Number(f.ano.value),
    lancamento: f.lancamento.value || null,
    duracao_min: Number(f.duracao.value) || null,
    episodios: existente?.episodios ?? null,
    classificacao: f.classificacao.value,
    generos,

    sinopse: f.sinopse.value.trim(),
    direcao: f.direcao.value.trim(),
    elenco: f.elenco.value
      .split(',')
      .map(nome => nome.trim())
      .filter(Boolean),

    poster: f.poster.value.trim() || null,
    banner: existente?.banner || null,
    trailer: f.trailer.value.trim() || null,

    popularidade: existente?.popularidade ?? 50,
    destaque: existente?.destaque ?? false
  };

  const resposta = await fetch('salvar-titulo.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(titulo)
  });

  const resultado = await resposta.json();

  if (!resposta.ok) {
    throw new Error(resultado.erro || 'Não foi possível salvar o título.');
  }

  window.location.href = 'painel.html?msg=salvo';
}  });

  // Ao marcar um gênero, o erro "escolha pelo menos um" some
  form.querySelector('.area-generos').addEventListener('change', () => {
    if (form.querySelector('input[name="generos[]"]:checked')) mostrarErro(erroGeneros(), '');
  });
}
