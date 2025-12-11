<?php
session_start();
require_once '../config/db_connect.php';

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'technician'])) {
    header("Location: ../index.php");
    exit();
}

$labs = ['Nexus', 'Sandbox', 'Raise', 'EdTech'];

// Get search query
$search = $_GET['search'] ?? '';
$search_results = [];
$total_results = 0;

// If there's a search query, fetch results from ALL labs
if (!empty($search)) {
    $search_term = "%{$search}%";
    $stmt = $conn->prepare("SELECT equipment_id, name, serial_number, pc_id, lab_location, status, added_at 
                            FROM equipment 
                            WHERE name LIKE ? OR serial_number LIKE ? OR pc_id LIKE ? OR status LIKE ?
                            ORDER BY lab_location, pc_id");
    $stmt->bind_param("ssss", $search_term, $search_term, $search_term, $search_term);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $search_results[] = $row;
    }

    $total_results = count($search_results);
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab Equipment Inventory</title>
    <link rel="stylesheet" href="../assets/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .header-btn.primary {
            background: #2739aeff;
            box-shadow: 0 6px 20px rgba(21, 9, 155, 0.4);
        }

        .header-btn:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.3);
        }

        /* SEARCH BAR STYLES */
        .search-container {
            background: white;
            padding: 25px 30px;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 25px;
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }

        .search-box {
            flex: 1;
            min-width: 250px;
            position: relative;
        }

        .search-box input {
            width: 100%;
            padding: 14px 48px 14px 20px;
            border: 2px solid #ddd;
            border-radius: 50px;
            font-size: 1rem;
            transition: all 0.3s;
            box-sizing: border-box;
        }

        .search-box input:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 15px rgba(52, 152, 219, 0.2);
        }

        .search-box .search-icon {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #7f8c8d;
            font-size: 1.1rem;
            pointer-events: none;
        }

        .search-btn {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            padding: 14px 32px;
            border: none;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
            font-size: 1rem;
        }

        .search-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(52, 152, 219, 0.4);
        }

        .clear-btn {
            background: #95a5a6;
            color: white;
            padding: 14px 28px;
            border: none;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
            font-size: 1rem;
        }

        .clear-btn:hover {
            background: #7f8c8d;
        }

        .search-info {
            background: linear-gradient(135deg, #e8f4f8, #d6eaf8);
            padding: 15px 25px;
            border-radius: 12px;
            color: #2c3e50;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            border-left: 5px solid #3498db;
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

        /* SEARCH RESULTS TABLE */
        .search-results {
            background: white;
            padding: 35px;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
        }

        .results-table {
            width: 100%;
            border-collapse: collapse;
        }

        .results-table th {
            background: #2980b9;
            color: white;
            padding: 16px;
            text-align: left;
            font-weight: 600;
        }

        .results-table td {
            padding: 16px;
            border-bottom: 1px solid #eee;
        }

        .results-table tr:hover {
            background: #f5f9ff;
        }

        .status-available {
            color: #27ae60;
            font-weight: bold;
        }

        .status-error {
            color: #e74c3c;
            font-weight: bold;
        }

        .status-pulled {
            color: #95a5a6;
            font-weight: bold;
        }

        .lab-badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            color: white;
        }

        .lab-nexus {
            background: #9b59b6;
        }

        .lab-sandbox {
            background: #f39c12;
        }

        .lab-raise {
            background: #e74c3c;
        }

        .lab-edtech {
            background: #27ae60;
        }

        .action-btn {
            padding: 8px 16px;
            margin-right: 6px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            display: inline-block;
            transition: all 0.3s;
            color: white;
        }

        .btn-view {
            background: #3498db;
        }

        .btn-edit {
            background: #27ae60;
        }

        .btn-view:hover,
        .btn-edit:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: #95a5a6;
            font-size: 1.2rem;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            display: block;
        }
    </style>
</head>

<body>
    <?php include 'include/sidebar.php'; ?>

    <div class="main-content">
        <div class="header-box">
            <div class="header-text">
                <h1>Lab Equipment Inventory</h1>
            </div>
            <div class="header-actions">
                <a href="add_equipment.php" class="header-btn primary">
                    <i class="fas fa-plus"></i> Add Equipment
                </a>
            </div>
        </div>

        <!-- SEARCH BAR -->
        <div class="search-container">
            <form method="GET" action="inventory.php" style="display: flex; gap: 15px; align-items: center; flex: 1; flex-wrap: wrap;">
                <div class="search-box">
                    <input
                        type="text"
                        name="search"
                        placeholder="Search across all labs: equipment name, PC ID, serial number, or status..."
                        value="<?= htmlspecialchars($search) ?>"
                        autofocus>
                    <i class="fas fa-search search-icon"></i>
                </div>
                <button type="submit" class="search-btn">
                    <i class="fas fa-search"></i>
                    Search All Labs
                </button>
                <?php if (!empty($search)): ?>
                    <a href="inventory.php" class="clear-btn">
                        <i class="fas fa-times"></i>
                        Clear
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <?php if (!empty($search)): ?>
            <!-- SEARCH RESULTS -->
            <div class="search-info">
                <i class="fas fa-info-circle"></i>
                <span>
                    Found <strong><?= $total_results ?></strong> result<?= $total_results != 1 ? 's' : '' ?> for "<strong><?= htmlspecialchars($search) ?></strong>" across all laboratories
                </span>
            </div>

            <div class="search-results">
                <?php if ($total_results > 0): ?>
                    <table class="results-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Equipment Name</th>
                                <th>PC ID</th>
                                <th>Serial Number</th>
                                <th>Laboratory</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1;
                            foreach ($search_results as $row): ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td><strong><?= htmlspecialchars($row['name']) ?></strong></td>
                                    <td><?= htmlspecialchars($row['pc_id'] ?: '—') ?></td>
                                    <td><?= htmlspecialchars($row['serial_number'] ?: '—') ?></td>
                                    <td>
                                        <span class="lab-badge lab-<?= strtolower($row['lab_location']) ?>">
                                            <?= htmlspecialchars($row['lab_location']) ?> Lab
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        $status = $row['status'];
                                        $class = $status === 'With Error' ? 'status-error' : ($status === 'Pulled out' ? 'status-pulled' : 'status-available');
                                        ?>
                                        <span class="<?= $class ?>"><?= htmlspecialchars($status) ?></span>
                                    </td>
                                    <td>
                                        <a href="inventory_view.php?lab=<?= urlencode($row['lab_location']) ?>" class="action-btn btn-view">
                                            <i class="fas fa-eye"></i> View Lab
                                        </a>
                                        <a href="edit_equipment.php?id=<?= $row['equipment_id'] ?>" class="action-btn btn-edit">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-search"></i>
                        <strong>No equipment found matching "<?= htmlspecialchars($search) ?>"</strong>
                        <p>Try searching with different keywords or browse labs below.</p>
                    </div>
                <?php endif; ?>
            </div>

        <?php else: ?>
            <!-- LAB CARDS (Default View) -->
            <div class="lab-grid">
                <?php foreach ($labs as $lab): ?>
                    <a href="inventory_view.php?lab=<?= urlencode($lab) ?>" class="lab-card">
                        <i class="fas fa-desktop"></i>
                        <div><?= htmlspecialchars($lab) ?> Lab</div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>

<?php $conn->close(); ?>