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

// ── Inactivity timeout (20 minutes) ─────────────────────────────────────────────
define('INACTIVITY_TIMEOUT',1200); // 

define('BASE_URL', rtrim(str_replace(
    str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']),
    '',
    str_replace('\\', '/', __DIR__)
), '/') . '/');

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
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        return;
    }

    $timeout = INACTIVITY_TIMEOUT;
    ?>
    <style>
      /* ... (All your existing CSS remains exactly the same) ... */
      :root {
        --modal-primary: #15b201;
        --modal-primary-hover: #0f8a00;
        --modal-bg: #ffffff;
        --modal-text: #0f172a;
        --modal-muted: #64748b;
        --modal-overlay: rgba(15, 23, 42, 0.7);
      }

      #inactivity-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: var(--modal-overlay);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        z-index: 10000;
        align-items: center;
        justify-content: center;
        padding: 20px;
        font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
        opacity: 0;
        transition: opacity 0.4s ease;
        overflow-y: auto;
      }

      #inactivity-overlay.show {
        display: flex;
        opacity: 1;
      }

      #inactivity-box {
        background: var(--modal-bg);
        border-radius: 24px;
        padding: 40px 24px;
        width: 100%;
        max-width: 400px;
        text-align: center;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.3);
        transform: scale(0.9) translateY(20px);
        transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        position: relative;
        margin: auto;
      }

      #inactivity-overlay.show #inactivity-box {
        transform: scale(1) translateY(0);
      }

      .timer-container {
        position: relative;
        width: 100px;
        height: 100px;
        margin: 0 auto 24px;
        display: flex;
        align-items: center;
        justify-content: center;
      }

      .timer-svg {
        transform: rotate(-90deg);
        width: 100px;
        height: 100px;
        overflow: visible;
      }

      .timer-bg {
        fill: none;
        stroke: #f1f5f9;
        stroke-width: 8;
      }

      .timer-progress {
        fill: none;
        stroke: var(--modal-primary);
        stroke-width: 8;
        stroke-linecap: round;
        stroke-dasharray: 282.7;
        stroke-dashoffset: 0;
        transition: stroke-dashoffset 1s linear, stroke 0.3s ease;
      }

      #inactivity-countdown {
        position: absolute;
        font-family: 'JetBrains Mono', monospace;
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--modal-text);
      }

      @keyframes ring-pulse {
        0% { stroke-width: 8; opacity: 1; }
        50% { stroke-width: 12; opacity: 0.7; }
        100% { stroke-width: 8; opacity: 1; }
      }

      .urgent-mode .timer-progress {
        stroke: #ef4444;
        animation: ring-pulse 1s infinite ease-in-out;
      }

      #inactivity-title {
        font-size: clamp(1.25rem, 5vw, 1.5rem);
        font-weight: 800;
        color: var(--modal-text);
        margin-bottom: 12px;
        letter-spacing: -0.02em;
      }

      #inactivity-box p {
        color: var(--modal-muted);
        font-size: 0.9rem;
        line-height: 1.5;
        margin-bottom: 32px;
        padding: 0 10px;
      }

      .inactivity-actions {
        display: flex;
        flex-direction: column;
        gap: 12px;
      }

      .btn-stay {
        all: unset;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        background: var(--modal-primary);
        color: #fff;
        padding: 14px 20px;
        border-radius: 14px;
        font-size: 0.95rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 4px 12px rgba(21, 178, 1, 0.2);
      }

      .btn-stay:hover { background: var(--modal-primary-hover); }
      .btn-stay:active { transform: scale(0.98); }

      .btn-logout {
        all: unset;
        color: var(--modal-muted);
        font-size: 0.85rem;
        font-weight: 600;
        padding: 10px;
        cursor: pointer;
        border-radius: 10px;
        transition: all 0.2s;
      }

      .btn-logout:hover {
        background: #fff1f2;
        color: #ef4444;
      }
    </style>

    <div id="inactivity-overlay">
      <div id="inactivity-box">
        <div class="timer-container">
            <svg class="timer-svg" viewBox="0 0 100 100">
                <circle class="timer-bg" cx="50" cy="50" r="45"></circle>
                <circle id="timer-progress" class="timer-progress" cx="50" cy="50" r="45"></circle>
            </svg>
            <div id="inactivity-countdown">--</div>
        </div>

        <h2 id="inactivity-title">Session Security</h2>
        <p>Your session is about to expire due to inactivity. Would you like to stay logged in?</p>
        
        <div class="inactivity-actions">
            <!-- UPDATED: Added ID and removed location.reload() -->
            <button id="btn-stay-active" class="btn-stay">
              <span>Continue Session</span>
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
            </button>
            <button class="btn-logout" onclick="location.href='<?= LOGOUT_URL ?>'">
                Log Out Now
            </button>
        </div>
      </div>
    </div>

    <script>
    (function () {
      const TIMEOUT       = <?= $timeout ?>;   
      const COUNTDOWN_VAL = 7;   
      const overlay       = document.getElementById('inactivity-overlay');
      const counter       = document.getElementById('inactivity-countdown');
      const progressRing  = document.getElementById('timer-progress');
      const stayBtn       = document.getElementById('btn-stay-active');
      const radius        = 45;
      const circumference = 2 * Math.PI * radius;

      let idleTimer     = null;
      let countTimer    = null;
      let remaining     = COUNTDOWN_VAL;
      let modalVisible  = false;

      progressRing.style.strokeDasharray = circumference;

      function setProgress(percent) {
        const offset = circumference - (percent / 100 * circumference);
        progressRing.style.strokeDashoffset = offset;
      }

      function showModal() {
        if (modalVisible) return;
        modalVisible = true;
        remaining    = COUNTDOWN_VAL;
        
        setProgress(100);
        counter.textContent = remaining;
        overlay.classList.add('show');

        countTimer = setInterval(function () {
            remaining--;
            counter.textContent = remaining;
            
            const percent = (remaining / COUNTDOWN_VAL) * 100;
            setProgress(percent);

            if (remaining <= 5) {
                overlay.classList.add('urgent-mode');
            }

            if (remaining <= 0) {
                clearInterval(countTimer);
                location.href = '<?= LOGOUT_URL ?>';
            }
        }, 1000);
      }

      // NEW: Function to resume without reloading
      function resumeSession() {
        if (!modalVisible) return;
        
        // 1. Stop the countdown
        clearInterval(countTimer);
        
        // 2. Hide modal and reset UI state
        overlay.classList.remove('show');
        overlay.classList.remove('urgent-mode');
        modalVisible = false;

        // 3. Optional: Ping server to keep PHP session alive
        // This prevents the session file on the server from expiring
        fetch(window.location.href, { method: 'HEAD', cache: 'no-store' });

        // 4. Restart the idle timer
        resetIdle();
      }

      function resetIdle() {
        if (modalVisible) return;
        clearTimeout(idleTimer);
        idleTimer = setTimeout(showModal, TIMEOUT * 1000);
      }

      // Attach click event to the stay button
      stayBtn.addEventListener('click', function(e) {
        e.preventDefault();
        resumeSession();
      });

      ['mousemove', 'keydown', 'mousedown', 'touchstart', 'scroll', 'click']
        .forEach(evt => {
          document.addEventListener(evt, resetIdle, { passive: true });
        });

      resetIdle();
    })();
    </script>
    <?php
}

register_shutdown_function('render_inactivity_modal');