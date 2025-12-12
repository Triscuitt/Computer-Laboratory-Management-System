<?php
session_start();
require_once "../dbconnection.php";
$conn = getConnection();

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

// REMOVED added_by join completely – column no longer exists
$sql = "SELECT equipment_id, name, serial_number, pc_id, lab_location, status, added_at
        FROM equipment 
        WHERE lab_location = ? 
        ORDER BY pc_id";

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .header-box {
            background: linear-gradient(135deg, #2980b9, #c0e0f6ff);
            color: white;
            padding: 32px 40px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(41, 128, 185, .3);
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
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .header-btn.back {
            background: #34495e;
        }

        .header-btn.primary {
            background: #27ae60;
            box-shadow: 0 6px 20px rgba(39, 174, 96, .4);
        }

        .header-btn.export {
            background: #e74c3c;
            box-shadow: 0 6px 20px rgba(231, 76, 60, .4);
        }

        .header-btn:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, .3);
        }

        /* SUCCESS/ERROR MESSAGE STYLES */
        .message-success {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            color: #155724;
            padding: 18px 24px;
            border-radius: 12px;
            margin: 20px 0;
            border-left: 6px solid #28a745;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.2);
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideIn 0.3s ease-out;
        }

        .message-error {
            background: linear-gradient(135deg, #f8d7da, #f5c6cb);
            color: #721c24;
            padding: 18px 24px;
            border-radius: 12px;
            margin: 20px 0;
            border-left: 6px solid #dc3545;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.2);
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideIn 0.3s ease-out;
        }

        .message-success i,
        .message-error i {
            font-size: 1.4rem;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .content-card {
            background: white;
            padding: 35px;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, .1);
        }

        .user-table {
            width: 100%;
            border-collapse: collapse;
        }

        .user-table th {
            background: #2980b9;
            color: white;
            padding: 16px;
            text-align: left;
        }

        .user-table td {
            padding: 16px;
            border-bottom: 1px solid #eee;
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
            font-size: .95rem;
            transition: all .3s;
        }

        .btn-edit {
            background: #3498db;
            color: white;
        }

        .btn-pullout {
            background: #e67e22;
            color: white;
        }

        .btn-pulled {
            background: #7f8c8d;
            color: white;
            cursor: not-allowed;
        }

        .action-btn:hover:not(.btn-pulled) {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, .25);
        }

        .status-available {
            color: #27ae60;
            font-weight: bold;
        }

        .status-error {
            color: #e74c3c;
            font-weight: bold;
        }

        .status-pulled {
            color: #95a5a6;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <?php include '../include/sidebar.php'; ?>

    <div class="main-content">
        <div class="header-box">
            <div class="header-text">
                <h1>Inventory – <?= htmlspecialchars($lab) ?> Lab</h1>
            </div>
            <div class="header-actions">
                <a href="inventory.php" class="header-btn back">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
                <a href="export_pdf.php?type=equipment&lab=<?= urlencode($lab) ?>" class="header-btn export">
                    <i class="fas fa-file-pdf"></i> Export PDF
                </a>
                <a href="add_equipment.php" class="header-btn primary">
                    <i class="fas fa-plus"></i> Add Equipment
                </a>
            </div>
        </div>

        <!-- EQUIPMENT SUCCESS MESSAGE -->
        <?php if (isset($_SESSION['equipment_success'])): ?>
            <div class="message-success">
                <i class="fas fa-check-circle"></i>
                <span><?= htmlspecialchars($_SESSION['equipment_success']) ?></span>
            </div>
            <?php unset($_SESSION['equipment_success']); ?>
        <?php endif; ?>

        <!-- EQUIPMENT ERROR MESSAGE -->
        <?php if (isset($_SESSION['equipment_error'])): ?>
            <div class="message-error">
                <i class="fas fa-exclamation-triangle"></i>
                <span><?= htmlspecialchars($_SESSION['equipment_error']) ?></span>
            </div>
            <?php unset($_SESSION['equipment_error']); ?>
        <?php endif; ?>

        <div class="content-card">
            <?php if ($result->num_rows > 0): ?>
                <table class="user-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Serial</th>
                            <th>PC ID</th>
                            <th>Status</th>
                            <th>Added Date</th>
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
                                    <?php
                                    $status = $row['status'];
                                    $class = $status === 'With Error' ? 'status-error' : ($status === 'Pulled out' ? 'status-pulled' : 'status-available');
                                    ?>
                                    <span class="<?= $class ?>"><?= htmlspecialchars($status) ?></span>
                                </td>
                                <td><?= $row['added_at'] ? date('M d, Y', strtotime($row['added_at'])) : '—' ?></td>
                                <td>
                                    <a href="edit_equipment.php?id=<?= $row['equipment_id'] ?>"
                                        class="action-btn btn-edit">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>

                                    <?php if ($row['status'] !== 'Pulled out'): ?>
                                        <a href="pullout_equipment.php?id=<?= $row['equipment_id'] ?>"
                                            class="action-btn btn-pullout"
                                            onclick="return confirm('⚠️ Mark this equipment as PULLED OUT?\n\nEquipment: <?= htmlspecialchars($row['name']) ?>\n\nThis action will update the equipment status.')">
                                            <i class="fas fa-box"></i> Pull Out
                                        </a>
                                    <?php else: ?>
                                        <span class="action-btn btn-pulled">
                                            <i class="fas fa-ban"></i> Pulled Out
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="text-align:center;padding:80px;color:#95a5a6;font-size:1.3rem;">
                    <i class="fas fa-inbox" style="font-size: 4rem; display: block; margin-bottom: 20px;"></i>
                    No equipment in this lab yet.<br><br>
                    <a href="add_equipment.php" class="header-btn primary" style="display: inline-block;">
                        <i class="fas fa-plus"></i> Add First Item
                    </a>
                </p>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>

<?php
$stmt->close();
$conn->close();
?>