<?php 
include('../../includes/header.php'); 

// Security: Ensure only Admins can see this
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../../login.php");
    exit();
    
}
?>

<div class="dashboard-container">
    <div class="sidebar">
        <h3>Admin Panel</h3>
        <a href="dashboard.php">📊 Dashboard Home</a>
        <a href="manage_users.php">👥 Manage Users</a>
        <a href="view_reports.php">📑 System Reports</a>
        <a href="complaints.php">💬 Complaints</a>
        <a href="../../logout.php" style="color: #ff69b4; margin-top: 20px;">🚪 Logout</a>
    </div>

    <div class="main-content">
        <div class="card">
            <h1>Admin Dashboard - Pinky Petal 🌸</h1>
            <p>Welcome back, <strong><?php echo $_SESSION['username']; ?></strong>. You have full system control.</p>
        </div>

        <div style="display: flex; gap: 20px; margin-top: 20px;">
            <div class="card" style="flex: 1; text-align: center;">
                <h3>Total Users</h3>
                <p style="font-size: 2rem; color: var(--main-pink); font-weight: bold;">24</p>
            </div>
            <div class="card" style="flex: 1; text-align: center;">
                <h3>Active Sellers</h3>
                <p style="font-size: 2rem; color: var(--main-pink); font-weight: bold;">8</p>
            </div>
            <div class="card" style="flex: 1; text-align: center;">
                <h3>Orders Today</h3>
                <p style="font-size: 2rem; color: var(--main-pink); font-weight: bold;">15</p>
            </div>
        </div>

        <div class="card" style="margin-top: 20px;">
            <h3>Quick System Actions</h3>
            <p>Manage the platform efficiently using the tools below:</p>
            <div style="display: flex; gap: 10px; margin-top: 15px;">
                <a href="manage_users.php" style="flex: 1;"><button>Manage All Users</button></a>
                <a href="view_reports.php" style="flex: 1;"><button>View System Reports</button></a>
            </div>
        </div>
    </div>
</div>

<?php include('../../includes/footer.php'); ?>