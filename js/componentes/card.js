/* ==========================================================================
   card.js — modelos de HTML reaproveitados entre as páginas (card do "Em alta",
   item de lista da busca/Watchlist) e o botão "+ Watchlist" com seu estado.
   Todo texto vindo dos dados passa por escaparHtml().
   ========================================================================== */

import { config, caminho } from '../nucleo/config.js';
import { estaNaWatchlist, alternarWatchlist } from '../nucleo/armazenamento.js';
import {
  escaparHtml, formatarNota, formatarDuracao, formatarClassificacao, plural
} from '../nucleo/util.js';

// Pôster do título ou a imagem padrão (img/sem-poster.svg).
// Caminhos locais (img/...) são resolvidos pela raiz para funcionar também dentro de admin/.
export function urlPoster(titulo) {
  const poster = titulo.poster || config().interface.imagem_padrao;
  return poster.startsWith('https://') ? poster : caminho(poster);
}

// Caminho para a ficha do título (as páginas do admin estão uma pasta abaixo)
export function linkTitulo(id, ancora = '') {
  return caminho(`titulo.php?id=${id}${ancora}`);
}

/* ---- Botão Watchlist: o conteúdo muda conforme a variante e o estado ---- */

// Variantes: "cartao" e "lista" (texto curto), "ficha" (texto longo), "icone" (só ícone)
function conteudoBotao(variante, nome, naLista) {
  const icone = `<i class="bi ${naLista ? 'bi-check-lg' : 'bi-plus-lg'}" aria-hidden="true"></i>`;
  const nomeOculto = `<span class="visualmente-oculto">: ${nome}</span>`;
  if (variante === 'icone') {
    const texto = naLista ? `Remover ${nome} da Watchlist` : `Adicionar ${nome} à Watchlist`;
    return `${icone}<span class="visualmente-oculto">${texto}</span>`;
  }
  if (variante === 'ficha') {
    return `${icone} ${naLista ? 'Na Watchlist' : 'Adicionar à Watchlist'}`;
  }
  return `${icone} ${naLista ? 'Na Watchlist' : 'Watchlist'}${nomeOculto}`;
}

// Deixa o botão de acordo com a Watchlist salva (aria-pressed + texto)
export function atualizarBotaoWatchlist(botao) {
  const naLista = estaNaWatchlist(botao.dataset.id);
  botao.setAttribute('aria-pressed', String(naLista));
  botao.innerHTML = conteudoBotao(botao.dataset.variante, escaparHtml(botao.dataset.nome), naLista);
}

export function botaoWatchlist(titulo, variante, classesExtras) {
  return `
    <button class="botao botao-watchlist ${classesExtras}" type="button"
      data-id="${titulo.id}" data-nome="${escaparHtml(titulo.titulo)}" data-variante="${variante}"
      aria-pressed="${estaNaWatchlist(titulo.id)}">
      ${conteudoBotao(variante, escaparHtml(titulo.titulo), estaNaWatchlist(titulo.id))}
    </button>`;
}

// Um único ouvinte no documento cuida de todos os botões, inclusive os criados depois
export function ativarBotoesWatchlist() {
  document.addEventListener('click', (evento) => {
    const botao = evento.target.closest('.botao-watchlist');
    if (botao) alternarWatchlist(botao.dataset.id);
  });
  // Quando a lista muda, todos os botões da página se atualizam
  document.addEventListener('watchlist-alterada', () => {
    document.querySelectorAll('.botao-watchlist[data-id]').forEach(atualizarBotaoWatchlist);
  });
  // Pôster que não carregar vira a imagem padrão
  document.addEventListener('error', (evento) => {
    const imagem = evento.target;
    // data-padrao evita repetir a troca se até a imagem padrão falhar
    if (imagem.tagName === 'IMG' && imagem.classList.contains('poster') && !imagem.dataset.padrao) {
      imagem.dataset.padrao = 'sim';
      imagem.src = caminho(config().interface.imagem_padrao);
    }
  }, true);
}

/* ---- Card de título (grade "Em alta" da página inicial) ---- */
export function cartaoTitulo(titulo) {
  const nome = escaparHtml(titulo.titulo);
  return `
    <li>
      <article class="cartao-titulo" data-id="${titulo.id}">
        <a href="${linkTitulo(titulo.id)}" tabindex="-1" aria-hidden="true">
          <img class="poster" src="${escaparHtml(urlPoster(titulo))}" alt="Pôster de ${nome}" width="300" height="450" loading="lazy">
        </a>
        <div class="cartao-corpo">
          <div class="cartao-linha-nota">
            <p class="nota"><i class="bi bi-star-fill" aria-hidden="true"></i> <span class="nota-valor">${formatarNota(titulo.media)}</span><span class="visualmente-oculto"> de 10</span></p>
            <a class="botao botao-texto" href="${linkTitulo(titulo.id, '#avaliar')}" aria-label="Avaliar ${nome}"><i class="bi bi-star" aria-hidden="true"></i></a>
          </div>
          <h3><a href="${linkTitulo(titulo.id)}">${nome}</a></h3>
          ${botaoWatchlist(titulo, 'cartao', 'botao-secundario botao-bloco')}
        </div>
      </article>
    </li>`;
}

/* ---- Item de lista (resultados da busca e Watchlist) ----
   acoes: "watchlist" mostra o botão + Watchlist; "remover" mostra Remover */
export function itemLista(titulo, numero, acoes = 'watchlist') {
  const nome = escaparHtml(titulo.titulo);
  const metadados = [titulo.ano, formatarClassificacao(titulo.classificacao), formatarDuracao(titulo), titulo.generos.join(', ')]
    .filter(Boolean)
    .map((item) => `<li>${escaparHtml(item)}</li>`)
    .join('');
  const votos = titulo.votos ? ` <span class="nota-votos">(${plural(titulo.votos, 'voto', 'votos')})</span>` : '';

  const botoes = acoes === 'remover'
    ? `<button class="botao botao-contorno botao-pequeno botao-remover" type="button" data-id="${titulo.id}">
         <i class="bi bi-x-lg" aria-hidden="true"></i> Remover<span class="visualmente-oculto"> ${nome}</span>
       </button>`
    : botaoWatchlist(titulo, 'lista', 'botao-secundario');

  return `
    <li>
      <article class="item-lista" data-id="${titulo.id}">
        <img class="poster" src="${escaparHtml(urlPoster(titulo))}" alt="Pôster de ${nome}" width="72" height="108" loading="lazy">
        <div class="item-info">
          <h2><a href="${linkTitulo(titulo.id)}">${numero}. ${nome} (${titulo.ano})</a></h2>
          <ul class="metadados">${metadados}</ul>
          <div class="item-nota">
            <p class="nota"><i class="bi bi-star-fill" aria-hidden="true"></i> <span class="nota-valor">${formatarNota(titulo.media)}</span>${votos}</p>
            <a class="botao botao-texto" href="${linkTitulo(titulo.id, '#avaliar')}"><i class="bi bi-star" aria-hidden="true"></i> Avaliar<span class="visualmente-oculto"> ${nome}</span></a>
          </div>
        </div>
        <div class="item-acoes">${botoes}</div>
      </article>
    </li>`;
}
