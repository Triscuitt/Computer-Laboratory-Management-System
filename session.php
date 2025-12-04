<?php
session_start();
require_once '../config/db_connect.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$labs = ['All Labs', 'Nexus Lab', 'Sandbox Lab', 'Raise Lab', 'EdTech Lab'];
$selected_lab = $_GET['lab'] ?? 'All Labs';

// Build filter condition safely
$lab_filter = '';
$params = [];
$types = '';

if ($selected_lab !== 'All Labs') {
    $lab_filter = "WHERE s.lab_name = ?";
    $params[] = $selected_lab;
    $types = 's';
}

// Active Sessions
$active_sql = "SELECT s.*, u.first_name, u.last_name,
               (SELECT COUNT(*) FROM session_attendance a WHERE a.session_id = s.session_id) as attendees
               FROM lab_sessions s 
               JOIN users u ON s.faculty_id = u.id 
               $lab_filter AND s.is_active = 1 
               ORDER BY s.created_at DESC";

$stmt = $conn->prepare($active_sql);
if ($types) $stmt->bind_param($types, ...$params);
$stmt->execute();
$active_result = $stmt->get_result();   // Fixed: use get_result(), not num_rows directly

// Past Sessions
$past_sql = "SELECT s.*, u.first_name, u.last_name,
             (SELECT COUNT(*) FROM session_attendance a WHERE a.session_id = s.session_id) as attendees,
             TIMESTAMPDIFF(MINUTE, s.created_at, COALESCE(s.expires_at, NOW())) as duration_min
             FROM lab_sessions s 
             JOIN users u ON s.faculty_id = u.id 
             $lab_filter AND s.is_active = 0 
             ORDER BY s.created_at DESC LIMIT 100";

$stmt = $conn->prepare($past_sql);
if ($types) $stmt->bind_param($types, ...$params);
$stmt->execute();
$past_result = $stmt->get_result();     // Fixed: same here
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab Session Records | Admin</title>
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
            text-align: left;
        }

        .header-box h1 {
            margin: 0;
            font-size: 2.4rem;
            font-weight: 700;
        }

        /* LAB FILTER BUTTONS — NOW FULLY VISIBLE & GORGEOUS */
        .lab-filter {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            justify-content: center;
            margin: 30px 0;
            padding: 20px;
            background: #0f517cff;
            border-radius: 16px;
            box-shadow: 0 8px 25px rgba(39, 1, 1, 0.08);
        }

        .filter-btn {
            padding: 14px 28px;
            border: none;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.05rem;
            cursor: pointer;
            transition: all 0.3s ease;
            color: white;
            min-width: 140px;
            text-align: center;
            text-decoration: none;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .filter-btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.3);
        }

        .filter-btn.active {
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.4);
            font-weight: 700;
        }

        /* Colors */
        .all {
            background: #34495e;
        }

        .nexus {
            background: #9b59b6;
        }

        .sandbox {
            background: #f39c12;
        }

        .raise {
            background: #e74c3c;
        }

        .edtech {
            background: #27ae60;
        }

        /* Session Cards */
        .session-card {
            background: white;
            padding: 25px;
            border-radius: 16px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .active-badge {
            background: #27ae60;
            color: white;
            padding: 10px 20px;
            border-radius: 30px;
            font-weight: bold;
        }

        .ended-badge {
            background: #95a5a6;
            color: white;
            padding: 10px 20px;
            border-radius: 30px;
        }

        .lab-tag {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.95rem;
            font-weight: bold;
            color: white;
        }

        .nexus-tag {
            background: #9b59b6;
        }

        .sandbox-tag {
            background: #f39c12;
        }

        .raise-tag {
            background: #e74c3c;
        }

        .edtech-tag {
            background: #27ae60;
        }

        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: #95a5a6;
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        }
    </style>
</head>

<body>
    <?php include 'include/sidebar.php'; ?>

    <div class="main-content">

        <!-- HEADER -->
        <div class="header-box">
            <h1>Lab Session Records</h1>
        </div>

        <!-- LAB FILTER BUTTONS — NOW VISIBLE & STUNNING -->
        <div class="lab-filter">
            <?php foreach ($labs as $lab):
                $class = strtolower(str_replace(' ', '-', $lab));
                $is_active = ($selected_lab === $lab) ? 'active' : '';
            ?>
                <a href="?lab=<?= urlencode($lab) ?>"
                    class="filter-btn <?= $class ?> <?= $is_active ?>">
                    <?= $lab === 'All Labs' ? 'All Labs' : $lab ?>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- ACTIVE SESSIONS -->
        <h2 style="color:#2c3e50; margin:30px 0 15px;">
            Active Sessions <?= $selected_lab !== 'All Labs' ? "— $selected_lab" : '' ?>
        </h2>
        <?php if ($active_result->num_rows > 0): ?>
            <?php while ($s = $active_result->fetch_assoc()): ?>
                <div class="session-card">
                    <div>
                        <span class="lab-tag <?= strtolower(str_replace(' ', '-', $s['lab_name'])) ?>-tag">
                            <?= htmlspecialchars($s['lab_name']) ?>
                        </span>
                        <strong style="margin-left:12px;">Code: <?= $s['session_code'] ?></strong><br>
                        <small style="color:#7f8c8d;">
                            By <?= htmlspecialchars($s['first_name'] . ' ' . $s['last_name']) ?> •
                            Started <?= date('M j, Y g:i A', strtotime($s['created_at'])) ?>
                        </small>
                    </div>
                    <span class="active-badge">
                        LIVE • <?= $s['attendees'] ?> student<?= $s['attendees'] != 1 ? 's' : '' ?>
                    </span>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-state">
                <h3>No active sessions <?= $selected_lab !== 'All Labs' ? "in $selected_lab" : '' ?></h3>
            </div>
        <?php endif; ?>

        <!-- PAST SESSIONS -->
        <h2 style="color:#2c3e50; margin:50px 0 15px;">
            Recent Sessions <?= $selected_lab !== 'All Labs' ? "— $selected_lab" : '' ?>
        </h2>
        <?php if ($past_result->num_rows > 0): ?>
            <?php while ($s = $past_result->fetch_assoc()): ?>
                <div class="session-card">
                    <div>
                        <span class="lab-tag <?= strtolower(str_replace(' ', '-', $s['lab_name'])) ?>-tag">
                            <?= htmlspecialchars($s['lab_name']) ?>
                        </span>
                        <strong style="margin-left:12px;">Code: <?= $s['session_code'] ?></strong> •
                        By <?= htmlspecialchars($s['first_name'] . ' ' . $s['last_name']) ?><br>
                        <small style="color:#7f8c8d;">
                            Duration: <?= $s['duration_min'] ?> min •
                            Attendees: <?= $s['attendees'] ?>
                        </small>
                    </div>
                    <span class="ended-badge">Ended</span>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-state">
                <h3>No past sessions recorded <?= $selected_lab !== 'All Labs' ? "for $selected_lab" : '' ?></h3>
            </div>
        <?php endif; ?>

    </div>
</body>

</html>

<?php
$stmt->close();
$conn->close();
?>