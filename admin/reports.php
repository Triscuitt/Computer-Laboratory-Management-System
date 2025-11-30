<?php


session_start();
require_once '../config/db_connect.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$metrics = [];
$error = '';

try {

    $result = $conn->query("SELECT COUNT(*) AS total_equipment FROM equipment");
    $metrics['total_equipment'] = $result->fetch_assoc()['total_equipment'];


    $sql = "SELECT status, COUNT(*) AS count FROM equipment GROUP BY status";
    $result = $conn->query($sql);
    $availability = [];
    while ($row = $result->fetch_assoc()) {
        $availability[$row['status']] = $row['count'];
    }
    $metrics['available'] = $availability['Available & working'] ?? 0;
    $metrics['error'] = $availability['With Error/Problem'] ?? 0;
    $metrics['pulled_out'] = $availability['Pulled out'] ?? 0;

    $sql = "SELECT lab_location, COUNT(*) AS count FROM equipment GROUP BY lab_location";
    $result = $conn->query($sql);
    $metrics['lab_counts'] = [];
    while ($row = $result->fetch_assoc()) {
        $metrics['lab_counts'][$row['lab_location']] = $row['count'];
    }


    $sql = "SELECT status, COUNT(*) AS count FROM borrowing_logs GROUP BY status";
    $result = $conn->query($sql);
    $request_status = [];
    while ($row = $result->fetch_assoc()) {
        $request_status[$row['status']] = $row['count'];
    }
    $metrics['pending_requests'] = $request_status['Pending'] ?? 0;
    $metrics['currently_borrowed'] = $request_status['Borrowed'] ?? 0;
} catch (Exception $e) {
    $error = "Error fetching reports data: " . $e->getMessage();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Management Reports | Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">

</head>

<body>

    <?php include 'include/sidebar.php'; ?>

    <div class="main-content">
        <div class="header">
            <h1>📊 Lab Management Reports</h1>
        </div>

        <?php if ($error): ?>
            <div class="message-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <h2>Key Inventory Metrics</h2>
        <div class="report-grid">

            <div class="metric-card metric-total">
                <h3>Total Assets</h3>
                <p class="value"><?php echo number_format($metrics['total_equipment']); ?></p>
            </div>

            <div class="metric-card metric-available">
                <h3>Available & Working</h3>
                <p class="value"><?php echo number_format($metrics['available']); ?></p>
            </div>

            <div class="metric-card metric-error">
                <h3>In Maintenance/Error</h3>
                <p class="value"><?php echo number_format($metrics['error']); ?></p>
            </div>

            <div class="metric-card metric-archived">
                <h3>Pulled Out/Archived</h3>
                <p class="value"><?php echo number_format($metrics['pulled_out']); ?></p>
            </div>

            <div class="metric-card metric-pending">
                <h3>Pending Requests</h3>
                <p class="value"><?php echo number_format($metrics['pending_requests']); ?></p>
            </div>

            <div class="metric-card metric-total">
                <h3>Currently Borrowed</h3>
                <p class="value"><?php echo number_format($metrics['currently_borrowed']); ?></p>
            </div>

        </div>


        <div class="metric-card metric-total" style="grid-column: span 2;">
            <h3>Total Equipment Count by Lab</h3>
            <ul class="lab-list">
                <?php
                $total_assets_in_labs = 0;
                foreach (['Nexus', 'Sandbox', 'Raise', 'EdTech'] as $lab) {
                    $count = $metrics['lab_counts'][$lab] ?? 0;
                    $total_assets_in_labs += $count;
                    echo "<li><span>🖥️" . htmlspecialchars($lab) . "</span> <span>" . number_format($count) . " </span></li>";
                }
                ?>
                <li style="font-weight: bold; border-top: 2px solid #ccc;">
                    <span>GRAND TOTAL</span>
                    <span><?php echo number_format($total_assets_in_labs); ?> Assets</span>
                </li>
            </ul>
        </div>
    </div>

    </div>

</body>

</html>