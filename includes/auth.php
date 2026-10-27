<?php

$tempoExpiracao = 15 * 60; // 15 minutos

ini_set('session.use_strict_mode', '1');

session_set_cookie_params([
    'httponly' => true,
    'secure' => !empty($_SERVER['HTTPS'])
        && $_SERVER['HTTPS'] !== 'off',
    'samesite' => 'Lax'
]);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (isset($_SESSION['id_usuario'])) {
    if (
        !isset($_SESSION['ultima_atividade']) ||
        time() - $_SESSION['ultima_atividade'] > $tempoExpiracao
    ) {
        $_SESSION = [];
        session_destroy();

        header('Location: ../login.php?expirada=1');
        exit;
    }

    $_SESSION['ultima_atividade'] = time();
}

function exigirLogin(): void
{
    if (!isset($_SESSION['id_usuario'])) {
        header('Location: ../login.php');
        exit;
    }
}