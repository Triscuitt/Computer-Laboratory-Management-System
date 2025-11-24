<?php
require "dbconnection.php";
require "utilities/utils.php";
require "utilities/db.php";

session_start();
checkPost();

function checkPost()
{
    if (isset($_POST['registerBtn'])) {
        register($_POST['stud_no'], $_POST['fname'], $_POST['midname'], $_POST['lname'], $_POST['suffix'], $_POST['username'], $_POST['email'], $_POST['password'], $_POST['confirmPassword']);
    }
}

function register($student_number, $first_name, $middle_name, $last_name, $suffix, $username, $email, $password, $confirmPassword)
{
    $student_number = trim($student_number);
    $fname = trim($first_name);
    $midname = trim($middle_name);
    $lname = trim($last_name);
    $suffix = trim($suffix);
    $email = cleanEmail($email);
    $username = trim($username);
    $password = trim($password);
    $confirmPassword =  trim($confirmPassword);

        if (empty($fname) || empty($lname) || empty($student_number) || empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
            $_SESSION['popUpMessage'] = "Input fields required.";
        } else if (strlen($fname) < 2 || strlen($lname) < 2) {
            $_SESSION['popUpMessage'] = "Username must be atleast 2 characters long.";
            /*}else if (checkUsername($fname, $midname, $lname, $suffix)) {
        $_SESSION['popUpMessage'] = "Name already exists."; */
        } else if (!preg_match('/^\d{4}-\d{5}$/', $student_number)) {
            $_SESSION['popUpMessage'] = "Invalid student number format.";
        } else if (checkEmail($email)) {
            $_SESSION['popUpMessage'] = "Email already exists.";
            /*}else if (!str_ends_with($email, '@dyci.edu.ph')){
        $_SESSION['popUpMessage'] = "Registration requires institutional email"; */
        } else if (strlen($password) < 8) {
            $_SESSION['popUpMessage'] = "Password must be atleast 8 characters long.";
        } else if ($password != $confirmPassword) {
            $_SESSION['popUpMessage'] = "Passwords do not match.";
        } else if (createUser($student_number, $fname, $midname, $lname, $suffix, $username, $email, password_hash($password, PASSWORD_DEFAULT))) {
            headto('verification.php');
            exit;
        } else {
            $_SESSION['popUpMessage'] = "Account creation failed.";
        }
    }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
</head>
<h1>Computer Laboratory Management System</h1>

<body>
    <h2>Create an Account</h2>
    <div clas="container">
        <form method="POST">
            <input type="text" name="stud_no" placeholder="Enter student number"> <br><br>
            <input type="text" name="fname" placeholder="Enter first name">
            <input type="text" name="midname" placeholder="Enter middle name"> <br><br>
            <input type="text" name="lname" placeholder="Enter last name">
            <input type="text" name="suffix" placeholder="Enter Suffix (Optional)"> <br><br>
            <input type="text" name="username" placeholder="Enter username"> <br><br>
            <input type="email" name="email" placeholder="Enter Email"> <br><br>
            <input type="password" name="password" placeholder="Enter Password">
            <input type="password" name="confirmPassword" placeholder="Confirm Password"> <br><br>
            <button type="submit" name="registerBtn">Register</button>
            <p>Already have an account? <a href="login.php">Login</a></p>
        </form>
        <?php displayMessage() ?>

    </div>
</body>

</html>