<?php
session_start();

require_once 'config/db.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = $pdo->prepare("SELECT id, username, password, institute_prefix FROM users WHERE username = ?");
    $stmt->execute([$username]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {

        if ($password === $row['password']) {

            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['institute_prefix'] = $row['institute_prefix'];

            header("Location: publications.php");
            exit();

        } else {
            $error = "Invalid Password!";
        }

    } else {
        $error = "User not found!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login | Spoken Institute</title>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="vendor/bootstrap-select/css/bootstrap-select.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">

    <style>
        body{
            background:#024283;
            font-family:Segoe UI,sans-serif;
        }

        .card{
            border:none;
            padding:25px;
            box-shadow:0 10px 30px rgba(0,0,0,.15);
        }

        h3{
            color:#bc2121;
            font-weight:bold;
        }

        h4{
            color:#555;
            margin-bottom:25px;
            text-transform:uppercase;
        }

        .form-control{
            height:48px;
            border-radius:0;
        }

        .btn-primary{
            width:100%;
            height:48px;
            border-radius:0;
            background:#bc2121;
            border:none;
            font-weight:600;
        }

        .btn-primary:hover{
            background:#991818;
        }

        .alert-error{
            background:#f8d7da;
            color:#842029;
            border:1px solid #f5c2c7;
            padding:10px;
            margin-bottom:15px;
            text-align:center;
        }
    </style>

</head>

<body>

<div class="fix-wrapper">
    <div class="container">
        <div class="row justify-content-center">

            <div class="col-lg-5 col-md-6">

                <div class="card">

                    <div class="text-center mb-4">
                        <img src="logo/logo.png" class="logo-auth" alt="">
                        <h3>SPOKEN INSTITUTE</h3>
                        <h4>Sign In To Dashboard</h4>
                    </div>

                    <?php
                    if($error!=""){
                        echo "<div class='alert-error'>$error</div>";
                    }
                    ?>

                    <form method="POST">

                        <div class="mb-3">
                            <label>Email</label>
                            <input
                                type="text"
                                name="username"
                                class="form-control"
                                placeholder="Enter Email"
                                required>
                        </div>

                        <div class="mb-4">
                            <label>Password</label>
                            <input
                                type="password"
                                name="password"
                                class="form-control"
                                placeholder="Enter Password"
                                required>
                        </div>

                        <button type="submit" name="login" class="btn btn-primary">
                            Sign In
                        </button>

                    </form>

                </div>

            </div>

        </div>
    </div>
</div>

</body>
</html>