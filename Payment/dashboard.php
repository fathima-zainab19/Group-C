<?php 
include('../../includes/header.php'); 
require_once('../../config/database.php');

// Fetch only 'Pending' orders
$sql = "SELECT orders.*, users.name as customer_name 
        FROM orders 
        JOIN users ON orders.customer_id = users.id 
        WHERE orders.status = 'Pending' 
        ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);
?>

<div class="dashboard-container">
    <div class="main-content">
        <h2>Payment Verification</h2>
        
        <?php if(isset($_GET['msg'])) echo "<p style='color:green;'>".$_GET['msg']."</p>"; ?>

        <table border="1" style="width:100%; border-collapse: collapse; text-align: center;">
            <tr style="background-color: #f8f8f8;">
                <th>Order </th>
                <th>Customer</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            <?php if(mysqli_num_rows($result) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td>#<?php echo $row['id']; ?></td>
                    <td><?php echo $row['customer_name']; ?></td>
                    <td>$<?php echo number_format($row['total'], 2); ?></td>
                    <td><span style="color:red;"><?php echo $row['status']; ?></span></td>
                    <td>
                        <a href="verify_payment.php?id=<?php echo $row['id']; ?>" 
                           onclick="return confirm('Are you sure you want to verify this payment?');">
                            <button style="background: #27ae60; color: white; padding: 5px 10px; cursor: pointer; border: none; border-radius: 3px;">
                                ✅ Confirm Payment
                            </button>
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="5" style="padding: 20px;">No pending payments to verify.</td></tr>
            <?php endif; ?>
        </table>
    </div>
</div>
<?php include('../../includes/footer.php'); ?>