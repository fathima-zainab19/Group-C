<?php 
include('../../includes/header.php'); 
require_once('../../config/database.php');

$user_id = $_SESSION['user_id'];

// Get total price from cart
$total_query = "SELECT SUM(p.price * c.quantity) as total 
                FROM cart c JOIN products p ON c.product_id = p.id 
                WHERE c.user_id = '$user_id'";
$result = mysqli_query($conn, $total_query);
$row = mysqli_fetch_assoc($result);
$grand_total = $row['total'] ?? 0;
?>

<div class="main-content">
    <div class="card" style="max-width: 500px; margin: auto; padding: 20px;">
        <h2>Complete Your Order</h2>
        <p>Total Amount: <strong>$<?php echo number_format($grand_total, 2); ?></strong></p>
        
        <form action="process_checkout.php" method="POST">
            <input type="hidden" name="total" value="<?php echo $grand_total; ?>">
            
            <label>Delivery Address:</label>
            <textarea name="address" required style="width:100%; height:80px; margin-bottom:15px;"></textarea>
            
            <label>Contact Number:</label>
            <input type="text" name="phone" required style="width:100%; margin-bottom:20px;">
            
            <button type="submit" style="width:100%; background: #d63384; color: white; padding: 10px; border: none; border-radius: 5px; cursor: pointer;">
                Place Order (Pay on Delivery)
            </button>
        </form>
    </div>
</div>

<?php include('../../includes/footer.php'); ?>