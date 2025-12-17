<?php

require "../dbconnection.php";
require "../utilities/utils.php";
require "../utilities/queries.php";
require_once '../model/user.php';
session_start();
checkIfLoggedIn();



if (isset($_POST['loginBtn'])) {
    login($_POST['email'], $_POST['password']);
}

function checkIfLoggedIn()
{
    if (isset($_SESSION['User'])) {
        redirectByRole($_SESSION['User']->getRole());
    }
}

function redirectByRole($role)
{
    switch ($role) {
        case 'admin':
        case 'technician':
            headto("../pages/dashboard.php");
            exit();
        case 'faculty':
        case 'student':
        default:
            headto("../pages/main.php");
            exit();
    }
}

function login($input, $password)
{
    $input = trim($input);
    $password = trim($password);

    if (empty($input) || empty($password)) {
        $_SESSION['alertMessage'] = "Please fill in all fields.";
        return;
    }

    // Use the fixed getHashedPassword from queries.php (supports username or email)
    $hashedPassword = getHashedPassword($input);

    if (!$hashedPassword) {
        $_SESSION['alertMessage'] = "Invalid email/username or password. Please try again.";
        echo $_SESSION['alertMessage'];
        // Debug: Uncomment below to see if user exists
        // $_SESSION['alertMessage'] .= " (User not found for: " . htmlspecialchars($input) . ")";
        return;
    }

    // Verify password and check if account is verified
    if (password_verify($password, $hashedPassword) && verifiedAccount($input)) {
        $user = getUser($input);

        if ($user) {
            // Regenerate session ID for security
            session_regenerate_id(true);
            $_SESSION["loggedInUser"] = getUserId($user->getEmail());
            $_SESSION['User'] = $user;

            // Redirect based on role
            redirectByRole($user->getRole());
        } else {
            $_SESSION['alertMessage'] = "Account not found.";
        }
    } else if (verifiedAccount($input) != 1) {
        $_SESSION['alertMessage'] = "Account not found or not activated. Please check your email for verification.";
    } else {
        $_SESSION['alertMessage'] = "Invalid email/username or password. Please try again.";
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Computer Laboratory Management System</title>
    <link rel="stylesheet" href="../asset/style.css">
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
                        <?php echo htmlspecialchars($_SESSION['alertMessage']);
                        unset($_SESSION['alertMessage']); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="form">
                    <div class="form-group">
                        <label>Email or Username <span class="required">*</span></label>
                        <div class="input-wrapper">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <input type="text" name="email" placeholder="Enter your email or username" class="input" required>
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

                    <p class="login-text">Don't have an account? <a href="../pages/registration.php" class="link">Register here</a></p>
                </form>
            </div>
        </div>
    </main>
</body>

</html>