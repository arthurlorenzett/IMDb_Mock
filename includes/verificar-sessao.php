<?php
/* ==========================================================================
   verificar-sessao.php — usado pelos endpoints JSON do painel (chamados pelo
   JS). Sem login, responde 401 em JSON em vez de redirecionar.
   As páginas do painel usam exigir_login(), que redireciona com header().
   Precisa ser incluído depois de config.php.
   ========================================================================== */

if (!admin_logado()) {
    responder_json(['erro' => 'Acesso não autorizado. Entre no painel de novo.'], 401);
}
