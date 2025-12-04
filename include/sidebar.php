<?php
// Get current page filename for active highlighting
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="sidebar">
    <!-- Logo Section -->
    <div class="sidebar-header">
        <div class="logo-container">
            <i class="fas fa-laptop-code"></i>
            <div class="logo-text">
                <h2>DYCI Lab</h2>
                <span class="subtitle">Admin Panel</span>
            </div>
        </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="sidebar-nav">
        <ul>
            <li class="<?= $current_page === 'dashboard.php' ? 'active' : '' ?>">
                <a href="dashboard.php">
                    <i class="fas fa-chart-line"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <li class="<?= $current_page === 'users.php' ? 'active' : '' ?>">
                <a href="users.php">
                    <i class="fas fa-users"></i>
                    <span>Manage Users</span>
                </a>
            </li>

            <li class="<?= $current_page === 'session.php' ? 'active' : '' ?>">
                <a href="session.php">
                    <i class="fas fa-clipboard-list"></i>
                    <span>Session Records</span>
                </a>
            </li>

            <li class="<?= $current_page === 'inventory.php' || $current_page === 'inventory_view.php' ? 'active' : '' ?>">
                <a href="inventory.php">
                    <i class="fas fa-desktop"></i>
                    <span>Lab Equipment</span>
                </a>
            </li>

            <li class="<?= $current_page === 'borrow.php' || $current_page === 'borrow_add.php' ? 'active' : '' ?>">
                <a href="borrow.php">
                    <i class="fas fa-hand-holding"></i>
                    <span>Borrow Management</span>
                </a>
            </li>

            <li class="<?= $current_page === 'request_panel.php' ? 'active' : '' ?>">
                <a href="request_panel.php">
                    <i class="fas fa-envelope-open-text"></i>
                    <span>Requests Panel</span>
                </a>
            </li>

            <li class="<?= $current_page === 'reports.php' ? 'active' : '' ?>">
                <a href="reports.php">
                    <i class="fas fa-file-alt"></i>
                    <span>Reports</span>
                </a>
            </li>
        </ul>
    </nav>

    <!-- User Profile Section -->
    <div class="sidebar-profile">
        <div class="profile-info">
            <div class="profile-avatar">
                <i class="fas fa-user-shield"></i>
            </div>
            <div class="profile-details">
                <strong><?= htmlspecialchars($_SESSION['first_name'] ?? 'Admin') ?></strong>
                <small><?= ucfirst($_SESSION['role'] ?? 'Administrator') ?></small>
            </div>
        </div>
    </div>

    <!-- Logout Button -->
    <div class="sidebar-logout">
        <a href="../logout.php" class="logout-btn" onclick="return confirm('Are you sure you want to log out?')">
            <i class="fas fa-sign-out-alt"></i>
            <span>Log Out</span>
        </a>
    </div>
</div>

<style>
    /* ============================================
   MODERN SIDEBAR STYLES
   ============================================ */

    .sidebar {
        width: 280px;
        height: 100vh;
        background: linear-gradient(180deg, #1e3c72 0%, #2a5298 100%);
        position: fixed;
        left: 0;
        top: 0;
        overflow-y: auto;
        box-shadow: 4px 0 20px rgba(0, 0, 0, 0.15);
        display: flex;
        flex-direction: column;
        z-index: 1000;
    }

    /* Custom Scrollbar */
    .sidebar::-webkit-scrollbar {
        width: 6px;
    }

    .sidebar::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.05);
    }

    .sidebar::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.2);
        border-radius: 10px;
    }

    .sidebar::-webkit-scrollbar-thumb:hover {
        background: rgba(255, 255, 255, 0.3);
    }

    /* ============================================
   HEADER / LOGO SECTION
   ============================================ */

    .sidebar-header {
        padding: 30px 20px 25px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .logo-container {
        display: flex;
        align-items: center;
        gap: 15px;
        color: white;
    }

    .logo-container i {
        font-size: 2.5rem;
        color: #64b5f6;
        text-shadow: 0 0 20px rgba(100, 181, 246, 0.5);
    }

    .logo-text h2 {
        margin: 0;
        font-size: 1.5rem;
        font-weight: 700;
        color: white;
        line-height: 1.2;
    }

    .logo-text .subtitle {
        font-size: 0.8rem;
        color: rgba(255, 255, 255, 0.7);
        font-weight: 400;
    }

    /* ============================================
   NAVIGATION MENU
   ============================================ */

    .sidebar-nav {
        flex: 1;
        padding: 20px 0;
    }

    .sidebar-nav ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .sidebar-nav li {
        margin: 5px 15px;
        border-radius: 12px;
        transition: all 0.3s ease;
    }

    .sidebar-nav li a {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 14px 18px;
        color: rgba(255, 255, 255, 0.85);
        text-decoration: none;
        border-radius: 12px;
        font-weight: 500;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .sidebar-nav li a::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        height: 100%;
        width: 4px;
        background: #64b5f6;
        transform: scaleY(0);
        transition: transform 0.3s ease;
    }

    .sidebar-nav li a i {
        font-size: 1.2rem;
        width: 24px;
        text-align: center;
        transition: all 0.3s ease;
    }

    .sidebar-nav li:hover a {
        background: rgba(255, 255, 255, 0.1);
        color: white;
        transform: translateX(5px);
    }

    .sidebar-nav li:hover a i {
        transform: scale(1.1);
        color: #64b5f6;
    }

    /* Active State */
    .sidebar-nav li.active a {
        background: rgba(100, 181, 246, 0.2);
        color: white;
        font-weight: 600;
        box-shadow: 0 4px 15px rgba(100, 181, 246, 0.3);
    }

    .sidebar-nav li.active a::before {
        transform: scaleY(1);
    }

    .sidebar-nav li.active a i {
        color: #64b5f6;
    }

    /* ============================================
   PROFILE SECTION
   ============================================ */

    .sidebar-profile {
        padding: 15px 20px;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .profile-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .profile-avatar {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.2rem;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    }

    .profile-details {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .profile-details strong {
        color: white;
        font-size: 0.95rem;
        font-weight: 600;
    }

    .profile-details small {
        color: rgba(255, 255, 255, 0.6);
        font-size: 0.75rem;
    }

    /* ============================================
   LOGOUT SECTION
   ============================================ */

    .sidebar-logout {
        padding: 20px 15px 25px;
    }

    .logout-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        padding: 14px 20px;
        background: linear-gradient(135deg, #e74c3c, #c0392b);
        color: white;
        text-decoration: none;
        border-radius: 12px;
        font-weight: 600;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);
    }

    .logout-btn:hover {
        background: linear-gradient(135deg, #c0392b, #a93226);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(231, 76, 60, 0.4);
    }

    .logout-btn i {
        font-size: 1.1rem;
    }

    /* ============================================
   RESPONSIVE DESIGN
   ============================================ */

    @media (max-width: 768px) {
        .sidebar {
            width: 100%;
            height: auto;
            position: relative;
        }

        .sidebar-nav li a span {
            display: inline;
        }
    }

    /* Ensure main content has proper margin */
    .main-content {
        margin-left: 280px;
        padding: 30px;
        min-height: 100vh;
        background: #f5f7fa;
    }

    @media (max-width: 768px) {
        .main-content {
            margin-left: 0;
        }
    }
</style>