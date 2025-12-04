<?php
session_start();
require_once '../config/db_connect.php';

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'technician'])) {
    header("Location: ../index.php");
    exit();
}

$sql = "SELECT ab.borrow_id, ab.borrow_date, ab.return_date, ab.status,
        u.id as user_id, u.first_name, u.last_name, u.student_number, u.role,
        e.equipment_id, e.name as equipment_name, e.pc_id, e.lab_location,
        archiver.first_name as archiver_first, archiver.last_name as archiver_last,
        ab.archived_at
        FROM archive_borrow ab
        JOIN users u ON ab.user_id = u.id
        JOIN equipment e ON ab.equipment_id = e.equipment_id
        LEFT JOIN users archiver ON ab.archived_by = archiver.id
        ORDER BY ab.archived_at DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archived Borrow Records | Admin</title>
    <link rel="stylesheet" href="../assets/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* Reuse styles from borrow.php */
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

        .header-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .header-btn {
            padding: 14px 28px;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            text-decoration: none;
            color: white;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .header-btn.back {
            background: #34495e;
            box-shadow: 0 6px 20px rgba(52, 73, 94, 0.4);
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

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1200px;
        }

        th {
            background: #2980b9;
            color: white;
            padding: 16px 12px;
            text-align: left;
            font-weight: 600;
        }

        td {
            padding: 16px 12px;
            border-bottom: 1px solid #eee;
            vertical-align: top;
        }

        tr:hover {
            background: #f8f9fa;
        }

        .status-badge {
            padding: 6px 14px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
            display: inline-block;
            min-width: 80px;
            text-align: center;
        }

        .status-borrowed {
            background: #fdf2e9;
            color: #e67e22;
        }

        .status-returned {
            background: #e8f5e9;
            color: #27ae60;
        }

        .status-overdue {
            background: #fdeded;
            color: #c0392b;
        }

        .action-btn {
            padding: 8px 18px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            display: inline-block;
            margin: 4px;
            transition: 0.3s;
        }

        .btn-restore {
            background: #3498db;
            color: white;
        }

        .btn-delete {
            background: #e74c3c;
            color: white;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .empty-state {
            text-align: center;
            padding: 80px;
            color: #95a5a6;
            font-size: 1.3rem;
        }

        .empty-state i {
            font-size: 4rem;
            display: block;
            margin-bottom: 20px;
        }

        /* Lab tags */
        .lab-tag {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            color: white;
        }

        .nexus-tag {
            background: #3498db;
        }

        .sandbox-tag {
            background: #e67e22;
        }

        .raise-tag {
            background: #27ae60;
        }

        .edtech-tag {
            background: #9b59b6;
        }

        /* Message styles */
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
        }

        @media (max-width: 768px) {
            .header-box {
                padding: 25px;
                flex-direction: column;
                text-align: center;
            }

            .header-actions {
                justify-content: center;
            }

            .content-card {
                padding: 20px;
            }
        }
    </style>
</head>

<body>
    <?php include 'include/sidebar.php'; ?>

    <div class="main-content">
        <!-- Header Box -->
        <div class="header-box">
            <div class="header-text">
                <h1>Archived Borrow Records</h1>
            </div>
            <div class="header-actions">
                <a href="borrow.php" class="header-btn back">
                    <i class="fas fa-arrow-left"></i> Back to Active Borrows
                </a>
            </div>
        </div>

        <!-- Success/Error Messages -->
        <?php if (isset($_SESSION['borrow_success'])): ?>
            <div class="message-success">
                <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_SESSION['borrow_success']) ?>
            </div>
            <?php unset($_SESSION['borrow_success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['borrow_error'])): ?>
            <div class="message-error">
                <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($_SESSION['borrow_error']) ?>
            </div>
            <?php unset($_SESSION['borrow_error']); ?>
        <?php endif; ?>

        <div class="content-card">
            <?php if ($result->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Borrower</th>
                            <th>Equipment</th>
                            <th>Lab</th>
                            <th>Borrow Date</th>
                            <th>Return Date</th>
                            <th>Status</th>
                            <th>Archived By</th>
                            <th>Archived At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()):
                            $borrower = $row['first_name'] . ' ' . $row['last_name'];
                            $borrower_info = $row['student_number'] ? $row['student_number'] : ucfirst($row['role']);
                        ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($borrower) ?></strong><br>
                                    <small style="color: #7f8c8d;"><?= htmlspecialchars($borrower_info) ?></small>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($row['equipment_name']) ?></strong><br>
                                    <small style="color: #7f8c8d;"><?= htmlspecialchars($row['pc_id'] ?: '—') ?></small>
                                </td>
                                <td>
                                    <span class="lab-tag <?= strtolower(str_replace(' ', '-', $row['lab_location'])) ?>-tag">
                                        <?= htmlspecialchars($row['lab_location']) ?>
                                    </span>
                                </td>
                                <td><?= date('M d, Y g:i A', strtotime($row['borrow_date'])) ?></td>
                                <td>
                                    <?= $row['return_date'] ? date('M d, Y g:i A', strtotime($row['return_date'])) : '—' ?>
                                </td>
                                <td>
                                    <span class="status-badge status-<?= strtolower($row['status']) ?>">
                                        <?= htmlspecialchars($row['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?= htmlspecialchars($row['archiver_first'] . ' ' . $row['archiver_last']) ?>
                                </td>
                                <td><?= date('M d, Y g:i A', strtotime($row['archived_at'])) ?></td>
                                <td>
                                    <!-- Optional: Add restore or delete actions -->
                                    <!-- For now, no actions, or add if needed -->
                                    <span style="color: #95a5a6;">Archived</span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-archive"></i>
                    <h3>No archived borrow records</h3>
                    <p>Archived records will appear here once you archive active borrows.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>

<?php
$conn->close();
?>