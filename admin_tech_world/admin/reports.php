<?php
/**
 * ADMIN - REPORTS VIEW
 * MEMBER 1 - ADMIN ROLE
 */

$pageTitle = 'Reports';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../controllers/AdminController.php';

$controller = new AdminController();

// Get date range from query
$startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
$endDate = $_GET['end_date'] ?? date('Y-m-d');

// Get report data
$salesReport = $controller->getSalesReport($startDate, $endDate);
$topSellers = $controller->getTopSellers(5);
$topProducts = $controller->getTopProducts(5);

// Calculate totals
$totalOrders = array_sum(array_column($salesReport, 'orders'));
$totalRevenue = array_sum(array_column($salesReport, 'revenue'));
?>

<div class="page-header">
    <h1>Reports & Analytics</h1>
    <p>View sales performance and analytics</p>
</div>

<!-- Date Filter -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">
            <div class="form-group" style="margin-bottom: 0;">
                <label for="start_date">Start Date</label>
                <input type="date" name="start_date" id="start_date" class="form-control"
                    value="<?php echo escape($startDate); ?>">
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label for="end_date">End Date</label>
                <input type="date" name="end_date" id="end_date" class="form-control"
                    value="<?php echo escape($endDate); ?>">
            </div>

            <button type="submit" class="btn btn-primary">Apply</button>
            <a href="<?php echo BASE_URL; ?>/views/admin/reports.php" class="btn btn-secondary">Reset</a>
        </form>
    </div>
</div>

<!-- Summary Stats -->
<div class="stats-grid">
    <div class="stats-card">
        <div class="stats-icon">🛒</div>
        <div class="stats-value">
            <?php echo $totalOrders; ?>
        </div>
        <div class="stats-label">Orders in Period</div>
    </div>

    <div class="stats-card green">
        <div class="stats-icon">💰</div>
        <div class="stats-value">
            <?php echo formatPrice($totalRevenue); ?>
        </div>
        <div class="stats-label">Revenue in Period</div>
    </div>

    <div class="stats-card orange">
        <div class="stats-icon">📊</div>
        <div class="stats-value">
            <?php echo $totalOrders > 0 ? formatPrice($totalRevenue / $totalOrders) : '$0.00'; ?>
        </div>
        <div class="stats-label">Average Order Value</div>
    </div>

    <div class="stats-card purple">
        <div class="stats-icon">📅</div>
        <div class="stats-value">
            <?php echo count($salesReport); ?>
        </div>
        <div class="stats-label">Days with Sales</div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
    <!-- Top Sellers -->
    <div class="card">
        <div class="card-header">
            <h3>🏆 Top Sellers</h3>
        </div>
        <div class="card-body">
            <?php if (!empty($topSellers)): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Seller</th>
                            <th>Orders</th>
                            <th>Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($topSellers as $seller): ?>
                            <tr>
                                <td>
                                    <strong>
                                        <?php echo escape($seller['full_name']); ?>
                                    </strong>
                                    <br><small class="text-muted">
                                        <?php echo escape($seller['email']); ?>
                                    </small>
                                </td>
                                <td>
                                    <?php echo $seller['total_orders']; ?>
                                </td>
                                <td>
                                    <?php echo formatPrice($seller['total_revenue']); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <p>No sales data available yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Top Products -->
    <div class="card">
        <div class="card-header">
            <h3>🔥 Top Products</h3>
        </div>
        <div class="card-body">
            <?php if (!empty($topProducts)): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Sold</th>
                            <th>Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($topProducts as $product): ?>
                            <tr>
                                <td>
                                    <strong>
                                        <?php echo escape($product['name']); ?>
                                    </strong>
                                    <br><small class="text-muted">
                                        <?php echo formatPrice($product['price']); ?> each
                                    </small>
                                </td>
                                <td>
                                    <?php echo $product['total_sold']; ?> units
                                </td>
                                <td>
                                    <?php echo formatPrice($product['total_revenue']); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <p>No product sales data available yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Daily Sales Table -->
<div class="card mt-3">
    <div class="card-header">
        <h3>📈 Daily Sales Report</h3>
    </div>
    <div class="card-body">
        <?php if (!empty($salesReport)): ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Orders</th>
                        <th>Revenue</th>
                        <th>Average</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($salesReport as $day): ?>
                        <tr>
                            <td>
                                <?php echo formatDate($day['date']); ?>
                            </td>
                            <td>
                                <?php echo $day['orders']; ?>
                            </td>
                            <td>
                                <?php echo formatPrice($day['revenue']); ?>
                            </td>
                            <td>
                                <?php echo formatPrice($day['revenue'] / $day['orders']); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon">📊</div>
                <h3>No sales in this period</h3>
                <p>Try selecting a different date range.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>