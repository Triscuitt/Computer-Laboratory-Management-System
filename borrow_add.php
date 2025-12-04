<?php
session_start();
require_once '../config/db_connect.php';

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'technician'])) {
    header("Location: ../index.php");
    exit();
}

$error = $success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = (int)$_POST['user_id'];
    $equipment_id = (int)$_POST['equipment_id'];

    if ($user_id <= 0 || $equipment_id <= 0) {
        $error = "Please select both a borrower and equipment.";
    } else {
        // Check if equipment is available
        $check = $conn->prepare("SELECT status FROM equipment WHERE equipment_id = ?");
        $check->bind_param("i", $equipment_id);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows === 0) {
            $error = "Equipment not found.";
        } else {
            $equip = $result->fetch_assoc();
            if ($equip['status'] !== 'Available') {
                $error = "This equipment is not available for borrowing.";
            } else {
                // Insert borrow record
                $stmt = $conn->prepare("INSERT INTO borrow (user_id, equipment_id, status) VALUES (?, ?, 'Borrowed')");
                $stmt->bind_param("ii", $user_id, $equipment_id);

                if ($stmt->execute()) {
                    // Update equipment status to Borrowed
                    $update = $conn->prepare("UPDATE equipment SET status = 'Borrowed' WHERE equipment_id = ?");
                    $update->bind_param("i", $equipment_id);
                    $update->execute();
                    $update->close();

                    $_SESSION['borrow_success'] = "Borrow transaction recorded successfully!";
                    header("Location: borrow.php");
                    exit();
                } else {
                    $error = "Failed to record transaction. Please try again.";
                }
                $stmt->close();
            }
        }
        $check->close();
    }
}

// Fetch all active users
$users = $conn->query("SELECT id, first_name, last_name, student_number, role FROM users WHERE account_status = 1 ORDER BY first_name, last_name");

// Fetch available equipment only
$equipment = $conn->query("SELECT equipment_id, name, pc_id, lab_location FROM equipment WHERE status = 'Available' ORDER BY lab_location, name");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Record New Borrow | Admin</title>
    <link rel="stylesheet" href="../assets/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .header-box {
            background: linear-gradient(135deg, #2980b9, #3498db);
            color: white;
            padding: 32px 40px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(41, 128, 185, 0.3);
            margin-bottom: 35px;
        }

        .header-box h1 {
            margin: 0;
            font-size: 2.2rem;
            font-weight: 700;
        }

        .back-link {
            display: inline-block;
            margin: 20px 0;
            color: #3498db;
            text-decoration: none;
            font-weight: 600;
            transition: 0.3s;
        }

        .back-link:hover {
            color: #2980b9;
        }

        .form-container {
            background: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
            max-width: 700px;
            margin: 0 auto;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
            font-size: 1rem;
        }

        .form-group select {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #ddd;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .form-group select:focus {
            border-color: #3498db;
            outline: none;
            box-shadow: 0 0 10px rgba(52, 152, 219, 0.3);
        }

        .btn-submit {
            background: #27ae60;
            color: white;
            padding: 16px 40px;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: all 0.3s;
            margin-top: 10px;
        }

        .btn-submit:hover {
            background: #229954;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(39, 174, 96, 0.3);
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
        }

        .info-box {
            background: #e8f4f8;
            border-left: 4px solid #3498db;
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 25px;
            color: #2c3e50;
        }

        .info-box i {
            color: #3498db;
            margin-right: 8px;
        }
    </style>
</head>

<body>
    <?php include 'include/sidebar.php'; ?>

    <div class="main-content">
        <div class="header-box">
            <h1>Record New Borrow Transaction</h1>
        </div>

        <a href="borrow.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Borrow Management
        </a>

        <?php if ($error): ?>
            <div class="message-error">
                <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <div class="info-box">
                <i class="fas fa-info-circle"></i>
                <strong>Note:</strong> This form records face-to-face borrow transactions. Select the borrower and the equipment they are taking.
            </div>

            <form method="POST">
                <div class="form-group">
                    <label for="user_id">
                        <i class="fas fa-user"></i> Select Borrower *
                    </label>
                    <select name="user_id" id="user_id" required>
                        <option value="">-- Choose Borrower --</option>
                        <?php while ($user = $users->fetch_assoc()): ?>
                            <option value="<?= $user['id'] ?>">
                                <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>
                                <?= $user['student_number'] ? " - " . htmlspecialchars($user['student_number']) : " (" . ucfirst($user['role']) . ")" ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="equipment_id">
                        <i class="fas fa-desktop"></i> Select Equipment *
                    </label>
                    <select name="equipment_id" id="equipment_id" required>
                        <option value="">-- Choose Equipment --</option>
                        <?php while ($equip = $equipment->fetch_assoc()): ?>
                            <option value="<?= $equip['equipment_id'] ?>">
                                <?= htmlspecialchars($equip['name']) ?>
                                <?= $equip['pc_id'] ? " (" . htmlspecialchars($equip['pc_id']) . ")" : "" ?>
                                - <?= htmlspecialchars($equip['lab_location']) ?> Lab
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-save"></i> Record Borrow Transaction
                </button>
            </form>
        </div>
    </div>
</body>

</html>

<?php
$conn->close();
?>