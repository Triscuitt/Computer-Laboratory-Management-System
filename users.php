<?php
session_start();
require_once '../config/db_connect.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Handle Archive Action
if (isset($_GET['archive']) && is_numeric($_GET['archive'])) {
    $user_id = (int)$_GET['archive'];
    $stmt = $conn->prepare("UPDATE users SET account_status = 0 WHERE id = ? AND role != 'admin'");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
    header("Location: users.php");
    exit();
}

$stmt = $conn->prepare("SELECT id, first_name, middle_name, last_name, suffix, username, email, role, student_number, created_at 
                        FROM users WHERE account_status = 1 ORDER BY created_at DESC");
$stmt->execute();
$users = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management Panel</title>
    <link rel="stylesheet" href="../assets/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .header-box {
            background: linear-gradient(135deg, #2980b9, #c2daeaff);
            color: white;
            padding: 32px 40px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(41, 128, 185, 0.3);
            margin-bottom: 35px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-box h1 {
            margin: 0;
            font-size: 2.4rem;
            font-weight: 700;
        }

        .btn-add {
            background: #2c3e50;
            color: white;
            padding: 12px 28px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
        }

        .btn-add:hover {
            transform: translateY(-3px);
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

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
        }

        th {
            background: #3498db;
            color: white;
            padding: 18px;
            text-align: left;
        }

        td {
            padding: 16px 18px;
            border-bottom: 1px solid #eee;
        }

        tr:hover {
            background: #f8f9fa;
        }

        .role-admin {
            background: #e74c3c;
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
        }

        .role-technician {
            background: #f39c12;
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
        }

        .role-faculty {
            background: #9b59b6;
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
        }

        .role-student {
            background: #27ae60;
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
        }

        .action-btn {
            padding: 8px 16px;
            margin-right: 8px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            display: inline-block;
            transition: all 0.3s;
        }

        .btn-edit {
            background: #3498db;
            color: white;
        }

        .btn-archive {
            background: #e74c3c;
            color: white;
        }

        .btn-edit:hover,
        .btn-archive:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }

        .empty-state {
            text-align: center;
            padding: 100px 20px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
        }

        .btn-primary {
            background: #2980b9;
            color: white;
            padding: 16px 40px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            box-shadow: 0 8px 25px rgba(41, 128, 185, 0.4);
            display: inline-block;
            margin-top: 20px;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(41, 128, 185, 0.5);
        }
    </style>
</head>

<body>
    <?php include 'include/sidebar.php'; ?>

    <div class="main-content">
        <div class="header-box">
            <h1>User Management Panel</h1>
            <div>
                <a href="export_pdf.php?type=users" class="btn-add" style="background: #e74c3c; margin-right: 10px;">
                    <i class="fas fa-file-pdf"></i> Export PDF
                </a>
                <a href="add_user.php" class="btn-add">
                    <i class="fas fa-plus"></i> Add New User
                </a>
            </div>
        </div>

        <!-- SUCCESS MESSAGE -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="message-success">
                <i class="fas fa-check-circle"></i>
                <span><?= htmlspecialchars($_SESSION['success']) ?></span>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <!-- ERROR MESSAGE -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="message-error">
                <i class="fas fa-exclamation-triangle"></i>
                <span><?= htmlspecialchars($_SESSION['error']) ?></span>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if ($users->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Student No.</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = $users->fetch_assoc()): ?>
                        <?php
                        $fullname = trim($user['first_name'] . ' ' . ($user['middle_name'] ? $user['middle_name'][0] . '. ' : '') . $user['last_name'] . ' ' . $user['suffix']);
                        ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($fullname) ?></strong></td>
                            <td><?= htmlspecialchars($user['username']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td>
                                <span class="role-<?= strtolower($user['role']) ?>">
                                    <?= ucfirst($user['role']) ?>
                                </span>
                            </td>
                            <td><?= $user['student_number'] ?: '—' ?></td>
                            <td><?= date('M j, Y', strtotime($user['created_at'])) ?></td>
                            <td>
                                <a href="edit_user.php?id=<?= $user['id'] ?>" class="action-btn btn-edit">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <?php if ($user['role'] !== 'admin'): ?>
                                    <a href="archive_action.php?type=user&id=<?= $user['id'] ?>"
                                        class="action-btn btn-archive"
                                        onclick="return confirm('⚠️ Archive this user?\n\nUser: <?= htmlspecialchars($fullname) ?>\nRole: <?= ucfirst($user['role']) ?>\n\nThis will move them to the archive and remove them from active users.')">
                                        <i class="fas fa-archive"></i> Archive
                                    </a>
                                <?php else: ?>
                                    <span style="color: #95a5a6; font-size: 0.85rem; font-style: italic;">
                                        <i class="fas fa-lock"></i> Protected
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-users" style="font-size: 4rem; color: #bdc3c7; margin-bottom: 20px;"></i>
                <h3 style="color: #7f8c8d;">No active users yet.</h3>
                <p style="color: #95a5a6; margin: 15px 0;">Start by creating your first user account.</p>
                <a href="add_user.php" class="btn-primary">
                    <i class="fas fa-user-plus"></i> Add Your First User Now
                </a>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>

<?php
$stmt->close();
$conn->close();
?>