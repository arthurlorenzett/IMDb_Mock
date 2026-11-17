/* ==========================================================================
   watchlist.js — P5: lista os títulos salvos no localStorage, com ordenação,
   filtro por tipo, remoção de um item e "Limpar lista" com confirmação.
   ========================================================================== */

import { carregarDados } from '../nucleo/dados.js';
import { comEstatisticas } from '../nucleo/regras.js';
import { lerWatchlist, removerDaWatchlist, limparWatchlist } from '../nucleo/armazenamento.js';
import { plural } from '../nucleo/util.js';
import { itemLista } from '../componentes/card.js';
import { fecharModal } from '../componentes/modal.js';

const ORDENACOES = {
  inclusao: (a, b) => b.adicionado_em.localeCompare(a.adicionado_em), // mais recentes primeiro
  nota: (a, b) => (b.media ?? -1) - (a.media ?? -1),
  ano: (a, b) => b.ano - a.ano,
  titulo: (a, b) => a.titulo.localeCompare(b.titulo, 'pt-BR')
};

export async function iniciar() {
  const { titulos, avaliacoes } = await carregarDados();
  const catalogo = new Map(comEstatisticas(titulos, avaliacoes).map((t) => [t.id, t]));

  const lista = document.getElementById('lista-watchlist');
  const vazia = document.getElementById('watchlist-vazia');
  const controles = document.querySelector('.barra-controles');
  const ordenar = document.getElementById('ordenar-watchlist');
  const filtroTipo = document.getElementById('filtro-tipo-watchlist');

  function desenhar() {
    // Junta cada item salvo com os dados do título (ignora títulos excluídos)
    const salvos = lerWatchlist()
      .filter((item) => catalogo.has(item.id))
      .map((item) => ({ ...catalogo.get(item.id), adicionado_em: item.adicionado_em }));

    const total = salvos.length;
    document.getElementById('contador-watchlist-pagina').textContent = plural(total, 'título', 'títulos');
    document.querySelector('#modal-limpar .modal-texto').textContent =
      `${total === 1 ? 'O título será removido' : `Os ${total} títulos serão removidos`} deste navegador.`;

    // Estado vazio: some a lista e os controles, aparece o convite para o Top 250
    vazia.hidden = total > 0;
    lista.hidden = total === 0;
    controles.hidden = total === 0;
    if (total === 0) return;

    const visiveis = salvos
      .filter((t) => !filtroTipo.value || t.tipo === filtroTipo.value)
      .sort(ORDENACOES[ordenar.value]);

    lista.innerHTML = visiveis.length
      ? visiveis.map((t, i) => itemLista(t, i + 1, 'remover')).join('')
      : '<li><p class="subtitulo">Nenhum título deste tipo na sua Watchlist.</p></li>';
  }

  ordenar.addEventListener('change', desenhar);
  filtroTipo.addEventListener('change', desenhar);

  // Remover um item (delegação: os botões são recriados a cada desenho)
  lista.addEventListener('click', (evento) => {
    const botao = evento.target.closest('.botao-remover');
    if (botao) removerDaWatchlist(botao.dataset.id);
  });

  document.getElementById('confirmar-limpar').addEventListener('click', () => {
    limparWatchlist();
    fecharModal('modal-limpar');
  });

  // Qualquer mudança na lista (inclusive em outra aba) redesenha a página
  document.addEventListener('watchlist-alterada', desenhar);
  window.addEventListener('storage', desenhar);

  desenhar();
}
