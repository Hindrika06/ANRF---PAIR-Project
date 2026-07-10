<?php
$rememberMe = isset($_POST['remember']);
session_set_cookie_params($rememberMe ? (30 * 24 * 60 * 60) : 0);
session_start();

require_once 'config/db.php';

$error = "";
$institute = "";

$institutes = [
    'cuk'    => 'Central University of Karnataka (CUK)',
    'kannur' => 'Kannur University',
    'mgu'    => 'Mahatma Gandhi University (MGU)',
    'ou'     => 'Osmania University (OU)',
    'svu'    => 'Sri Venkateswara University (SVU)',
    'yvu'    => 'Yogi Vemana University (YVU)',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username  = trim($_POST['username']);
    $password  = trim($_POST['password']);
    $institute = trim($_POST['institute'] ?? '');

    if ($institute === '' || !isset($institutes[$institute])) {
        $error = "Please select your institute!";
    } else {

        $stmt = $pdo->prepare("SELECT id, username, password, institute_prefix, role FROM users WHERE username = ?");
        $stmt->execute([$username]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $role = $row['role'] ?? 'admin';
            $isAuthorizedForInstitute = ($role === 'super_admin') || ($row['institute_prefix'] === $institute);

            if (!$isAuthorizedForInstitute) {
                $error = "This account is not authorized for the selected institute!";
            } elseif (password_verify($password, $row['password'])) {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['institute_prefix'] = $row['institute_prefix'];
                $_SESSION['role'] = $role;
                $_SESSION['active_prefix'] = $institute;

                header("Location: publications.php");
                exit();
            } elseif ($password === $row['password']) {
                // If the stored password is plain text, migrate it to a hash on first successful login.
                $newHash = password_hash($password, PASSWORD_DEFAULT);
                $update = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
                $update->execute([$newHash, $row['id']]);

                $_SESSION['user_id'] = $row['id'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['institute_prefix'] = $row['institute_prefix'];
                $_SESSION['role'] = $role;
                $_SESSION['active_prefix'] = $institute;

                header("Location: publications.php");
                exit();
            } else {
                $error = "Invalid password!";
            }
        } else {
            $error = "User not found!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Login | ANRF-PAIR</title>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link href="vendor/bootstrap-select/css/bootstrap-select.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">

    <style>
        :root {
            --color-primary: #0F4C81;
            --color-secondary: #2563EB;
            --color-accent: #DC2626;
            --color-bg: #F8FAFC;
            --color-border: #E2E8F0;
            --color-text: #0F172A;
            --color-muted: #64748B;
        }

        * { box-sizing: border-box; }

        html, body {
            height: 100%;
            margin: 0;
        }

        body {
            font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
            color: var(--color-text);
            -webkit-font-smoothing: antialiased;
        }

        /* ============================================================
           SHELL + MESH BACKGROUND
           ============================================================ */
        .auth-shell {
            position: relative;
            display: flex;
            min-height: 100vh;
            overflow: hidden;
            background:
                radial-gradient(circle at 12% 18%, #EFF6FF 0%, transparent 42%),
                radial-gradient(circle at 88% 12%, #EDE9FE 0%, transparent 48%),
                radial-gradient(circle at 78% 88%, #F5F3FF 0%, transparent 48%),
                radial-gradient(circle at 15% 92%, #EFF6FF 0%, transparent 42%),
                var(--color-bg);
            animation: shellFade .6s ease both;
        }

        .ambient-shape {
            position: absolute;
            border-radius: 50%;
            filter: blur(90px);
            opacity: .6;
            pointer-events: none;
            z-index: 0;
        }
        .shape-a { width: 460px; height: 460px; top: -160px;  left: -140px;  background: #DBEAFE; }
        .shape-b { width: 420px; height: 420px; top: 22%;     right: -170px; background: #EDE9FE; }
        .shape-c { width: 380px; height: 380px; bottom: -150px; left: 12%;   background: #F5F3FF; }

        /* ============================================================
           FORM PANEL
           ============================================================ */
        .auth-right {
            position: absolute;
            inset: 0;
            z-index: 2;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 24px;
        }

        .auth-card {
            width: 100%;
            max-width: 520px;
            background: rgba(255, 255, 255, .95);
            border: 1px solid var(--color-border);
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, .08);
            padding: 48px 44px;
            -webkit-backdrop-filter: blur(20px);
            backdrop-filter: blur(20px);
            animation: slideUp .6s cubic-bezier(.22,1,.36,1) .1s both;
        }

        .auth-card-header {
            text-align: center;
            margin-bottom: 36px;
        }
        .card-logo {
            width: 56px;
            height: auto;
            margin-bottom: 18px;
            animation: fadeIn .8s ease .2s both;
        }
        .auth-card-header h2 {
            margin: 0 0 8px;
            font-size: 26px;
            font-weight: 800;
            color: var(--color-text);
        }
        .auth-card-header p {
            margin: 0;
            font-size: 14px;
            color: var(--color-muted);
        }

        /* ============================================================
           FORM FIELDS
           ============================================================ */
        .field-group { margin-bottom: 24px; }

        .field-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 13px;
            font-weight: 600;
            color: var(--color-text);
        }

        .input-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            left: 18px;
            display: flex;
            color: var(--color-muted);
            pointer-events: none;
        }
        .input-icon svg { width: 18px; height: 18px; }

        .input-wrap input,
        .input-wrap select {
            width: 100%;
            height: 56px;
            padding: 0 18px 0 50px;
            border-radius: 16px;
            border: 1px solid var(--color-border);
            background: #F8FAFC;
            font-family: inherit;
            font-size: 15px;
            color: var(--color-text);
            transition: border-color .2s ease, box-shadow .2s ease, background .2s ease;
        }
        .input-wrap input::placeholder { color: #94A3B8; }

        .input-wrap select {
            appearance: none;
            -webkit-appearance: none;
            padding-right: 42px;
            cursor: pointer;
        }
        .select-chevron {
            position: absolute;
            right: 18px;
            display: flex;
            color: var(--color-muted);
            pointer-events: none;
        }
        .select-chevron svg { width: 16px; height: 16px; }

        .input-wrap input:focus,
        .input-wrap select:focus {
            outline: none;
            border-color: var(--color-secondary);
            background: #fff;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, .12);
        }

        .toggle-visibility {
            position: absolute;
            right: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border: none;
            background: transparent;
            color: var(--color-muted);
            border-radius: 8px;
            cursor: pointer;
            transition: color .2s ease, background .2s ease;
        }
        .toggle-visibility:hover { color: var(--color-primary); background: #EEF2FF; }
        .toggle-visibility svg { width: 19px; height: 19px; }
        .input-wrap.has-toggle input { padding-right: 48px; }

        /* ============================================================
           REMEMBER / FORGOT ROW
           ============================================================ */
        .field-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 28px;
        }
        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13.5px;
            color: var(--color-text);
            cursor: pointer;
        }
        .checkbox-label input {
            width: 16px;
            height: 16px;
            accent-color: var(--color-primary);
            cursor: pointer;
        }
        .forgot-link {
            font-size: 13.5px;
            font-weight: 600;
            color: var(--color-secondary);
            text-decoration: none;
        }
        .forgot-link:hover { text-decoration: underline; }

        /* ============================================================
           SUBMIT BUTTON
           ============================================================ */
        .btn-submit {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            height: 56px;
            border: none;
            border-radius: 16px;
            background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-secondary) 100%);
            background-size: 160% 160%;
            background-position: 0% 50%;
            color: #fff;
            font-family: inherit;
            font-size: 15.5px;
            font-weight: 700;
            letter-spacing: .2px;
            cursor: pointer;
            transition: transform .3s ease, box-shadow .3s ease, background-position .5s ease;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 28px rgba(15, 76, 129, .25);
            background-position: 100% 50%;
        }
        .btn-submit:active { transform: translateY(0); }
        .btn-submit .btn-arrow { display: flex; transition: transform .25s ease; }
        .btn-submit:hover .btn-arrow { transform: translateX(3px); }
        .btn-submit .btn-arrow svg { width: 18px; height: 18px; }

        .btn-submit.is-loading { color: transparent; pointer-events: none; }
        .btn-submit.is-loading .btn-arrow { opacity: 0; }
        .btn-submit.is-loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            margin: -10px 0 0 -10px;
            border: 2.5px solid rgba(255, 255, 255, .4);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin .7s linear infinite;
        }

        /* ============================================================
           TRUST / SECURITY ROW
           ============================================================ */
        .trust-row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
            gap: 18px;
            margin-top: 28px;
            padding-top: 24px;
            border-top: 1px solid var(--color-border);
        }
        .trust-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            font-weight: 500;
            color: var(--color-muted);
        }
        .trust-item svg { width: 14px; height: 14px; color: #94A3B8; }

        .signup-note {
            margin-top: 20px;
            text-align: center;
            font-size: 13.5px;
            color: var(--color-muted);
        }
        .signup-note a {
            color: var(--color-primary);
            font-weight: 700;
            text-decoration: none;
        }
        .signup-note a:hover { text-decoration: underline; }

        .alert-error {
            background: #FEF2F2;
            color: #B91C1C;
            border: 1px solid #FECACA;
            border-radius: 12px;
            padding: 12px 16px;
            margin-bottom: 20px;
            font-size: 13.5px;
            text-align: center;
        }
        .alert-success {
            background: #F0FDF4;
            color: #166534;
            border: 1px solid #BBF7D0;
            border-radius: 12px;
            padding: 12px 16px;
            margin-bottom: 20px;
            font-size: 13.5px;
            text-align: center;
        }

        /* ============================================================
           ANIMATIONS
           ============================================================ */
        @keyframes shellFade { from { opacity: 0; } to { opacity: 1; } }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(28px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        @media (prefers-reduced-motion: reduce) {
            .auth-shell, .auth-card, .card-logo { animation: none !important; }
        }

        /* ============================================================
           RESPONSIVE
           ============================================================ */
        @media (max-width: 480px) {
            .auth-card { padding: 34px 22px; border-radius: 18px; }
            .trust-row { gap: 12px 16px; }
        }
    </style>

</head>

<body>

<div class="auth-shell">

    <div class="ambient-shape shape-a"></div>
    <div class="ambient-shape shape-b"></div>
    <div class="ambient-shape shape-c"></div>

    <div class="auth-right">
        <div class="auth-card">

            <div class="auth-card-header">
                <img src="logo/logo.png" alt="" class="card-logo">
                <h2>Welcome Back</h2>
                <p>Sign in to access the ANRF-PAIR Admin Portal</p>
            </div>

            <?php
            if ($error !== "") {
                echo "<div class='alert-error'>" . htmlspecialchars($error) . "</div>";
            }
            if (isset($_GET['registered']) && $_GET['registered'] === '1') {
                echo "<div class='alert-success'>Registration successful. Please login.</div>";
            }
            ?>

            <form method="POST" id="loginForm">

                <div class="field-group">
                    <label for="institute">Institute</label>
                    <div class="input-wrap">
                        <span class="input-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 10l9-6 9 6"/><path d="M4 10v9M9 10v9M15 10v9M20 10v9"/><path d="M2 19h20"/></svg>
                        </span>
                        <select id="institute" name="institute" required>
                            <option value="" disabled <?php echo ($institute === '') ? 'selected' : ''; ?>>Select the Institute</option>
                            <?php foreach ($institutes as $prefix => $label): ?>
                                <option value="<?php echo htmlspecialchars($prefix); ?>" <?php echo ($institute === $prefix) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($label); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <span class="select-chevron" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                        </span>
                    </div>
                </div>

                <div class="field-group">
                    <label for="username">Email</label>
                    <div class="input-wrap">
                        <span class="input-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/></svg>
                        </span>
                        <input id="username" type="text" name="username" placeholder="you@institution.edu.in" autocomplete="username" required>
                    </div>
                </div>

                <div class="field-group">
                    <label for="password">Password</label>
                    <div class="input-wrap has-toggle">
                        <span class="input-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="11" width="16" height="9" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
                        </span>
                        <input id="password" type="password" name="password" placeholder="Enter your password" autocomplete="current-password" required>
                        <button type="button" class="toggle-visibility" id="togglePassword" aria-label="Show password" aria-pressed="false">
                            <svg id="eyeIcon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                </div>

                <div class="field-row">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember" <?php echo $rememberMe ? 'checked' : ''; ?>>
                        <span>Remember me</span>
                    </label>
                    <a href="mailto:pairdirecorate@uohyd.ac.in?subject=Password%20Reset%20Request" class="forgot-link">Forgot Password?</a>
                </div>

                <button type="submit" name="login" class="btn-submit" id="submitBtn">
                    <span class="btn-label">Sign In</span>
                    <span class="btn-arrow" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                    </span>
                </button>

            </form>

            <div class="trust-row">
                <span class="trust-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l7 3v6c0 4.5-3 7.5-7 9-4-1.5-7-4.5-7-9V6l7-3z"/></svg>
                    Secure Authentication
                </span>
                <span class="trust-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="11" width="16" height="9" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
                    End-to-End Encryption
                </span>
                <span class="trust-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M8.5 12.5l2.5 2.5 5-5"/></svg>
                    Government Research Portal
                </span>
            </div>

            <div class="signup-note">
                Don't have an account? <a href="register.php">Sign up</a>
            </div>

        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    var toggleBtn = document.getElementById('togglePassword');
    var passwordInput = document.getElementById('password');
    var eyeIcon = document.getElementById('eyeIcon');

    var EYE_OPEN = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
    var EYE_OFF  = '<path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a21.8 21.8 0 0 1 5.06-6.06M9.9 4.24A10.94 10.94 0 0 1 12 4c7 0 11 8 11 8a21.77 21.77 0 0 1-3.22 4.44M14.12 14.12a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>';

    toggleBtn.addEventListener('click', function () {
        var isHidden = passwordInput.type === 'password';
        passwordInput.type = isHidden ? 'text' : 'password';
        eyeIcon.innerHTML = isHidden ? EYE_OFF : EYE_OPEN;
        toggleBtn.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
        toggleBtn.setAttribute('aria-pressed', isHidden ? 'true' : 'false');
    });

    var form = document.getElementById('loginForm');
    var submitBtn = document.getElementById('submitBtn');
    form.addEventListener('submit', function () {
        if (form.checkValidity()) {
            submitBtn.classList.add('is-loading');
        }
    });

});
</script>

</body>
</html>
