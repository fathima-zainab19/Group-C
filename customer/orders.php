<?php
/**
 * CUSTOMER - ORDERS VIEW
 * MEMBER 2 - CUSTOMER ROLE
 */

$pageTitle = 'My Orders';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../controllers/CustomerController.php';

$controller = new CustomerController();

// Check if viewing single order
$orderDetails = null;
if (isset($_GET['id'])) {
    $orderDetails = $controller->getOrderDetails((int) $_GET['id']);
}

$orders = $controller->getOrders();
?>

<?php if ($orderDetails): ?>
    <!-- Single Order View -->
    <div class="page-header">
        <a href="<?php echo BASE_URL; ?>/views/customer/orders.php" class="btn btn-secondary mb-2">← Back to Orders</a>
        <h1>Order #
            <?php echo $orderDetails['id']; ?>
        </h1>
        <p>Placed on
            <?php echo formatDateTime($orderDetails['order_date']); ?>
        </p>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">
        <!-- Order Items -->
        <div class="card">
            <div class="card-header">
                <h3>Order Items</h3>
                <span class="badge badge-<?php
                echo match ($orderDetails['status']) {
                    'delivered' => 'success',
                    'cancelled' => 'danger',
                    'pending' => 'warning',
                    'shipped' => 'info',
                    default => 'secondary'
                };
                ?>" style="font-size: 0.875rem;">
                    <?php echo strtoupper($orderDetails['status']); ?>
                </span>
            </div>
            <div class="card-body">
                <?php foreach ($orderDetails['items'] as $item): ?>
                    <div
                        style="display: flex; gap: 1rem; padding: 1rem; border-bottom: 1px solid var(--gray-200); align-items: center;">
                        <div
                            style="width: 60px; height: 60px; background: var(--gray-100); border-radius: var(--radius); display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                            📦</div>
                        <div style="flex: 1;">
                            <h4 style="margin: 0 0 0.25rem 0;">
                                <?php echo escape($item['name']); ?>
                            </h4>
                            <p class="text-muted" style="margin: 0;">Qty:
                                <?php echo $item['quantity']; ?> ×
                                <?php echo formatPrice($item['price']); ?>
                            </p>
                        </div>
                        <div style="font-weight: 600;">
                            <?php echo formatPrice($item['quantity'] * $item['price']); ?>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div style="padding: 1rem; text-align: right;">
                    <strong style="font-size: 1.25rem;">Total:
                        <?php echo formatPrice($orderDetails['total_amount']); ?>
                    </strong>
                </div>
            </div>
        </div>

        <!-- Order Info -->
        <div>
            <!-- Delivery Status -->
            <?php if ($orderDetails['delivery']): ?>
                <div class="card mb-3">
                    <div class="card-header">
                        <h3>🚚 Delivery Status</h3>
                    </div>
                    <div class="card-body">
                        <span class="badge badge-<?php
                        echo match ($orderDetails['delivery']['status']) {
                            'delivered' => 'success',
                            'in_transit' => 'info',
                            'picked_up' => 'primary',
                            default => 'warning'
                        };
                        ?>" style="font-size: 0.875rem; margin-bottom: 1rem; display: inline-block;">
                            <?php echo DELIVERY_STATUSES[$orderDetails['delivery']['status']] ?? $orderDetails['delivery']['status']; ?>
                        </span>

                        <?php if ($orderDetails['delivery']['notes']): ?>
                            <p class="text-muted" style="margin: 0;"><strong>Note:</strong>
                                <?php echo escape($orderDetails['delivery']['notes']); ?>
                            </p>
                        <?php endif; ?>

                        <?php if ($orderDetails['delivery']['delivered_date']): ?>
                            <p style="margin: 0.5rem 0 0 0; color: var(--accent-success);">
                                ✓ Delivered on
                                <?php echo formatDateTime($orderDetails['delivery']['delivered_date']); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Shipping Info -->
            <div class="card mb-3">
                <div class="card-header">
                    <h3>📍 Shipping Address</h3>
                </div>
                <div class="card-body">
                    <p style="margin: 0 0 0.5rem 0;">
                        <?php echo nl2br(escape($orderDetails['shipping_address'])); ?>
                    </p>
                    <p style="margin: 0;"><strong>Phone:</strong>
                        <?php echo escape($orderDetails['shipping_phone']); ?>
                    </p>
                </div>
            </div>

            <!-- Payment Info -->
            <div class="card">
                <div class="card-header">
                    <h3>💳 Payment</h3>
                </div>
                <div class="card-body">
                    <p style="margin: 0 0 0.5rem 0;"><strong>Method:</strong>
                        <?php echo strtoupper($orderDetails['payment_method']); ?>
                    </p>
                    <p style="margin: 0;">
                        <strong>Status:</strong>
                        <span
                            class="badge badge-<?php echo $orderDetails['payment_status'] === 'paid' ? 'success' : 'warning'; ?>">
                            <?php echo ucfirst($orderDetails['payment_status']); ?>
                        </span>
                    </p>
                </div>
            </div>

            <!-- Need Help? -->
            <div class="card mt-3">
                <div class="card-body text-center">
                    <p style="margin: 0 0 1rem 0;">Need help with this order?</p>
                    <a href="<?php echo BASE_URL; ?>/views/customer/support.php?order_id=<?php echo $orderDetails['id']; ?>"
                        class="btn btn-outline-primary">
                        💬 Contact Support
                    </a>
                </div>
            </div>
        </div>
    </div>

<?php else: ?>
    <!-- Orders List -->
    <div class="page-header">
        <h1>My Orders</h1>
        <p>Track and manage your orders</p>
    </div>

    <?php if (!empty($orders)): ?>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><strong>#
                                    <?php echo $order['id']; ?>
                                </strong></td>
                            <td>
                                <?php echo $order['item_count']; ?> item(s)
                            </td>
                            <td>
                                <?php echo formatPrice($order['total_amount']); ?>
                            </td>
                            <td>
                                <span class="badge badge-<?php
                                echo match ($order['status']) {
                                    'delivered' => 'success',
                                    'cancelled' => 'danger',
                                    'pending' => 'warning',
                                    'shipped' => 'info',
                                    default => 'secondary'
                                };
                                ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php echo formatDate($order['order_date']); ?>
                            </td>
                            <td>
                                <a href="<?php echo BASE_URL; ?>/views/customer/orders.php?id=<?php echo $order['id']; ?>"
                                    class="btn btn-sm btn-primary">View Details</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <div class="empty-icon">📦</div>
            <h3>No orders yet</h3>
            <p>You haven't placed any orders. Start shopping now!</p>
            <a href="<?php echo BASE_URL; ?>/views/customer/products.php" class="btn btn-primary btn-lg">Browse Products</a>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>