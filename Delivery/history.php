<?php
/**
 * DELIVERY STAFF - HISTORY VIEW
 * MEMBER 4 - DELIVERY ROLE
 */

$pageTitle = 'Delivery History';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../controllers/DeliveryController.php';

$controller = new DeliveryController();
$history = $controller->getHistory();
$stats = $controller->getStats();
?>

<div class="page-header">
    <h1>Delivery History</h1>
    <p>View your completed deliveries</p>
</div>

<!-- Stats -->
<div class="stats-grid">
    <div class="stats-card green">
        <div class="stats-icon">📊</div>
        <div class="stats-value">
            <?php echo count($history); ?>
        </div>
        <div class="stats-label">Total Deliveries</div>
    </div>

    <div class="stats-card">
        <div class="stats-icon">📅</div>
        <div class="stats-value">
            <?php echo $stats['this_week'] ?? 0; ?>
        </div>
        <div class="stats-label">This Week</div>
    </div>

    <div class="stats-card purple">
        <div class="stats-icon">📆</div>
        <div class="stats-value">
            <?php echo $stats['this_month'] ?? 0; ?>
        </div>
        <div class="stats-label">This Month</div>
    </div>
</div>

<!-- History Table -->
<div class="table-container">
    <div class="table-header">
        <h3>Completed Deliveries</h3>
    </div>

    <?php if (!empty($history)): ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Address</th>
                    <th>Amount</th>
                    <th>Delivered On</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($history as $delivery): ?>
                    <tr>
                        <td><strong>#
                                <?php echo $delivery['order_id']; ?>
                            </strong></td>
                        <td>
                            <?php echo escape($delivery['customer_name']); ?>
                        </td>
                        <td style="max-width: 300px;">
                            <?php echo escape($delivery['shipping_address']); ?>
                        </td>
                        <td>
                            <?php echo formatPrice($delivery['total_amount']); ?>
                        </td>
                        <td>
                            <span class="badge badge-success">✓ Delivered</span>
                            <br><small class="text-muted">
                                <?php echo formatDateTime($delivery['delivered_date']); ?>
                            </small>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="empty-state">
            <div class="empty-icon">📜</div>
            <h3>No delivery history</h3>
            <p>Your completed deliveries will appear here.</p>
            <a href="<?php echo BASE_URL; ?>/views/delivery/deliveries.php" class="btn btn-primary">View Available
                Deliveries</a>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>