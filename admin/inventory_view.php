<?php


session_start();
require_once '../config/db_connect.php';

if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'technician')) {
    header("Location: ../index.php");
    exit();
}

$lab_location = $_GET['lab'] ?? null;


$allowed_labs = ['Nexus', 'Sandbox', 'Raise', 'EdTech'];
if (!in_array($lab_location, $allowed_labs)) {

    header("Location: inventory.php");
    exit();
}


$sql = "
    SELECT 
        e.equipment_id, e.name, e.serial_number, e.status, e.pc_id, u.fullname AS added_by_name
    FROM equipment e
    LEFT JOIN users u ON e.added_by = u.user_id
    WHERE e.lab_location = ? 
    ORDER BY e.status DESC, e.pc_id ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $lab_location);
$stmt->execute();
$result = $stmt->get_result();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($lab_location); ?> Inventory | Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>

<body>

    <?php include 'include/sidebar.php'; ?>

    <div class="main-content">
        <div class="header">
            <h1>🖥️ Inventory for <?php echo htmlspecialchars($lab_location); ?> Lab</h1>
            <a href="inventory.php" style="text-decoration: none;">
                <button class="action-button-primary" style="background-color: #34495e; margin-right: 10px;">← Back to Labs</button>
            </a>
            <button onclick="window.location.href='add_equipment.php'" class="action-button-primary">+ Add Equipment</button>
        </div>

        <table class="user-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Equipment Name</th>
                    <th>Serial Number</th>
                    <th>PC/Unit ID</th>
                    <th>Status</th>
                    <th>Added By</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $status_class = ($row['status'] == 'With Error/Problem') ? 'status-error' : (($row['status'] == 'Available & working') ? 'status-working' : 'status-pulled');

                        echo "<tr>";
                        echo "<td>" . $row['equipment_id'] . "</td>";
                        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['serial_number']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['pc_id'] ?? 'N/A') . "</td>";
                        echo "<td><span class='$status_class'>" . $row['status'] . "</span></td>";
                        echo "<td>" . htmlspecialchars($row['added_by_name'] ?? 'System') . "</td>";
                        echo "<td>
                                <a href='edit_equipment.php?id=" . $row['equipment_id'] . "' class='action-link edit'>Update</a> | 
                                <a href='archive_equipment.php?id=" . $row['equipment_id'] . "' class='action-link delete' onclick='return confirm(\"Archive/Pull Out this equipment?\")'>Archive</a>
                              </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='7'>No equipment records found for the **" . htmlspecialchars($lab_location) . "** lab.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

</body>

</html>