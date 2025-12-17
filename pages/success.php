<?php 
    session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification Success - Computer Laboratory Management System</title>
    <link rel="stylesheet" href="../asset/style.css">
</head>
<body>
    <main>
        <div class="success-container">
            <div class="success-card">
                <div class="success-icon-wrapper">
                    <div class="success-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                
                <h1 class="success-title">Verification Successful!</h1>
                <p class="success-message">Your account has been successfully verified and activated. You can now access all features of the Computer Laboratory Management System.</p>
                
                <div class="success-actions">
                    <a href="login.php" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                        </svg>
                        Proceed to Login
                    </a>
                </div>
                
                <div class="success-footer">
                    <p class="success-footer-text">Welcome to our laboratory management platform!</p>
                </div>
            </div>
        </div>
    </main>
</body>
</html>