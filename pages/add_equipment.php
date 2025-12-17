<?php
session_start();
require_once "../dbconnection.php";
$conn = getConnection();
if ($_SESSION['User']->getRole() != 'admin') {
    header("Location: ../index.php");
    exit();
}

$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim($_POST['name']);
    $serial      = trim($_POST['serial_number'] ?? '');
    $pc_id       = trim($_POST['pc_id'] ?? '');
    $lab         = $_POST['lab_location'];
    $status      = $_POST['status'];

    if (empty($name) || empty($lab) || empty($status)) {
        $error = "Please fill in all required fields.";
    } else {
        $stmt = $conn->prepare("INSERT INTO equipment 
            (name, serial_number, pc_id, lab_location, status) 
            VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $name, $serial, $pc_id, $lab, $status);

        if ($stmt->execute()) {
            $success = "Equipment added successfully!";
            // Clear form
            $_POST = [];
        } else {
            $error = "Failed to add equipment. Please try again.";
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
    <title>Add Equipment</title>
    <link rel="stylesheet" href="../assets/admin.css">
    <style>
        .form-container {
            max-width: 600px;
            margin: 40px auto;
            background: white;
            padding: 35px;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
        }

        input,
        select {
            width: 100%;
            padding: 14px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-size: 1rem;
        }

        button {
            background: #27ae60;
            color: white;
            padding: 14px 30px;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            cursor: pointer;
        }

        button:hover {
            background: #219653;
        }

        .back-link {
            display: inline-block;
            margin: 20px 0;
            color: #3498db;
            text-decoration: none;
        }
    </style>
</head>

<body>
    <?php include '../include/sidebar.php'; ?>
    <div class="main-content">
        <div class="form-container">
            <h2>Add New Equipment</h2>
            <a href="inventory.php" class="back-link">← Back to Inventory</a>

            <?php if ($success): ?>
                <p style="color:green;font-weight:bold;"><?= $success ?></p>
            <?php endif; ?>
            <?php if ($error): ?>
                <p style="color:red;font-weight:bold;"><?= $error ?></p>
            <?php endif; ?>

            <form method="POST">
                <input type="text" name="name" placeholder="Equipment Name (e.g. Desktop PC 05)" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">

                <input type="text" name="serial_number" placeholder="Serial Number (optional)" value="<?= htmlspecialchars($_POST['serial_number'] ?? '') ?>">

                <input type="text" name="pc_id" placeholder="PC ID (e.g. PC-NX-015)" value="<?= htmlspecialchars($_POST['pc_id'] ?? '') ?>">

                <select name="lab_location" required>
                    <option value="">-- Select Lab --</option>
                    <option value="Nexus" <?= (($_POST['lab_location'] ?? '') == 'Nexus') ? 'selected' : '' ?>>Nexus Lab</option>
                    <option value="Sandbox" <?= (($_POST['lab_location'] ?? '') == 'Sandbox') ? 'selected' : '' ?>>Sandbox Lab</option>
                    <option value="Raise" <?= (($_POST['lab_location'] ?? '') == 'Raise') ? 'selected' : '' ?>>Raise Lab</option>
                    <option value="EdTech" <?= (($_POST['lab_location'] ?? '') == 'EdTech') ? 'selected' : '' ?>>EdTech Lab</option>
                </select>

                <select name="status" required>
                    <option value="Available" <?= (($_POST['status'] ?? 'Available') == 'Available') ? 'selected' : '' ?>>Available</option>
                    <option value="With Error" <?= (($_POST['status'] ?? '') == 'With Error') ? 'selected' : '' ?>>With Error</option>
                    <option value="Pulled out" <?= (($_POST['status'] ?? '') == 'Pulled out') ? 'selected' : '' ?>>Pulled out</option>
                </select>

                <button type="submit">Add Equipment</button>
            </form>
        </div>
    </div>
</body>

</html>