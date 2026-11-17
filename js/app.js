/* ==========================================================================
   app.js — ponto de entrada de todas as páginas (<script type="module">).
   1. aplica o tema salvo; 2. carrega o site.json; 3. liga cabeçalho, modais
   e botões de Watchlist; 4. chama o script da página, escolhido pelo
   atributo <body data-pagina="...">.
   ========================================================================== */

import { carregarSite } from './nucleo/config.js';
import { iniciarTema } from './componentes/tema.js';
import { iniciarLayout } from './componentes/layout.js';
import { iniciarModais } from './componentes/modal.js';
import { ativarBotoesWatchlist } from './componentes/card.js';

// Nomes aceitos em data-pagina → arquivo em js/paginas/
const PAGINAS = ['inicio', 'busca', 'titulo', 'top250', 'watchlist', 'login', 'painel', 'titulo-form'];

// Aviso no topo do <main> quando algo dá errado (ex.: site aberto via file://)
function mostrarFalha(erro) {
  console.error(erro);
  const aviso = document.createElement('p');
  aviso.className = 'alerta alerta-erro';
  aviso.setAttribute('role', 'alert');
  aviso.innerHTML = '<i class="bi bi-x-circle" aria-hidden="true"></i> ' +
    '<span>Não foi possível carregar os dados do site. Abra o projeto por um servidor local ' +
    '(Live Server ou <code>python -m http.server</code>), como explicado no README.</span>';
  document.getElementById('conteudo')?.prepend(aviso);
}

async function iniciar() {
  iniciarTema();
  try {
    await carregarSite();
    iniciarLayout();
    iniciarModais();
    ativarBotoesWatchlist();

    const pagina = document.body.dataset.pagina;
    if (PAGINAS.includes(pagina)) {
      const modulo = await import(`./paginas/${pagina}.js`);
      await modulo.iniciar();
    }
  } catch (erro) {
    mostrarFalha(erro);
  }
}

iniciar();
