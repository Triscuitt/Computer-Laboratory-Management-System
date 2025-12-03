<?php
session_start();
require_once '../config/db_connect.php';

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'technician'])) {
    header("Location: ../index.php");
    exit();
}

$error = $success = '';

// CRITICAL FIX: Use NULL if user_id is missing → avoids foreign key error
$added_by = $_SESSION['user_id'] ?? NULL;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim($_POST['name']);
    $serial      = trim($_POST['serial_number'] ?? '');
    $pc_id       = trim($_POST['pc_id'] ?? '');
    $lab         = $_POST['lab_location'];        // Values: Nexus, Sandbox, Raise, EdTech
    $status      = $_POST['status'];

    if (empty($name) || empty($lab) || empty($status)) {
        $error = "Please fill in all required fields.";
    } else {
        // Safe insert — added_by can be NULL
        $stmt = $conn->prepare("INSERT INTO equipment 
            (name, serial_number, pc_id, lab_location, status, added_by) 
            VALUES (?, ?, ?, ?, ?, ?)");

        // "i" for integer, but NULL is allowed
        $stmt->bind_param("sssssi", $name, $serial, $pc_id, $lab, $status, $added_by);

        if ($stmt->execute()) {
            $success = "Equipment added successfully!";
        } else {
            $error = "Database error: " . $stmt->error;
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Equipment</title>
    <link rel="stylesheet" href="../assets/admin.css">
    <style>
        .header-box {
            background: linear-gradient(135deg, #2980b9, #ecececff);
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

        .message-success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }

        .message-error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }
    </style>
</head>

<body>
    <?php include 'include/sidebar.php'; ?>

    <div class="main-content">
        <div class="header-box">
            <div class="header-text">
                <h1>Add New Equipment</h1>
            </div>
            <div class="header-actions">
                <a href="inventory.php" class="header-btn back">Back to Labs</a>
            </div>
        </div>

        <?php if ($error)   echo "<div class='message-error'>$error</div>"; ?>
        <?php if ($success) echo "<div class='message-success'>$success</div>"; ?>

        <div class="form-container">
            <form method="POST">
                <label>Equipment Name *</label>
                <input type="text" name="name" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">

                <label>Serial Number</label>
                <input type="text" name="serial_number" value="<?= htmlspecialchars($_POST['serial_number'] ?? '') ?>">

                <label>PC / Unit ID</label>
                <input type="text" name="pc_id" value="<?= htmlspecialchars($_POST['pc_id'] ?? '') ?>">

                <label>Lab Location *</label>
                <select name="lab_location" required>
                    <option value="" disabled selected>-- Select Lab --</option>
                    <option value="Nexus">Nexus Lab</option>
                    <option value="Sandbox">Sandbox Lab</option>
                    <option value="Raise">Raise Lab</option>
                    <option value="EdTech">EdTech Lab</option>
                </select>

                <label>Status *</label>
                <select name="status" required>
                    <option value="Available">Available</option>
                    <option value="With Error">With Error</option>
                    <option value="Pulled out">Pulled out</option>
                </select>

                <button type="submit" style="width:100%; margin-top:25px; padding:16px; background:#27ae60; color:white; border:none; border-radius:12px; font-size:1.1rem; font-weight:600; cursor:pointer; box-shadow:0 6px 20px rgba(17,109,56,0.4);">
                    Add Equipment
                </button>
            </form>
        </div>
    </div>
</body>

</html>