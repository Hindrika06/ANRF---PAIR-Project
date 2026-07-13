<?php
$rememberMe = isset($_POST['remember']);
session_set_cookie_params($rememberMe ? (30 * 24 * 60 * 60) : 0);
session_start();

require_once 'config.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = $pdo->prepare("SELECT id, username, password, institute_prefix, role FROM users WHERE username = ?");
    $stmt->execute([$username]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $role = $row['role'] ?? 'admin';

        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id']          = $row['id'];
            $_SESSION['username']         = $row['username'];
            $_SESSION['institute_prefix'] = $row['institute_prefix'];
            $_SESSION['role']             = $role;
            $_SESSION['active_prefix']    = $row['institute_prefix'];

            header("Location: admin/publications.php");
            exit();
        } elseif ($password === $row['password']) {
            // Migrate plain-text password to hash on first login
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            $update  = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
            $update->execute([$newHash, $row['id']]);

            $_SESSION['user_id']          = $row['id'];
            $_SESSION['username']         = $row['username'];
            $_SESSION['institute_prefix'] = $row['institute_prefix'];
            $_SESSION['role']             = $role;
            $_SESSION['active_prefix']    = $row['institute_prefix'];

            header("Location: admin/publications.php");
            exit();
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "User not found!";
    }
}

$pageTitle = "Login | ANRF–PAIR Project";
$activePage = "login";
?>

<style>
    /* Clean custom overrides for login page matching demo2 theme */
    .login-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
        padding: 40px 35px;
        margin-top: 20px;
        margin-bottom: 20px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .login-card-header {
        text-align: center;
        margin-bottom: 30px;
    }
    
    .login-logo {
        max-height: 65px;
        width: auto;
        margin-bottom: 15px;
    }
    
    .login-card-header h3 {
        font-size: 24px;
        font-weight: 700;
        color: #024283; /* Theme Primary Blue */
        margin: 0 0 8px 0;
    }
    
    .login-card-header p {
        font-size: 14px;
        color: #64748b;
        margin: 0;
    }
    
    /* Custom form styling with Bootstrap Input Groups */
    .input-group-login {
        width: 100%;
        display: flex !important;
        align-items: stretch;
    }
    
    .input-group-login .input-group-addon {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-right: none;
        border-radius: 10px 0 0 10px;
        color: #64748b;
        font-size: 16px;
        width: 46px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }
    
    .input-group-login .form-control {
        height: 48px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        font-size: 14px;
        color: #0f172a;
        box-shadow: none;
        transition: all 0.2s ease;
        padding-left: 12px;
        margin: 0;
        flex: 1;
    }
    
    /* For username input where there's no button on the right */
    .input-group-login input#username.form-control {
        border-radius: 0 10px 10px 0 !important;
    }
    
    /* For password input where there's a button on the right */
    .input-group-login input#password.form-control {
        border-radius: 0 !important;
        border-right: none;
    }
    
    .input-group-login .input-group-btn {
        display: flex;
        margin: 0;
    }
    
    .input-group-login .toggle-password-btn {
        height: 48px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-left: none;
        border-radius: 0 10px 10px 0 !important;
        color: #64748b;
        font-size: 16px;
        padding: 0 16px;
        box-shadow: none;
        outline: none !important;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .input-group-login .toggle-password-btn:hover {
        color: #024283;
        background: #f1f5f9;
    }
    
    /* Focus Highlights */
    .input-group-login:focus-within .input-group-addon {
        border-color: #024283;
        background: #ffffff;
        color: #024283;
    }
    
    .input-group-login:focus-within .form-control {
        border-color: #024283;
        background: #ffffff;
    }
    
    .input-group-login:focus-within .toggle-password-btn {
        border-color: #024283;
        background: #ffffff;
    }
    
    /* Remember / Forgot row */
    .form-group-row-custom {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: 15px;
        margin-bottom: 25px;
    }
    
    .remember-me-custom {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        color: #475569;
        font-weight: 500;
        cursor: pointer;
        margin: 0;
    }
    
    .remember-me-custom input {
        width: 15px;
        height: 15px;
        cursor: pointer;
        margin: 0;
    }
    
    .forgot-link-custom {
        font-size: 13px;
        font-weight: 600;
        color: #bc2121; /* Theme primary red */
        text-decoration: none;
    }
    
    .forgot-link-custom:hover {
        text-decoration: underline;
        color: #9e1b1b;
    }
    
    /* Login Button */
    .btn-login-custom {
        width: 100%;
        height: 50px;
        background: #024283; /* Theme Blue */
        border: none;
        border-radius: 10px;
        color: #ffffff;
        font-size: 15px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.25s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 12px rgba(2, 66, 131, 0.15);
    }
    
    .btn-login-custom:hover {
        background: #bc2121; /* Dynamic transition to Theme Red */
        box-shadow: 0 6px 16px rgba(188, 33, 33, 0.25);
        transform: translateY(-1px);
        color: #ffffff;
        text-decoration: none;
    }
    
    .btn-login-custom:active {
        transform: translateY(0);
    }
    
    /* Loading Spinner classes */
    .btn-login-custom.is-loading {
        color: transparent !important;
        pointer-events: none;
        position: relative;
    }
    
    .btn-login-custom.is-loading::after {
        content: '';
        position: absolute;
        width: 20px;
        height: 20px;
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-top-color: #ffffff;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }
    
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
    
    /* Footer Sign Up link */
    .login-footer-custom {
        margin-top: 25px;
        padding-top: 20px;
        border-top: 1px solid #f1f5f9;
        text-align: center;
        font-size: 13.5px;
        color: #64748b;
    }
    
    .login-footer-custom a {
        color: #024283;
        font-weight: 700;
        text-decoration: none;
    }
    
    .login-footer-custom a:hover {
        text-decoration: underline;
    }
</style>

<body class="page-sub-page">
<div class="wrapper">

    <!-- Header -->
    <?php include 'header.php'; ?>

    <!-- Breadcrumbs -->
    <div class="container">
        <ol class="breadcrumb" style="margin-bottom: 0px;">
            <li><a href="index.php">Home</a></li>
            <li class="active">Login</li>
        </ol>
    </div>

    <!-- Page Content -->
    <div id="page-content">
        <section id="login-section" style="margin-top: 10px;">
            <div class="block" style="background-color: #fff; padding: 40px 0;">
                <div class="container">
                    <div class="row">
                        <div class="col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">
                            <div class="login-card">
                                
                                <div class="login-card-header">
                                    <img src="2.png" alt="ANRF-PAIR Logo" class="login-logo">
                                    <h3>Welcome Back</h3>
                                    <p>Sign in to access the ANRF-PAIR Admin Portal</p>
                                </div>
                                
                                <?php if ($error !== ""): ?>
                                    <div class="alert alert-danger text-center" style="font-weight: 600; font-size: 13.5px; border-radius: 8px; margin-bottom: 20px;"><?= htmlspecialchars($error) ?></div>
                                <?php endif; ?>
                                
                                <?php if (isset($_GET['registered']) && $_GET['registered'] === '1'): ?>
                                    <div class="alert alert-success text-center" style="font-weight: 600; font-size: 13.5px; border-radius: 8px; margin-bottom: 20px;">Registration successful. Please login.</div>
                                <?php endif; ?>

                                <form method="POST" id="loginForm">
                                    <div class="form-group">
                                        <label for="username">Email Address</label>
                                        <div class="input-group input-group-login">
                                            <span class="input-group-addon"><i class="fa fa-envelope-o"></i></span>
                                            <input id="username" type="text" name="username" class="form-control" placeholder="you@institution.edu.in" autocomplete="username" required>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="password">Password</label>
                                        <div class="input-group input-group-login">
                                            <span class="input-group-addon"><i class="fa fa-lock"></i></span>
                                            <input id="password" type="password" name="password" class="form-control" placeholder="Enter your password" autocomplete="current-password" required>
                                            <span class="input-group-btn">
                                                <button class="btn btn-default toggle-password-btn" type="button" id="togglePassword" aria-label="Toggle password">
                                                    <i class="fa fa-eye" id="eyeIcon"></i>
                                                </button>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="form-group-row-custom">
                                        <label class="remember-me-custom">
                                            <input type="checkbox" name="remember" <?= $rememberMe ? 'checked' : '' ?>> Remember me
                                        </label>
                                        <a href="mailto:pairdirecorate@uohyd.ac.in?subject=Password%20Reset%20Request" class="forgot-link-custom">Forgot Password?</a>
                                    </div>

                                    <button type="submit" name="login" class="btn-login-custom" id="submitBtn">
                                        Sign In <i class="fa fa-arrow-right" style="margin-left: 6px;"></i>
                                    </button>
                                </form>

                                <div class="login-footer-custom">
                                    Don't have an account? <a href="admin/register.php">Sign up</a>
                                </div>
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div><!-- end #page-content -->

    <!-- Footer -->
    <?php include 'footer.php'; ?>

</div><!-- end .wrapper -->

<script>
document.addEventListener('DOMContentLoaded', function () {
    var toggleBtn = document.getElementById('togglePassword');
    var passwordInput = document.getElementById('password');
    var eyeIcon = document.getElementById('eyeIcon');

    toggleBtn.addEventListener('click', function () {
        var isHidden = passwordInput.type === 'password';
        passwordInput.type = isHidden ? 'text' : 'password';
        eyeIcon.className = isHidden ? 'fa fa-eye-slash' : 'fa fa-eye';
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
