<?php
session_start();
require_once '../dbconnection.php';

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'technician'])) {
    header("Location: ../index.php");
    exit();
}

// Get the database connection
$conn = getConnection();

$total_users = $conn->query("SELECT COUNT(*) FROM users WHERE account_status = 1")->fetch_row()[0] ?? 0;
$total_equipment = $conn->query("SELECT COUNT(*) FROM equipment")->fetch_row()[0] ?? 0;
$equipment_with_error = $conn->query("SELECT COUNT(*) FROM equipment WHERE status = 'With Error'")->fetch_row()[0] ?? 0;

$pending_requests = 0;
if ($conn->query("SHOW TABLES LIKE 'request'")->num_rows > 0) {
    $pending_requests = $conn->query("SELECT COUNT(*) FROM request WHERE status = 'Pending'")->fetch_row()[0] ?? 0;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .header {
            background: linear-gradient(135deg, #2980b9, #dbe9f2ff);
            color: white;
            padding: 35px;
            border-radius: 16px;
            margin-bottom: 30px;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 2.5rem;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin: 30px 0;
        }

        .card {
            background: white;
            padding: 30px;
            border-radius: 16px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: 0.3s;
        }

        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }

        .card h3 {
            font-size: 3rem;
            margin: 0;
            color: #2c3e50;
        }

        .card p {
            color: #7f8c8d;
            margin: 10px 0 0;
            font-weight: 600;
        }

        .icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }

        .users {
            color: #9b59b6;
        }

        .equipment {
            color: #3498db;
        }

        .requests {
            color: #f39c12;
        }

        .errors {
            color: #e74c3c;
        }
    </style>
</head>

<body>
    <?php include '../include/sidebar.php'; ?>

    <div class="main-content">
        <div class="header">
            <h1 style="color:white">Welcome back, <?= htmlspecialchars($_SESSION['first_name'] ?? 'Admin') ?>!</h1>
            <p style="font-size: 1.5rem;color:#2c3e50">DYCI Computer Lab Management System</p>
        </div>

        <div class="cards">
            <div class="card">
                <div class="icon users"><i class="fas fa-users"></i></div>
                <h3><?= $total_users ?></h3>
                <p>Total Active Users</p>
            </div>

            <div class="card">
                <div class="icon equipment"><i class="fas fa-desktop"></i></div>
                <h3><?= $total_equipment ?></h3>
                <p>Total Active Equipment</p>
            </div>

            <div class="card">
                <div class="icon requests"><i class="fas fa-clock"></i></div>
                <h3><?= $pending_requests ?></h3>
                <p>Pending Requests</p>
            </div>

            <div class="card" style="border-left:6px solid #e74c3c;">
                <div class="icon errors"><i class="fas fa-exclamation-triangle"></i></div>
                <h3><?= $equipment_with_error ?></h3>
                <p>Equipment with Error</p>
            </div>
        </div>
    </div>
</body>

</html>

<?php $conn->close(); ?>