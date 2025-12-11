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

// Get search query
$search = $_GET['search'] ?? '';

// Build query with search
if (!empty($search)) {
    $search_term = "%{$search}%";
    $stmt = $conn->prepare("SELECT id, first_name, middle_name, last_name, suffix, username, email, role, student_number, created_at 
                            FROM users 
                            WHERE account_status = 1 
                            AND (first_name LIKE ? OR last_name LIKE ? OR username LIKE ? OR email LIKE ? OR student_number LIKE ?)
                            ORDER BY created_at DESC");
    $stmt->bind_param("sssss", $search_term, $search_term, $search_term, $search_term, $search_term);
} else {
    $stmt = $conn->prepare("SELECT id, first_name, middle_name, last_name, suffix, username, email, role, student_number, created_at 
                            FROM users WHERE account_status = 1 ORDER BY created_at DESC");
}

$stmt->execute();
$users = $stmt->get_result();
$total_results = $users->num_rows;
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
            flex-wrap: wrap;
            gap: 20px;
        }

        .header-box h1 {
            margin: 0;
            font-size: 2.4rem;
            font-weight: 700;
        }

        .header-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn-add {
            background: #2c3e50;
            color: white;
            padding: 12px 28px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }

        .btn-add:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        }

        .btn-export {
            background: #e74c3c;
            box-shadow: 0 6px 20px rgba(231, 76, 60, 0.3);
        }

        .btn-export:hover {
            background: #c0392b;
        }

        /* SEARCH BAR STYLES */
        .search-container {
            background: white;
            padding: 25px 30px;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 25px;
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }

        .search-box {
            flex: 1;
            min-width: 250px;
            position: relative;
        }

        .search-box input {
            width: 100%;
            padding: 14px 48px 14px 20px;
            border: 2px solid #ddd;
            border-radius: 50px;
            font-size: 1rem;
            transition: all 0.3s;
            box-sizing: border-box;
        }

        .search-box input:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 15px rgba(52, 152, 219, 0.2);
        }

        .search-box .search-icon {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #7f8c8d;
            font-size: 1.1rem;
            pointer-events: none;
        }

        .search-btn {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            padding: 14px 32px;
            border: none;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
            font-size: 1rem;
        }

        .search-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(52, 152, 219, 0.4);
        }

        .clear-btn {
            background: #95a5a6;
            color: white;
            padding: 14px 28px;
            border: none;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
            font-size: 1rem;
        }

        .clear-btn:hover {
            background: #7f8c8d;
        }

        .search-info {
            background: linear-gradient(135deg, #e8f4f8, #d6eaf8);
            padding: 15px 25px;
            border-radius: 12px;
            color: #2c3e50;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            border-left: 5px solid #3498db;
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

        .highlight {
            background-color: #fff3cd;
            font-weight: bold;
            padding: 2px 4px;
            border-radius: 3px;
        }
    </style>
</head>

<body>
    <?php include 'include/sidebar.php'; ?>

    <div class="main-content">
        <div class="header-box">
            <h1>User Management Panel</h1>
            <div class="header-actions">
                <a href="export_pdf.php?type=users" class="btn-add btn-export">
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

        <!-- SEARCH BAR -->
        <div class="search-container">
            <form method="GET" action="users.php" style="display: flex; gap: 15px; align-items: center; flex: 1; flex-wrap: wrap;">
                <div class="search-box">
                    <input
                        type="text"
                        name="search"
                        placeholder="Search by name, username, email, or student number..."
                        value="<?= htmlspecialchars($search) ?>"
                        autofocus>
                    <i class="fas fa-search search-icon"></i>
                </div>
                <button type="submit" class="search-btn">
                    <i class="fas fa-search"></i>
                    Search
                </button>
                <?php if (!empty($search)): ?>
                    <a href="users.php" class="clear-btn">
                        <i class="fas fa-times"></i>
                        Clear
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <!-- SEARCH RESULTS INFO -->
        <?php if (!empty($search)): ?>
            <div class="search-info">
                <i class="fas fa-info-circle"></i>
                <span>
                    Found <strong><?= $total_results ?></strong> result<?= $total_results != 1 ? 's' : '' ?> for "<strong><?= htmlspecialchars($search) ?></strong>"
                </span>
            </div>
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
                <i class="fas fa-<?= !empty($search) ? 'search' : 'users' ?>" style="font-size: 4rem; color: #bdc3c7; margin-bottom: 20px;"></i>
                <?php if (!empty($search)): ?>
                    <h3 style="color: #7f8c8d;">No users found matching "<?= htmlspecialchars($search) ?>"</h3>
                    <p style="color: #95a5a6; margin: 15px 0;">Try searching with different keywords or clear the search.</p>
                    <a href="users.php" class="btn-primary">
                        <i class="fas fa-times"></i> Clear Search
                    </a>
                <?php else: ?>
                    <h3 style="color: #7f8c8d;">No active users yet.</h3>
                    <p style="color: #95a5a6; margin: 15px 0;">Start by creating your first user account.</p>
                    <a href="add_user.php" class="btn-primary">
                        <i class="fas fa-user-plus"></i> Add Your First User Now
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>

<?php
$stmt->close();
$conn->close();
?>