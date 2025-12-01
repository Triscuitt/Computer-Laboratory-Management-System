<?php

session_start();

$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';
$_SESSION['fullname'] = 'Main Admin';


header("Location: admin/dashboard.php");
exit();
