<?php 
    session_start();
    require_once "utilities/db.php";
    require_once "utilities/utils.php";

    checkPost();

    function checkPost(){
        if(isset($_POST["logout"])) {
            logout();
            headto("login.php");
        }
    }


    function logout(){
        session_unset();
        session_destroy();
    }

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    Welcome to homepage <?php ?>
    <P>This is a test homepage</P>

    <form method="POST">
        <button type="submit" name="logout">logout</button>
    </form>
</body>
</html>