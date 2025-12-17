<?php
require_once "../model/user.php";
session_start();
require_once "../dbconnection.php";
$conn = getConnection();

if ($_SESSION['User']->getRole() != 'admin') {
    header("Location: ../index.php");
    exit();
}
$labs = ['Nexus', 'Sandbox', 'Raise', 'EdTech'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab Equipment Inventory</title>
    <link rel="stylesheet" href="../assets/admin.css">
    <style>
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
            background: #2739aeff;
            box-shadow: 0 6px 20px rgba(21, 9, 155, 0.4);
        }

        .header-btn:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.3);
        }

        .lab-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
        }

        .lab-card {
            background: white;
            padding: 40px 20px;
            border-radius: 16px;
            text-align: center;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
            transition: 0.3s;
            text-decoration: none;
            color: #2c3e50;
            font-size: 1.4rem;
            font-weight: 600;
        }

        .lab-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
        }

        .lab-card i {
            font-size: 4rem;
            margin-bottom: 20px;
            color: #3498db;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
    <?php include '../include/sidebar.php'; ?>

    <div class="main-content">

        <!-- SAME HEADER BOX -->
        <div class="header-box">
            <div class="header-text">
                <h1>Lab Equipment Inventory</h1>
            </div>
            <div class="header-actions">
                <a href="add_equipment.php" class="header-btn primary">+ Add Equipment</a>
            </div>
        </div>

        <div class="lab-grid">
            <?php foreach ($labs as $lab): ?>
                <a href="inventory_view.php?lab=<?= urlencode($lab) ?>" class="lab-card">
                    <i class="fas fa-desktop"></i>
                    <div><?= htmlspecialchars($lab) ?> Lab</div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</body>

</html>