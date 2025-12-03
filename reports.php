<?php
session_start();
require_once '../config/db_connect.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$metrics = [
    'total_equipment' => 0,
    'available' => 0,
    'error' => 0,
    'pulled_out' => 0,
    'pending_requests' => 0,
    'currently_borrowed' => 0,
    'lab_counts' => ['Nexus' => 0, 'Sandbox' => 0, 'Raise' => 0, 'EdTech' => 0]
];

$error = '';

try {
    // Total Equipment
    $result = $conn->query("SELECT COUNT(*) AS total FROM equipment");
    $metrics['total_equipment'] = $result->fetch_assoc()['total'];

    // Equipment by Status
    $result = $conn->query("SELECT status, COUNT(*) AS count FROM equipment GROUP BY status");
    while ($row = $result->fetch_assoc()) {
        if ($row['status'] === 'Available') $metrics['available'] = $row['count'];
        if ($row['status'] === 'With Error') $metrics['error'] = $row['count'];
        if ($row['status'] === 'Pulled out') $metrics['pulled_out'] = $row['count'];
    }

    // Equipment by Lab
    $result = $conn->query("SELECT lab_location, COUNT(*) AS count FROM equipment GROUP BY lab_location");
    while ($row = $result->fetch_assoc()) {
        if (isset($metrics['lab_counts'][$row['lab_location']])) {
            $metrics['lab_counts'][$row['lab_location']] = $row['count'];
        }
    }

    // Borrowing Requests (if borrowing_logs table exists)
    if ($conn->query("SHOW TABLES LIKE 'borrowing_logs'")->num_rows > 0) {
        $result = $conn->query("SELECT status, COUNT(*) AS count FROM borrowing_logs GROUP BY status");
        while ($row = $result->fetch_assoc()) {
            if ($row['status'] === 'Pending') $metrics['pending_requests'] = $row['count'];
            if ($row['status'] === 'Borrowed') $metrics['currently_borrowed'] = $row['count'];
        }
    }
} catch (Exception $e) {
    $error = "Error loading reports: " . $e->getMessage();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Management Reports | Admin</title>
    <link rel="stylesheet" href="../assets/admin.css">
    <style>
        /* SAME HEADER BOX AS ALL OTHER PAGES */
        .header-box {
            background: linear-gradient(135deg, #2980b9, #ffffffff);
            color: white;
            padding: 32px 40px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(41, 128, 185, 0.3);
            margin-bottom: 35px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .header-text h1 {
            margin: 0;
            font-size: 2.2rem;
            font-weight: 700;
        }

        .header-btn {
            padding: 14px 32px;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            text-decoration: none;
            color: white;
            transition: all 0.3s;
        }

        .header-btn:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.3);
        }

        .report-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .metric-card {
            background: white;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: 0.3s;
        }

        .metric-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        .metric-card h3 {
            margin: 0 0 15px;
            color: #2c3e50;
            font-size: 1.1rem;
            font-weight: 600;
        }

        .metric-card .value {
            font-size: 2.8rem;
            font-weight: 700;
            margin: 10px 0 0;
        }

        .metric-total .value {
            color: #3498db;
        }

        .metric-available .value {
            color: #27ae60;
        }

        .metric-error .value {
            color: #e74c3c;
        }

        .metric-archived .value {
            color: #95a5a6;
        }

        .metric-pending .value {
            color: #f39c12;
        }

        .lab-summary-card {
            grid-column: 1 / -1;
            background: white;
            padding: 35px;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
        }

        .lab-list {
            list-style: none;
            padding: 0;
            margin: 20px 0 0;
        }

        .lab-list li {
            display: flex;
            justify-content: space-between;
            padding: 14px 0;
            border-bottom: 1px solid #eee;
            font-size: 1.2rem;
        }

        .lab-list li:last-child {
            border-bottom: 3px solid #3498db;
            font-weight: bold;
            font-size: 1.4rem;
            color: #2c3e50;
        }
    </style>
</head>

<body>
    <?php include 'include/sidebar.php'; ?>

    <div class="main-content">

        <!-- SAME BEAUTIFUL HEADER BOX -->
        <div class="header-box">
            <div class="header-text">
                <h1>Lab Management Reports</h1>
            </div>
            <div class="header-actions">

            </div>
        </div>

        <?php if ($error): ?>
            <div class="message-error"><?= htmlspecialchars($error) ?></div>
        <?php else: ?>

            <div class="report-grid">

                <div class="metric-card metric-total">
                    <h3>Total Equipment</h3>
                    <p class="value"><?= number_format($metrics['total_equipment']) ?></p>
                </div>

                <div class="metric-card metric-available">
                    <h3>Available & Working</h3>
                    <p class="value"><?= number_format($metrics['available']) ?></p>
                </div>

                <div class="metric-card metric-error">
                    <h3>With Error</h3>
                    <p class="value"><?= number_format($metrics['error']) ?></p>
                </div>

                <div class="metric-card metric-archived">
                    <h3>Pulled Out</h3>
                    <p class="value"><?= number_format($metrics['pulled_out']) ?></p>
                </div>

                <div class="metric-card metric-pending">
                    <h3>Pending Requests</h3>
                    <p class="value"><?= number_format($metrics['pending_requests']) ?></p>
                </div>

                <div class="metric-card metric-total">
                    <h3>Currently Borrowed</h3>
                    <p class="value"><?= number_format($metrics['currently_borrowed']) ?></p>
                </div>

            </div>

            <!-- Lab Summary Card -->
            <div class="lab-summary-card">
                <h2 style="margin-top:0; color:#2c3e50;">Equipment Distribution by Lab</h2>
                <ul class="lab-list">
                    <?php
                    $grand_total = 0;
                    foreach (['Nexus', 'Sandbox', 'Raise', 'EdTech'] as $lab):
                        $count = $metrics['lab_counts'][$lab];
                        $grand_total += $count;
                    ?>
                        <li>
                            <span><?= htmlspecialchars($lab) ?> Lab</span>
                            <span><?= number_format($count) ?> items</span>
                        </li>
                    <?php endforeach; ?>
                    <li>
                        <span>GRAND TOTAL</span>
                        <span><?= number_format($grand_total) ?> Assets</span>
                    </li>
                </ul>
            </div>

        <?php endif; ?>

    </div>
</body>

</html>