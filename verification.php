<?php 
    require "utilities/utils.php";
    require "utilities/db.php";
    require_once "dbconnection.php";
    session_start();


         $email = $_SESSION['email'];
         if(isset($_POST['verify'])){
            $enteredOtp = $_POST['otp_code'];
            $userId = getUserId($email);
            $storedHash = verifyOtp($userId);
            if(password_verify($enteredOtp, $storedHash)) {
                updateAccount($email);
                headto('success.php');
                
                
            }else {
                $_SESSION['popUpMessage'] = "Invalid or expired OTP";
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

    <p>Verification sent to: <?php echo $email?> please check your inbox</p>

    <form method="POST">
        <input type="text" name="otp_code" placeholder="Enter OTP"> <br><br>
        <button type="submit" name="verify">Verify</button>
        <button type="submit" name="resend">Resend OTP</button><br><br>
    </form>
    <?php displayMessage()?>
</body>
</html>
