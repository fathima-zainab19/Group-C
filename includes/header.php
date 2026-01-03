<?php
/**
 * HEADER INCLUDE FILE
 * SHARED FILE - Include at the top of dashboard pages
 */

require_once __DIR__ . '/../config/config.php';

// Check if user is logged in for dashboard pages
if (!isLoggedIn()) {
    redirect('/login.php');
}

$currentUser = [
    'id' => $_SESSION['user_id'],
    'username' => $_SESSION['username'],
    'email' => $_SESSION['email'],
    'role' => $_SESSION['user_role'],
    'full_name' => $_SESSION['full_name'] ?? $_SESSION['username']
];

$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>
        <?php echo APP_NAME; ?>
    </title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>const BASE_URL = '<?php echo BASE_URL; ?>';</script>
</head>

<body>
    <div class="dashboard-layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-brand">
                <span class="logo-icon">🛒</span>
                <span class="logo-text">
                    <?php echo APP_NAME; ?>
                </span>
            </div>

            <div class="sidebar-user">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($currentUser['full_name'], 0, 1)); ?>
                </div>
                <div class="user-info">
                    <div class="user-name">
                        <?php echo escape($currentUser['full_name']); ?>
                    </div>
                    <div class="user-role">
                        <?php echo ROLE_NAMES[$currentUser['role']] ?? $currentUser['role']; ?>
                    </div>
                </div>
            </div>

            <nav class="sidebar-nav">
                <?php
                // Navigation items based on role
                $navItems = [];

                switch ($currentUser['role']) {
                    case 'admin':
                        $navItems = [
                            ['url' => '/views/admin/dashboard.php', 'icon' => '📊', 'text' => 'Dashboard'],
                            ['url' => '/views/admin/manage_users.php', 'icon' => '👥', 'text' => 'Manage Users'],
                            ['url' => '/views/admin/manage_categories.php', 'icon' => '📁', 'text' => 'Categories'],
                            ['url' => '/views/admin/reports.php', 'icon' => '📈', 'text' => 'Reports'],
                        ];
                        break;
                    case 'customer':
                        $navItems = [
                            ['url' => '/views/customer/dashboard.php', 'icon' => '🏠', 'text' => 'Dashboard'],
                            ['url' => '/views/customer/products.php', 'icon' => '🛍️', 'text' => 'Products'],
                            ['url' => '/views/customer/cart.php', 'icon' => '🛒', 'text' => 'Cart'],
                            ['url' => '/views/customer/orders.php', 'icon' => '📦', 'text' => 'My Orders'],
                            ['url' => '/views/customer/support.php', 'icon' => '💬', 'text' => 'Support'],
                        ];
                        break;
                    case 'seller':
                        $navItems = [
                            ['url' => '/views/seller/dashboard.php', 'icon' => '📊', 'text' => 'Dashboard'],
                            ['url' => '/views/seller/products.php', 'icon' => '📦', 'text' => 'My Products'],
                            ['url' => '/views/seller/add_product.php', 'icon' => '➕', 'text' => 'Add Product'],
                            ['url' => '/views/seller/orders.php', 'icon' => '📋', 'text' => 'Orders'],
                        ];
                        break;
                    case 'delivery':
                        $navItems = [
                            ['url' => '/views/delivery/dashboard.php', 'icon' => '📊', 'text' => 'Dashboard'],
                            ['url' => '/views/delivery/deliveries.php', 'icon' => '🚚', 'text' => 'Deliveries'],
                            ['url' => '/views/delivery/history.php', 'icon' => '📜', 'text' => 'History'],
                        ];
                        break;
                    case 'support':
                        $navItems = [
                            ['url' => '/views/support/dashboard.php', 'icon' => '📊', 'text' => 'Dashboard'],
                            ['url' => '/views/support/tickets.php', 'icon' => '🎫', 'text' => 'Tickets'],
                        ];
                        break;
                }

                foreach ($navItems as $item):
                    $isActive = strpos($_SERVER['PHP_SELF'], $item['url']) !== false;
                    ?>
                    <a href="<?php echo BASE_URL . $item['url']; ?>"
                        class="sidebar-nav-item <?php echo $isActive ? 'active' : ''; ?>">
                        <span class="nav-icon">
                            <?php echo $item['icon']; ?>
                        </span>
                        <span class="nav-text">
                            <?php echo $item['text']; ?>
                        </span>
                    </a>
                <?php endforeach; ?>
            </nav>

            <div class="sidebar-section">
                <div class="sidebar-section-title">Account</div>
                <nav class="sidebar-nav">
                    <a href="<?php echo BASE_URL; ?>/index.php" class="sidebar-nav-item">
                        <span class="nav-icon">🏪</span>
                        <span class="nav-text">Store Front</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>/logout.php" class="sidebar-nav-item">
                        <span class="nav-icon">🚪</span>
                        <span class="nav-text">Logout</span>
                    </a>
                </nav>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Mobile sidebar toggle -->
            <button class="btn btn-secondary d-none" id="sidebarToggle" style="margin-bottom: 1rem;">
                ☰ Menu
            </button>

            <?php
            // Display flash messages
            $success = getFlashMessage('success');
            $error = getFlashMessage('error');
            $warning = getFlashMessage('warning');
            $info = getFlashMessage('info');

            if ($success): ?>
                <div class="alert alert-success">
                    <?php echo escape($success); ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?php echo escape($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($warning): ?>
                <div class="alert alert-warning">
                    <?php echo escape($warning); ?>
                </div>
            <?php endif; ?>

            <?php if ($info): ?>
                <div class="alert alert-info">
                    <?php echo escape($info); ?>
                </div>
            <?php endif; ?>