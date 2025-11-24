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
    $stmt = $conn->prepare("SELECT first_name, middle_name, last_name, suffix FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param('s', $email);
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
    $userId = $stmt->insert_id;


    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->SMTPAuth = true;
    $mail->Host = "smtp.gmail.com";
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->SMTPKeepAlive = true;
    $mail->Username = "trjistanbendoyr@gmail.com";
    $mail->Password = "woar pqng vyro kazq ";

    $mail->isHTML(true);

    if ($result) {
        $otp = rand(100000, 999999);
        $_SESSION['otp'] = $otp;
        $_SESSION['email'] = $email;
        $mail->setFrom("trjistanbendoyr@gmail.com", "OTP Verification");
        $mail->addAddress($_POST['email']);
        $mail->Subject = "Account Activation";
        $mail->Body = "Hello $first_name, This is a Test  <br><br>
            This is your OTP: $otp <br>
            Please enter this OTP to verify your email <br>";
            



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

function verifyOtp($email){
    $conn = getConnection();
    $stmt = $conn->prepare("UPDATE users SET account_status = 1 WHERE email = ? LIMIT 1");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->close();
    $conn->close();

}

function verifyAccount($email){
    $conn = getConnection();
    $stmt = $conn->prepare("SELECT account_status FROM users WHERE email = ? LIMIT 1 ");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    $stmt->close();
    $conn->close();

    if ($row = $result->fetch_assoc()) {
        $output = $row['account_status'];
    }
    return $output;
}



    

