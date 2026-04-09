<?php 
declare(strict_types=1);

// 1. Load config (which likely starts the session and provides get_pdo)
require_once __DIR__ . '/../config.php';

// Ensure session is active so we can destroy it
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Clear "Remember Me" from DB and Browser
if (!empty($_COOKIE['remember_token'])) {
    try {
        $pdo = get_pdo();
        $pdo->prepare('DELETE FROM remember_tokens WHERE token_hash = :hash')
            ->execute([':hash' => hash('sha256', $_COOKIE['remember_token'])]);
    } catch (Exception $e) {
        // Silently fail DB error so logout still happens
        error_log("Logout DB Error: " . $e->getMessage());
    }

    // Delete the cookie from the browser
    setcookie('remember_token', '', [
        'expires'  => time() - 3600,
        'path'     => '/',
        'secure'   => true, // Matches your login settings
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
}

// 3. Completely wipe the session
$_SESSION = [];

// Destroy the session cookie in the browser
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
    );
}

// Destroy the session on the server
session_destroy();

// 4. Redirect
header("Location: login.php");
exit;