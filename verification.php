<?php 
    session_start();
    require "utilities/utils.php";
    require "utilities/queries.php";
    require_once "dbconnection.php";


         $email = $_SESSION['email'];
         if(isset($_POST['verify'])){
            $enteredOtp = $_POST['otp_code'] ?? '';
            $result = verifyOtpInput($enteredOtp);
            
            if($result['status']) {
                $_SESSION['alertMessage'] = $result['message'];
                headto($result['redirect']);
            } else {
                $_SESSION['alertMessage'] = $result['message'];
                clearPost();

            }
        }
            if(isset($_POST['resend'])){
                $result = resendOtp();
                $_SESSION['alertMessage'] = $result['message'];
                clearPost();
            }
           
?>

<<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Account - Computer Laboratory Management System</title>
    <link rel="stylesheet" href="asset/style.css">
</head>
<body>
    <main>
        <div class="container verification-container">
            <!-- Left Side Panel -->
            <div class="side-panel">
                <div class="verification-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h1>Verification Required</h1>
                <p>We've sent a verification code to your email address. Please check your inbox and enter the code to activate your account.</p>
            </div>
            
            <!-- Right Form Panel -->
            <div class="form-panel">
                <div class="form-header">
                    <h2 class="subtitle">Verify Your Account</h2>
                    <p class="form-description">Enter the 6-digit code sent to <strong><?php echo htmlspecialchars($email); ?></strong></p>
                </div>
                
                <?php if(isset($_SESSION['alertMessage'])): ?>
                    <div class="alert <?php echo (strpos($_SESSION['alertMessage'], 'success') !== false || strpos($_SESSION['alertMessage'], 'verified') !== false) ? 'alert-success' : 'alert-error'; ?>">
                        <?php echo $_SESSION['alertMessage']; unset($_SESSION['alertMessage']); ?>
                    </div>
                <?php endif; ?> 
                
                <form method="POST" class="form verification-form">
                    <div class="form-group">
                        <label>Verification Code <span class="required">*</span></label>
                        <div class="input-wrapper">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                            </svg>
                            <input type="text" name="otp_code" placeholder="Enter 6-digit code" class="input otp-input" maxlength="6" pattern="[0-9]{6}" autocomplete="off">
                        </div>
                        <small class="input-hint">Please enter the 6-digit code from your email</small>
                    </div>
                    
                    <button type="submit" name="verify" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Verify Account
                    </button>
                    
                    <div class="divider">
                        <span>Didn't receive the code?</span>
                    </div>
                    
                    <button type="submit" name="resend" class="btn btn-secondary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Resend Code
                    </button>
                    
                    <p class="login-text">Having trouble? <a href="login.php" class="link">Back to Login</a></p>
                </form>
            </div>
        </div>
    </main>
</body>
</html>