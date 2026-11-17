/* ==========================================================================
   config.js — carrega o data/site.json (arquivo mestre) uma única vez
   e resolve caminhos relativos à raiz do site (páginas do admin/ usam "..").
   ========================================================================== */

let site = null;

// Raiz do site vinda de <body data-raiz="."> (ou ".." dentro de admin/)
export function raiz() {
  return document.body.dataset.raiz || '.';
}

// Monta um caminho a partir da raiz: caminho('data/site.json') → '../data/site.json' no admin
export function caminho(relativo) {
  return `${raiz()}/${relativo}`;
}

// Busca o site.json; as chamadas seguintes reaproveitam o mesmo objeto
export async function carregarSite() {
  if (site) return site;
  const resposta = await fetch(caminho('data/site.json'));
  if (!resposta.ok) {
    throw new Error(`Falha ao carregar site.json (${resposta.status})`);
  }
  site = await resposta.json();
  return site;
}

// Acesso síncrono depois que carregarSite() terminou (usado pelos componentes)
export function config() {
  return site;
}
