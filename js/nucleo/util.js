/* ==========================================================================
   util.js — funções pequenas de formatação e segurança usadas em várias páginas
   ========================================================================== */

// Escapa texto antes de colocar dentro de innerHTML (evita injeção de HTML)
export function escaparHtml(texto) {
  return String(texto ?? '')
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#39;');
}

// Minúsculas e sem acento: "Ficção" → "ficcao" (para comparar na busca)
export function normalizar(texto) {
  return String(texto ?? '')
    .normalize('NFD')
    .replace(/[̀-ͯ]/g, '')
    .toLowerCase()
    .trim();
}

// 8.4 → "8,4"; título sem avaliações aprovadas → "—"
export function formatarNota(media) {
  if (media === null || media === undefined) return '—';
  return media.toFixed(1).replace('.', ',');
}

// Filme: 125 → "2h 05min". Série: "8 episódios"
export function formatarDuracao(titulo) {
  if (titulo.tipo === 'serie') {
    return titulo.episodios ? `${titulo.episodios} episódios` : 'Série';
  }
  const minutos = Number(titulo.duracao_min);
  if (!minutos) return '';
  const horas = Math.floor(minutos / 60);
  const resto = String(minutos % 60).padStart(2, '0');
  return horas > 0 ? `${horas}h ${resto}min` : `${minutos}min`;
}

// "14" → "14 anos"; "L" → "Livre"
export function formatarClassificacao(classificacao) {
  return classificacao === 'L' ? 'Livre' : `${classificacao} anos`;
}

// "2026-10-12T19:20:00" → "12/10/2026"
export function formatarData(iso) {
  const [ano, mes, dia] = String(iso).slice(0, 10).split('-');
  return `${dia}/${mes}/${ano}`;
}

export function formatarTipo(tipo) {
  return tipo === 'serie' ? 'Série' : 'Filme';
}

// Plural simples: plural(1, 'título', 'títulos') → "1 título"
export function plural(quantidade, singular, pluralTexto) {
  return `${quantidade} ${quantidade === 1 ? singular : pluralTexto}`;
}

// Parâmetros da URL atual (?id=3&q=farol)
export function parametros() {
  return new URLSearchParams(window.location.search);
}

// Troca {min}, {max}... de uma mensagem do site.json pelos valores
export function preencher(mensagem, valores = {}) {
  return mensagem.replace(/\{(\w+)\}/g, (trecho, nome) => valores[nome] ?? trecho);
}
