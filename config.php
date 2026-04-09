<?php
/**
 * config.php — Database connection & global security settings
 * Include this file at the top of every PHP page that needs DB access.
 */

declare(strict_types=1);

// 1. SECURITY: Hide errors from public & setup safety net
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(E_ALL);

set_exception_handler(function ($e) {
    error_log(sprintf("Uncaught Exception: %s in %s:%d", $e->getMessage(), $e->getFile(), $e->getLine()));
    if (!headers_sent()) { http_response_code(500); }
    echo "A system error occurred. Please try again later.";
    exit;
});

// ── Environment ──────────────────────────────────────────────────────────────
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'ydy_hrm');   // ← change
define('DB_USER', 'root');         // ← change
define('DB_PASS', '');     // ← change
define('DB_CHARSET', 'utf8mb4');

// Redirect users here after a successful login
define('LOGIN_REDIRECT', '../dashboard.php');

// How long (seconds) a "remember me" session cookie lasts (30 days)
define('REMEMBER_ME_TTL', 60 * 60 * 24 * 30);

// Max failed login attempts before locking the account
define('MAX_LOGIN_ATTEMPTS', 5);

// Lockout duration in seconds (15 minutes)
define('LOCKOUT_SECONDS', 900);

// ── Session hardening ─────────────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        // 'secure'   => true,          // HTTPS only — set to false on local dev
        'httponly' => true,          // JS cannot read the cookie
        'samesite' => 'Strict',
    ]);
    session_start();
}

function get_pdo(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', DB_HOST, DB_PORT, DB_NAME, DB_CHARSET);
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log('DB connection failed: ' . $e->getMessage());
            // CHANGED: Throw an exception instead of exit()
            throw new Exception("Database connection failed. Contact your admin.");
        }
    }
    return $pdo;
}

/**
 * Generate (or retrieve existing) CSRF token for the current session.
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate the CSRF token submitted with a form.
 * Returns TRUE if valid, FALSE if invalid.
 */
function csrf_verify(): bool
{
    // 1. Check if token exists in both POST and SESSION
    $submitted = $_POST['csrf_token'] ?? '';
    $stored    = $_SESSION['csrf_token'] ?? '';

    // 2. If either is empty, it's an invalid request
    if ($submitted === '' || $stored === '') {
        return false;
    }

    // 3. Use hash_equals to prevent timing attacks
    // Returns true if they match, false otherwise
    return hash_equals($stored, $submitted);
}
// ── Utility ───────────────────────────────────────────────────────────────────

/**
 * Safely redirect and terminate execution.
 */
function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

/**
 * Return a sanitised string (removes leading/trailing whitespace + HTML entities).
 */
function clean(string $value): string
{
    return htmlspecialchars(trim($value), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
