<?php
session_start();
require "dbconnection.php";
require "utilities/utils.php";
require "utilities/validation.php";
require "utilities/queries.php";

checkPost();
clearPost();


function checkPost()
{
    if (isset($_POST['registerBtn'])) {
        $_SESSION['formData'] = $_POST;
        register($_POST['stud_no'], $_POST['fname'], $_POST['midname'], $_POST['lname'], $_POST['suffix'], $_POST['username'], $_POST['email'], $_POST['password'], $_POST['confirmPassword']);
    }
}


function register($student_number, $first_name, $middle_name, $last_name, $suffix, $username, $email, $password, $confirmPassword)
{
    $stud_no = validateStudentNumber($student_number);
    $fname = trim($first_name);
    $midname = trim($middle_name);
    $lname = trim($last_name);
    $suffix = trim($suffix);
    $email = trim($email);
    $username = trim($username);
    $password = trim($password);
    $confirmPassword =  trim($confirmPassword);
    $validateEmail = validateEmail($email);
    $validatePassword = validatePassword($password, $confirmPassword);
    $field = validateEmptyField($student_number, $fname, $lname, $email, $username, $password, $confirmPassword);
    $length = validateLength($fname, $lname);

        if (!$field['status']) {
            $_SESSION['alertMessage'] = $field['message'];
        }else if (!$length['status']) {
            $_SESSION['alertMessage'] = $length['message'];
        }else if (!$stud_no['status']) {
            $_SESSION['alertMessage'] = $stud_no['message']; 
        }else if (!$validateEmail['status']){
            $_SESSION['alertMessage'] = $validateEmail['message']; 
        }else if (!$validatePassword['status']) {
            $_SESSION['alertMessage'] = $validatePassword['message'];
        }else if (createUser($student_number, $fname, $midname, $lname, $suffix, $username, $email, password_hash($password, PASSWORD_DEFAULT))) {
            unset($_SESSION['formData']);
            headto('verification.php');
        } else {
            $_SESSION['alertMessage'] = "Account creation failed.";
            headto('registration.php');
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="asset/style.css">
</head>
<body>
    <header>
        <h1 class="title">Computer Laboratory Management System</h1>
    </header>

    <main>
        <h2 class="subtitle">Create an Account</h2>
        <div class="container">
            <form method="POST" class="form">
                <input type="text" name="stud_no" placeholder="Enter student number" class="input">
                <input type="text" name="fname" placeholder="Enter first name" class="input">
                <input type="text" name="midname" placeholder="Enter middle name" class="input">
                <input type="text" name="lname" placeholder="Enter last name" class="input">
                <input type="text" name="suffix" placeholder="Enter Suffix (Optional)" class="input">
                <input type="text" name="username" placeholder="Enter username" class="input">
                <input type="email" name="email" placeholder="Enter Email" class="input">
                <input type="password" name="password" placeholder="Enter Password" class="input">
                <input type="password" name="confirmPassword" placeholder="Confirm Password" class="input">

                <button type="submit" name="registerBtn" class="btn">Register</button>
                <p class="login-text">Already have an account? <a href="login.php" class="link">Login here</a></p>
            </form>
            <?php displayMessage() ?>
        </div>
    </main>
</body>
</html>