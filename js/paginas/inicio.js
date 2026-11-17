/* ==========================================================================
   inicio.js — P1: carrossel de destaques, "Top 10 da semana" e "Em alta",
   todos gerados a partir de data/titulos.json.
   ========================================================================== */

import { config } from '../nucleo/config.js';
import { carregarDados } from '../nucleo/dados.js';
import { comEstatisticas } from '../nucleo/regras.js';
import { escaparHtml, formatarNota } from '../nucleo/util.js';
import { cartaoTitulo, linkTitulo, urlPoster } from '../componentes/card.js';
import { iniciarCarrossel } from '../componentes/carrossel.js';

function montarCarrossel(destaques) {
  const total = destaques.length;
  document.getElementById('carrossel-trilho').innerHTML = destaques.map((t, i) => `
    <div class="carrossel-slide" role="group" aria-roledescription="slide" aria-label="${i + 1} de ${total}" data-slide="${i}">
      <img src="${escaparHtml(t.banner || urlPoster(t))}" alt="Imagem ilustrativa do destaque ${escaparHtml(t.titulo)} (${t.ano})" width="1280" height="720" ${i > 0 ? 'loading="lazy"' : ''}>
      <div class="carrossel-legenda">
        <a href="${linkTitulo(t.id)}">
          <i class="bi bi-play-circle" aria-hidden="true"></i>
          <div>
            <h3>${escaparHtml(t.titulo)}</h3>
            <p>${escaparHtml(t.generos.join(' · '))}</p>
          </div>
        </a>
      </div>
    </div>`).join('');

  document.getElementById('carrossel-indicadores').innerHTML = destaques.map((t, i) => `
    <button type="button" data-ir-slide="${i}"><span class="visualmente-oculto">Ir para o slide ${i + 1}: ${escaparHtml(t.titulo)}</span></button>`).join('');

  iniciarCarrossel(document.getElementById('carrossel'), config().interface.carrossel_intervalo_ms);
}

function montarTopSemana(titulos) {
  document.getElementById('lista-top10').innerHTML = titulos.map((t, i) => `
    <li class="top10-item">
      <span class="top10-posicao">${i + 1}</span>
      <img class="poster" src="${escaparHtml(urlPoster(t))}" alt="Pôster de ${escaparHtml(t.titulo)}" width="40" height="60" loading="lazy">
      <div>
        <a href="${linkTitulo(t.id)}">${escaparHtml(t.titulo)} (${t.ano})</a>
        <p class="nota"><i class="bi bi-star-fill" aria-hidden="true"></i> <span class="nota-valor">${formatarNota(t.media)}</span> <span class="nota-votos">· ${escaparHtml(t.generos[0])}</span></p>
      </div>
    </li>`).join('');
}

export async function iniciar() {
  const { titulos, avaliacoes } = await carregarDados();
  const lista = comEstatisticas(titulos, avaliacoes);
  const home = config().catalogo.home;

  // Destaques: títulos marcados com "destaque": true
  const destaques = lista.filter((t) => t.destaque).slice(0, home.destaques_carrossel);
  if (destaques.length > 0) montarCarrossel(destaques);

  // Top da semana: os mais populares
  const populares = [...lista].sort((a, b) => b.popularidade - a.popularidade);
  montarTopSemana(populares.slice(0, home.top_semana));

  // Em alta: lançamentos mais recentes (sem data de lançamento, vale o ano)
  const data = (t) => t.lancamento || String(t.ano);
  const recentes = [...lista].sort((a, b) => data(b).localeCompare(data(a)));
  document.getElementById('lista-em-alta').innerHTML = recentes.slice(0, home.em_alta).map(cartaoTitulo).join('');
}
