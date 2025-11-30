<?php


session_start();


require_once '../config/db_connect.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}


$sql = "SELECT user_id, fullname, student_no, username, email, role, is_verified, is_archived
        FROM users 
        WHERE is_archived = 0 
        ORDER BY role DESC, fullname ASC";
$result = $conn->query($sql);

if (!$result) {
    die("Error fetching user data: " . $conn->error);
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users | Admin</title>
    <link rel="stylesheet" href="../assets/admin.css">
</head>

<body>

    <?php include 'include/sidebar.php'; ?>

    <div class="main-content">
        <div class="header">
            <h1>👥 User Management Panel</h1>
            <button
                onclick="window.location.href='add_user.php'"
                style="padding: 10px 20px; background-color: #2ecc71; color: white; border: none; border-radius: 5px; cursor: pointer;">+ Add New User</button>
        </div>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="message-success"><?php echo $_SESSION['success_message'];
                                            unset($_SESSION['success_message']); ?></div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="message-error"><?php echo $_SESSION['error_message'];
                                        unset($_SESSION['error_message']); ?></div>
        <?php endif; ?>

        <table class="user-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Full Name</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Verified</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {

                        $verification_status = $row['is_verified'] ? '✅ Yes' : '❌ No';
                        $role_class = 'role-' . $row['role'];

                        echo "<tr>";
                        echo "<td>" . $row['user_id'] . "</td>";
                        echo "<td>" . htmlspecialchars($row['fullname']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['email']) . "</td>";

                        echo "<td><span class='$role_class'>" . ucfirst($row['role']) . "</span></td>";
                        echo "<td>" . $verification_status . "</td>";
                        echo "<td>
                                <a href='edit_user.php?id=" . $row['user_id'] . "' class='action-link edit'>Edit</a> | 
                                <a href='archive_user.php?id=" . $row['user_id'] . "' class='action-link delete' onclick='return confirm(\"Are you sure you want to archive this user?\")'>Archive</a>
                            </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='7'>No users found in the database.</td></tr>";
                }
                ?>
            </tbody>
        </table>

    </div>

</body>

</html>