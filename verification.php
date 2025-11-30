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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify</title>
</head>
<body>
    <h2>Verify your Account</h2>

    <!--<p>Verification sent to: <?php echo $email?> please check your inbox</p> -->

    <form method="POST">
        <input type="text" name="otp_code" placeholder="Enter OTP" maxlength="6"> <br><br>
        <button type="submit" name="verify">Verify</button>
        <button type="submit" name="resend">Resend OTP</button><br><br>
    </form>
    <?php displayMessage();?>
</body>
</html>