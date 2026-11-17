/* ==========================================================================
   regras.js — regras de negócio da Parte 1 (RN01, RN02, RN03).
   Média, votos e Top 250 não ficam gravados: são calculados aqui a partir
   das avaliações. Na Parte 3 as mesmas regras serão repetidas no PHP.
   ========================================================================== */

import { config } from './config.js';
import { normalizar } from './util.js';

// RN02: só avaliações aprovadas aparecem no site e entram na média
export function avaliacoesVisiveis(avaliacoes, tituloId) {
  const status = config().regras.RN02.status_visivel;
  return avaliacoes.filter((a) => a.titulo_id === Number(tituloId) && a.status === status);
}

// Média com 1 casa decimal (RN02) e quantidade de votos aprovados
export function estatisticas(avaliacoes, tituloId) {
  const aprovadas = avaliacoesVisiveis(avaliacoes, tituloId);
  if (aprovadas.length === 0) return { media: null, votos: 0 };
  const soma = aprovadas.reduce((total, a) => total + Number(a.nota), 0);
  const casas = 10 ** config().regras.RN02.casas_decimais;
  return { media: Math.round((soma / aprovadas.length) * casas) / casas, votos: aprovadas.length };
}

// Devolve cópias dos títulos com .media e .votos calculados
export function comEstatisticas(titulos, avaliacoes) {
  return titulos.map((t) => ({ ...t, ...estatisticas(avaliacoes, t.id) }));
}

// RN01: o mesmo e-mail não pode avaliar o mesmo título duas vezes (qualquer status)
export function emailJaAvaliou(avaliacoes, tituloId, email) {
  const alvo = normalizar(email);
  return avaliacoes.some((a) => a.titulo_id === Number(tituloId) && normalizar(a.email) === alvo);
}

// RN03: mínimo de avaliações aprovadas; ordem por média e, no empate, por votos
export function rankingTop250(titulosComEstatisticas) {
  const { minimo_aprovadas: minimo, limite } = config().regras.RN03;
  return titulosComEstatisticas
    .filter((t) => t.votos >= minimo)
    .sort((a, b) => b.media - a.media || b.votos - a.votos)
    .slice(0, limite);
}
