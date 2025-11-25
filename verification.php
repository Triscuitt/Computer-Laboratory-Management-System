<?php 
    require "utilities/utils.php";
    require "utilities/db.php";
    require_once "dbconnection.php";
    session_start();

         $email = $_SESSION['email'];
        if (isset($_POST['verify'])) {
            $otp = $_SESSION['otp'];
            $otp_code = $_POST['otp_code'];

            if ($otp != $otp_code) {
                $_SESSION['popUpMessage'] = "Invalid OTP code";
            } else {
                verifyOtp($email);
                $_SESSION['popUpMessage'] = "Registration Succesful You can now <a href=login.php>Login</a>. ";
                headto('registration.php');
                exit;
            }
        } 
    

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify</title>
</head>
<body>
    <h2>Verify your Account</h2>

    <p>Your Email is: <?php echo $email?></p>

    <form method="POST">
        <input type="text" name="otp_code" placeholder="Enter OTP"> <br><br>
        <button type="submit" name="verify">Verify</button>
        <button type="submit" name="resend">Resend OTP</button>
    </form>
</body>
</html>