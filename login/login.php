<?php 
declare(strict_types=1);
require_once __DIR__ . '/../config.php';

// Initialization for the UI Header
$currentDate = date('F j, Y');
$currentTime = date('h:i:s A');

$lockout_seconds = 300;    // 5 minutes
$max_ip_attempts = 5;     // Max attempts from one IP before a hard block.
$max_fail_before_delay = 3; // How many fails before the "sleep" delay kicks in.

// 1. DEVICE IDENTIFICATION (Must be before any HTML)
$deviceId = $_COOKIE['bgt_dev_id'] ?? '';
if (empty($deviceId)) {
    $deviceId = bin2hex(random_bytes(32));
    setcookie('bgt_dev_id', $deviceId, [
        'expires' => time() + (86400 * 365 * 5), // 5 years
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
}

if (!empty($_SESSION['user_id'])) { 
    redirect(LOGIN_REDIRECT); 
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!csrf_verify()) {
            throw new Exception("Security session expired. Please refresh the page.");
        }
        
        $login_identity = trim($_POST['login_identity'] ?? '');
        $password       = trim($_POST['password'] ?? '');
        $ip_address     = $_SERVER['REMOTE_ADDR'];

        if ($login_identity === '' || $password === '') {
            $error = 'Please enter your username/email and password.';
        } else {
            $pdo = get_pdo(); 

            // --- MAINTENANCE: DELETE ATTEMPTS OLDER THAN 1 DAY ---
            $pdo->exec("DELETE FROM login_attempts WHERE attempted_at < DATE_SUB(NOW(), INTERVAL 1 DAY)");

            // 2. CHECK GLOBAL IP HEALTH (Hard block for massive bot attacks)
            $stmtIp = $pdo->prepare("SELECT COUNT(*) FROM login_attempts WHERE ip_address = ? AND attempted_at > DATE_SUB(NOW(), INTERVAL $lockout_seconds SECOND)");
            $stmtIp->execute([$ip_address]);
            
            if ((int)$stmtIp->fetchColumn() > $max_ip_attempts) { 
                $error = "Traffic from this IP has been restricted. Try again in " . ($lockout_seconds / 60) . " minutes.";
            }

            // Only proceed with login check if there isn't an IP block error
            if (empty($error)) {
                // 3. CALCULATE THROTTLE (Delay)
                $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM login_attempts WHERE login_identity = ? AND device_id = ? AND attempted_at > DATE_SUB(NOW(), INTERVAL $lockout_seconds SECOND)");
                $stmtCount->execute([$login_identity, $deviceId]);
                $failCount = (int)$stmtCount->fetchColumn();

                if ($failCount >= $max_fail_before_delay) {
                    $delay = ($failCount - ($max_fail_before_delay - 1)) * 2; 
                    if ($delay > 30) $delay = 30; 
                    sleep($delay); 
                }

                // 4. SEARCH FOR USER
                $stmt = $pdo->prepare('
                    SELECT u.id, u.username, u.email, u.password_hash, u.status, u.employee_id, u.role_id, r.name AS role_name 
                    FROM users u
                    JOIN roles r ON u.role_id = r.id
                    WHERE u.email = :ident1 OR u.username = :ident2 
                    LIMIT 1
                ');
                $stmt->execute([':ident1' => $login_identity, ':ident2' => $login_identity]);
                $user = $stmt->fetch();

                // Default hash to prevent timing attacks
                $hashToVerify = $user ? $user['password_hash'] : '$2y$10$vI8.3O6QxMa./s0.4RWPqOtLRe68W.E4mR0a/t1.x/W6f1y5';

                if ($user && password_verify($password, $hashToVerify)) {
                    if ($user['status'] !== 'Active') {
                        $error = 'Account inactive. Please contact your administrator.';
                    } else {
                        // SUCCESS! 
                        $pdo->prepare('DELETE FROM login_attempts WHERE login_identity = ?')->execute([$login_identity]);
                        session_regenerate_id(true);
                        
                        $permStmt = $pdo->prepare('SELECT module_key FROM v_user_access_resolver WHERE user_id = :uid AND final_access_allowed = 1');
                        $permStmt->execute([':uid' => $user['id']]);
                        
                        $_SESSION['user_id']     = $user['id'];
                        $_SESSION['emp_id']      = $user['employee_id'];
                        $_SESSION['username']    = $user['username'];
                        $_SESSION['email']       = $user['email'];
                        $_SESSION['role']        = $user['role_name'];
                        $_SESSION['permissions'] = $permStmt->fetchAll(PDO::FETCH_COLUMN);

                        $pdo->prepare('UPDATE users SET last_login = NOW() WHERE id = :uid')->execute([':uid' => $user['id']]);
                        redirect(LOGIN_REDIRECT);
                    }
                } else {
                    // FAILURE: Record the attempt
                    $pdo->prepare("INSERT INTO login_attempts (ip_address, device_id, login_identity) VALUES (?, ?, ?)")
                        ->execute([$ip_address, $deviceId, $login_identity]);
                    $error = 'Invalid username/email or password.';
                }
            }
        }
    } catch (Exception $e) {
        $error = "System Error: " . $e->getMessage();
    }
} 

$csrf = csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BGT Enterprise | Secure Access</title> 
<style>
     @font-face {
        font-family: 'Plus Jakarta Sans';
        src: url('<?= BASE_URL ?>assets/fonts/PlusJakartaSans-Regular.ttf') format('truetype');
        font-weight: 400;
        font-style: normal;
    }
    @font-face {
        font-family: 'Plus Jakarta Sans';
        src: url('<?= BASE_URL ?>assets/fonts/PlusJakartaSans-Medium.ttf') format('truetype');
        font-weight: 500;
        font-style: normal;
    }
    @font-face {
        font-family: 'Plus Jakarta Sans';
        src: url('<?= BASE_URL ?>assets/fonts/PlusJakartaSans-SemiBold.ttf') format('truetype');
        font-weight: 600;
        font-style: normal;
    }
    @font-face {
        font-family: 'Plus Jakarta Sans';
        src: url('<?= BASE_URL ?>assets/fonts/PlusJakartaSans-Bold.ttf') format('truetype');
        font-weight: 700;
        font-style: normal;
    }
    @font-face {
        font-family: 'Plus Jakarta Sans';
        src: url('<?= BASE_URL ?>assets/fonts/PlusJakartaSans-ExtraBold.ttf') format('truetype');
        font-weight: 800;
        font-style: normal;
    }

    :root { --primary: #15b201; --primary-dark: #0e8a00; --primary-light: #f1fcf0; --text-main: #0f172a; --text-muted: #64748b; --border: #e2e8f0; --bg-body: #f8fafc; --radius: 14px; --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Plus Jakarta Sans', sans-serif;  background: var(--bg-body); color: var(--text-main); height: 100vh; overflow: hidden; }
    .auth-container { display: grid; grid-template-columns: 1fr 1fr; height: 100vh; width: 100%; }
    
    .brand-panel { background-color: #105808; background-image: radial-gradient(circle at 20% 30%, rgba(21, 178, 1, 0.8) 0%, transparent 50%), radial-gradient(circle at 80% 70%, rgba(0, 50, 0, 0.6) 0%, transparent 50%); background-size: 200% 200%; animation: liquidMove 15s ease-in-out infinite alternate; display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden; min-height: 100vh; width: 100%; color: white; }
    .brand-panel::before, .brand-panel::after { content: ""; position: absolute; bottom: 0; left: 0; width: 200%; height: 120px; background: rgba(255,255,255,0.05); backdrop-filter: blur(15px); -webkit-mask: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1100 120" preserveAspectRatio="none"><path d="M0,120V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5,73.84-4.36,147.54,16.88,218.2,38.5,88.56,27.1,187.15,31.7,276,14.5,77.31-15,152.14-53,241-43.5V120H0Z" fill="black"/></svg>') repeat-x; mask: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1100 120" preserveAspectRatio="none"><path d="M0,120V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5,73.84-4.36,147.54,16.88,218.2,38.5,88.56,27.1,187.15,31.7,276,14.5,77.31-15,152.14-53,241-43.5V120H0Z" fill="black"/></svg>') repeat-x; -webkit-mask-size: calc(50% + 1px) 100%; mask-size: calc(50% + 1px) 100%; animation: waveLoop 20s linear infinite; z-index: 1; }
    .brand-panel::after { height: 100px; background: rgba(255,255,255,0.08); backdrop-filter: blur(30px); animation: waveLoop 30s linear infinite reverse; z-index: 2; }
    .brand-content { position: relative; z-index: 5; max-width: 600px; padding: 40px; }
    .logo-box { background: white; display: inline-flex; padding: 10px 18px; border-radius: 12px; margin-bottom: 40px; box-shadow: 0 15px 30px rgba(0,0,0,0.1);   }
    .logo-box img { width: 300px; }
    
    @keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
    .brand-panel h1 { font-family: 'Plus Jakarta Sans', sans-serif; font-size: 3.5rem; font-weight: 800; line-height: 1; margin-bottom: 20px; letter-spacing: -0.04em; }
    .brand-panel p { font-size: 1.1rem; opacity: 0.85; line-height: 1.5; }
    
    .form-panel { background: white; display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 40px; position: relative; }
    .form-card { width: 100%; max-width: 500px;  }
    .view { display: none; animation: slideIn 0.5s ease-out forwards; }
    .view.active { display: block; }
    .form-header { margin-bottom: 35px; }
    .form-header h2 { font-family: 'Plus Jakarta Sans', sans-serif; font-size: 2rem; font-weight: 800; letter-spacing: -0.02em; margin-bottom: 8px; }
    .form-header p { color: var(--text-muted); font-size: 0.95rem; }
    
    .input-group { margin-bottom: 24px; position: relative; }
    .input-group label { display: block; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); margin-bottom: 10px; padding-left: 2px; }
    .input-ctrl { width: 100%; height: 58px; background: #f8fafc; border: 2px solid #f1f5f9; border-radius: var(--radius); padding: 0 20px; font: inherit; font-weight: 500; transition: var(--transition); }
    .input-ctrl:focus { outline: none; background: white; border-color: var(--primary); box-shadow: 0 0 0 4px var(--primary-light); }

    .password-wrapper { position: relative; display: flex; align-items: center; }
    .password-wrapper .input-ctrl { padding-right: 55px; }
    .toggle-password-btn { position: absolute; right: 12px; background: none; border: none; color: var(--text-muted); cursor: pointer; display: flex; align-items: center; justify-content: center; padding: 8px; transition: var(--transition); z-index: 10; border-radius: 8px; }
    .toggle-password-btn:hover { color: var(--primary); background: var(--primary-light); }
    .toggle-password-btn svg { width: 22px; height: 22px; }

    .form-options { display: flex; justify-content: space-between; align-items: center; margin-bottom: 35px; font-size: 0.9rem; }
    .checkbox-wrap { display: flex; align-items: center; gap: 12px; color: var(--text-muted); font-weight: 600; cursor: pointer; user-select: none; }
    .checkbox-wrap input { position: absolute; opacity: 0; width: 0; height: 0; }
    .checkmark { height: 24px; width: 24px; background: white; border: 2px solid var(--border); border-radius: 8px; position: relative; transition: all 0.2s ease; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .checkbox-wrap input:checked ~ .checkmark { background: var(--primary); border-color: var(--primary); box-shadow: 0 4px 12px rgba(21,178,1,0.25); }
    .checkmark::after { content: ""; position: absolute; display: none; left: 50%; top: 48%; width: 6px; height: 11px; border: solid white; border-width: 0 2.5px 2.5px 0; transform: translate(-50%, -50%) rotate(45deg); }
    .checkbox-wrap input:checked ~ .checkmark::after { display: block; }
    
    .btn-primary { width: 100%; height: 58px; display: flex; align-items: center; justify-content: center; gap: 12px; border: none; border-radius: var(--radius); background: var(--primary); color: white; font: inherit; font-size: 0.95rem; font-weight: 700; letter-spacing: 0.02em; cursor: pointer; transition: var(--transition); position: relative; overflow: hidden; box-shadow: 0 10px 25px -5px rgba(21, 178, 1, 0.3); }
    .btn-primary:hover { background: var(--primary-dark); transform: translateY(-2px); box-shadow: 0 15px 30px -5px rgba(21, 178, 1, 0.4); }
    .error-banner { display: none; background: #fff1f2; border: 1px solid #fecdd3; color: #be123c; padding: 12px 16px; border-radius: 10px; font-size: 0.85rem; font-weight: 500; margin-bottom: 20px; align-items: center; gap: 10px; animation: shake 0.4s both; }
    
    .spinner { display: none; width: 20px; height: 20px; border: 3px solid rgba(255,255,255,0.3); border-radius: 50%; border-top-color: white; animation: spin 0.8s linear infinite; }
    .btn-primary.is-loading .btn-text, .btn-primary.is-loading svg { display: none; }
    .btn-primary.is-loading .spinner { display: block; }
    
    .footer-note { position: absolute; bottom: 40px; color: #cbd5e1; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; }
    #notification-hub { position: fixed; top: 30px; right: 30px; z-index: 9999; display: flex; flex-direction: column; gap: 12px; }
    .bgt-toast { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); border: 1px solid var(--border); border-left: 5px solid var(--primary); padding: 16px 24px; border-radius: var(--radius); box-shadow: 0 15px 30px rgba(0, 0, 0, 0.08); display: flex; align-items: center; gap: 15px; min-width: 320px; transform: translateX(120%); transition: all 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55); }
    .bgt-toast.active { transform: translateX(0); }

    @keyframes spin { to { transform: rotate(360deg); } }
    @keyframes slideIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes waveLoop { 0% { transform: translateX(0); } 100% { transform: translateX(-50%); } }
    @keyframes liquidMove { 0% { background-position: 0% 0%; } 100% { background-position: 100% 100%; } }
    @keyframes shake { 10%, 90% { transform: translate3d(-1px, 0, 0); } 20%, 80% { transform: translate3d(2px, 0, 0); } 30%, 50%, 70% { transform: translate3d(-4px, 0, 0); } 40%, 60% { transform: translate3d(4px, 0, 0); } }
    
    .btn-link { background: none; border: none; color: var(--primary); font-weight: 600; font-size: 0.9rem; cursor: pointer; padding: 4px 8px; border-radius: 6px; transition: var(--transition); font-family: 'Plus Jakarta Sans', sans-serif;  }
    .btn-link:hover { background: var(--primary-light); color: var(--primary-dark); }
    .back-btn { display: inline-flex; align-items: center; gap: 8px; color: var(--text-muted); font-weight: 600; font-size: 0.85rem; margin-bottom: 24px; cursor: pointer; transition: var(--transition); padding: 8px 12px; margin-left: -12px; border-radius: 8px; background: none; border: none; font-family: 'Plus Jakarta Sans', sans-serif;  }
    .back-btn:hover { color: var(--text-main); background: #f1f5f9; }

    .status-header {
        position: absolute;
        top: 30px;
        right: 40px;
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.9);
        padding: 12px 25px;
        border-radius: 50px;
        display: flex;
        align-items: center;
        gap: 15px;
        font-family: 'Plus Jakarta Sans', sans-serif; 
        font-size: 0.9rem;
        font-weight: 600;
        color: #1e293b;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
        z-index: 100;
        letter-spacing: 0.5px;
    }
    .status-header .time-segment {
        color: var(--primary);
        border-left: 2px solid #e2e8f0;
        padding-left: 15px;
    }

    @media (max-width: 850px) {
        body { overflow: auto; height: auto; }
        .auth-container { grid-template-columns: 1fr; }
        .brand-panel { display: none; }
        .form-panel { min-height: 100vh; padding: 60px 20px; }
        .status-header { position: fixed; top: 15px; right: 15px; padding: 8px 15px; font-size: 0.8rem; }
        .footer-note { position: relative; bottom: 0; margin-top: 40px; }
    }
</style>
</head>
<body>

<div class="auth-container">
    <aside class="brand-panel">
        <img src="../assets/img/bgwhiter.png" alt="BGT Logo" style="position:absolute;top:0;right:-10px;width:100px; animation: float 1s ease-in-out infinite;">
        <div class="brand-content">
            <div class="logo-box">
                <img src="../assets/img/bgt.png" alt="BGT Logo">
            </div>
            <h1>Login Portal.</h1>
            <p>Welcome back. Secure access for Bull Green Trading members.</p>
        </div>
    </aside>

    <main class="form-panel">
        <div class="status-header">
            <span id="current-date"><?= $currentDate ?></span>
            <span class="time-segment" id="current-time"><?= $currentTime ?></span>
        </div>
        
        <img src="../assets/img/bgwhitel.png" alt="BGT Logo" style="position:absolute;top:0;left:-10px;width:100px;  animation: float 1s ease-in-out infinite;">
        
        <div class="form-card">
            <!-- LOGIN VIEW -->
            <div class="view active" id="login-view">
                <div class="form-header">
                    <h2>Identity Verification</h2>
                    <p>Please sign in to validate your credentials and proceed.</p>
                </div>

                <?php if ($error): ?>
                <div class="error-banner" style="display:flex;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
                <?php endif; ?>

                <form method="POST" action="login.php" id="login-form" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">

                    <div class="input-group">
                        <label>Login Identity</label>
                        <input type="text" name="login_identity" id="email-field" class="input-ctrl" 
                                placeholder="Username / Email" 
                                value="<?= htmlspecialchars($_POST['login_identity'] ?? '') ?>" 
                                autocomplete="username">
                    </div>

                    <div class="input-group">
                        <label>Security Password</label>
                        <div class="password-wrapper">
                            <input type="password" name="password" id="pass-field" class="input-ctrl"
                                placeholder="*******" autocomplete="current-password">
                            
                            <button type="button" id="toggle-password" class="toggle-password-btn">
                                <svg id="eye-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                <svg id="eye-off-icon" style="display:none;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                    <line x1="1" y1="1" x2="23" y2="23"></line>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="form-options">
                        <label class="checkbox-wrap">
                            <input type="checkbox" name="remember_me" value="1">
                            <span class="checkmark"></span>
                            <span>Remember me</span>
                        </label>
                        <button type="button" class="btn-link" onclick="toggleView('forgot-view')">Forgot credentials?</button>
                    </div>

                    <button type="submit" class="btn-primary" id="login-btn">
                        <span class="btn-text">Log in to Workspace</span>
                        <div class="spinner"></div>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14m-7-7 7 7-7 7"/></svg>
                    </button>
                </form>
            </div>

            <!-- FORGOT PASSWORD VIEW -->
            <div class="view" id="forgot-view">
                <button type="button" class="back-btn" onclick="toggleView('login-view')">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                    Back to login
                </button>
                <div class="form-header">
                    <h2>Recover Access</h2>
                    <p>Enter your email to receive recovery instructions.</p>
                </div>
                <div id="forgot-error" class="error-banner">
                    <span class="error-text">Please enter a valid email.</span>
                </div>
                <div class="input-group">
                    <label>Your Email</label>
                    <input type="email" id="forgot-email" class="input-ctrl" placeholder="e.g. user@bullgreentrading.com">
                </div>
                <button class="btn-primary" onclick="handleReset()">
                    <span class="btn-text">Send Recovery Link</span>
                    <div class="spinner"></div>
                </button>
            </div>
        </div>
        <footer class="footer-note">Bull Green Trading PLC &bull; &copy; <?= date('Y') ?> YDY Systems</footer>
    </main>
</div>

<div id="notification-hub"></div>

<script>
const $ = id => document.getElementById(id);
const views = document.querySelectorAll('.view');

window.bgtNotify = (title, message, type = 'success') => {
    const hub = $('notification-hub');
    const toast = document.createElement('div');
    toast.className = `bgt-toast ${type}`;
    toast.innerHTML = `<div class="msg-content"><strong style="display:block; font-size:13px;">${title}</strong><span style="font-size:14px; color:#64748b;">${message}</span></div>`;
    hub.appendChild(toast);
    setTimeout(() => toast.classList.add('active'), 10);
    setTimeout(() => {
        toast.classList.remove('active');
        setTimeout(() => toast.remove(), 500);
    }, 4500);
};

window.toggleView = (viewId) => views.forEach(v => v.classList.toggle('active', v.id === viewId));

$('toggle-password')?.addEventListener('click', () => {
    const field = $('pass-field'), eye = $('eye-icon'), eyeOff = $('eye-off-icon');
    const isPass = field.type === 'password';
    field.type = isPass ? 'text' : 'password';
    eye.style.display = isPass ? 'none' : 'block';
    eyeOff.style.display = isPass ? 'block' : 'none';
    field.focus();
});

$('login-form')?.addEventListener('submit', () => $('login-btn').classList.add('is-loading'));

window.handleReset = () => {
    const inp = $('forgot-email'), err = $('forgot-error'), val = inp.value.trim();
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) {
        err.style.display = 'flex';
        inp.classList.add('error');
        return;
    }
    bgtNotify('Link Sent', 'Check your inbox for instructions.', 'success');
    toggleView('login-view');
};

function updateClock() {
    const now = new Date();
    const dateString = now.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
    const timeString = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
    
    if($('current-date')) $('current-date').innerText = dateString;
    if($('current-time')) $('current-time').innerText = timeString;
}

setInterval(updateClock, 1000);
if (window.history.replaceState) window.history.replaceState(null, null, window.location.href);
</script>
</body>
</html>