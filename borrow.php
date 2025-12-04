<?php
session_start();
require_once '../config/db_connect.php';

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'technician'])) {
    header("Location: ../index.php");
    exit();
}

// Get filter
$status_filter = $_GET['status'] ?? 'all';

// Build query based on filter
$where_clause = "";
if ($status_filter !== 'all') {
    $where_clause = "WHERE b.status = '$status_filter'";
}

$sql = "SELECT b.borrow_id, b.borrow_date, b.return_date, b.status,
        u.id as user_id, u.first_name, u.last_name, u.student_number, u.role,
        e.equipment_id, e.name as equipment_name, e.pc_id, e.lab_location
        FROM borrow b
        JOIN users u ON b.user_id = u.id
        JOIN equipment e ON b.equipment_id = e.equipment_id
        $where_clause
        ORDER BY b.borrow_date DESC";

$result = $conn->query($sql);

// Get statistics
$stats = [
    'borrowed' => $conn->query("SELECT COUNT(*) as count FROM borrow WHERE status = 'Borrowed'")->fetch_assoc()['count'],
    'returned' => $conn->query("SELECT COUNT(*) as count FROM borrow WHERE status = 'Returned'")->fetch_assoc()['count'],
    'overdue' => $conn->query("SELECT COUNT(*) as count FROM borrow WHERE status = 'Overdue'")->fetch_assoc()['count']
];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrow Management | Admin</title>
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

        .btn-primary {
            background: #27ae60;
            box-shadow: 0 6px 20px rgba(39, 174, 96, 0.4);
        }

        .btn-archive-view {
            background: #95a5a6;
            box-shadow: 0 6px 20px rgba(149, 165, 166, 0.4);
        }

        .header-btn:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.3);
        }

        /* Statistics Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 16px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
        }

        .stat-card h3 {
            margin: 0 0 10px;
            font-size: 2.5rem;
            font-weight: 700;
        }

        .stat-card p {
            margin: 0;
            color: #7f8c8d;
            font-weight: 600;
        }

        .stat-borrowed h3 {
            color: #f39c12;
        }

        .stat-returned h3 {
            color: #27ae60;
        }

        .stat-overdue h3 {
            color: #e74c3c;
        }

        /* Filter Buttons */
        .filter-bar {
            background: white;
            padding: 20px;
            border-radius: 16px;
            margin-bottom: 25px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: center;
        }

        .filter-btn {
            padding: 12px 24px;
            border: 2px solid #ddd;
            border-radius: 50px;
            background: white;
            color: #2c3e50;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
        }

        .filter-btn:hover {
            border-color: #3498db;
            color: #3498db;
            transform: translateY(-2px);
        }

        .filter-btn.active {
            background: #3498db;
            color: white;
            border-color: #3498db;
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
        }

        /* Table Styles */
        .content-card {
            background: white;
            padding: 35px;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
            overflow-x: auto;
        }

        .borrow-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 900px;
        }

        .borrow-table th {
            background: #2980b9;
            color: white;
            padding: 16px;
            text-align: left;
            font-weight: 600;
        }

        .borrow-table td {
            padding: 16px;
            border-bottom: 1px solid #eee;
        }

        .borrow-table tr:hover {
            background: #f5f9ff;
        }

        /* Status Badges */
        .status-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
            display: inline-block;
        }

        .status-borrowed {
            background: #fff3cd;
            color: #856404;
        }

        .status-returned {
            background: #d4edda;
            color: #155724;
        }

        .status-overdue {
            background: #f8d7da;
            color: #721c24;
        }

        /* Action Buttons */
        .action-btn {
            padding: 8px 16px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-return {
            background: #27ae60;
            color: white;
        }

        .btn-return:hover {
            background: #229954;
            transform: translateY(-2px);
        }

        .btn-archive {
            background: #95a5a6;
            color: white;
            margin-left: 8px;
        }

        .btn-archive:hover {
            background: #7f8c8d;
            transform: translateY(-2px);
        }

        /* Messages */
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

        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: #95a5a6;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <?php include 'include/sidebar.php'; ?>

    <div class="main-content">
        <div class="header-box">
            <div class="header-text">
                <h1>Borrow Management</h1>
            </div>
            <div class="header-actions">
                <a href="borrow_archive.php" class="header-btn btn-archive-view">
                    <i class="fas fa-archive"></i> View Archive
                </a>
                <a href="borrow_add.php" class="header-btn btn-primary">
                    <i class="fas fa-plus"></i> Record New Borrow
                </a>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card stat-borrowed">
                <h3><?= $stats['borrowed'] ?></h3>
                <p>Currently Borrowed</p>
            </div>
            <div class="stat-card stat-returned">
                <h3><?= $stats['returned'] ?></h3>
                <p>Returned Items</p>
            </div>
            <div class="stat-card stat-overdue">
                <h3><?= $stats['overdue'] ?></h3>
                <p>Overdue Items</p>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="filter-bar">
            <span style="font-weight: 600; color: #2c3e50; margin-right: 10px;">Filter by Status:</span>
            <a href="borrow.php?status=all" class="filter-btn <?= $status_filter === 'all' ? 'active' : '' ?>">All</a>
            <a href="borrow.php?status=Borrowed" class="filter-btn <?= $status_filter === 'Borrowed' ? 'active' : '' ?>">Borrowed</a>
            <a href="borrow.php?status=Returned" class="filter-btn <?= $status_filter === 'Returned' ? 'active' : '' ?>">Returned</a>
            <a href="borrow.php?status=Overdue" class="filter-btn <?= $status_filter === 'Overdue' ? 'active' : '' ?>">Overdue</a>
        </div>

        <!-- Success/Error Messages -->
        <?php if (isset($_SESSION['borrow_success'])): ?>
            <div class="message-success">
                <i class="fas fa-check-circle"></i>
                <span><?= htmlspecialchars($_SESSION['borrow_success']) ?></span>
            </div>
            <?php unset($_SESSION['borrow_success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['borrow_error'])): ?>
            <div class="message-error">
                <i class="fas fa-exclamation-triangle"></i>
                <span><?= htmlspecialchars($_SESSION['borrow_error']) ?></span>
            </div>
            <?php unset($_SESSION['borrow_error']); ?>
        <?php endif; ?>

        <!-- Borrow Records Table -->
        <div class="content-card">
            <?php if ($result->num_rows > 0): ?>
                <table class="borrow-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Borrower</th>
                            <th>Equipment</th>
                            <th>Lab Location</th>
                            <th>Borrow Date</th>
                            <th>Return Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $i = 1;
                        while ($row = $result->fetch_assoc()):
                            $borrower = htmlspecialchars($row['first_name'] . ' ' . $row['last_name']);
                            $student_no = $row['student_number'] ? htmlspecialchars($row['student_number']) : ucfirst($row['role']);
                        ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td>
                                    <strong><?= $borrower ?></strong><br>
                                    <small style="color: #7f8c8d;"><?= $student_no ?></small>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($row['equipment_name']) ?></strong><br>
                                    <small style="color: #7f8c8d;"><?= htmlspecialchars($row['pc_id'] ?: '—') ?></small>
                                </td>
                                <td><?= htmlspecialchars($row['lab_location']) ?></td>
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
                                    <?php if ($row['status'] === 'Borrowed' || $row['status'] === 'Overdue'): ?>
                                        <a href="borrow_return.php?id=<?= $row['borrow_id'] ?>"
                                            class="action-btn btn-return"
                                            onclick="return confirm('Mark this item as RETURNED?\n\nEquipment: <?= htmlspecialchars($row['equipment_name']) ?>\nBorrower: <?= $borrower ?>')">
                                            <i class="fas fa-check"></i> Return
                                        </a>
                                    <?php endif; ?>
                                    <a href="borrow_archive_action.php?id=<?= $row['borrow_id'] ?>"
                                        class="action-btn btn-archive"
                                        onclick="return confirm('Archive this borrow record?\n\nYou can still view it in the Archive section.')">
                                        <i class="fas fa-archive"></i> Archive
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-box-open"></i>
                    <h3>No borrow records found</h3>
                    <p>Start by recording a new borrow transaction</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>

<?php
$conn->close();
?>