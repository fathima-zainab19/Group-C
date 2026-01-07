<?php 
include('../../includes/header.php'); 
require_once('../../config/database.php');

if (!isset($_SESSION['user_id'])) { header("Location: ../../login.php"); exit(); }

$user_id = $_SESSION['user_id'];
// Join with products table to get names and prices
$sql = "SELECT cart.id as cart_id, products.name, products.price, cart.quantity 
        FROM cart 
        JOIN products ON cart.product_id = products.id 
        WHERE cart.user_id = '$user_id'";
$result = mysqli_query($conn, $sql);
?>

<div class="dashboard-container">
    <div class="sidebar">
        <h3>Customer Menu</h3>
        <a href="dashboard.php">🏠 Home</a>
        <a href="browse_products.php">👗 Browse Dresses</a>
        <a href="cart.php" style="background: var(--main-pink);">🛒 My Shopping Cart</a>
        <a href="my_orders.php">📦 Track My Orders</a>
        <a href="../../logout.php" style="color: #ff69b4; margin-top: 20px;">🚪 Logout</a>
    </div>

    <div class="main-content">
        <div class="card">
            <h2>Your Shopping Cart 🛒</h2>
            <table style="width: 100%; border-collapse: collapse; margin-top: 20px; background: white;">
                <thead>
                    <tr style="background: #f8f8f8; border-bottom: 2px solid #eee;">
                        <th style="padding: 12px; text-align: left;">Product</th>
                        <th style="padding: 12px;">Price</th>
                        <th style="padding: 12px;">Qty</th>
                        <th style="padding: 12px;">Subtotal</th>
                        <th style="padding: 12px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $total = 0;
                    if(mysqli_num_rows($result) > 0):
                        while($item = mysqli_fetch_assoc($result)): 
                            $subtotal = $item['price'] * $item['quantity'];
                            $total += $subtotal;
                    ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 12px;"><?php echo $item['name']; ?></td>
                        <td style="padding: 12px; text-align: center;">$<?php echo number_format($item['price'], 2); ?></td>
                        <td style="padding: 12px; text-align: center;"><?php echo $item['quantity']; ?></td>
                        <td style="padding: 12px; text-align: center; font-weight: bold;">$<?php echo number_format($subtotal, 2); ?></td>
                        <td style="padding: 12px; text-align: center;">
                            <a href="remove_item.php?id=<?php echo $item['cart_id']; ?>" style="color: red; text-decoration: none;">❌</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="padding: 20px; text-align: center;">Your cart is empty!</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php if($total > 0): ?>
            <div style="text-align: right; margin-top: 20px;">
                <h2 style="color: var(--main-pink);">Total: $<?php echo number_format($total, 2); ?></h2>
                <a href="checkout.php"><button style="background: #27ae60; color: white; padding: 15px 40px; border: none; border-radius: 5px; cursor: pointer; font-size: 1.1rem; margin-top: 10px;">Proceed to Checkout 💳</button></a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include('../../includes/footer.php'); ?>