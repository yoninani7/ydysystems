<?php 
declare(strict_types=1);
require_once __DIR__ . '/../config.php';

// Redirect authenticated users
if (!empty($_SESSION['user_id'])) {
    redirect(LOGIN_REDIRECT);
}

$errors  = [];
$success = '';
$fields  = ['username' => '', 'email' => ''];   // repopulate on error

// ── POST: process registration ────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. CSRF
    csrf_verify();

    // 2. Collect
    $username   = trim($_POST['username']         ?? '');
    $email      = trim($_POST['email']            ?? '');
    $password   = trim($_POST['password']         ?? '');
    $password2  = trim($_POST['password_confirm'] ?? '');
    $emp_id     = trim($_POST['emp_id']           ?? '');   // optional

    $fields = ['username' => $username, 'email' => $email];

    // 3. Validate username
    if ($username === '') {
        $errors['username'] = 'Username is required.';
    } elseif (strlen($username) < 3 || strlen($username) > 60) {
        $errors['username'] = 'Username must be 3–60 characters.';
    } elseif (!preg_match('/^[a-zA-Z0-9._-]+$/', $username)) {
        $errors['username'] = 'Username may only contain letters, numbers, dots, dashes, and underscores.';
    }

    // 4. Validate email
    if ($email === '') {
        $errors['email'] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    } elseif (strlen($email) > 150) {
        $errors['email'] = 'Email must not exceed 150 characters.';
    }

    // 5. Validate password
    if ($password === '') {
        $errors['password'] = 'Password is required.';
    } elseif (strlen($password) < 8) {
        $errors['password'] = 'Password must be at least 8 characters.';
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $errors['password'] = 'Password must contain at least one uppercase letter.';
    } elseif (!preg_match('/[0-9]/', $password)) {
        $errors['password'] = 'Password must contain at least one number.';
    } elseif (!preg_match('/[^a-zA-Z0-9]/', $password)) {
        $errors['password'] = 'Password must contain at least one special character.';
    }

    // 6. Confirm password
    if (empty($errors['password']) && $password !== $password2) {
        $errors['password_confirm'] = 'Passwords do not match.';
    }

    // 7. Optional emp_id — must be a positive integer if provided
    $emp_id_value = null;
    if ($emp_id !== '') {
        if (!ctype_digit($emp_id) || (int)$emp_id <= 0) {
            $errors['emp_id'] = 'Employee ID must be a positive number.';
        } else {
            $emp_id_value = (int)$emp_id;
        }
    }

    // 8. DB uniqueness check + insert (only if no validation errors)
    if (empty($errors)) {
        $pdo = get_pdo();

        // Check duplicates
        $chk = $pdo->prepare(
            'SELECT
                SUM(username = :uname) AS uname_taken,
                SUM(email    = :email) AS email_taken
             FROM system_users'
        );
        $chk->execute([':uname' => $username, ':email' => $email]);
        $taken = $chk->fetch();

        if ((int)$taken['uname_taken'] > 0) {
            $errors['username'] = 'That username is already taken.';
        }
        if ((int)$taken['email_taken'] > 0) {
            $errors['email'] = 'An account with that email already exists.';
        }

        if (empty($errors)) {
            $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

            $ins = $pdo->prepare(
                'INSERT INTO system_users
                     (emp_id, username, email, password_hash, status, created_at)
                 VALUES
                     (:emp_id, :username, :email, :hash, :status, NOW())'
            );
            $ins->execute([
                ':emp_id'   => $emp_id_value,
                ':username' => $username,
                ':email'    => $email,
                ':hash'     => $hash,
                ':status'   => 'Active',   // change to 'Inactive' if admin approval is required
            ]);

            $success = 'Account created successfully. You can now <a href="login.php">sign in</a>.';
            $fields  = ['username' => '', 'email' => ''];
        }
    }
}

$csrf = csrf_token();

// ── Password strength indicator (JS-side) ─────────────────────────────────────
// scored 0-4 based on: length ≥8, uppercase, number, special char
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BGT Enterprise | Create Account</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #15b201;
            --primary-dark: #0e8a00;
            --primary-light: #f1fcf0;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --bg-body: #f8fafc;
            --radius: 14px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            min-height: 100vh;
        }

        .auth-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            min-height: 100vh;
        }

        .brand-panel {
            background-color: #0d8a00;
            background-image:
                radial-gradient(circle at 20% 30%, rgba(21,178,1,0.8) 0%, transparent 50%),
                radial-gradient(circle at 80% 70%, rgba(0,50,0,0.6) 0%, transparent 50%);
            background-size: 200% 200%;
            animation: liquidMove 15s ease-in-out infinite alternate;
            display: flex; align-items: center; justify-content: center;
            position: sticky; top: 0; height: 100vh;
            overflow: hidden; color: white;
        }

        .brand-panel::before {
            content: ""; position: absolute; bottom: 0; left: 0;
            width: 200%; height: 120px;
            background: rgba(255,255,255,0.05); backdrop-filter: blur(15px);
            -webkit-mask-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1100 120" preserveAspectRatio="none"><path d="M0,120V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5,73.84-4.36,147.54,16.88,218.2,38.5,88.56,27.1,187.15,31.7,276,14.5,77.31-15,152.14-53,241-43.5V120H0Z" fill="black"/></svg>');
            mask-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1100 120" preserveAspectRatio="none"><path d="M0,120V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5,73.84-4.36,147.54,16.88,218.2,38.5,88.56,27.1,187.15,31.7,276,14.5,77.31-15,152.14-53,241-43.5V120H0Z" fill="black"/></svg>');
            -webkit-mask-size: calc(50% + 1px) 100%; mask-size: calc(50% + 1px) 100%;
            mask-repeat: repeat-x; -webkit-mask-repeat: repeat-x;
            animation: waveLoop 20s linear infinite; z-index: 1;
        }

        @keyframes waveLoop { 0% { transform: translateX(0); } 100% { transform: translateX(-50%); } }
        @keyframes liquidMove { 0% { background-position: 0% 0%; } 100% { background-position: 100% 100%; } }

        .brand-content { position: relative; z-index: 5; max-width: 600px; padding: 40px; }
        .logo-box {
            background: white; display: inline-flex; padding: 10px 18px;
            border-radius: 12px; margin-bottom: 40px; box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }
        .logo-box img { width: 300px; }
        .brand-panel h1 {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 3.5rem; font-weight: 800;
            line-height: 1; margin-bottom: 20px; letter-spacing: -0.04em;
        }
        .brand-panel p { font-size: 1.1rem; opacity: 0.85; line-height: 1.7; }
        .brand-panel ul { list-style: none; margin-top: 24px; }
        .brand-panel ul li {
            display: flex; align-items: center; gap: 12px;
            font-size: 0.95rem; opacity: 0.9; margin-bottom: 12px;
        }
        .brand-panel ul li::before {
            content: "✓"; background: rgba(255,255,255,0.2);
            width: 24px; height: 24px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.75rem; font-weight: 800; flex-shrink: 0;
        }

        .form-panel {
            background: white; display: flex; flex-direction: column;
            justify-content: center; align-items: center;
            padding: 60px 40px; position: relative;
        }

        .form-card { width: 100%; max-width: 420px; }

        .form-header { margin-bottom: 32px; }
        .form-header h2 {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 2rem; font-weight: 800;
            letter-spacing: -0.02em; margin-bottom: 8px;
        }
        .form-header p { color: var(--text-muted); font-size: 0.95rem; }

        .input-group { margin-bottom: 20px; }
        .input-group label {
            display: block; font-size: 0.75rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.05em;
            color: var(--text-muted); margin-bottom: 8px;
        }
        .input-group label .optional {
            font-weight: 500; text-transform: none; opacity: 0.7; letter-spacing: 0;
        }

        .input-ctrl {
            width: 100%; height: 56px;
            background: #f8fafc; border: 2px solid #f1f5f9;
            border-radius: var(--radius); padding: 0 20px;
            font-family: inherit; font-size: 1rem; font-weight: 500;
            transition: var(--transition);
        }
        .input-ctrl:focus {
            outline: none; background: white;
            border-color: var(--primary); box-shadow: 0 0 0 4px var(--primary-light);
        }
        .input-ctrl.has-error { border-color: #fda4af !important; background: #fff1f2 !important; }
        .input-ctrl.is-valid { border-color: #86efac !important; background: #f0fdf4 !important; }

        .field-error {
            display: flex; align-items: center; gap: 6px;
            color: #be123c; font-size: 0.8rem; font-weight: 500;
            margin-top: 6px; padding-left: 2px;
        }

        /* Password strength bar */
        .strength-wrap { margin-top: 8px; }
        .strength-bars {
            display: flex; gap: 4px; margin-bottom: 4px;
        }
        .strength-bar {
            height: 4px; flex: 1; background: #e2e8f0;
            border-radius: 99px; transition: background 0.3s ease;
        }
        .strength-bar.active-1 { background: #ef4444; }
        .strength-bar.active-2 { background: #f97316; }
        .strength-bar.active-3 { background: #eab308; }
        .strength-bar.active-4 { background: var(--primary); }
        .strength-label { font-size: 0.75rem; color: var(--text-muted); font-weight: 500; }

        .row-2col { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }

        .btn-primary {
            width: 100%; height: 58px;
            display: flex; align-items: center; justify-content: center; gap: 12px;
            border: none; border-radius: var(--radius);
            background: var(--primary); color: white;
            font-family: 'Inter', sans-serif; font-size: 0.95rem;
            font-weight: 700; letter-spacing: 0.02em;
            cursor: pointer; transition: var(--transition);
            position: relative; overflow: hidden; margin-top: 8px;
            box-shadow: 0 10px 25px -5px rgba(21, 178, 1, 0.3);
        }
        .btn-primary:hover { background: var(--primary-dark); transform: translateY(-2px); }
        .btn-primary:active { transform: translateY(0); filter: brightness(0.9); }

        .success-banner {
            background: #f0fdf4; border: 1px solid #86efac; color: #166534;
            padding: 14px 18px; border-radius: 10px;
            font-size: 0.9rem; font-weight: 500; margin-bottom: 24px;
        }
        .success-banner a { color: var(--primary); font-weight: 700; }

        .divider {
            display: flex; align-items: center; gap: 12px;
            margin: 24px 0; color: var(--text-muted); font-size: 0.85rem;
        }
        .divider::before, .divider::after {
            content: ''; flex: 1; height: 1px; background: var(--border);
        }

        .login-link {
            text-align: center; color: var(--text-muted); font-size: 0.9rem;
        }
        .login-link a { color: var(--primary); font-weight: 700; text-decoration: none; }
        .login-link a:hover { text-decoration: underline; }

        .footer-note {
            text-align: center; margin-top: 32px;
            color: #cbd5e1; font-size: 0.7rem;
            font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em;
        }

        @media (max-width: 850px) {
            .auth-container { grid-template-columns: 1fr; }
            .brand-panel { display: none; }
        }
        @media (max-width: 480px) {
            .row-2col { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<div class="auth-container">

    <!-- Brand Panel -->
    <aside class="brand-panel">
        <img src="../assets/bgwhiter.png" alt="BGT Logo" style="position:absolute;top:0;right:-10px;width:100px;">
        <div class="brand-content">
            <div class="logo-box">
                <img src="../assets/bgt.png" alt="BGT Logo">
            </div>
            <h1>Join BGT.</h1>
            <p>Create your secure workspace account to access the Bull Green Trading platform.</p>
            <ul>
                <li>Encrypted credentials with bcrypt hashing</li>
                <li>Session-protected access control</li>
                <li>Role-based permissions per employee</li>
                <li>Full audit trail on every action</li>
            </ul>
        </div>
    </aside>

    <!-- Registration Form -->
    <main class="form-panel">
        <div class="form-card">

            <div class="form-header">
                <h2>Create Account</h2>
                <p>Fill in the details below to register your BGT workspace account.</p>
            </div>

            <?php if ($success): ?>
            <div class="success-banner">
                <?= $success ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="register.php" id="reg-form" novalidate>
                <input type="hidden" name="csrf_token" value="<?= $csrf ?>">

                <!-- Row: Username -->
                <div class="input-group">
                    <label>Username</label>
                    <input type="text" name="username" id="f-username"
                           class="input-ctrl <?= isset($errors['username']) ? 'has-error' : '' ?>"
                           placeholder="e.g. john.doe"
                           value="<?= htmlspecialchars($fields['username']) ?>"
                           maxlength="60" autocomplete="username">
                    <?php if (isset($errors['username'])): ?>
                    <div class="field-error">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <?= htmlspecialchars($errors['username']) ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Row: Email -->
                <div class="input-group">
                    <label>Email Address</label>
                    <input type="email" name="email" id="f-email"
                           class="input-ctrl <?= isset($errors['email']) ? 'has-error' : '' ?>"
                           placeholder="user@bullgreentrading.com"
                           value="<?= htmlspecialchars($fields['email']) ?>"
                           maxlength="150" autocomplete="email">
                    <?php if (isset($errors['email'])): ?>
                    <div class="field-error">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <?= htmlspecialchars($errors['email']) ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Row: Password + Confirm side by side -->
                <div class="row-2col">
                    <div class="input-group">
                        <label>Password</label>
                        <input type="password" name="password" id="f-password"
                               class="input-ctrl <?= isset($errors['password']) ? 'has-error' : '' ?>"
                               placeholder="Min 8 chars"
                               autocomplete="new-password"
                               oninput="updateStrength(this.value)">
                        <?php if (isset($errors['password'])): ?>
                        <div class="field-error">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            <?= htmlspecialchars($errors['password']) ?>
                        </div>
                        <?php endif; ?>
                        <div class="strength-wrap" id="strength-wrap" style="display:none;">
                            <div class="strength-bars">
                                <div class="strength-bar" id="sb1"></div>
                                <div class="strength-bar" id="sb2"></div>
                                <div class="strength-bar" id="sb3"></div>
                                <div class="strength-bar" id="sb4"></div>
                            </div>
                            <span class="strength-label" id="strength-label"></span>
                        </div>
                    </div>

                    <div class="input-group">
                        <label>Confirm Password</label>
                        <input type="password" name="password_confirm" id="f-confirm"
                               class="input-ctrl <?= isset($errors['password_confirm']) ? 'has-error' : '' ?>"
                               placeholder="Repeat password"
                               autocomplete="new-password">
                        <?php if (isset($errors['password_confirm'])): ?>
                        <div class="field-error">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            <?= htmlspecialchars($errors['password_confirm']) ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Row: Employee ID (optional) -->
                <div class="input-group">
                    <label>Employee ID <span class="optional">(optional)</span></label>
                    <input type="number" name="emp_id" id="f-empid"
                           class="input-ctrl <?= isset($errors['emp_id']) ? 'has-error' : '' ?>"
                           placeholder="Link to an existing employee record"
                           min="1">
                    <?php if (isset($errors['emp_id'])): ?>
                    <div class="field-error">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <?= htmlspecialchars($errors['emp_id']) ?>
                    </div>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn-primary">
                    Create Account
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14m-7-7 7 7-7 7"/></svg>
                </button>
            </form>

            <div class="divider">or</div>

            <div class="login-link">
                Already have an account? <a href="login.php">Sign in</a>
            </div>

            <div class="footer-note">
                Bull Green Trading PLC &bull; &copy; 2026 YDY Systems
            </div>

        </div><!-- /form-card -->
    </main>

</div><!-- /auth-container -->

<script>
// Real-time password strength meter
function updateStrength(val) {
    const wrap = document.getElementById('strength-wrap');
    const label = document.getElementById('strength-label');
    const bars = [
        document.getElementById('sb1'),
        document.getElementById('sb2'),
        document.getElementById('sb3'),
        document.getElementById('sb4'),
    ];

    if (!val) { wrap.style.display = 'none'; return; }
    wrap.style.display = 'block';

    let score = 0;
    if (val.length >= 8)            score++;
    if (/[A-Z]/.test(val))          score++;
    if (/[0-9]/.test(val))          score++;
    if (/[^a-zA-Z0-9]/.test(val))   score++;

    const labels = ['', 'Weak', 'Fair', 'Good', 'Strong'];
    label.textContent = labels[score];

    bars.forEach((b, i) => {
        b.className = 'strength-bar';
        if (i < score) b.classList.add('active-' + score);
    });
}

// Live confirm-match indicator
document.getElementById('f-confirm')?.addEventListener('input', function () {
    const pw = document.getElementById('f-password').value;
    this.classList.toggle('is-valid', this.value === pw && this.value !== '');
    this.classList.toggle('has-error', this.value !== pw && this.value !== '');
});
</script>

</body>
</html>
