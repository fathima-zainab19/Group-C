<?php
/**
 * DELIVERY STAFF DASHBOARD VIEW
 * MEMBER 4 - DELIVERY ROLE
 */

$pageTitle = 'Delivery Dashboard';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../controllers/DeliveryController.php';

$controller = new DeliveryController();
$data = $controller->dashboard();
$stats = $controller->getStats();
?>

<div class="page-header">
    <h1>Delivery Dashboard</h1>
    <p>Manage your deliveries and track your performance</p>
</div>

<!-- Stats Cards -->
<div class="stats-grid">
    <div class="stats-card orange">
        <div class="stats-icon">📦</div>
        <div class="stats-value">
            <?php echo $data['pending_deliveries']; ?>
        </div>
        <div class="stats-label">Pending Deliveries</div>
    </div>

    <div class="stats-card green">
        <div class="stats-icon">✅</div>
        <div class="stats-value">
            <?php echo $data['completed_today']; ?>
        </div>
        <div class="stats-label">Completed Today</div>
    </div>

    <div class="stats-card purple">
        <div class="stats-icon">📊</div>
        <div class="stats-value">
            <?php echo $data['total_completed']; ?>
        </div>
        <div class="stats-label">Total Completed</div>
    </div>

    <div class="stats-card">
        <div class="stats-icon">🆕</div>
        <div class="stats-value">
            <?php echo $data['available_pickups']; ?>
        </div>
        <div class="stats-label">Available for Pickup</div>
    </div>
</div>

<!-- Performance Stats -->
<div class="card mb-3">
    <div class="card-header">
        <h3>📈 Your Performance</h3>
    </div>
    <div class="card-body">
        <div style="display: flex; gap: 3rem; justify-content: center;">
            <div style="text-align: center;">
                <div style="font-size: 2rem; font-weight: 700; color: var(--primary-color);">
                    <?php echo $stats['this_week'] ?? 0; ?>
                </div>
                <div class="text-muted">This Week</div>
            </div>
            <div style="text-align: center;">
                <div style="font-size: 2rem; font-weight: 700; color: var(--accent-success);">
                    <?php echo $stats['this_month'] ?? 0; ?>
                </div>
                <div class="text-muted">This Month</div>
            </div>
        </div>
    </div>
</div>

<!-- Current Deliveries -->
<div class="card">
    <div class="card-header">
        <h3>🚚 Current Deliveries</h3>
        <a href="<?php echo BASE_URL; ?>/views/delivery/deliveries.php" class="btn btn-sm btn-primary">Manage
            Deliveries</a>
    </div>
    <div class="card-body">
        <?php if (!empty($data['current_deliveries'])): ?>
            <?php foreach ($data['current_deliveries'] as $delivery): ?>
                <div
                    style="padding: 1rem; border: 1px solid var(--gray-200); border-radius: var(--radius); margin-bottom: 1rem;">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                        <div>
                            <strong>Order #
                                <?php echo $delivery['order_id']; ?>
                            </strong>
                            <span class="badge badge-<?php
                            echo match ($delivery['status']) {
                                'assigned' => 'warning',
                                'picked_up' => 'info',
                                'in_transit' => 'primary',
                                default => 'secondary'
                            };
                            ?>" style="margin-left: 0.5rem;">
                                <?php echo DELIVERY_STATUSES[$delivery['status']] ?? $delivery['status']; ?>
                            </span>
                        </div>
                        <strong>
                            <?php echo formatPrice($delivery['total_amount']); ?>
                        </strong>
                    </div>
                    <p style="margin: 0 0 0.5rem 0;"><strong>Customer:</strong>
                        <?php echo escape($delivery['customer_name']); ?>
                    </p>
                    <p style="margin: 0 0 0.5rem 0;"><strong>Address:</strong>
                        <?php echo escape($delivery['shipping_address']); ?>
                    </p>
                    <p style="margin: 0;"><strong>Phone:</strong>
                        <?php echo escape($delivery['shipping_phone']); ?>
                    </p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon">📦</div>
                <h3>No active deliveries</h3>
                <p>Accept new deliveries from the deliveries page.</p>
                <a href="<?php echo BASE_URL; ?>/views/delivery/deliveries.php" class="btn btn-primary">View Available
                    Deliveries</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>