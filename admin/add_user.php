<?php

session_start();
require_once '../config/db_connect.php';


if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'technician')) {
    header("Location: ../index.php");
    exit();
}

$error = '';
$success = '';
$form_data = $_POST;


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $fullname = $conn->real_escape_string($form_data['fullname']);
    $student_no = $conn->real_escape_string($form_data['student_no']);
    $username = $conn->real_escape_string($form_data['username']);
    $password = $form_data['password'];
    $email = $conn->real_escape_string($form_data['email']);
    $role = $conn->real_escape_string($form_data['role']);


    if (empty($fullname) || empty($username) || empty($password) || empty($email) || empty($role)) {
        $error = "All fields marked with (*) are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long.";
    } else {

        $password_hash = password_hash($password, PASSWORD_DEFAULT);


        $sql = "INSERT INTO users (fullname, student_no, username, password_hash, email, role, is_verified) 
                VALUES (?, ?, ?, ?, ?, ?, 1)";

        $stmt = $conn->prepare($sql);


        $student_no_value = (!empty($student_no) && ($role == 'student')) ? $student_no : NULL;


        $stmt->bind_param(
            "ssssss",
            $fullname,
            $student_no_value,
            $username,
            $password_hash,
            $email,
            $role
        );


        if ($stmt->execute()) {
            $success = "User **{$fullname}** ({$role}) successfully created!";

            $form_data = array();
        } else {

            if ($conn->errno == 1062) {
                $error = "Registration failed: The username or email already exists.";
            } else {
                $error = "Database Error: " . $conn->error;
            }
        }
        $stmt->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Add New User | Admin</title>
    <link rel="stylesheet" href="../assets/admin.css">
</head>

<body>

    <?php include 'include/sidebar.php'; ?>

    <div class="main-content">
        <div class="header">
            <h1>➕ Add New User</h1>
            <a href="users.php" style="text-decoration: none;">
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

            <form action="add_user.php" method="POST">

                <label for="fullname">Full Name (*)</label>
                <input type="text" id="fullname" name="fullname" value="<?php echo htmlspecialchars($form_data['fullname'] ?? ''); ?>" required>

                <label for="username">Username (*)</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($form_data['username'] ?? ''); ?>" required>

                <label for="email">School Email Address (*)</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>" required>

                <label for="password">Password (*)</label>
                <input type="password" id="password" name="password" required>

                <label for="role">Role (*)</label>
                <select id="role" name="role" required>
                    <option value="">-- Select Role --</option>
                    <?php $selected_role = $form_data['role'] ?? ''; ?>
                    <option value="student" <?php if ($selected_role == 'student') echo 'selected'; ?>>Student</option>
                    <option value="faculty" <?php if ($selected_role == 'faculty') echo 'selected'; ?>>Faculty Member</option>
                    <option value="technician" <?php if ($selected_role == 'technician') echo 'selected'; ?>>Technician</option>
                    <option value="admin" <?php if ($selected_role == 'admin') echo 'selected'; ?>>Admin</option>
                </select>

                <label for="student_no">Student No. (Required for Student Role)</label>
                <input type="text" id="student_no" name="student_no" value="<?php echo htmlspecialchars($form_data['student_no'] ?? ''); ?>">

                <button type="submit" class="action-button-primary" style="width: 100%; margin-top: 15px;">Create Account</button>
            </form>
        </div>
    </div>

</body>

</html>