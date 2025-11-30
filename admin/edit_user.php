<?php


session_start();
require_once '../config/db_connect.php';


if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$user_id = $_GET['id'] ?? null;
if (!$user_id || !is_numeric($user_id)) {
    header("Location: users.php");
    exit();
}

$error = '';
$success = '';
$user = null;


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $fullname = $conn->real_escape_string($_POST['fullname']);
    $student_no = $conn->real_escape_string($_POST['student_no']);
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $role = $conn->real_escape_string($_POST['role']);
    $new_password = $_POST['new_password'];


    $password_update = '';
    if (!empty($new_password)) {
        if (strlen($new_password) < 8) {
            $error = "New password must be at least 8 characters long.";
        } else {

            $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $password_update = ", password_hash = '" . $password_hash . "'";
        }
    }

    if (empty($error)) {

        $sql = "UPDATE users SET 
                fullname = ?, 
                student_no = ?, 
                username = ?, 
                email = ?, 
                role = ?
                {$password_update} 
                WHERE user_id = ?";

        $stmt = $conn->prepare($sql);


        $stmt->bind_param("sssssi", $fullname, $student_no, $username, $email, $role, $user_id);

        if ($stmt->execute()) {
            $success = "User **{$fullname}** (ID: {$user_id}) updated successfully!";
        } else {
            if ($conn->errno == 1062) {
                $error = "Update failed: Username or email already exists.";
            } else {
                $error = "Database Error: " . $conn->error;
            }
        }
        $stmt->close();
    }
}


$sql_fetch = "SELECT user_id, fullname, student_no, username, email, role FROM users WHERE user_id = ?";
$stmt_fetch = $conn->prepare($sql_fetch);
$stmt_fetch->bind_param("i", $user_id);
$stmt_fetch->execute();
$result_fetch = $stmt_fetch->get_result();

if ($result_fetch->num_rows === 1) {
    $user = $result_fetch->fetch_assoc();
} else {

    header("Location: users.php");
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit User | Admin</title>
    <link rel="stylesheet" href="../assets/admin.css">
</head>

<body>

    <?php include 'include/sidebar.php'; ?>

    <div class="main-content">
        <div class="header">
            <h1>✏️ Edit User: <?php echo htmlspecialchars($user['fullname']); ?></h1>
            <a href="users.php" style="text-decoration: none; color: white;">
                <button class="action-button-primary" style="background-color: #34495e;">← Back to Users</button>
            </a>
        </div>

        <div class="form-container">
            <?php if ($error): ?>
                <div class="message-error"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="message-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <form action="edit_user.php?id=<?php echo $user_id; ?>" method="POST">

                <label for="fullname">Full Name</label>
                <input type="text" id="fullname" name="fullname" value="<?php echo htmlspecialchars($user['fullname']); ?>" required>

                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>

                <label for="email">School Email Address</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

                <label for="role">Role</label>
                <select id="role" name="role" required>
                    <option value="student" <?php if ($user['role'] == 'student') echo 'selected'; ?>>Student</option>
                    <option value="faculty" <?php if ($user['role'] == 'faculty') echo 'selected'; ?>>Faculty Member</option>
                    <option value="technician" <?php if ($user['role'] == 'technician') echo 'selected'; ?>>Technician</option>
                    <option value="admin" <?php if ($user['role'] == 'admin') echo 'selected'; ?>>Admin</option>
                </select>

                <label for="student_no">Student No. (Required for Student Role)</label>
                <input type="text" id="student_no" name="student_no" value="<?php echo htmlspecialchars($user['student_no'] ?? ''); ?>">

                <hr style="margin: 25px 0; border-color: #eee;">

                <label for="new_password">New Password (Leave blank to keep existing password)</label>
                <input type="password" id="new_password" name="new_password">

                <button type="submit" class="action-button-primary" style="width: 100%; margin-top: 15px;">Save Changes</button>
            </form>
        </div>
    </div>

</body>

</html>