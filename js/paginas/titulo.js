/* ==========================================================================
   titulo.js — P3: preenche a ficha do título (?id=) com os dados do JSON,
   lista as avaliações aprovadas (RN02) e trata o modal "Escrever avaliação"
   com validação e a regra de e-mail único por título (RN01).
   ========================================================================== */

import { config } from '../nucleo/config.js';
import { carregarDados } from '../nucleo/dados.js';
import { avaliacoesVisiveis, estatisticas } from '../nucleo/regras.js';
import {
  escaparHtml, formatarNota, formatarDuracao, formatarClassificacao, formatarTipo,
  formatarData, parametros, plural
} from '../nucleo/util.js';
import { urlPoster, atualizarBotaoWatchlist } from '../componentes/card.js';
import { abrirModal } from '../componentes/modal.js';
import { prepararFormulario, mostrarErro } from '../componentes/validacao.js';

const $ = (id) => document.getElementById(id);

// "Marina Souza" → "Marina S." (não expõe o nome completo de quem avaliou)
function nomeCurto(nome) {
  const partes = nome.trim().split(/\s+/);
  return partes.length > 1 ? `${partes[0]} ${partes[partes.length - 1][0]}.` : partes[0];
}

function mostrarNaoEncontrado() {
  document.title = 'Título não encontrado · IMDb Clone';
  $('conteudo').innerHTML = `
    <section class="estado-vazio">
      <i class="bi bi-film" aria-hidden="true"></i>
      <h1>Título não encontrado</h1>
      <p>O endereço pode estar errado ou o título foi removido do catálogo.</p>
      <a class="botao botao-primario" href="busca.html">Ver todos os títulos</a>
    </section>`;
}

/* ---- Ficha: título, metadados, nota, mídia, gêneros, sinopse e equipe ---- */
function preencherFicha(t, media, votos) {
  const nome = escaparHtml(t.titulo);
  document.title = `${t.titulo} (${t.ano}) · IMDb Clone`;
  document.querySelector('meta[name="description"]')?.setAttribute('content', t.sinopse.slice(0, 150));

  $('ficha-titulo').dataset.id = t.id;
  $('titulo-nome').textContent = t.titulo;
  document.querySelector('.ficha-topo .metadados').innerHTML = [
    `<time datetime="${escaparHtml(t.lancamento || t.ano)}">${t.ano}</time>`,
    formatarClassificacao(t.classificacao),
    formatarDuracao(t),
    formatarTipo(t.tipo)
  ].filter(Boolean).map((item) => `<li>${item}</li>`).join('');

  $('nota-media').textContent = formatarNota(media);
  document.querySelector('.ficha-notas .nota-votos').textContent =
    votos ? `(${plural(votos, 'avaliação', 'avaliações')})` : '(sem avaliações)';

  const poster = document.querySelector('.ficha-poster');
  poster.src = urlPoster(t);
  poster.alt = `Pôster de ${t.titulo}`;

  const capa = document.querySelector('.trailer img');
  capa.src = t.banner || urlPoster(t);
  capa.alt = `Cena ilustrativa do trailer de ${t.titulo}`;
  $('botao-trailer').querySelector('.visualmente-oculto').textContent = `de ${t.titulo}`;

  // Botões de trailer abrem o modal do trailer
  document.querySelectorAll('#botao-trailer, [data-abrir-trailer]').forEach((botao) => {
    botao.dataset.abrirModal = 'modal-trailer';
  });

  // Botão principal da Watchlist (amarelo)
  const botaoWatchlist = document.querySelector('.ficha-acoes .botao-watchlist');
  Object.assign(botaoWatchlist.dataset, { id: t.id, nome: t.titulo, variante: 'ficha' });
  atualizarBotaoWatchlist(botaoWatchlist);

  // "Fotos" abre a imagem grande em outra aba
  const fotos = document.querySelector('.ficha-acoes a.ficha-acao-secundaria');
  fotos.href = t.banner || urlPoster(t);
  fotos.target = '_blank';
  fotos.rel = 'noopener';
  fotos.innerHTML = '<i class="bi bi-images" aria-hidden="true"></i> Ver foto<span class="visualmente-oculto"> (abre em nova aba)</span>';

  // Chips de gênero levam para a busca já filtrada
  document.querySelector('.lista-chips').innerHTML = t.generos.map((g) =>
    `<li><a class="chip" href="busca.html?genero=${encodeURIComponent(g)}">${escaparHtml(g)}</a></li>`).join('');

  $('titulo-sinopse').textContent = t.sinopse;

  const linkBusca = (pessoa) =>
    `<a href="busca.html?q=${encodeURIComponent(pessoa)}">${escaparHtml(pessoa)}</a>`;
  const [direcao, elenco] = document.querySelectorAll('.ficha-equipe dd');
  direcao.innerHTML = t.direcao ? linkBusca(t.direcao) : '—';
  elenco.innerHTML = t.elenco.length
    ? `<ul class="lista-elenco">${t.elenco.map((p) => `<li>${linkBusca(p)}</li>`).join('')}</ul>`
    : '—';

  // Modal do trailer: mostra a cena e, se houver, o link para o vídeo no IMDb
  $('titulo-modal-trailer').textContent = `Trailer: ${t.titulo}`;
  $('trailer-imagem').src = capa.src;
  $('trailer-imagem').alt = capa.alt;
  const link = $('trailer-link');
  link.hidden = !t.trailer;
  if (t.trailer) link.href = t.trailer;
  $('trailer-texto').textContent = t.trailer
    ? 'O vídeo do trailer abre no site do IMDb, em uma nova aba.'
    : 'Este título ainda não tem trailer cadastrado.';

  $('titulo-modal-avaliacao').innerHTML = `Avaliar: ${nome}`;
  document.querySelector('#form-avaliacao input[name="titulo_id"]').value = t.id;
}

/* ---- Avaliações aprovadas (mais recentes primeiro) ---- */
function montarAvaliacoes(avaliacoes, tituloId) {
  const visiveis = avaliacoesVisiveis(avaliacoes, tituloId)
    .sort((a, b) => b.criada_em.localeCompare(a.criada_em));

  document.querySelector('#titulo-avaliacoes .nota-votos').textContent = `(${visiveis.length})`;
  $('lista-avaliacoes').innerHTML = visiveis.length
    ? visiveis.map((a) => `
      <article class="avaliacao">
        <p class="nota"><i class="bi bi-star-fill" aria-hidden="true"></i> <span class="nota-valor">${a.nota}</span><span class="nota-max">/10</span></p>
        ${a.titulo ? `<h3>${escaparHtml(a.titulo)}</h3>` : ''}
        <p>${escaparHtml(a.comentario)}</p>
        <p class="subtitulo">${escaparHtml(nomeCurto(a.nome))} · <time datetime="${escaparHtml(a.criada_em)}">${formatarData(a.criada_em)}</time></p>
      </article>`).join('')
    : '<p class="subtitulo">Ainda não há avaliações aprovadas. Seja o primeiro a avaliar!</p>';
}

/* ---- Modal de avaliação: validação + RN01 + RN02 ---- */
function mostrarAlerta(tipo, texto) {
  const alerta = $('alerta-avaliacao');
  alerta.className = `alerta alerta-${tipo}`;
  alerta.querySelector('.bi').className = `bi ${tipo === 'sucesso' ? 'bi-check-circle' : 'bi-x-circle'}`;
  alerta.querySelector('span').textContent = texto;
  alerta.hidden = false;
}



function prepararModalAvaliacao(avaliacoes) {
  const form = $('form-avaliacao');
  const regras = config().regras;

  prepararFormulario(form, {
    async aoEnviar() {
      const dados = Object.fromEntries(new FormData(form));
      const tituloId = Number(dados.titulo_id);

      try {
        const resposta = await fetch('api/enviar-avaliacao.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            titulo_id: tituloId,
            nome: dados.nome.trim(),
            email: dados.email.trim(),
            nota: Number(dados.nota),
            titulo: dados.titulo.trim(),
            comentario: dados.comentario.trim()
          })
        });

        const resultado = await resposta.json();

        if (!resposta.ok) {
          throw new Error(resultado.erro || 'Não foi possível enviar a avaliação.');
        }

        form.reset();
        form.elements.titulo_id.value = tituloId;

        mostrarAlerta('sucesso', regras.RN02.mensagem);

      } catch (erro) {
        mostrarAlerta('erro', erro.message);
      }
    }
  });

  $('modal-avaliacao').addEventListener('close', () => {
    $('alerta-avaliacao').hidden = true;
  });
}


export async function iniciar() {
  const { titulos, avaliacoes } = await carregarDados();
  const id = Number(parametros().get('id'));
  const titulo = titulos.find((t) => t.id === id);
  if (!titulo) {
    mostrarNaoEncontrado();
    return;
  }

  const { media, votos } = estatisticas(avaliacoes, id);
  preencherFicha(titulo, media, votos);
  montarAvaliacoes(avaliacoes, id);
  prepararModalAvaliacao(avaliacoes);

  // A ficha fica escondida no HTML até os dados chegarem (evita mostrar conteúdo vazio)
  $('ficha-titulo').hidden = false;
  $('avaliacoes').hidden = false;

  // Links "Avaliar" e "Trailer" de outras páginas chegam com #avaliar / #trailer
  if (window.location.hash === '#avaliar') abrirModal('modal-avaliacao');
  if (window.location.hash === '#trailer') abrirModal('modal-trailer');
}
