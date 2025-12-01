<?php
session_start();
require "dbconnection.php";
require "utilities/utils.php";
require "utilities/queries.php";
require_once 'model/user.php';

checkIfLoggedIn();
checkPost();

function checkIfLoggedIn()
{
    if (isset($_SESSION['loggedInUser'])) {
        headto("main.php");
    }
}

function checkPost()
{
    if (isset($_POST['loginBtn'])) {
        login($_POST['email'], $_POST['password']);
    }
}

function login($email, $password)
{
    $email = trim($email);
    $password = trim($password);

    if (password_verify($password, getHashedPassword($email)) && verifiedAccount($email)) {
        session_regenerate_id(true);
        $_SESSION["loggedInUser"] = getUserId($email);
        $_SESSION['User'] = getUser($email);
        headto("main.php");
        exit;
    } else if (!verifiedAccount($email)) {
        $_SESSION['alertMessage'] = "Account not found or not activated.";
    } else {
        $_SESSION['alertMessage'] = "Invalid username or password";
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Computer Laboratory Management System</title>
    <link rel="stylesheet" href="asset/style.css">
</head>

<body>
    <main>
        <div class="container login-container">
            <!-- Left Side Panel -->
            <div class="side-panel">
                <h1>Computer Laboratory Management System</h1>
                <p>Welcome back! Login to access your laboratory management dashboard.</p>
            </div>

            <!-- Right Form Panel -->
            <div class="form-panel">
                <div class="form-header">
                    <h2 class="subtitle">Welcome Back</h2>
                    <p class="form-description">Enter your credentials to access your account</p>
                </div>

                <?php if (isset($_SESSION['alertMessage'])): ?>
                    <div class="alert alert-error">
                        <?php echo $_SESSION['alertMessage'];
                        unset($_SESSION['alertMessage']); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="form">
                    <div class="form-group">
                        <label>Email <span class="required">*</span></label>
                        <div class="input-wrapper">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <input type="email" name="email" placeholder="Enter your email address" class="input" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Password <span class="required">*</span></label>
                        <div class="input-wrapper">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            <input type="password" name="password" placeholder="Enter your password" class="input" required>
                        </div>
                    </div>

                    <button type="submit" name="loginBtn" class="btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                        </svg>
                        Login
                    </button>

                    <p class="login-text">Don't have an account? <a href="registration.php" class="link">Register here</a></p>
                </form>
            </div>
        </div>
    </main>
</body>

</html>