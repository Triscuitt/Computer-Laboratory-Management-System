<?php
require_once "../model/user.php";
session_start();
require_once "../dbconnection.php";
$conn = getConnection();

if ($_SESSION['User']->getRole() != 'admin') {
    header("Location: ../index.php");
    exit();
}

$metrics = [
    'total_equipment'     => 0,
    'available'           => 0,
    'with_error'          => 0,
    'pulled_out'          => 0,
    'pending_requests'    => 0,
    'currently_borrowed'  => 0,
    'lab_counts'          => ['Nexus' => 0, 'Sandbox' => 0, 'Raise' => 0, 'EdTech' => 0]
];

try {
    // Total Equipment
    $result = $conn->query("SELECT COUNT(*) AS total FROM equipment");
    $metrics['total_equipment'] = $result->fetch_assoc()['total'];

    // Equipment Status Breakdown
    $result = $conn->query("SELECT status, COUNT(*) AS count FROM equipment GROUP BY status");
    while ($row = $result->fetch_assoc()) {
        switch ($row['status']) {
            case 'Available':
                $metrics['available'] = $row['count'];
                break;
            case 'With Error':
                $metrics['with_error'] = $row['count'];
                break;
            case 'Pulled out':
                $metrics['pulled_out'] = $row['count'];
                break;
        }
    }

    // Equipment per Lab
    $result = $conn->query("SELECT lab_location, COUNT(*) AS count FROM equipment GROUP BY lab_location");
    while ($row = $result->fetch_assoc()) {
        if (isset($metrics['lab_counts'][$row['lab_location']])) {
            $metrics['lab_counts'][$row['lab_location']] = $row['count'];
        }
    }

    // Pending IT Support Requests (from request table)
    $result = $conn->query("SELECT COUNT(*) AS total FROM request WHERE status = 'Pending'");
    $metrics['pending_requests'] = $result->fetch_assoc()['total'];

    // Currently Borrowed Equipment
    $result = $conn->query("SELECT COUNT(*) AS total FROM borrow WHERE status = 'Borrowed'");
    $metrics['currently_borrowed'] = $result->fetch_assoc()['total'];
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .header-box {
            background: linear-gradient(135deg, #2980b9, #c0e0f6ff);
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

        /* EXPORT BUTTONS SECTION */
        .export-section {
            background: white;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .export-section h2 {
            margin: 0 0 20px;
            font-size: 1.5rem;
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .export-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
        }

        .export-btn {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            padding: 18px 24px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.05rem;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .export-btn i {
            font-size: 1.3rem;
        }

        .export-btn:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(231, 76, 60, 0.4);
        }

        .export-btn.all-equipment {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
        }

        .export-btn.errors {
            background: linear-gradient(135deg, #f39c12, #e67e22);
        }

        .export-btn.users {
            background: linear-gradient(135deg, #9b59b6, #8e44ad);
        }

        .export-btn.sessions {
            background: linear-gradient(135deg, #16a085, #138d75);
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

        .metric-pulled .value {
            color: #95a5a6;
        }

        .metric-pending .value {
            color: #f39c12;
        }

        .metric-borrowed .value {
            color: #9b59b6;
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
    <?php include '../include/sidebar.php'; ?>

    <div class="main-content">
        <div class="header-box">
            <div class="header-text">
                <h1>Lab Management Reports</h1>
            </div>
        </div>

        <!-- EXPORT BUTTONS SECTION -->
        <div class="export-section">
            <h2>
                <i class="fas fa-download"></i>
                Export Reports to PDF
            </h2>
            <div class="export-buttons">
                <a href="export_pdf.php?type=equipment&lab=all" class="export-btn all-equipment">
                    <i class="fas fa-file-pdf"></i>
                    <span>Export All Equipment</span>
                </a>

                <a href="export_pdf.php?type=equipment_errors" class="export-btn errors">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>Export Errors Only</span>
                </a>

                <a href="export_pdf.php?type=users" class="export-btn users">
                    <i class="fas fa-users"></i>
                    <span>Export All Users</span>
                </a>

                <a href="export_pdf.php?type=sessions&lab=all" class="export-btn sessions">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Export Lab Sessions</span>
                </a>
            </div>
        </div>

        <?php if (isset($error)): ?>
            <div style="background:#e74c3c;color:white;padding:20px;border-radius:12px;margin:20px 0;">
                <?= htmlspecialchars($error) ?>
            </div>
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
                    <p class="value"><?= number_format($metrics['with_error']) ?></p>
                </div>

                <div class="metric-card metric-pulled">
                    <h3>Pulled Out</h3>
                    <p class="value"><?= number_format($metrics['pulled_out']) ?></p>
                </div>

                <div class="metric-card metric-pending">
                    <h3>Pending Support Requests</h3>
                    <p class="value"><?= number_format($metrics['pending_requests']) ?></p>
                </div>

                <div class="metric-card metric-borrowed">
                    <h3>Currently Borrowed</h3>
                    <p class="value"><?= number_format($metrics['currently_borrowed']) ?></p>
                </div>
            </div>

            <!-- Lab Distribution -->
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