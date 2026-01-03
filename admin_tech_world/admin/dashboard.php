<?php
/**
 * ADMIN DASHBOARD VIEW
 * MEMBER 1 - ADMIN ROLE
 */

$pageTitle = 'Admin Dashboard';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../controllers/AdminController.php';

$controller = new AdminController();
$stats = $controller->dashboard();
?>

<div class="page-header">
    <h1>Admin Dashboard</h1>
    <p>Welcome back! Here's what's happening with your store.</p>
</div>

<!-- Statistics Cards -->
<div class="stats-grid">
    <div class="stats-card">
        <div class="stats-icon">👥</div>
        <div class="stats-value">
            <?php echo array_sum($stats['users_by_role'] ?? []); ?>
        </div>
        <div class="stats-label">Total Users</div>
    </div>

    <div class="stats-card green">
        <div class="stats-icon">📦</div>
        <div class="stats-value">
            <?php echo $stats['total_products'] ?? 0; ?>
        </div>
        <div class="stats-label">Products</div>
    </div>

    <div class="stats-card orange">
        <div class="stats-icon">🛒</div>
        <div class="stats-value">
            <?php echo $stats['total_orders'] ?? 0; ?>
        </div>
        <div class="stats-label">Total Orders</div>
    </div>

    <div class="stats-card purple">
        <div class="stats-icon">💰</div>
        <div class="stats-value">
            <?php echo formatPrice($stats['total_revenue'] ?? 0); ?>
        </div>
        <div class="stats-label">Total Revenue</div>
    </div>
</div>

<!-- Additional Stats -->
<div class="stats-grid">
    <div class="stats-card red">
        <div class="stats-icon">⏳</div>
        <div class="stats-value">
            <?php echo $stats['pending_approvals'] ?? 0; ?>
        </div>
        <div class="stats-label">Pending Approvals</div>
    </div>

    <div class="stats-card">
        <div class="stats-icon">🎫</div>
        <div class="stats-value">
            <?php echo $stats['open_tickets'] ?? 0; ?>
        </div>
        <div class="stats-label">Open Tickets</div>
    </div>

    <div class="stats-card green">
        <div class="stats-icon">🏪</div>
        <div class="stats-value">
            <?php echo $stats['users_by_role']['seller'] ?? 0; ?>
        </div>
        <div class="stats-label">Sellers</div>
    </div>

    <div class="stats-card orange">
        <div class="stats-icon">🚚</div>
        <div class="stats-value">
            <?php echo $stats['users_by_role']['delivery'] ?? 0; ?>
        </div>
        <div class="stats-label">Delivery Staff</div>
    </div>
</div>

<!-- Recent Orders -->
<div class="card mt-3">
    <div class="card-header">
        <h3>Recent Orders</h3>
        <a href="<?php echo BASE_URL; ?>/views/admin/reports.php" class="btn btn-sm btn-outline-primary">View All</a>
    </div>
    <div class="card-body">
        <?php if (!empty($stats['recent_orders'])): ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stats['recent_orders'] as $order): ?>
                        <tr>
                            <td>#
                                <?php echo $order['id']; ?>
                            </td>
                            <td>
                                <?php echo escape($order['customer_name']); ?>
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
                                    default => 'info'
                                };
                                ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php echo formatDate($order['order_date']); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon">📋</div>
                <h3>No orders yet</h3>
                <p>Orders will appear here once customers start shopping.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Quick Actions -->
<div class="card mt-3">
    <div class="card-header">
        <h3>Quick Actions</h3>
    </div>
    <div class="card-body">
        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
            <a href="<?php echo BASE_URL; ?>/views/admin/manage_users.php?status=pending" class="btn btn-warning">
                ⏳ Review Pending Users (
                <?php echo $stats['pending_approvals'] ?? 0; ?>)
            </a>
            <a href="<?php echo BASE_URL; ?>/views/admin/manage_categories.php" class="btn btn-primary">
                📁 Manage Categories
            </a>
            <a href="<?php echo BASE_URL; ?>/views/admin/reports.php" class="btn btn-success">
                📈 View Reports
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>