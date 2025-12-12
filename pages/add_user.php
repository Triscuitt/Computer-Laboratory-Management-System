<?php
session_start();
require_once "../dbconnection.php";
$conn = getConnection();

if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'technician')) {
    header("Location: ../index.php");
    exit();
}

$error = $success = '';


// Inside add_user.php — after form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name     = trim($_POST['first_name']);
    $middle_name    = trim($_POST['middle_name'] ?? '');
    $last_name      = trim($_POST['last_name']);
    $suffix         = trim($_POST['suffix'] ?? '');
    $student_number = !empty($_POST['student_number']) ? trim($_POST['student_number']) : NULL;
    $username       = trim($_POST['username']);
    $email          = trim($_POST['email']);
    $password       = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role           = $_POST['role'];
    $account_status = 1; // 1 = active

    // Basic validation
    if (empty($first_name) || empty($last_name) || empty($username) || empty($email)) {
        $error = "Please fill in all required fields.";
    } elseif ($role === 'student' && empty($student_number)) {
        $error = "Student number is required for students.";
    } else {
        // Check if username or email already exists
        $check = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $check->bind_param("ss", $username, $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "Username or Email already taken. Please choose another.";
        } else {
            // INSERT NEW USER
            $stmt = $conn->prepare("INSERT INTO users 
                (first_name, middle_name, last_name, suffix, student_number, username, email, password, role, account_status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            $stmt->bind_param(
                "sssssssssi",
                $first_name,
                $middle_name,
                $last_name,
                $suffix,
                $student_number,
                $username,
                $email,
                $password,
                $role,
                $account_status
            );

            if ($stmt->execute()) {
                $success = "User <strong>$username</strong> added successfully!";
            } else {
                $error = "Database error. Try again.";
            }
            $stmt->close();
        }
        $check->close();
    }
}
?>
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New User | Admin</title>
    <link rel="stylesheet" href="../assets/admin.css">
    <style>
        /* SAME HEADER BOX AS ALL OTHER PAGES */
        .header-box {
            background: linear-gradient(135deg, #2980b9, #ffffffff);
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

        .header-btn {
            padding: 14px 32px;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            text-decoration: none;
            color: white;
            transition: all 0.3s;
        }

        .header-btn.primary {
            background: #27ae60;
            box-shadow: 0 6px 20px rgba(17, 109, 56, 0.4);
        }

        .header-btn.back {
            background: #34495e;
        }

        .header-btn:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.3);
        }

        .form-container {
            background: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
            max-width: 650px;
            margin: 0 auto;
        }

        .form-container label {
            display: block;
            margin: 20px 0 8px;
            font-weight: 600;
            color: #2c3e50;
        }

        .form-container input,
        .form-container select {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #ddd;
            border-radius: 10px;
            font-size: 1rem;
        }

        .form-container input:focus,
        .form-container select:focus {
            border-color: #3498db;
            outline: none;
            box-shadow: 0 0 10px rgba(52, 152, 219, 0.3);
        }
    </style>
</head>

<body>
    <?php include '../include/sidebar.php'; ?>

    <div class="main-content">

        <!-- EXACT SAME HEADER BOX (no description text) -->
        <div class="header-box">
            <div class="header-text">
                <h1>Add New User</h1>
            </div>
            <div class="header-actions">
                <a href="users.php" class="header-btn back">Back to Users</a>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="message-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="message-success"><?= $success ?></div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST">
                <label>First Name *</label>
                <input type="text" name="first_name" value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>" required>

                <label>Middle Name</label>
                <input type="text" name="middle_name" value="<?= htmlspecialchars($_POST['middle_name'] ?? '') ?>">

                <label>Last Name *</label>
                <input type="text" name="last_name" value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>" required>

                <label>Suffix</label>
                <input type="text" name="suffix" value="<?= htmlspecialchars($_POST['suffix'] ?? '') ?>">

                <label>Student Number <?php echo ($_POST['role'] ?? '') === 'student' ? '*' : '' ?></label>
                <input type="text" name="student_number" value="<?= htmlspecialchars($_POST['student_number'] ?? '') ?>">

                <label>Username *</label>
                <input type="text" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>

                <label>Email *</label>
                <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>

                <label>Password *</label>
                <input type="password" name="password" required minlength="8">

                <label>Role *</label>
                <select name="role" required>
                    <option value="">-- Select Role --</option>
                    <option value="admin" <?= ($_POST['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="technician" <?= ($_POST['role'] ?? '') === 'technician' ? 'selected' : '' ?>>Technician</option>
                    <option value="faculty" <?= ($_POST['role'] ?? '') === 'faculty' ? 'selected' : '' ?>>Faculty</option>
                    <option value="student" <?= ($_POST['role'] ?? '') === 'student' ? 'selected' : '' ?>>Student</option>
                </select>

                <button type="submit" class="header-btn primary" style="width:100%; margin-top:25px;">
                    Create User Account
                </button>
            </form>
        </div>
    </div>
</body>

</html>