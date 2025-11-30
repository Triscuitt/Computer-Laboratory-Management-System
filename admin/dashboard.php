<?php

session_start();


require_once '../config/db_connect.php';


if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}


$total_users = 0;
$result = $conn->query("SELECT COUNT(*) AS total FROM users");
if ($result && $result->num_rows > 0) {
    $data = $result->fetch_assoc();
    $total_users = $data['total'];
}


$equipment_errors = 0;
$result_errors = $conn->query("SELECT COUNT(*) AS errors FROM equipment WHERE status = 'With Error/Problem'");
if ($result_errors && $result_errors->num_rows > 0) {
    $data_errors = $result_errors->fetch_assoc();
    $equipment_errors = $data_errors['errors'];
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/admin.css">
</head>

<body>

    <?php include 'include/sidebar.php'; ?>

    <div class="main-content">

        <div class="header">
            <h1>Welcome, <?php echo isset($_SESSION['fullname']) ? $_SESSION['fullname'] : 'Admin'; ?>!</h1>
            <span>DYCI Computer Lab Management System</span>
        </div>

        <div class="cards-container">

            <div class="card">
                <h3><?php echo $total_users; ?></h3>
                <p>Total Registered Users</p>
            </div>

            <div class="card">
                <h3>0</h3>
                <p>Total Lab Equipment</p>
            </div>

            <div class="card">
                <h3>0</h3>
                <p>Pending Requests</p>
            </div>

            <div class="card" style="border: 2px solid red;">
                <h3 style="color: red;"><?php echo $equipment_errors; ?></h3>
                <p>Equipment Reported with Error</p>
            </div>

        </div>

        <h2 style="margin-top: 40px;">System Overview</h2>
    </div>

</body>

</html>