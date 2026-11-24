<?php

$host = 'database';
$banco = 'imdb_clone';
$usuario = 'imdb_user';
$senha = 'imdb_password';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$banco;charset=utf8mb4",
        $usuario,
        $senha,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );

} catch (PDOException $erro) {
    http_response_code(500);
    exit('Erro ao conectar ao banco de dados.');
}

