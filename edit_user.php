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

$error = $success = '';

// === SHOW MESSAGES FROM URL (after redirect) ===
if (isset($_GET['success'])) {
    $success = "User updated successfully!";
}
if (isset($_GET['error'])) {
    $error = urldecode($_GET['error']);
}

// Fetch user
$stmt = $conn->prepare("SELECT id, first_name, middle_name, last_name, suffix, username, email, role, student_number 
                        FROM users WHERE id = ? AND account_status = 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "User not found or archived.";
    header("Location: users.php");
    exit();
}
$user = $result->fetch_assoc();
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name     = trim($_POST['first_name']);
    $middle_name    = trim($_POST['middle_name'] ?? '');
    $last_name      = trim($_POST['last_name']);
    $suffix         = trim($_POST['suffix'] ?? '');
    $username       = trim($_POST['username']);
    $email          = trim($_POST['email']);
    $role           = $_POST['role'];
    $student_number = $role === 'student' ? trim($_POST['student_number']) : null;
    $new_password   = $_POST['new_password'] ?? '';

    // Validation
    if (empty($first_name) || empty($last_name) || empty($username) || empty($email)) {
        $error = "All required fields must be filled.";
    } elseif ($role === 'student' && empty($student_number)) {
        $error = "Student number is required for students.";
    } else {
        // Check duplicate username/email
        $check = $conn->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
        $check->bind_param("ssi", $username, $email, $user_id);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $error = "Username or email already taken by another user.";
        }
        $check->close();

        if (empty($error)) {
            // Password update
            if (!empty($new_password)) {
                if (strlen($new_password) < 6) {
                    $error = "Password must be at least 6 characters.";
                } else {
                    $hash = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE users SET 
                        first_name=?, middle_name=?, last_name=?, suffix=?, username=?, email=?, role=?, student_number=?, password=?
                        WHERE id=?");
                    $stmt->bind_param("sssssssssi", $first_name, $middle_name, $last_name, $suffix, $username, $email, $role, $student_number, $hash, $user_id);
                }
            } else {
                $stmt = $conn->prepare("UPDATE users SET 
                    first_name=?, middle_name=?, last_name=?, suffix=?, username=?, email=?, role=?, student_number=?
                    WHERE id=?");
                $stmt->bind_param("ssssssssi", $first_name, $middle_name, $last_name, $suffix, $username, $email, $role, $student_number, $user_id);
            }

            if (empty($error)) {
                if ($stmt->execute()) {
                    // SUCCESS → redirect with message
                    header("Location: edit_user.php?id=$user_id&success=1");
                    exit();
                } else {
                    $error = "Update failed. Please try again.";
                }
                $stmt->close();
            }
        }
    }

    // If error → stay on page and show message
    if (!empty($error)) {
        // We stay here, $error will be shown below
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link rel="stylesheet" href="../assets/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .header-box {
            background: linear-gradient(135deg, #2980b9, #3498db);
            color: white;
            padding: 30px;
            border-radius: 16px;
            margin-bottom: 30px;
            text-align: center;
        }

        .form-container {
            background: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
            max-width: 700px;
            margin: 0 auto;
        }

        input,
        select {
            width: 100%;
            padding: 14px;
            margin: 8px 0 20px;
            border: 2px solid #ddd;
            border-radius: 10px;
            font-size: 1rem;
        }

        input:focus,
        select:focus {
            border-color: #3498db;
            outline: none;
            box-shadow: 0 0 10px rgba(52, 152, 219, 0.3);
        }

        button {
            background: #27ae60;
            color: white;
            padding: 16px;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            margin-top: 10px;
        }

        button:hover {
            background: #219a52;
        }

        .back-btn {
            display: inline-block;
            margin-bottom: 20px;
            color: #3498db;
            text-decoration: none;
            font-weight: 600;
        }

        .back-btn:hover {
            text-decoration: underline;
        }

        /* MESSAGES — NOW ALWAYS VISIBLE & BEAUTIFUL */
        .message-success {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            color: #155724;
            padding: 18px;
            border-radius: 12px;
            margin: 20px 0;
            border-left: 6px solid #28a745;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.2);
            text-align: center;
        }

        .message-error {
            background: linear-gradient(135deg, #f8d7da, #f5c6cb);
            color: #721c24;
            padding: 18px;
            border-radius: 12px;
            margin: 20px 0;
            border-left: 6px solid #dc3545;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.2);
            text-align: center;
        }
    </style>
</head>

<body>
    <?php include 'include/sidebar.php'; ?>

    <div class="main-content">
        <div class="header-box">
            <h1>Edit User: <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h1>
        </div>

        <a href="users.php" class="back-btn">← Back to Users List</a>

        <!-- SUCCESS / ERROR MESSAGES — ALWAYS SHOW -->
        <?php if ($success): ?>
            <div class="message-success">
                <i class="fas fa-check-circle"></i> <?= $success ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="message-error">
                <i class="fas fa-exclamation-triangle"></i> <?= $error ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST">
                <label>First Name *</label>
                <input type="text" name="first_name" value="<?= htmlspecialchars($user['first_name']) ?>" required>

                <label>Middle Name</label>
                <input type="text" name="middle_name" value="<?= htmlspecialchars($user['middle_name'] ?? '') ?>">

                <label>Last Name *</label>
                <input type="text" name="last_name" value="<?= htmlspecialchars($user['last_name']) ?>" required>

                <label>Suffix</label>
                <input type="text" name="suffix" value="<?= htmlspecialchars($user['suffix'] ?? '') ?>">

                <label>Username *</label>
                <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>

                <label>Email *</label>
                <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>

                <label>Role *</label>
                <select name="role" required>
                    <option value="student" <?= $user['role'] == 'student' ? 'selected' : '' ?>>Student</option>
                    <option value="faculty" <?= $user['role'] == 'faculty' ? 'selected' : '' ?>>Faculty</option>
                    <option value="technician" <?= $user['role'] == 'technician' ? 'selected' : '' ?>>Technician</option>
                    <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                </select>

                <label>Student Number <?= $user['role'] === 'student' ? '(Required)' : '' ?></label>
                <input type="text" name="student_number" value="<?= htmlspecialchars($user['student_number'] ?? '') ?>"
                    <?= $user['role'] === 'student' ? 'required' : '' ?>>

                <hr style="margin:35px 0; border:none; border-top:2px dashed #eee;">

                <label>New Password <small>(leave blank to keep current)</small></label>
                <input type="password" name="new_password" placeholder="Enter new password (min. 6 chars)">

                <button type="submit">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </form>
        </div>
    </div>
</body>

</html>

<?php $conn->close(); ?>