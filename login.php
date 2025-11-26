<?php 
    require "dbconnection.php";
    require "utilities/utils.php";
    require "utilities/queries.php";
    session_start();
    
    checkIfLoggedIn();
    checkPost();

    function checkIfLoggedIn(){
        if(isset($_SESSION['loggedInUser'])) {
            headto("homepage.php");
            exit;
        } 
    }

    function checkPost(){
        if(isset($_POST['loginBtn'])) {
            login($_POST['email'], $_POST['password']);
        }
    }

    function login($email, $password) {
        $email = trim($email);
        $password = trim($password);

        if(password_verify($password, getHashedPassword($email)) && verifiedAccount($email)) {
            session_regenerate_id(true);
            $_SESSION["loggedInUser"] = getUserId($email);
            headto("homepage.php");
            exit;
        } else if (!verifiedAccount($email)) {
            $_SESSION['popUpMessage'] = "Account not found or not activated.";
        }
        else {
            $_SESSION['popUpMessage'] = "Invalid username or password";
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
<body>
    <h1>Computer Laboratory Management System</h1>
    <h2>Login</h2>
    <div clas="container">
        <form method="POST">
            <input type="email" name="email" placeholder="Enter Email"> <br><br>
            <input type="password" name="password" placeholder="Enter Password"> <br><br>
            <button type="submit" name="loginBtn">Login</button>
            <p>Don't have an Account? <a href="registration.php">Register</a></p><br>
        </form>
        <?php displayMessage()?>

    </div>
</body>
</html>