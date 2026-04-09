<?php
/**
 * Registration Page Logic
 */
require_once '../config.php'; // Ensure this path correctly points to your config file

$errors = [];
$success = "";
$fields = ['username' => '', 'email' => ''];

// Use the token function from your config.php
$csrf = csrf_token();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. Verify CSRF using your config utility
    if (!csrf_verify()) {
        $errors['global'] = "Security validation failed. Please try again.";
    } else {
        // 2. Collect and Clean Input using your config utility
        $fields['username'] = clean($_POST['username'] ?? '');
        $fields['email']    = clean($_POST['email'] ?? '');
        $password           = $_POST['password'] ?? '';
        $password_confirm   = $_POST['password_confirm'] ?? '';

        // 3. Validation
        if (empty($fields['username'])) {
            $errors['username'] = "Username is required.";
        } elseif (strlen($fields['username']) > 30) {
            $errors['username'] = "Username is too long (max 30).";
        }

        if (empty($fields['email'])) {
            $errors['email'] = "Email is required.";
        } elseif (!filter_var($fields['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = "Invalid email format.";
        }

        if (strlen($password) < 8) {
            $errors['password'] = "Password must be at least 8 characters.";
        }

        if ($password !== $password_confirm) {
            $errors['password_confirm'] = "Passwords do not match.";
        }

        // 4. Database Interaction 
// 4. Database Interaction
if (empty($errors)) {
    try {
        $pdo = get_pdo();

        // Check for existing records
        $check = $pdo->prepare("SELECT username, email FROM users WHERE username = ? OR email = ?");
        $check->execute([$fields['username'], $fields['email']]);
        $existing = $check->fetch();

        if ($existing) {
            if ($existing['username'] === $fields['username']) $errors['username'] = "Username already taken.";
            if ($existing['email'] === $fields['email']) $errors['email'] = "Email already registered.";
        } else {
            
            // --- NEW LOGIC TO MATCH YOUR DATABASE ---
            
            // 1. Find the ID for the 'Super Admin' role from your 'roles' table
            $roleQuery = $pdo->prepare("SELECT id FROM roles WHERE name = 'Super Admin' LIMIT 1");
            $roleQuery->execute();
            $roleRow = $roleQuery->fetch();

            if (!$roleRow) {
                throw new Exception("Default role 'Super Admin' not found in database. Please seed the roles table.");
            }
            $target_role_id = $roleRow['id'];

            // 2. Hash the password
            $password_hash = password_hash($password, PASSWORD_BCRYPT);

            // 3. Perform the Insert using 'role_id' instead of 'role'
            $sql = "INSERT INTO users (username, email, password_hash, role_id, status) 
                    VALUES (:username, :email, :password_hash, :role_id, 'Active')";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':username'      => $fields['username'],
                ':email'         => $fields['email'],
                ':password_hash' => $password_hash,
                ':role_id'       => $target_role_id
            ]);

            $success = "Account created! You can now <a href='login.php' style='color:inherit; font-weight:700;'>Sign in</a>.";
            $fields = ['username' => '', 'email' => ''];
        }
    } catch (Exception $e) {
        $errors['global'] = "Registration failed: " . $e->getMessage();
    }
}
    }
}
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

        .form-panel {
            background: white; display: flex; flex-direction: column;
            justify-content: center; align-items: center;
            padding: 30px 10px; position: relative;
        }

        .form-card { width: 100%; max-width: 520px; }
        .form-header h2 {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 2rem; font-weight: 800;
            letter-spacing: -0.02em; margin-bottom: 8px;
        }
        .form-header p { color: var(--text-muted); font-size: 0.95rem; margin-bottom: 32px;}

        .input-group { margin-bottom: 20px; }
        .input-group label {
            display: block; font-size: 0.75rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.05em;
            color: var(--text-muted); margin-bottom: 8px;
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

        /* Password Toggle Styles */
        .password-container {
            position: relative;
            display: flex;
            align-items: center;
        }
        .password-toggle-icon {
            position: absolute;
            right: 15px;
            cursor: pointer;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
            user-select: none;
            z-index: 5;
        }
        .password-toggle-icon:hover {
            color: var(--primary);
        }
        .input-ctrl-pass {
            padding-right: 50px; /* Make room for the icon */
        }

        .field-error {
            color: #be123c; font-size: 0.8rem; font-weight: 500;
            margin-top: 6px; padding-left: 2px;
        }

        .strength-wrap { margin-top: 8px; }
        .strength-bars { display: flex; gap: 4px; margin-bottom: 4px; }
        .strength-bar { height: 4px; flex: 1; background: #e2e8f0; border-radius: 99px; transition: background 0.3s ease; }
        .strength-bar.active-1 { background: #ef4444; }
        .strength-bar.active-2 { background: #f97316; }
        .strength-bar.active-3 { background: #eab308; }
        .strength-bar.active-4 { background: var(--primary); }
        .strength-label { font-size: 0.75rem; color: var(--text-muted); font-weight: 500; }

        .row-2col { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }

        .btn-primary {
            width: 100%; height: 58px;
            display: flex; align-items: center; justify-content: center;
            border: none; border-radius: var(--radius);
            background: var(--primary); color: white;
            font-size: 0.95rem; font-weight: 700;
            cursor: pointer; transition: var(--transition);
            margin-top: 8px;
            box-shadow: 0 10px 25px -5px rgba(21, 178, 1, 0.3);
        }
        .btn-primary:hover { background: var(--primary-dark); transform: translateY(-2px); }

        .success-banner {
            background: #f0fdf4; border: 1px solid #86efac; color: #166534;
            padding: 14px 18px; border-radius: 10px; font-size: 0.9rem; margin-bottom: 24px;
        }
        .error-banner {
            background: #fff1f2; border: 1px solid #fda4af; color: #be123c;
            padding: 14px 18px; border-radius: 10px; margin-bottom: 24px;
            font-size: 0.9rem; font-weight: 600;
        }

        @media (max-width: 850px) { .auth-container { grid-template-columns: 1fr; } .brand-panel { display: none; } }
    </style>
</head>
<body>

<div class="auth-container">
    <aside class="brand-panel">
         <img src="../assets/img/bgwhiter.png" alt="BGT Logo" style="position:absolute;top:0;right:-10px;width:100px;">
        <div class="brand-content">
            <div class="logo-box"><img src="../assets/img/bgt.png" alt="BGT Logo"></div>
            <h1>Join BGT.</h1>
            <p>Create your secure workspace account.</p>
        </div>
    </aside>

    <main class="form-panel">
        <div class="form-card">
            <div class="form-header">
                <h2>Create Account</h2>
                <p>Register your BGT workspace account.</p>
            </div>

            <?php if (isset($errors['global'])): ?>
                <div class="error-banner"><?= htmlspecialchars($errors['global']) ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="success-banner"><?= $success ?></div>
            <?php endif; ?>

            <form method="POST" action="" id="reg-form" novalidate>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">

                <div class="input-group">
                    <label>Username</label>
                    <input type="text" name="username" class="input-ctrl <?= isset($errors['username']) ? 'has-error' : '' ?>"
                           placeholder="e.g. john.doe" value="<?= htmlspecialchars($fields['username']) ?>">
                    <?php if (isset($errors['username'])): ?><div class="field-error"><?= $errors['username'] ?></div><?php endif; ?>
                </div>

                <div class="input-group">
                    <label>Email Address</label>
                    <input type="email" name="email" class="input-ctrl <?= isset($errors['email']) ? 'has-error' : '' ?>"
                           placeholder="user@bullgreentrading.com" value="<?= htmlspecialchars($fields['email']) ?>">
                    <?php if (isset($errors['email'])): ?><div class="field-error"><?= $errors['email'] ?></div><?php endif; ?>
                </div>

                <div class="row-2col">
                    <div class="input-group">
                        <label>Password</label>
                        <div class="password-container">
                            <input type="password" name="password" id="f-password" 
                                   class="input-ctrl input-ctrl-pass <?= isset($errors['password']) ? 'has-error' : '' ?>"
                                   placeholder="Min. 8 characters"
                                   oninput="updateStrength(this.value)">
                            <div class="password-toggle-icon" onclick="togglePasswords()">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="eye-icon"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                            </div>
                        </div>
                    </div>
                    <div class="input-group">
                        <label>Confirm</label>
                        <div class="password-container">
                            <input type="password" name="password_confirm" id="f-password-confirm" 
                                   class="input-ctrl input-ctrl-pass <?= isset($errors['password_confirm']) ? 'has-error' : '' ?>"
                                   placeholder="Repeat password">
                            <div class="password-toggle-icon" onclick="togglePasswords()">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="eye-icon"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                            </div>
                        </div>
                    </div>
                </div>
                <?php if (isset($errors['password'])): ?><div class="field-error"><?= $errors['password'] ?></div><?php endif; ?>
                <?php if (isset($errors['password_confirm'])): ?><div class="field-error"><?= $errors['password_confirm'] ?></div><?php endif; ?>

                <div class="strength-wrap" id="strength-wrap" style="display:none; margin-bottom: 20px;">
                    <div class="strength-bars">
                        <div class="strength-bar" id="sb1"></div>
                        <div class="strength-bar" id="sb2"></div>
                        <div class="strength-bar" id="sb3"></div>
                        <div class="strength-bar" id="sb4"></div>
                    </div>
                    <span class="strength-label" id="strength-label"></span>
                </div>
                 
                <button type="submit" class="btn-primary">Create Account</button>
            </form>

            <div style="text-align: center; margin-top: 24px; color: var(--text-muted);">
                Already have an account? <a href="login.php" style="color: var(--primary); font-weight: 700; text-decoration: none;">Sign in</a>
            </div>
        </div>
    </main>
</div>

<script>
/**
 * Toggles visibility for both password inputs
 */
function togglePasswords() {
    const p1 = document.getElementById('f-password');
    const p2 = document.getElementById('f-password-confirm');
    
    // Determine the new type based on the first field
    const newType = p1.type === 'password' ? 'text' : 'password';
    
    p1.type = newType;
    p2.type = newType;
}

function updateStrength(val) {
    const wrap = document.getElementById('strength-wrap');
    const label = document.getElementById('strength-label');
    const bars = [
        document.getElementById('sb1'), document.getElementById('sb2'),
        document.getElementById('sb3'), document.getElementById('sb4'),
    ];

    if (!val) { wrap.style.display = 'none'; return; }
    wrap.style.display = 'block';

    let score = 0;
    if (val.length >= 8) score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^a-zA-Z0-9]/.test(val)) score++;

    const labels = ['', 'Weak', 'Fair', 'Good', 'Strong'];
    label.textContent = labels[score];

    bars.forEach((b, i) => {
        b.className = 'strength-bar';
        if (i < score) b.classList.add('active-' + score);
    });
}
</script>

</body>
</html>