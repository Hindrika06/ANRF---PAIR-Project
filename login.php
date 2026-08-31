<?php
$rememberMe = isset($_POST['remember']);
$cookieLifetime = $rememberMe ? (30 * 24 * 60 * 60) : 0;
$isSecure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';

if (session_status() === PHP_SESSION_NONE) {
    if (PHP_VERSION_ID >= 70300) {
        session_set_cookie_params([
            'lifetime' => $cookieLifetime,
            'path' => '/',
            'domain' => '',
            'secure' => $isSecure,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    } else {
        session_set_cookie_params($cookieLifetime, '/; samesite=Lax', '', $isSecure, true);
    }
    session_start();
}

require_once 'config.php';

$error = "";

if (isset($_SESSION['login_error'])) {
    $error = $_SESSION['login_error'];
    unset($_SESSION['login_error']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = "Please fill in all fields.";
    } else {
        $stmt = $pdo->prepare("SELECT id, username, password, institute_prefix, role FROM users WHERE LOWER(username) = LOWER(?) OR username = ?");
        $stmt->execute([$username, $username]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // SEC-03: Authenticate strictly via password_verify(). Plaintext fallback removed.
        if ($row && !empty($row['role']) && password_verify($password, $row['password'])) {
            $role = $row['role'];

            // Generate cryptographically secure post-authentication tab-isolated session ID (prevents session fixation)
            $tabToken = bin2hex(random_bytes(32));
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_write_close();
            }
            session_id($tabToken);
            session_start();

            $_SESSION['tab_token']        = $tabToken;
            $_SESSION['user_id']          = $row['id'];
            $_SESSION['username']         = $row['username'];
            $_SESSION['institute_prefix'] = $row['institute_prefix'];
            $_SESSION['role']             = $role;
            $_SESSION['active_prefix']    = $row['institute_prefix'];
            $_SESSION['LAST_ACTIVITY']    = time();

            header("Location: admin/dashboard.php?tab_token=" . urlencode($tabToken));
            exit();
        } else {
            // SEC-04 / Security Hardening: Generic error message to prevent user enumeration
            $error = "Invalid username/email or password.";
        }
    }
}

$pageTitle = "Admin Portal Login | ANRF–PAIR Project";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Load custom fonts & icons -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="assets/css/font-awesome.css" rel="stylesheet" type="text/css">
    <style>
        * {
            box-sizing: border-box;
        }
        body.login-page-body {
            background: #024283; /* Rich blue background matching the first image */
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            font-family: 'Nunito Sans', 'Poppins', sans-serif;
            overflow: hidden; /* Prevent body-level scrolling */
        }
        .login-container {
            width: 100%;
            max-width: 400px; /* Reduced width for a more compact card */
            padding: 15px;
        }
        .login-card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.25);
            padding: 30px 35px; /* Reduced padding to avoid scrolling */
            text-align: center;
        }
        .login-logo {
            max-width: 140px; /* Reduced logo size */
            height: auto;
            margin: 0 auto;
            display: block;
        }
        .login-title {
            color: #b21e1e; /* Bold red "SPOKE ADMIN" */
            font-weight: 800;
            font-size: 20px; /* Slightly smaller size */
            margin-top: 12px;
            margin-bottom: 2px;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }
        .login-subtitle {
            color: #4b5563; /* Dark gray "SIGN IN TO DASHBOARD" */
            font-weight: 700;
            font-size: 12px; /* Slightly smaller size */
            letter-spacing: 0.03em;
            margin-top: 0;
            margin-bottom: 25px; /* Reduced spacing */
            text-transform: uppercase;
        }
        .form-group {
            text-align: left;
            margin-bottom: 18px; /* Compact field margins */
        }
        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #94a3b8; /* Soft blue-gray label color */
            margin-bottom: 6px;
        }
        .form-control-login {
            width: 100%;
            height: 44px; /* Reduced field height */
            background: #eef5fc; /* Soft blue background color for fields */
            border: 1.2px solid #cbd5e1;
            border-radius: 6px;
            padding: 8px 14px;
            font-size: 14px;
            color: #0f172a;
            outline: none;
            transition: all 0.2s ease;
        }
        .form-control-login::placeholder {
            color: #94a3b8;
            opacity: 0.7;
        }
        .form-control-login:focus {
            background: #ffffff;
            border-color: #024283;
            box-shadow: 0 0 0 3px rgba(2, 66, 131, 0.15);
        }
        .password-wrapper {
            position: relative;
            width: 100%;
        }
        .password-wrapper .form-control-login {
            padding-right: 42px;
        }
        .password-toggle-btn {
            position: absolute;
            right: 6px;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            padding: 6px 8px;
            margin: 0;
            cursor: pointer;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            transition: color 0.2s ease, background-color 0.2s ease;
            font-size: 15px;
            line-height: 1;
            z-index: 2;
        }
        .password-toggle-btn:hover,
        .password-toggle-btn:focus {
            color: #024283;
            outline: none;
            background-color: rgba(2, 66, 131, 0.08);
        }
        .btn-login-custom {
            width: 100%;
            height: 44px; /* Reduced button height */
            background: #b21e1e; /* Solid dark red button matching image */
            border: none;
            border-radius: 6px;
            color: #ffffff;
            font-size: 14.5px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-top: 8px;
            box-shadow: 0 4px 10px rgba(178, 30, 30, 0.2);
        }
        .btn-login-custom:hover {
            background: #991818;
            box-shadow: 0 6px 14px rgba(153, 24, 24, 0.3);
            transform: translateY(-1px);
        }
        .btn-login-custom:active {
            transform: translateY(0);
        }
        .alert-custom {
            background: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fee2e2;
            border-radius: 6px;
            padding: 10px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 15px;
            text-align: center;
        }
    </style>
</head>
<body class="login-page-body">
<div class="login-container">
    <div class="login-card">
        <!-- Center image logo -->
        <img src="2.png" alt="ANRF-PAIR Logo" class="login-logo">
        <h2 class="login-title">ADMIN PORTAL</h2>
        <p class="login-subtitle">SIGN IN TO DASHBOARD</p>

        <?php if ($error !== ""): ?>
            <div class="alert-custom"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if (isset($_GET['timeout']) && $_GET['timeout'] === '1'): ?>
            <div class="alert-custom">Your session has expired due to 30 minutes of inactivity. Please log in again.</div>
        <?php endif; ?>

        <?php if (isset($_GET['registered']) && $_GET['registered'] === '1'): ?>
            <div class="alert-custom" style="background:#ecfdf5; color:#047857; border-color:#d1fae5; margin-bottom: 15px;">Registration successful. Please login.</div>
        <?php endif; ?>

        <form method="POST" autocomplete="off">
            <!-- Email field -->
            <div class="form-group">
                <label class="form-label" for="username">Email</label>
                <input id="username" type="text" name="username" class="form-control-login" placeholder="Username" autocomplete="off" required>
            </div>

            <!-- Password field -->
            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <div class="password-wrapper">
                    <input id="password" type="password" name="password" class="form-control-login" placeholder="Password" autocomplete="new-password" required>
                    <button type="button" id="togglePasswordBtn" class="password-toggle-btn" aria-label="Show password" title="Show password">
                        <i id="passwordToggleIcon" class="fa fa-eye" aria-hidden="true"></i>
                    </button>
                </div>
            </div>

            <!-- Sign in button -->
            <button type="submit" class="btn-login-custom">
                Sign In
            </button>
        </form>
    </div>
</div>
<script>
window.addEventListener('load', function () {
    document.getElementById('username').value = '';
    document.getElementById('password').value = '';
});

document.addEventListener('DOMContentLoaded', function () {
    const passwordInput = document.getElementById('password');
    const toggleBtn = document.getElementById('togglePasswordBtn');
    const toggleIcon = document.getElementById('passwordToggleIcon');

    if (passwordInput && toggleBtn && toggleIcon) {
        toggleBtn.addEventListener('click', function (e) {
            e.preventDefault();
            const isPassword = passwordInput.type === 'password';

            if (isPassword) {
                passwordInput.type = 'text';
                toggleIcon.className = 'fa fa-eye-slash';
                toggleBtn.setAttribute('aria-label', 'Hide password');
                toggleBtn.setAttribute('title', 'Hide password');
            } else {
                passwordInput.type = 'password';
                toggleIcon.className = 'fa fa-eye';
                toggleBtn.setAttribute('aria-label', 'Show password');
                toggleBtn.setAttribute('title', 'Show password');
            }
        });
    }
});
</script>
</body>
</html>
