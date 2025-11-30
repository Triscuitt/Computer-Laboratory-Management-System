<?php 
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;

    require "phpmailer/vendor/autoload.php";

    function sendOtpEmail($email, $otp, $username){
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->SMTPAuth = true;
    $mail->Host = "smtp.gmail.com";
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    $mail->SMTPKeepAlive = true;
    $mail->Username = "computerlaboratorymanagement@gmail.com";
    $mail->Password = "bezs puqx ssbs xjgw";
    $mail->isHTML(true);

     $mail->setFrom("computerlaboratorymanagement@gmail.com", "Computer Laboratory Management System");
        $mail->addAddress($email);
        $mail->Subject = "Account Activation";
        $mail->Body = "Dear <strong>$username</strong>, <br><br>
            To complete your verification process, please use the One-Time Password (OTP) provided below: <br><br>
            Verification Code: <strong>$otp</strong> <br><br>
            This code is valid for the next 5 minutes. Please do not share this OTP with anyone for security reasons. <br>
            If you did not request this verification, please ignore this email or contact our support team immediately.<br><br>
            Thanks, <br>
            Comlabsystem Team.
            ";

        try {
            $mail->send();
            return true;
        } catch (Exception $e) {
            return false;
        } 
    }

function resendOtp(){
    if(!isset($_SESSION['email']) || !isset($_SESSION['username'])){
        return [
            'status' => false,
            'message' => 'Session expired. please register again'
        ];
    }

    if(isset($_SESSION['otp_last_sent'])) {
        $timeElapsed = time() - $_SESSION['otp_last_sent'];
        if($timeElapsed < 60) {
            $remainingTime = 60 - $timeElapsed;
            return [
                'status' => false,
                'message' => 'Please wait ' . $remainingTime . ' seconds before requesting a new OTP'
            ];
        }
    }
    
    $email = $_SESSION['email'];
    $username = $_SESSION['username'];
    $userId = getUserId($email);
    $otp = generateOtp($userId);

    //$_SESSION['otp_created_at'] = time();
    $_SESSION['otp_last_sent'] = time();

    $resendCode = sendOtpEmail($email, $otp, $username);

    if($resendCode) {
        return [
            'status' => true,
            'message' => 'OTP has been resent successfully! Please check your email ' . $email,
        ];
    } else {
        return [
            'status' => false,
            'message' => 'Failed to send Otp. Please try again.'
        ];
    }
}

function verifyOtpInput($enteredOtp) {
    if(!isset($_SESSION['email'])) {
        return [
            'status' => false,
            'message' =>'Session Expired. Please register again.',
            'redirect' => 'registration.php'
        ];
    }

    if(empty($enteredOtp) || strlen($enteredOtp) !== 6) {
        return [
            'status' => false,
            'message' => 'Please enter a valid 6-digit OTP.'
        ];
    }

     $email = $_SESSION['email'];
     $userId = getUserId($email);

     if(!$userId){
        return [
            'status' => false,
            'message' => 'User not found. Please register again.',
            'redirect' => 'registration.php'
        ];
     }

     $storedHash = verifyOtp($userId);

     if($storedHash && password_verify($enteredOtp, $storedHash)) {
        updateAccount($email);

        // unset($_SESSION['otp_created_at']);
         unset($_SESSION['otp_last_sent']);
        
        return [
            'status' => true,
            'message' => 'Account verified successfully!',
            'redirect' => 'success.php'
        ];
     } else {
          return [
            'status' => false,
            'message' => 'Invalid or expired OTP. Please try again.'
            
        ];
     }

     
}
?>