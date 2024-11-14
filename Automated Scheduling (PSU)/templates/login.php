<?php
include '../config/functions.php';
handleLogin();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Automated Scheduling System</title>

    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../statics/css/login.css">
</head>
<body>
    <div class="top-nav">
        <img src="../statics/img/psu-logo.png" class="psu-logo" height="80px">
        <img src="../statics/img/psu-name.png" class="psu-name" height="75px">
    </div>
    <div class="form-container">
        <div class="col col-1">
            <div class="login-name">
                <h1> AUTOMATED <br> SCHEDULING <br> SYSTEM </h1>
                <h2>Lingayen Campus</h2>
                <hr>
                <p>
                    This thesis project, <strong>"Automated Scheduling System for Pangasinan State University - Lingayen Campus"</strong>,
                    was created and developed by <strong>John Paul Viray</strong> and <strong>Flordeliza Diaz</strong>,
                    as part of the requirements for the Bachelor of Science in Mathematics major in CIT program,
                    under the Department of Mathematics, Pangasinan State University.
                </p>
            </div>
        </div>
 
        <div class="col col-2">
            <div class="login-form">
                <form id="loginForm" action="" method="post">
                    <h2 class="form-title">Welcome Back</h2>
                    <h4 class="form-featured">Please Login to your Account.</h4>
                    <div class="input-box">
                        <input type="text" id="username" name="username" class="input-field" placeholder="User Name" required>
                        <i class='bx bxs-user icon'></i>
                    </div>
                    <div class="input-box">
                        <input type="password" id="password" name="password" class="input-field" placeholder="Password" required>
                        <i class='bx bxs-lock-alt icon'></i>
                    </div>
                    <div class="forgot-pass">
                        <a href="#">Forgot Password?</a>
                    </div>
                    <div class="input-box">
                        <button type="submit" name="login" value="Login" class="input-login">
                            <span>Login</span>
                        </button>
                    </div>
                    <div class="policy-help">
                        <a href="#">Policy</a>
                        <a href="#">Help</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>