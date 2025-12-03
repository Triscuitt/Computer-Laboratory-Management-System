<?php
session_start();
require_once '../config/db_connect.php';

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'technician'])) {
    header("Location: ../index.php");
    exit();
}

$id = $_GET['id'] ?? 0;
if (!$id || !is_numeric($id)) {
    header("Location: inventory.php");
    exit();
}

$error = $success = '';

// Fetch equipment
$stmt = $conn->prepare("SELECT * FROM equipment WHERE id = ? AND is_archived = 0");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    $_SESSION['error'] = "Equipment not found.";
    header("Location: inventory.php");
    exit();
}
$equip = $result->fetch_assoc();
$stmt->close();

// Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $serial = trim($_POST['serial_number'] ?? '');
    $pc_id = trim($_POST['pc_id'] ?? '');
    $lab = $_POST['lab_location'];
    $status = $_POST['status'];

    if (empty($name) || empty($lab) || empty($status)) {
        $error = "Please fill all required fields.";
    } else {
        $stmt = $conn->prepare("UPDATE equipment SET name=?, serial_number=?, pc_id=?, lab_location=?, status=? WHERE id=?");
        $stmt->bind_param("sssssi", $name, $serial, $pc_id, $lab, $status, $id);
        if ($stmt->execute()) {
            $success = "Equipment updated successfully!";
            // Refresh data
            header("Location: edit_equipment.php?id=$id&success=1");
            exit();
        } else {
            $error = "Update failed.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Equipment</title>
    <link rel="stylesheet" href="../assets/admin.css">
    <style>
        .header-box {
            background: linear-gradient(135deg, #2980b9, #3498db);
            color: white;
            padding: 35px;
            border-radius: 16px;
            margin-bottom: 30px;
            text-align: center;
        }

        .form-container {
            background: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            max-width: 700px;
            margin: 0 auto;
        }

        input,
        select {
            width: 100%;
            padding: 14px;
            margin: 10px 0 20px;
            border: 2px solid #ddd;
            border-radius: 10px;
            font-size: 1rem;
        }

        input:focus,
        select:focus {
            border-color: #3498db;
            outline: none;
        }

        button {
            background: #27ae60;
            color: white;
            padding: 16px;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            cursor: pointer;
            width: 100%;
            margin-top: 20px;
        }

        .back-btn {
            display: inline-block;
            margin: 20px 0;
            color: #3498db;
            font-weight: 600;
            text-decoration: none;
        }

        .message-success {
            background: #d4edda;
            color: #155724;
            padding: 18px;
            border-radius: 12px;
            margin: 20px 0;
            text-align: center;
            font-weight: 600;
        }

        .message-error {
            background: #f8d7da;
            color: #721c24;
            padding: 18px;
            border-radius: 12px;
            margin: 20px 0;
            text-align: center;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <?php include 'include/sidebar.php'; ?>

    <div class="main-content">
        <div class="header-box">
            <h1>Edit Equipment</h1>
            <p>ID: <?= $equip['id'] ?> • <?= htmlspecialchars($equip['name']) ?></p>
        </div>

        <a href="inventory.php" class="back-btn">← Back to Inventory</a>

        <?php if (isset($_GET['success'])): ?>
            <div class="message-success">Equipment updated successfully!</div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="message-error"><?= $error ?></div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST">
                <label>Equipment Name *</label>
                <input type="text" name="name" value="<?= htmlspecialchars($equip['name']) ?>" required>

                <label>Serial Number</label>
                <input type="text" name="serial_number" value="<?= htmlspecialchars($equip['serial_number'] ?? '') ?>">

                <label>PC / Unit ID</label>
                <input type="text" name="pc_id" value="<?= htmlspecialchars($equip['pc_id'] ?? '') ?>">

                <label>Lab Location *</label>
                <select name="lab_location" required>
                    <option value="Nexus" <?= $equip['lab_location'] == 'Nexus' ? 'selected' : '' ?>>Nexus Lab</option>
                    <option value="Sandbox" <?= $equip['lab_location'] == 'Sandbox' ? 'selected' : '' ?>>Sandbox Lab</option>
                    <option value="Raise" <?= $equip['lab_location'] == 'Raise' ? 'selected' : '' ?>>Raise Lab</option>
                    <option value="EdTech" <?= $equip['lab_location'] == 'EdTech' ? 'selected' : '' ?>>EdTech Lab</option>
                </select>

                <label>Status *</label>
                <select name="status" required>
                    <option value="Available" <?= $equip['status'] == 'Available' ? 'selected' : '' ?>>Available</option>
                    <option value="With Error" <?= $equip['status'] == 'With Error' ? 'selected' : '' ?>>With Error</option>
                    <option value="Pulled out" <?= $equip['status'] == 'Pulled out' ? 'selected' : '' ?>>Pulled out</option>
                </select>

                <button type="submit">Save Changes</button>
            </form>
        </div>
    </div>
</body>

</html>