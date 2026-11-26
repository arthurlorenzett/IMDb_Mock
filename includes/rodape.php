<?php
/* ==========================================================================
   rodape.php — rodapé global com o aviso de clone educacional (exigido pelo
   enunciado) e o script do site. Fecha o <body> e o <html>.
   ========================================================================== */
?>
<?php if (($area ?? 'publica') === 'admin'): ?>
  </div><!-- fim .painel-layout -->
<?php endif; ?>

  <!-- ===== Rodapé (aviso de clone educacional exigido pelo enunciado) ===== -->
  <footer class="rodape" id="rodape">
    <nav aria-label="Links do rodapé">
      <ul class="rodape-links">
        <li><a href="#">Ajuda</a></li>
        <li><a href="#">Termos de uso</a></li>
        <li><a href="#">Privacidade</a></li>
        <li><a href="<?= e(site()['projeto']['repositorio']) ?>">Repositório no GitHub</a></li>
      </ul>
    </nav>
    <p>IMDb Clone: trabalho acadêmico feito apenas para fins educacionais (Desenvolvimento Web I, IFC Concórdia). Este não é o site oficial do IMDb e não representa o IMDb nem a Amazon. As informações dos filmes e séries foram consultadas no IMDb; os pôsteres e as imagens pertencem aos seus detentores e ficam salvos no próprio site; os trailers são fictícios, feitos pelo grupo; avaliações e usuários são ilustrativos.</p>
    <p>Logotipo: marca registrada da IMDb.com, Inc., arquivo do Wikimedia Commons. Dados dos títulos consultados no IMDb, sem links para o site original. Ícones: Bootstrap Icons.</p>
  </footer>
  <!-- JS externo (módulos ES): app.js carrega o site.json e o script desta página -->
  <script type="module" src="<?= $raiz ?>/js/app.js"></script>
</body>
</html>
