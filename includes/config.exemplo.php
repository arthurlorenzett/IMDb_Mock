<?php
/* ==========================================================================
   config.exemplo.php — modelo de credenciais do banco.
   Só é preciso se o seu MySQL não for o padrão do XAMPP (root sem senha):
   copie este arquivo para config.local.php (ignorado pelo Git) e ajuste.
   ========================================================================== */

return [
    'host'    => '127.0.0.1',
    'porta'   => 3306,
    'banco'   => 'imdb_clone',
    'usuario' => 'root',
    'senha'   => ''
];
