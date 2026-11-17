/* ==========================================================================
   dados.js — única porta de entrada dos dados de títulos e avaliações.
   Parte 2: lê data/*.json e aplica por cima o que foi alterado neste
   navegador (localStorage). Parte 3: basta trocar site.json → dados.fonte
   para "api" e os mesmos formatos virão dos arquivos PHP.
   ========================================================================== */

import { config, caminho } from './config.js';
import { ler, salvar } from './armazenamento.js';

// Dados já carregados nesta página (evita buscar o JSON duas vezes)
let cache = null;

async function buscarJson(relativo) {
  const resposta = await fetch(caminho(relativo));
  if (!resposta.ok) throw new Error(`Falha ao carregar ${relativo} (${resposta.status})`);
  return resposta.json();
}

// Junta o JSON original com as alterações locais (cadastros, exclusões, moderação)
function aplicarAlteracoesLocais(titulosBase, avaliacoesBase) {
  const salvos = ler('titulos-salvos', {});      // { id: título completo } (novos ou editados)
  const excluidos = ler('titulos-excluidos', []); // [ids]
  const enviadas = ler('avaliacoes-enviadas', []); // avaliações feitas neste navegador
  const moderacao = ler('moderacao', {});        // { idAvaliacao: "aprovada" | "rejeitada" }

  // Títulos: substitui os editados, acrescenta os novos e tira os excluídos
  const porId = new Map(titulosBase.map((t) => [t.id, t]));
  Object.values(salvos).forEach((t) => porId.set(t.id, t));
  const titulos = [...porId.values()].filter((t) => !excluidos.includes(t.id));

  // Avaliações: originais + enviadas, com o status decidido no painel
  const idsValidos = new Set(titulos.map((t) => t.id));
  const avaliacoes = [...avaliacoesBase, ...enviadas]
    .map((a) => (moderacao[a.id] ? { ...a, status: moderacao[a.id] } : a))
    .filter((a) => idsValidos.has(a.titulo_id));

  return { titulos, avaliacoes };
}

// Carrega títulos e avaliações da fonte configurada no site.json
export async function carregarDados() {
  if (cache) return cache;
  const fonte = config().dados.fonte;          // "json" agora, "api" na Parte 3
  const enderecos = config().dados[fonte];
  const [titulos, avaliacoes] = await Promise.all([
    buscarJson(enderecos.titulos),
    buscarJson(enderecos.avaliacoes)
  ]);
  // Alterações do localStorage só valem na Parte 2 (sem servidor). Com a API,
  // o MySQL é a única fonte e restos de testes no navegador são ignorados.
  cache = fonte === 'json' ? aplicarAlteracoesLocais(titulos, avaliacoes) : { titulos, avaliacoes };
  return cache;
}

// Próximo id livre (simula o AUTO_INCREMENT do MySQL: nunca reaproveita um id)
function proximoId(...listasDeIds) {
  return Math.max(0, ...listasDeIds.flat().map(Number)) + 1;
}

/* ---- Escritas (Parte 2: localStorage; Parte 3: POST para o PHP) ---- */

// Cria (sem id) ou atualiza (com id) um título
export function salvarTitulo(titulo) {
  const salvos = ler('titulos-salvos', {});
  const excluidos = ler('titulos-excluidos', []);
  const id = Number(titulo.id) || proximoId(cache.titulos.map((t) => t.id), Object.keys(salvos), excluidos);
  const completo = { ...titulo, id };
  salvos[id] = completo;
  salvar('titulos-salvos', salvos);

  const posicao = cache.titulos.findIndex((t) => t.id === id);
  if (posicao >= 0) cache.titulos[posicao] = completo;
  else cache.titulos.push(completo);
  return completo;
}

// Exclui o título e, junto, as avaliações dele (como um ON DELETE CASCADE)
export function excluirTitulo(id) {
  const excluidos = ler('titulos-excluidos', []);
  salvar('titulos-excluidos', [...excluidos, Number(id)]);
  cache.titulos = cache.titulos.filter((t) => t.id !== Number(id));
  cache.avaliacoes = cache.avaliacoes.filter((a) => a.titulo_id !== Number(id));
}

// RN02: toda avaliação nova entra como pendente
export function enviarAvaliacao(dados) {
  const enviadas = ler('avaliacoes-enviadas', []);
  const nova = {
    id: proximoId(cache.avaliacoes.map((a) => a.id), enviadas.map((a) => a.id), Object.keys(ler('moderacao', {}))),
    ...dados,
    status: config().regras.RN02.status_inicial,
    criada_em: new Date().toISOString().slice(0, 19)
  };
  salvar('avaliacoes-enviadas', [...enviadas, nova]);
  cache.avaliacoes.push(nova);
  return nova;
}

// Aprovar ou rejeitar uma avaliação pendente (painel)
export function moderarAvaliacao(id, status) {
  const moderacao = ler('moderacao', {});
  moderacao[id] = status;
  salvar('moderacao', moderacao);
  const avaliacao = cache.avaliacoes.find((a) => a.id === Number(id));
  if (avaliacao) avaliacao.status = status;
}
