<?php
session_start();
require_once '../config/db_connect.php';
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'technician'])) {
    header("Location: ../index.php");
    exit();
}

$lab = $_GET['lab'] ?? '';
$allowed = ['Nexus', 'Sandbox', 'Raise', 'EdTech'];
if (!in_array($lab, $allowed)) {
    header("Location: inventory.php");
    exit();
}

// SAFE QUERY — works even if is_archived column doesn't exist yet
$sql = "SELECT e.*, u.first_name, u.last_name 
        FROM equipment e 
        LEFT JOIN users u ON e.added_by = u.id 
        WHERE e.lab_location = ?";

// If the column exists, add the filter (prevents showing archived items)
$check = $conn->query("SHOW COLUMNS FROM equipment LIKE 'is_archived'");
if ($check && $check->num_rows > 0) {
    $sql .= " AND e.is_archived = 0";
}

$sql .= " ORDER BY e.pc_id";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $lab);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($lab) ?> Lab Inventory</title>
    <link rel="stylesheet" href="../assets/admin.css">
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

        .header-btn {
            padding: 14px 28px;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            text-decoration: none;
            color: white;
            transition: all .3s;
            margin-left: 10px;
        }

        .header-btn.back {
            background: #34495e;
        }

        .header-btn.primary {
            background: #27ae60;
            box-shadow: 0 6px 20px rgba(39, 174, 96, 0.4);
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
        }

        .user-table th {
            background: #2980b9;
            color: white;
            padding: 16px;
        }

        .user-table tr:hover {
            background: #f5f9ff;
        }

        .action-btn {
            display: inline-block;
            padding: 10px 18px;
            margin: 0 6px;
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
            font-size: 0.95rem;
            transition: all .3s;
        }

        .btn-edit {
            background: #3498db;
            color: white;
        }

        .btn-archive {
            background: #e74c3c;
            color: white;
        }

        .action-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
        }
    </style>
</head>

<body>
    <?php include 'include/sidebar.php'; ?>

    <div class="main-content">
        <div class="header-box">
            <div class="header-text">
                <h1>Inventory — <?= htmlspecialchars($lab) ?> Lab</h1>
            </div>
            <div class="header-actions">
                <a href="inventory.php" class="header-btn back">Back</a>
                <a href="add_equipment.php" class="header-btn primary">+ Add Equipment</a>
            </div>
        </div>

        <div class="content-card">
            <?php if ($result->num_rows > 0): ?>
                <table class="user-table" style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Serial</th>
                            <th>PC ID</th>
                            <th>Status</th>
                            <th>Added By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1;
                        while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td><strong><?= htmlspecialchars($row['name']) ?></strong></td>
                                <td><?= htmlspecialchars($row['serial_number'] ?: '—') ?></td>
                                <td><?= htmlspecialchars($row['pc_id'] ?: '—') ?></td>
                                <td>
                                    <span style="color:<?= $row['status'] == 'With Error' ? '#e74c3c' : '#27ae60' ?>; font-weight:bold;">
                                        <?= $row['status'] ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')) ?: 'System' ?></td>
                                <td>
                                    <a href="edit_equipment.php?id=<?= $row['id'] ?>" class="action-btn btn-edit">Edit</a>
                                    <a href="archive_action.php?type=equipment&id=<?= $row['id'] ?>"
                                        class="action-btn btn-archive"
                                        onclick="return confirm('Move to Archive Center?')">
                                        Archive
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="text-align:center;padding:80px;color:#95a5a6;font-size:1.3rem;">
                    No equipment in this lab yet.<br><br>
                    <a href="add_equipment.php" class="header-btn primary">+ Add First Item</a>
                </p>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>
<?php $stmt->close();
$conn->close(); ?>