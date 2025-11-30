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

    $name = $conn->real_escape_string($form_data['name']);
    $serial_number = $conn->real_escape_string($form_data['serial_number']);
    $pc_id = $conn->real_escape_string($form_data['pc_id']);
    $lab_location = $conn->real_escape_string($form_data['lab_location']);
    $description = $conn->real_escape_string($form_data['description']);
    $status = $conn->real_escape_string($form_data['status']);


    $added_by = $_SESSION['user_id'];

    if (empty($name) || empty($serial_number) || empty($status) || empty($lab_location)) {
        $error = "Equipment Name, Serial Number, Lab Location, and Status are required.";
    } else {

        $sql = "INSERT INTO equipment (name, serial_number, pc_id, lab_location, description, status, added_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);


        $pc_id_value = !empty($pc_id) ? $pc_id : NULL;
        $description_value = !empty($description) ? $description : NULL;


        $stmt->bind_param(
            "ssssssi",
            $name,
            $serial_number,
            $pc_id_value,
            $lab_location,
            $description_value,
            $status,
            $added_by
        );


        if ($stmt->execute()) {
            $success = "Equipment **{$name}** (S/N: {$serial_number}) successfully added to inventory in the **{$lab_location}** lab!";
            $form_data = array();
        } else {
            if ($conn->errno == 1062) {
                $error = "Registration failed: The Serial Number or PC/Unit ID already exists in the inventory.";
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
    <title>Add New Equipment | Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>

<body>

    <?php include 'include/sidebar.php'; ?>

    <div class="main-content">
        <div class="header">
            <h1>➕ Add New Lab Equipment</h1>
            <a href="inventory.php" style="text-decoration: none;">
                <button class="action-button-primary" style="background-color: #34495e;">← Back to Labs</button>
            </a>
        </div>

        <div class="form-container">
            <?php if ($error): ?>
                <div class="message-error"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="message-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <form action="add_equipment.php" method="POST">

                <label for="name">Equipment Name (*)</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($form_data['name'] ?? ''); ?>" required>

                <label for="serial_number">Serial Number (*)</label>
                <input type="text" id="serial_number" name="serial_number" value="<?php echo htmlspecialchars($form_data['serial_number'] ?? ''); ?>" required>

                <label for="pc_id">PC/Unit ID (e.g., PC-01, Monitor-A)</label>
                <input type="text" id="pc_id" name="pc_id" value="<?php echo htmlspecialchars($form_data['pc_id'] ?? ''); ?>">

                <label for="lab_location">Lab Location (*)</label>
                <select id="lab_location" name="lab_location" required>
                    <option value="">-- Select Lab --</option>
                    <?php $selected_lab = $form_data['lab_location'] ?? ''; ?>
                    <option value="Nexus" <?php if ($selected_lab == 'Nexus') echo 'selected'; ?>>Nexus</option>
                    <option value="Sandbox" <?php if ($selected_lab == 'Sandbox') echo 'selected'; ?>>Sandbox</option>
                    <option value="Raise" <?php if ($selected_lab == 'Raise') echo 'selected'; ?>>Raise</option>
                    <option value="EdTech" <?php if ($selected_lab == 'EdTech') echo 'selected'; ?>>EdTech</option>
                </select>

                <label for="status">Status (*)</label>
                <select id="status" name="status" required>
                    <option value="">-- Select Status --</option>
                    <?php $selected_status = $form_data['status'] ?? ''; ?>
                    <option value="Available & working" <?php if ($selected_status == 'Available & working') echo 'selected'; ?>>Available & Working</option>
                    <option value="With Error/Problem" <?php if ($selected_status == 'With Error/Problem') echo 'selected'; ?>>With Error/Problem</option>
                    <option value="Pulled out" <?php if ($selected_status == 'Pulled out') echo 'selected'; ?>>Pulled Out (Archived)</option>
                </select>

                <label for="description">Description / Notes</label>
                <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($form_data['description'] ?? ''); ?></textarea>

                <button type="submit" class="action-button-primary" style="width: 100%; margin-top: 15px;">Add to Inventory</button>
            </form>
        </div>
    </div>

</body>

</html>