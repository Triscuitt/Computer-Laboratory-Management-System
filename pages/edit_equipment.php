<?php
require_once "../model/user.php";
session_start();
require_once "../dbconnection.php";
$conn = getConnection();

// Turn on error reporting (remove in production if you want)
ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SESSION['User']->getRole() != 'admin') {
    header("Location: ../index.php");
    exit();
}

$equipment_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($equipment_id <= 0) {
    $_SESSION['error'] = "Invalid equipment ID.";
    header("Location: inventory.php");
    exit();
}

$error = $success = '';

// Fetch equipment
$stmt = $conn->prepare("SELECT * FROM equipment WHERE equipment_id = ?");
$stmt->bind_param("i", $equipment_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Equipment not found.";
    header("Location: inventory.php");
    exit();
}
$equip = $result->fetch_assoc();
$stmt->close();

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim($_POST['name'] ?? '');
    $serial      = trim($_POST['serial_number'] ?? '');
    $pc_id       = trim($_POST['pc_id'] ?? '');
    $lab         = $_POST['lab_location'] ?? '';
    $status      = $_POST['status'] ?? '';

    if (empty($name) || empty($lab) || empty($status)) {
        $error = "Please fill all required fields.";
    } else {
        $stmt = $conn->prepare("UPDATE equipment SET 
            name = ?, serial_number = ?, pc_id = ?, lab_location = ?, status = ? 
            WHERE equipment_id = ?");
        $stmt->bind_param("sssssi", $name, $serial, $pc_id, $lab, $status, $equipment_id);

        if ($stmt->execute()) {
            $success = "Equipment updated successfully!";
            $equip['name']          = $name;
            $equip['serial_number'] = $serial;
            $equip['pc_id']         = $pc_id;
            $equip['lab_location']  = $lab;
            $equip['status']        = $status;
        } else {
            $error = "Update failed: " . $stmt->error;
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
        body {
            background: #f1f5f9;
            font-family: Arial, sans-serif;
        }

        .form-container {
            max-width: 600px;
            margin: 40px auto;
            background: white;
            padding: 35px;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #2c3e50;
            margin-top: 0;
        }

        .back-link {
            display: inline-block;
            margin: 15px 0;
            color: #3498db;
            text-decoration: none;
            font-weight: 600;
        }

        input,
        select {
            width: 100%;
            padding: 14px;
            margin: 12px 0;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-size: 1rem;
            box-sizing: border-box;
        }

        button {
            background: #3498db;
            color: white;
            padding: 14px 30px;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            cursor: pointer;
            margin-top: 10px;
        }

        button:hover {
            background: #2980b9;
        }

        .success {
            background: #d5f4e6;
            color: #27ae60;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
        }

        .error {
            background: #fadbd8;
            color: #e74c3c;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
        }
    </style>
</head>

<body>
    <?php include '../include/sidebar.php'; ?>

    <div class="main-content">
        <div class="form-container">
            <h2>Edit Equipment</h2>
            <a href="inventory.php" class="back-link">Back to Inventory</a>

            <?php if ($success): ?>
                <div class="success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <label>Equipment Name</label>
                <input type="text" name="name" required value="<?= htmlspecialchars($equip['name']) ?>">

                <label>Serial Number (optional)</label>
                <input type="text" name="serial_number" value="<?= htmlspecialchars($equip['serial_number'] ?? '') ?>">

                <label>PC ID (e.g. PC-NX-015)</label>
                <input type="text" name="pc_id" value="<?= htmlspecialchars($equip['pc_id'] ?? '') ?>">

                <label>Lab Location</label>
                <select name="lab_location" required>
                    <option value="Nexus" <?= $equip['lab_location'] == 'Nexus'   ? 'selected' : '' ?>>Nexus Lab</option>
                    <option value="Sandbox" <?= $equip['lab_location'] == 'Sandbox' ? 'selected' : '' ?>>Sandbox Lab</option>
                    <option value="Raise" <?= $equip['lab_location'] == 'Raise'   ? 'selected' : '' ?>>Raise Lab</option>
                    <option value="EdTech" <?= $equip['lab_location'] == 'EdTech'  ? 'selected' : '' ?>>EdTech Lab</option>
                </select>

                <label>Status</label>
                <select name="status" required>
                    <option value="Available" <?= $equip['status'] == 'Available'   ? 'selected' : '' ?>>Available</option>
                    <option value="With Error" <?= $equip['status'] == 'With Error'  ? 'selected' : '' ?>>With Error</option>
                    <option value="Pulled out" <?= $equip['status'] == 'Pulled out'  ? 'selected' : '' ?>>Pulled out</option>
                </select>

                <button type="submit">Update Equipment</button>
            </form>
        </div>
    </div>
</body>

</html>

<?php $conn->close(); ?>