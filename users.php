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
        }
    </style>
</head>

<body>
    <?php include 'include/sidebar.php'; ?>

    <div class="main-content">
        <div class="header-box">
            <h1>User Management Panel</h1>
            <a href="add_user.php" class="btn-add">
                <i class="fas fa-plus"></i> Add New User
            </a>
        </div>

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
                                        onclick="return confirm('Move this user to archive?')">
                                        Archive
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-state">
                <h3>No active users yet.</h3>
                <a href="add_user.php" class="btn-primary">Add Your First User Now</a>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>

<?php
$stmt->close();
$conn->close();
?>