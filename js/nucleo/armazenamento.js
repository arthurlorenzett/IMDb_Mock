/* ==========================================================================
   armazenamento.js — leitura e escrita no localStorage com o prefixo do projeto
   e as funções da Watchlist. Na Parte 2 não há servidor: tudo que o usuário
   altera fica salvo só neste navegador.
   ========================================================================== */

// Mesmo valor de site.json → armazenamento.prefixo (fixo aqui porque o tema
// é aplicado antes do site.json terminar de carregar)
const PREFIXO = 'imdbclone:';

// Lê um valor salvo em JSON; devolve "padrao" se não existir ou se o navegador bloquear
export function ler(chave, padrao) {
  try {
    const texto = localStorage.getItem(PREFIXO + chave);
    return texto === null ? padrao : JSON.parse(texto);
  } catch {
    return padrao;
  }
}

export function salvar(chave, valor) {
  try {
    localStorage.setItem(PREFIXO + chave, JSON.stringify(valor));
  } catch {
    // Modo privado ou armazenamento cheio: o site continua funcionando sem salvar
  }
}

export function remover(chave) {
  try {
    localStorage.removeItem(PREFIXO + chave);
  } catch {
    // ignora, como em salvar()
  }
}

/* ---- Watchlist: lista de { id, adicionado_em } ---- */

export function lerWatchlist() {
  return ler('watchlist', []);
}

export function estaNaWatchlist(id) {
  return lerWatchlist().some((item) => item.id === Number(id));
}

// Avisa a página inteira (contador do cabeçalho, botões) que a lista mudou
function avisarMudanca(lista) {
  salvar('watchlist', lista);
  document.dispatchEvent(new CustomEvent('watchlist-alterada', { detail: lista }));
}

// Adiciona se não estiver, remove se estiver. Devolve true quando o título ficou na lista
export function alternarWatchlist(id) {
  const numero = Number(id);
  const lista = lerWatchlist();
  const jaEsta = lista.some((item) => item.id === numero);
  const nova = jaEsta
    ? lista.filter((item) => item.id !== numero)
    : [...lista, { id: numero, adicionado_em: new Date().toISOString() }];
  avisarMudanca(nova);
  return !jaEsta;
}

export function removerDaWatchlist(id) {
  avisarMudanca(lerWatchlist().filter((item) => item.id !== Number(id)));
}

export function limparWatchlist() {
  avisarMudanca([]);
}
