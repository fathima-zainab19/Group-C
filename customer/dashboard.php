<?php
/**
 * CUSTOMER DASHBOARD VIEW
 * MEMBER 2 - CUSTOMER ROLE
 */

$pageTitle = 'Dashboard';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../controllers/CustomerController.php';

$controller = new CustomerController();
$data = $controller->dashboard();
?>

<div class="page-header">
    <h1>Welcome,
        <?php echo escape($_SESSION['full_name'] ?? $_SESSION['username']); ?>!
    </h1>
    <p>Here's your shopping overview</p>
</div>

<!-- Stats Cards -->
<div class="stats-grid">
    <div class="stats-card">
        <div class="stats-icon">📦</div>
        <div class="stats-value">
            <?php echo $data['total_orders']; ?>
        </div>
        <div class="stats-label">Total Orders</div>
    </div>

    <div class="stats-card green">
        <div class="stats-icon">🛒</div>
        <div class="stats-value">
            <?php echo $data['cart_items']; ?>
        </div>
        <div class="stats-label">Cart Items</div>
    </div>

    <div class="stats-card orange">
        <div class="stats-icon">🚚</div>
        <div class="stats-value">
            <?php echo $data['pending_orders']; ?>
        </div>
        <div class="stats-label">Pending Orders</div>
    </div>

    <div class="stats-card purple">
        <div class="stats-icon">🎫</div>
        <div class="stats-value">
            <?php echo $data['open_tickets']; ?>
        </div>
        <div class="stats-label">Support Tickets</div>
    </div>
</div>

<!-- Quick Actions -->
<div class="card mb-3">
    <div class="card-body">
        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
            <a href="<?php echo BASE_URL; ?>/views/customer/products.php" class="btn btn-primary">🛍️ Browse
                Products</a>
            <a href="<?php echo BASE_URL; ?>/views/customer/cart.php" class="btn btn-success">🛒 View Cart</a>
            <a href="<?php echo BASE_URL; ?>/views/customer/orders.php" class="btn btn-secondary">📦 My Orders</a>
            <a href="<?php echo BASE_URL; ?>/views/customer/support.php" class="btn btn-outline-primary">💬 Get
                Support</a>
        </div>
    </div>
</div>

<!-- Featured Products -->
<div class="card mb-3">
    <div class="card-header">
        <h3>🔥 Featured Products</h3>
        <a href="<?php echo BASE_URL; ?>/views/customer/products.php" class="btn btn-sm btn-outline-primary">View
            All</a>
    </div>
    <div class="card-body">
        <?php if (!empty($data['featured_products'])): ?>
            <div class="products-grid">
                <?php foreach ($data['featured_products'] as $product): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <div class="product-placeholder">📦</div>
                            <div class="product-overlay">
                                <a href="<?php echo BASE_URL; ?>/views/customer/products.php?id=<?php echo $product['id']; ?>"
                                    class="btn btn-primary btn-sm">View Details</a>
                            </div>
                        </div>
                        <div class="product-info">
                            <span class="product-category">
                                <?php echo escape($product['category_name']); ?>
                            </span>
                            <h3 class="product-name">
                                <?php echo escape($product['name']); ?>
                            </h3>
                            <div class="product-footer">
                                <span class="product-price">
                                    <?php echo formatPrice($product['price']); ?>
                                </span>
                                <span class="product-stock <?php echo $product['stock'] > 0 ? 'in-stock' : 'out-of-stock'; ?>">
                                    <?php echo $product['stock'] > 0 ? 'In Stock' : 'Out of Stock'; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <p>No products available yet. Check back soon!</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Recent Orders -->
<div class="card">
    <div class="card-header">
        <h3>📋 Recent Orders</h3>
        <a href="<?php echo BASE_URL; ?>/views/customer/orders.php" class="btn btn-sm btn-outline-primary">View All</a>
    </div>
    <div class="card-body">
        <?php if (!empty($data['recent_orders'])): ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['recent_orders'] as $order): ?>
                        <tr>
                            <td>#
                                <?php echo $order['id']; ?>
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
                                    class="btn btn-sm btn-secondary">View</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon">📦</div>
                <h3>No orders yet</h3>
                <p>Start shopping to see your orders here!</p>
                <a href="<?php echo BASE_URL; ?>/views/customer/products.php" class="btn btn-primary">Browse Products</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>