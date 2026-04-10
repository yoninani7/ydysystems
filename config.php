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
define('DB_NAME', 'ydy_hrm');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

define('LOGIN_REDIRECT', '../dashboard.php');
define('REMEMBER_ME_TTL', 60 * 60 * 24 * 30);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_SECONDS', 900);

// ── Inactivity timeout (seconds) ─────────────────────────────────────────────
define('INACTIVITY_TIMEOUT', 5); // 5 seconds for testing

define('BASE_URL', '/'); // or '/your-subfolder/' if the app is not at root
define('LOGOUT_URL', BASE_URL . 'login/logout.php');
// ── Session hardening ─────────────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        // 'secure'   => true,
        'httponly' => true,
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
            throw new Exception("Database connection failed. Contact your admin.");
        }
    }
    return $pdo;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_verify(): bool
{
    $submitted = $_POST['csrf_token'] ?? '';
    $stored    = $_SESSION['csrf_token'] ?? '';
    if ($submitted === '' || $stored === '') {
        return false;
    }
    return hash_equals($stored, $submitted);
}

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

function clean(string $value): string
{
    return htmlspecialchars(trim($value), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// ── Inactivity Modal ──────────────────────────────────────────────────────────
/**
 * Outputs the inactivity modal HTML + JS.
 * Called via register_shutdown_function so it appends after every page's output.
 */
function render_inactivity_modal(): void
{
    // Do not inject on AJAX requests
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        return;
    }

    $timeout = INACTIVITY_TIMEOUT;
    ?>
    <!-- ── THEME-ALIGNED INACTIVITY MODAL ── -->
    <style>
      #inactivity-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.45); /* Matching your --text color with alpha */
        backdrop-filter: blur(8px);
        z-index: 10000;
        align-items: center;
        justify-content: center;
        padding: 20px;
        font-family: 'Plus Jakarta Sans', sans-serif;
      }
      #inactivity-overlay.show {
        display: flex;
        animation: modalIn .3s cubic-bezier(.16, 1, .3, 1) forwards;
      }
      #inactivity-box {
        background: var(--surface, #ffffff);
        border: 1px solid var(--border, #e2e8f0);
        border-top: 4px solid #15b201; /* Your --primary color */
        border-radius: 14px;
        padding: 40px;
        max-width: 400px;
        width: 100%;
        text-align: center;
        box-shadow: 0 20px 50px rgba(0,0,0,0.15);
      }
      #inactivity-icon-container {
        width: 64px;
        height: 64px;
        background: #f1fcf0; /* Your --primary-light */
        color: #15b201; /* Your --primary */
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 24px;
        box-shadow: 0 10px 15px -3px rgba(21, 178, 1, 0.15);
      }
      #inactivity-title {
        font-size: 1.25rem;
        font-weight: 800;
        color: #0f172a; /* Your --text */
        margin-bottom: 8px;
        letter-spacing: -0.02em;
      }
      #inactivity-box p {
        color: #64748b; /* Your --muted */
        font-size: 0.85rem;
        line-height: 1.6;
        margin-bottom: 24px;
      }
      #inactivity-countdown-wrap {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 12px 24px;
        border-radius: 12px;
        margin-bottom: 32px;
      }
      #inactivity-countdown {
        font-family: 'JetBrains Mono', monospace;
        font-size: 1.75rem;
        font-weight: 800;
        color: #15b201;
      }
      .inactivity-btn-primary {
        all: unset;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        background: #15b201;
        color: #fff;
        padding: 12px 24px;
        border-radius: 10px;
        font-size: 0.85rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s;
        width: calc(100% - 48px);
        margin: 0 auto;
      }
      .inactivity-btn-primary:hover {
        background: #0f8a00; /* Your --primary-dark */
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(21, 178, 1, 0.25);
      }
      
      @keyframes modalIn {
        from { opacity: 0; transform: translateY(20px) scale(0.95); }
        to { opacity: 1; transform: translateY(0) scale(1); }
      }
    </style>

    <div id="inactivity-overlay">
      <div id="inactivity-box">
        <div id="inactivity-icon-container">
            <!-- Clock icon using raw SVG to avoid dependency issues -->
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </div>
        <h2 id="inactivity-title">Session Intelligence</h2>
        <p>Security protocols detected inactivity. For your protection, this session will be terminated in:</p>
        
        <div id="inactivity-countdown-wrap">
            <span id="inactivity-countdown">--</span>
        </div>

        <button class="inactivity-btn-primary" onclick="location.reload()">
          <span>Maintain Secure Session</span>
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
        </button>
      </div>
    </div>

    <script>
    (function () {
      var TIMEOUT   = <?= $timeout ?>;   
      var COUNTDOWN = <?= $timeout ?>;   
      var overlay   = document.getElementById('inactivity-overlay');
      var counter   = document.getElementById('inactivity-countdown');

      var idleTimer     = null;
      var countTimer    = null;
      var remaining     = COUNTDOWN;
      var modalVisible  = false;

      function showModal() {
        if (modalVisible) return;
        modalVisible = true;
        remaining    = COUNTDOWN;
        counter.textContent = remaining + 's';
        overlay.classList.add('show');

       countTimer = setInterval(function () {
            remaining--;
            counter.textContent = remaining + 's';
            if (remaining <= 0) {
                clearInterval(countTimer);
                location.href = '<?= LOGOUT_URL ?>'; // ← root-relative, always correct
            }
            }, 1000);
      }

      function resetIdle() {
        if (modalVisible) return;
        clearTimeout(idleTimer);
        idleTimer = setTimeout(showModal, TIMEOUT * 1000);
      }

      ['mousemove', 'keydown', 'mousedown', 'touchstart', 'scroll', 'click']
        .forEach(function (evt) {
          document.addEventListener(evt, resetIdle, { passive: true });
        });

      resetIdle();
    })();
    </script>
    <?php
}

register_shutdown_function('render_inactivity_modal');