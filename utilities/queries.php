<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;


require_once "dbconnection.php";
require "phpmailer/vendor/autoload.php";


function getHashedPassword($email)
{
    $conn = getConnection();
    $stmt = $conn->prepare("SELECT password FROM users WHERE email = ? LIMIT 1");

    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();
    $hashedPassword = null;

    if ($row = $result->fetch_assoc()) {
        $hashedPassword = $row['password'];
    }

    $stmt->close();
    $conn->close();

    return $hashedPassword;
}

function getUserId($email)
{
    $conn = getConnection();
    $stmt = $conn->prepare("SELECT id FROM users where email = ?");

    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();
    $output = null;

    if ($row = $result->fetch_assoc()) {
        $output = $row['id'];
    }

    $stmt->close();
    $conn->close();

    return $output;
}

/*function checkUsername($fname, $midname, $lname, $suffix)
{
    $conn = getConnection();
    $stmt = $conn->prepare("SELECT fname, midname, lname, suffix FROM users WHERE fname = ? AND midname = ? AND lname = ? AND suffix = ? LIMIT 1");
    $stmt->bind_param('ssss', $fname, $midname, $lname, $suffix);
    $stmt->execute();

    $result = $stmt->get_result();
    $exists = $result->num_rows > 0;

    $stmt->close();
    $conn->close();

    return $exists;
} */

function checkEmail($email)
{
    $conn = getConnection();
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param('s', $email);
    $stmt->execute();

    $result = $stmt->get_result();
    $exists = $result->num_rows > 0;

    $stmt->close();
    $conn->close();

    return $exists;
}

function checkStudentNumber($student_number)
{
    $conn = getConnection();
    $stmt = $conn->prepare("SELECT id FROM users WHERE student_number = ? LIMIT 1");
    $stmt->bind_param('s', $student_number);
    $stmt->execute();

    $result = $stmt->get_result();
    $exists = $result->num_rows > 0;

    $stmt->close();
    $conn->close();

    return $exists;
}

function createUser($student_number, $first_name, $middle_name, $last_name, $suffix, $username, $email, $hashedPassword)
{
    $conn = getConnection();
    $stmt = $conn->prepare("INSERT INTO users (student_number, first_name, middle_name, last_name, suffix, username, email, password, account_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)");
    $stmt->bind_param("ssssssss", $student_number, $first_name, $middle_name, $last_name, $suffix, $username, $email, $hashedPassword);
    $result = $stmt->execute();
    $userId = $conn->insert_id;


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

    if ($result) {
        $otp = generateOtp($userId);
        $_SESSION['otp'] = $otp;
        $_SESSION['email'] = $email;
        $mail->setFrom("computerlaboratorymanagement@gmail.com", "Computer Laboratory Management System");
        $mail->addAddress($_POST['email']);
        $mail->Subject = "Account Activation";
        $mail->Body = "Dear $username, <br><br>
            To complete your verification process, please use the One-Time Password (OTP) provided below: <br><br>
            Verification Code: $otp <br><br>
            This code is valid for the next 5 minutes. Please do not share this OTP with anyone for security reasons. <br>
            If you did not request this verification, please ignore this email or contact our support team immediately.<br><br>
            Thanks, <br>
            Comlabsystem Team.
            ";

        try {
            $mail->send();
            headto('verification.php');
            exit;
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer error: " . $mail->ErrorInfo;
            headto('registration.php');
            exit;
        }
    }

    $stmt->close();
    $conn->close();

    return $result;
}

function updateAccount($email){
    $conn = getConnection();
    $stmt = $conn->prepare("UPDATE users SET account_status = 1 WHERE email = ? LIMIT 1");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->close();
    $conn->close();

}

function verifiedAccount($email){
    $conn = getConnection();
    $stmt = $conn->prepare("SELECT account_status FROM users WHERE email = ? LIMIT 1 ");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    $output = null;
    
    if ($row = $result->fetch_assoc()) {
        $output = $row['account_status'];
    }
    $stmt->close();
    $conn->close();

    return $output;
}

function verifyOtp($id){
    $conn = getConnection();
    $stmt = $conn->prepare("SELECT code FROM verification_codes WHERE user_id = ? AND expires_at > NOW() ORDER BY expires_at DESC LIMIT 1");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
   
    $verificationCode = null;

    if ($row = $result->fetch_assoc()) {
        $verificationCode = $row['code'];

    }
    $stmt->close();
    $conn->close();
    
    return $verificationCode;

}

function generateOtp($userId){
    $conn = getConnection();
    $otp = rand(100000, 999999);
    $hashedOtp = password_hash($otp, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO verification_codes (user_id, code)  VALUES (?, ?)");
    $stmt->bind_param('is', $userId, $hashedOtp);
    $stmt->execute();

    $stmt->close();
    $conn->close();

    return $otp;

}
