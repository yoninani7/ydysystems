<?php 
declare(strict_types=1);
session_start(); 

// This is correct because config.php is one level up (..)
require_once __DIR__ . '/../config.php';

// ... (Keep all your session clearing code here) ...

if (!empty($_COOKIE['remember_token'])) {
    $pdo = get_pdo();
    $pdo->prepare('DELETE FROM remember_tokens WHERE token_hash = :hash')
        ->execute([':hash' => hash('sha256', $_COOKIE['remember_token'])]);

    setcookie('remember_token', '', [
        'expires'  => time() - 3600,
        'path'     => '/',
        'secure'   => true,
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
}

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
    );
}

session_destroy();

// CORRECTED REDIRECT:
// Since you are already inside the 'login' folder, just point to login.php
header("Location: login.php");
exit;