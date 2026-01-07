<?php 
include('../../includes/header.php'); 
require_once('../../config/database.php');

$customer_id = $_SESSION['user_id'];

// Check if the table exists to avoid the fatal error
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'orders'");
if (mysqli_num_rows($table_check) == 0) {
    die("<div class='card'>Error: The 'orders' table has not been created in the database yet.</div>");
}

$sql = "SELECT * FROM orders WHERE customer_id = '$customer_id' ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);
?>

<div class="dashboard-container">
    <div class="sidebar">
        <h3>Customer Menu</h3>
        <a href="dashboard.php">🏠 Home</a>
        <a href="browse_products.php">👗 Browse Dresses</a>
        <a href="my_orders.php" style="background: var(--main-pink);">📦 Track My Orders</a>
        <a href="../../logout.php" style="color: #ff69b4; margin-top: 20px;">🚪 Logout</a>
    </div>

    <div class="main-content">
        <div class="card">
            <h2>Your Order History</h2>
            <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
                <tr style="background: #f8f8f8; border-bottom: 2px solid #eee;">
                    <th style="padding: 10px; text-align: left;">Order ID</th>
                    <th style="padding: 10px;">Date</th>
                    <th style="padding: 10px;">Total Price</th>
                    <th style="padding: 10px;">Status</th>
                </tr>
                <?php if(mysqli_num_rows($result) > 0): ?>
                    <?php while($order = mysqli_fetch_assoc($result)): 
                        // Color coding for status
                        $status_color = "#f39c12"; // Orange for Pending
                        if($order['status'] == 'Paid') $status_color = "#27ae60"; // Green
                        if($order['status'] == 'Dispatched') $status_color = "#3498db"; // Blue
                    ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 10px;">#<?php echo $order['id']; ?></td>
                        <td style="padding: 10px; text-align: center;"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                        <td style="padding: 10px; text-align: center;">$<?php echo number_format($order['total'], 2); ?></td>
                        <td style="padding: 10px; text-align: center;">
                            <span style="background: <?php echo $status_color; ?>; color: white; padding: 5px 10px; border-radius: 15px; font-size: 0.8rem;">
                                <?php echo $order['status']; ?>
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4" style="text-align:center; padding: 20px;">You haven't placed any orders yet.</td></tr>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>

<?php include('../../includes/footer.php'); ?>