<?php 
include('../../includes/header.php');
require_once('../../models/Customer.php');
require_once('../../config/database.php');

$customerModel = new Customer($conn);
$orders = $customerModel->getMyOrders($_SESSION['user_id']); // READ operation 
?>

<div class="card">
    <h2>My Order Status</h2>
    <table>
        <tr>
            <th>Order ID</th>
            <th>Date</th>
            <th>Total</th>
            <th>Status</th>
        </tr>
        <?php while($order = mysqli_fetch_assoc($orders)): ?>
        <tr>
            <td>#<?php echo $order['id']; ?></td>
            <td><?php echo $order['created_at']; ?></td>
            <td>$<?php echo $order['total']; ?></td>
            <td><strong><?php echo $order['status']; ?></strong></td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

<?php include('../../includes/footer.php'); ?>