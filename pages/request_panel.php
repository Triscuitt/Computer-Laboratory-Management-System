<?php
session_start();
require_once "../dbconnection.php";
$conn = getConnection();

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'technician'])) {
    header("Location: ../index.php");
    exit();
}

// Check if 'request' table exists
$table_exists = $conn->query("SHOW TABLES LIKE 'request'")->num_rows > 0;

$requests = [];
$total_requests = 0;

if ($table_exists) {
    $sql = "SELECT r.*, u.first_name, u.last_name, u.student_number, u.role
            FROM request r
            LEFT JOIN users u ON r.submitter_id = u.id
            ORDER BY 
                FIELD(r.request_priority, 'High', 'Medium', 'Low'),
                r.request_id DESC";

    $result = $conn->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $requests[] = $row;
        }
        $total_requests = count($requests);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IT Support Requests | Admin</title>
    <link rel="stylesheet" href="../assets/admin.css">
    <style>
        .header-box {
            background: linear-gradient(135deg, #2980b9, #e6ecf4ff);
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

        .content-card {
            background: white;
            padding: 35px;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
            overflow-x: auto;
        }

        .request-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 900px;
        }

        .request-table th {
            background: #2980b9;
            color: white;
            padding: 16px 12px;
            text-align: left;
            font-weight: 600;
        }

        .request-table td {
            padding: 16px 12px;
            border-bottom: 1px solid #eee;
        }

        .request-table tr:hover {
            background: #f5f9ff;
        }

        /* Priority Badges */
        .priority-low {
            background: #27ae60;
            color: white;
            padding: 6px 12px;
            border-radius: 8px;
            font-weight: bold;
            font-size: 0.9rem;
        }

        .priority-medium {
            background: #f39c12;
            color: white;
            padding: 6px 12px;
            border-radius: 8px;
            font-weight: bold;
            font-size: 0.9rem;
        }

        .priority-high {
            background: #e74c3c;
            color: white;
            padding: 6px 12px;
            border-radius: 8px;
            font-weight: bold;
            font-size: 0.9rem;
        }

        /* Type Icons */
        .type-software i {
            color: #9b59b6;
        }

        .type-purchase i {
            color: #f39c12;
        }

        .type-peripheral i {
            color: #3498db;
        }

        .type-hardware i {
            color: #e67e22;
        }

        .action-btn {
            padding: 8px 16px;
            border: none;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            text-decoration: none;
            font-size: 0.9rem;
            margin: 0 4px;
        }

        .btn-resolve {
            background: #27ae60;
        }

        .btn-delete {
            background: #e74c3c;
        }

        .action-btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }

        .info-badge.dark {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            background: rgba(30, 35, 45, 0.85);
            color: #e0e0e0;
            padding: 14px 28px;
            border-radius: 50px;
            font-size: 1.15rem;
            font-weight: 700;
            backdrop-filter: blur(12px);
            box-shadow:
                0 8px 32px rgba(0, 0, 0, 0.4),
                0 0 20px rgba(52, 152, 219, 0.15),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(70, 130, 180, 0.3);
            transition: all 0.35s ease;
            letter-spacing: 0.5px;
        }

        .info-badge.dark:hover {
            transform: translateY(-6px);
            background: rgba(40, 45, 60, 0.95);
            box-shadow:
                0 15px 40px rgba(0, 0, 0, 0.5),
                0 0 30px rgba(52, 152, 219, 0.25);
            border-color: rgba(100, 180, 255, 0.4);
        }

        .info-badge.dark i {
            font-size: 1.4rem;
            color: #64b5f6;
            text-shadow: 0 0 10px rgba(100, 180, 255, 0.4);
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
    <?php include '../include/sidebar.php'; ?>

    <div class="main-content">

        <!-- BEAUTIFUL HEADER BOX -->
        <div class="header-box">
            <div class="header-text">
                <h1>Requests Panel</h1>
            </div>
            <div class="header-actions">
                <div class="info-badge dark">
                    <i class="fas fa-bell"></i>
                    <?= $total_requests ?> Active Request<?= $total_requests !== 1 ? 's' : '' ?>
                </div>
            </div>
        </div>

        <div class="content-card">
            <?php if (!$table_exists): ?>
                <div style="text-align:center; padding:80px 20px; color:#7f8c8d;">
                    <h3>Support Request System Not Active</h3>
                    <p>Run this SQL to enable it:</p>
                    <pre style="background:#2c3e50;color:#1abc9c;padding:20px;border-radius:12px;max-width:800px;margin:20px auto;overflow-x:auto;">
CREATE TABLE request (
    request_id INT AUTO_INCREMENT PRIMARY KEY,
    submitter_id INT NOT NULL,
    request_title VARCHAR(50) NOT NULL,
    request_type ENUM('Software installation', 'Purchase', 'Peripheral', 'Hardware') DEFAULT 'Hardware',
    request_priority ENUM('Low', 'Medium', 'High'),
    request_description VARCHAR(250),
    FOREIGN KEY (submitter_id) REFERENCES users(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;</pre>
                </div>

            <?php elseif (empty($requests)): ?>
                <div style="text-align:center; padding:100px 20px; color:#95a5a6; font-size:1.4rem;">
                    <p>No support requests at the moment.</p>
                    <p>All systems are running smoothly!</p>
                </div>

            <?php else: ?>
                <table class="request-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Submitted By</th>
                            <th>Title</th>
                            <th>Type</th>
                            <th>Priority</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $i => $r):
                            $name = trim($r['first_name'] . ' ' . $r['last_name']);
                        ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($name) ?></strong><br>
                                    <small style="color:#95a5a6;">
                                        <?= $r['student_number'] ?: $r['role'] ?>
                                    </small>
                                </td>
                                <td><strong><?= htmlspecialchars($r['request_title']) ?></strong></td>
                                <td class="type-<?= strtolower(str_replace(' ', '-', $r['request_type'])) ?>">
                                    <i class="fas <?= $r['request_type'] === 'Software installation' ? 'fa-code' : ($r['request_type'] === 'Purchase' ? 'fa-shopping-cart' : ($r['request_type'] === 'Peripheral' ? 'fa-keyboard' : 'fa-microchip')) ?>"></i>
                                    <?= $r['request_type'] ?>
                                </td>
                                <td>
                                    <span class="priority-<?= strtolower($r['request_priority'] ?? 'low') ?>">
                                        <?= $r['request_priority'] ?? 'Low' ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($r['request_description'] ?: '—') ?></td>
                                <td>
                                    <a href="resolve_request.php?id=<?= $r['request_id'] ?>" class="action-btn btn-resolve"
                                        onclick="return confirm('Mark as resolved?')">Resolve</a>
                                    <a href="delete_request.php?id=<?= $r['request_id'] ?>" class="action-btn btn-delete"
                                        onclick="return confirm('Delete this request?')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>

<?php $conn->close(); ?>