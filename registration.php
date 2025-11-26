<?php
require "dbconnection.php";
require "utilities/utils.php";
require "utilities/validation.php";
require "utilities/queries.php";

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

        if ($field !== true) {
            $_SESSION['popUpMessage'] = $field;
        }else if ($length !== true) {
            $_SESSION['popUpMessage'] = $length;
        }else if ($stud_no !== true) {
            $_SESSION['popUpMessage'] = $stud_no; 
        }else if ($validateEmail !== true){
            $_SESSION['popUpMessage'] = $validateEmail; 
        }else if ($validatePassword !== true) {
            $_SESSION['popUpMessage'] = $validatePassword;
        }else if (createUser($student_number, $fname, $midname, $lname, $suffix, $username, $email, password_hash($password, PASSWORD_DEFAULT))) {
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
            <p>Already have an account? <a href="login.php">Login here</a></p>
        </form>
        <?php displayMessage() ?>

    </div>
</body>

</html>