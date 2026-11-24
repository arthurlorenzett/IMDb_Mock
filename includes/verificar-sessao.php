<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION['admin'])) {
    http_response_code(401);

    header('Content-Type: application/json; charset=utf-8');

    echo json_encode([
        'erro' => 'Acesso não autorizado.'
    ]);

    exit;
}
