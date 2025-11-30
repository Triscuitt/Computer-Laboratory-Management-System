<?php


session_start();
require_once '../config/db_connect.php';


if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'technician')) {
    header("Location: ../index.php");
    exit();
}

$conn->close();


$labs = ['Nexus', 'Sandbox', 'Raise', 'EdTech'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipment Inventory | Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .lab-button-container {
            display: flex;
            justify-content: space-around;
            gap: 20px;
            margin-top: 30px;
        }

        .lab-button {
            flex-grow: 1;
            padding: 40px 20px;
            text-align: center;
            font-size: 1.5em;
            font-weight: bold;
            color: white;
            background-color: #3498db;

            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s;
            text-decoration: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .lab-button:hover {
            background-color: #2980b9;
        }
    </style>
</head>

<body>

    <?php include 'include/sidebar.php'; ?>

    <div class="main-content">
        <div class="header">
            <h1>🖥️ Lab Equipment Inventory</h1>
        </div>

        <h2>Select a Laboratory to Manage Inventory</h2>

        <div class="lab-button-container">
            <?php foreach ($labs as $lab): ?>
                <?php

                $link = "inventory_view.php?lab=" . urlencode($lab);
                ?>
                <a href="<?php echo $link; ?>" class="lab-button">
                    <?php echo htmlspecialchars($lab); ?>
                </a>
            <?php endforeach; ?>
        </div>

    </div>

</body>

</html>