<?php

session_start();

require_once __DIR__ . '/../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método não permitido.');
}

$email = trim($_POST['email'] ?? '');
$senha = $_POST['senha'] ?? '';

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $senha === '') {
    header('Location: login.html?erro=1');
    exit;
}

$sql = "
    SELECT id, nome, email, senha_hash
    FROM administradores
    WHERE email = :email
    LIMIT 1
";

$stmt = $pdo->prepare($sql);
$stmt->execute(['email' => $email]);

$admin = $stmt->fetch();

if (!$admin || !password_verify($senha, $admin['senha_hash'])) {
    header('Location: login.html?erro=1');
    exit;
}

session_regenerate_id(true);

$_SESSION['admin'] = [
    'id' => $admin['id'],
    'nome' => $admin['nome'],
    'email' => $admin['email']
];

header('Location: painel.html');
exit;

