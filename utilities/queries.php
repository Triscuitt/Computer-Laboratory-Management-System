<?php
require_once "../dbconnection.php";
require_once "../mailer.php";
require_once '../model/user.php';

/* Get hashed password by email
    return: string 
*/
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

/* Get user by email
    return: user object
*/
function getUser($email)
{
    $conn = getConnection();
    $stmt = $conn->prepare("SELECT * FROM users where email = ?");

    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();
    $user = null;

    if ($row = $result->fetch_assoc()) {
        $user = new User($row['first_name'], $row['last_name'], $row['role'], $row['username'],$row['student_number'], $row['email'], $row['password']);
    }

    $stmt->close();
    $conn->close();

    return $user;
}

// Get only userId by email
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

// check email if exists
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

function createUser($student_number, $first_name, $middle_name, $last_name, $suffix, $role, $username, $email, $hashedPassword)
{
    $conn = getConnection();
    $stmt = $conn->prepare("INSERT INTO users (student_number, first_name, middle_name, last_name, suffix, role, username, email, password, account_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0)");
    $stmt->bind_param("sssssssss", $student_number, $first_name, $middle_name, $last_name, $suffix, $role, $username, $email, $hashedPassword);
    $result = $stmt->execute();
    $userId = $conn->insert_id;

    /*
    if ($result) {
        $otp = generateOtp($userId);
        $_SESSION['otp'] = $otp;
        $_SESSION['email'] = $email;
        $_SESSION['username'] = $username;
        $_SESSION['user_id'] = $userId;
        $_SESSION['otp_last_sent'] = time();

        $sendOtpEmail = sendOtpEmail($email, $otp, $username);


        if (!$sendOtpEmail) {
            echo "Message could not be sent. Please Try again.";
            headto("registration.php");
        }
    }
    */

    $stmt->close();
    $conn->close();

    return $result;
}


function updateAccount($email)
{
    $conn = getConnection();
    $stmt = $conn->prepare("UPDATE users SET account_status = 1 WHERE email = ? LIMIT 1");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->close();
    $conn->close();
}

function verifiedAccount($email)
{
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

function verifyOtp($email)
{
    $conn = getConnection();
    $stmt = $conn->prepare("SELECT code FROM verification_codes WHERE user_email = ? AND expires_at > NOW() ORDER BY expires_at DESC LIMIT 1");
    $stmt->bind_param('s', $email);
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


function generateOtp($email)
{
    $conn = getConnection();
    $otp = rand(100000, 999999);

    $deleteStmt = $conn->prepare("DELETE FROM verification_codes WHERE user_email = ?");
    $deleteStmt->bind_param('s', $email);
    $deleteStmt->execute();
    $deleteStmt->close();

    $hashedOtp = password_hash($otp, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO verification_codes (user_email, code)  VALUES (?, ?)");
    $stmt->bind_param('ss', $email, $hashedOtp);
    $stmt->execute();

    $stmt->close();
    $conn->close();

    return $otp;
}

function sendOtp($email, $username){
    $otp = generateOtp($email);

    $sendOtpEmail = sendOtpEmail($email, $otp, $username);


    if (!$sendOtpEmail) {
       echo "Message could not be sent. Please Try again.";
       headto("registration.php");
    }
}
