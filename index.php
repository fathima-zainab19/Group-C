<?php
/**
 * ============================================================================
 * LANDING PAGE / HOME PAGE
 * ============================================================================
 * Main entry point of the application.
 * Shows featured products, categories, and promotional content.
 * 
 * SHARED FILE - Accessible to all users
 * ============================================================================
 */

require_once __DIR__ . '/config/config.php';

// Get featured products and categories for display
$db = Database::getInstance()->getConnection();

// Get active categories
$stmt = $db->prepare("SELECT * FROM categories WHERE status = 'active' LIMIT 6");
$stmt->execute();
$categories = $stmt->fetchAll();

// Get featured products
$stmt = $db->prepare("
    SELECT p.*, c.name as category_name, u.full_name as seller_name 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    JOIN users u ON p.seller_id = u.id 
    WHERE p.status = 'active' 
    ORDER BY p.created_at DESC 
    LIMIT 8
");
$stmt->execute();
$featuredProducts = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php echo APP_NAME; ?> -
        <?php echo APP_TAGLINE; ?>
    </title>
    <meta name="description"
        content="<?php echo APP_NAME; ?> - Your one-stop destination for online shopping. Browse thousands of products from trusted sellers.">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
        <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <a href="<?php echo BASE_URL; ?>" class="navbar-brand">
                <span class="logo-icon">🛒</span>
                <span class="logo-text">
                    <?php echo APP_NAME; ?>
                </span>
            </a>

            <div class="navbar-menu">
                <a href="#categories" class="nav-link">Categories</a>
                <a href="#products" class="nav-link">Products</a>
                <a href="#about" class="nav-link">About</a>
            </div>

            <div class="navbar-actions">
                <?php if (isLoggedIn()): ?>
                    <a href="<?php echo BASE_URL . ROLE_DASHBOARDS[getCurrentUserRole()]; ?>" class="btn btn-outline-light">
                        Dashboard
                    </a>
                    <a href="<?php echo BASE_URL; ?>/logout.php" class="btn btn-light">
                        Logout
                    </a>
                <?php else: ?>
                    <a href="<?php echo BASE_URL; ?>/login.php" class="btn btn-outline-light">
                        Login
                    </a>
                    <a href="<?php echo BASE_URL; ?>/register.php" class="btn btn-light">
                        Sign Up
                    </a>
                <?php endif; ?>
            </div>

            <button class="navbar-toggle" id="navToggle">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-background"></div>
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">Shop Smarter, <span class="gradient-text">Live Better</span></h1>
                <p class="hero-subtitle">Discover amazing products from trusted sellers. Fast delivery, secure payments,
                    and 24/7 support.</p>
                <div class="hero-actions">
                    <?php if (isLoggedIn()): ?>
                        <a href="<?php echo BASE_URL; ?>/views/customer/products.php" class="btn btn-primary btn-lg">
                            Start Shopping
                        </a>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>/register.php" class="btn btn-primary btn-lg">
                            Get Started
                        </a>
                        <a href="<?php echo BASE_URL; ?>/login.php" class="btn btn-outline-white btn-lg">
                            Sign In
                        </a>
                    <?php endif; ?>
                </div>
                <div class="hero-stats">
                    <div class="stat">
                        <span class="stat-number">10K+</span>
                        <span class="stat-label">Products</span>
                    </div>
                    <div class="stat">
                        <span class="stat-number">5K+</span>
                        <span class="stat-label">Customers</span>
                    </div>
                    <div class="stat">
                        <span class="stat-number">500+</span>
                        <span class="stat-label">Sellers</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="section categories-section" id="categories">
        <div class="container">
            <div class="section-header">
                <h2>Shop by Category</h2>
                <p>Explore our wide range of product categories</p>
            </div>

            <div class="categories-grid">
                <?php foreach ($categories as $category): ?>
                    <a href="<?php echo BASE_URL; ?>/views/customer/products.php?category=<?php echo $category['id']; ?>"
                        class="category-card">
                        <div class="category-icon">
                            <?php
                            $icons = [
                                'Electronics' => '📱',
                                'Fashion' => '👗',
                                'Home & Living' => '🏠',
                                'Books' => '📚',
                                'Sports & Outdoors' => '⚽',
                                'Beauty & Health' => '💄'
                            ];
                            echo $icons[$category['name']] ?? '📦';
                            ?>
                        </div>
                        <h3>
                            <?php echo escape($category['name']); ?>
                        </h3>
                        <p>
                            <?php echo escape($category['description']); ?>
                        </p>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Featured Products Section -->
    <section class="section products-section" id="products">
        <div class="container">
            <div class="section-header">
                <h2>Featured Products</h2>
                <p>Handpicked products just for you</p>
            </div>

            <div class="products-grid">
                <?php foreach ($featuredProducts as $product): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <?php if ($product['image']): ?>
                                <img src="<?php echo BASE_URL; ?>/public/uploads/<?php echo escape($product['image']); ?>"
                                    alt="<?php echo escape($product['name']); ?>">
                            <?php else: ?>
                                <div class="product-placeholder">📦</div>
                            <?php endif; ?>
                            <div class="product-overlay">
                                <a href="<?php echo BASE_URL; ?>/views/customer/products.php?id=<?php echo $product['id']; ?>"
                                    class="btn btn-primary btn-sm">
                                    View Details
                                </a>
                            </div>
                        </div>
                        <div class="product-info">
                            <span class="product-category">
                                <?php echo escape($product['category_name']); ?>
                            </span>
                            <h3 class="product-name">
                                <?php echo escape($product['name']); ?>
                            </h3>
                            <p class="product-seller">by
                                <?php echo escape($product['seller_name']); ?>
                            </p>
                            <div class="product-footer">
                                <span class="product-price">
                                    <?php echo formatPrice($product['price']); ?>
                                </span>
                                <?php if ($product['stock'] > 0): ?>
                                    <span class="product-stock in-stock">In Stock</span>
                                <?php else: ?>
                                    <span class="product-stock out-of-stock">Out of Stock</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="section-footer">
                <a href="<?php echo BASE_URL; ?>/views/customer/products.php" class="btn btn-outline-primary btn-lg">
                    View All Products
                </a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="section features-section" id="about">
        <div class="container">
            <div class="section-header">
                <h2>Why Choose
                    <?php echo APP_NAME; ?>?
                </h2>
                <p>We're committed to providing the best shopping experience</p>
            </div>

            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">🚀</div>
                    <h3>Fast Delivery</h3>
                    <p>Get your orders delivered quickly with our efficient delivery network.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🔒</div>
                    <h3>Secure Payments</h3>
                    <p>Your transactions are protected with industry-standard encryption.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">💯</div>
                    <h3>Quality Products</h3>
                    <p>All products are verified and sourced from trusted sellers.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🎧</div>
                    <h3>24/7 Support</h3>
                    <p>Our support team is always ready to help you with any queries.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="section cta-section">
        <div class="container">
            <div class="cta-content">
                <h2>Ready to Start Selling?</h2>
                <p>Join thousands of sellers and grow your business with
                    <?php echo APP_NAME; ?>
                </p>
                <a href="<?php echo BASE_URL; ?>/register.php" class="btn btn-light btn-lg">
                    Become a Seller
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <a href="<?php echo BASE_URL; ?>" class="navbar-brand">
                        <span class="logo-icon">🛒</span>
                        <span class="logo-text">
                            <?php echo APP_NAME; ?>
                        </span>
                    </a>
                    <p>
                        <?php echo APP_TAGLINE; ?>
                    </p>
                </div>

                <div class="footer-links">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="#categories">Categories</a></li>
                        <li><a href="#products">Products</a></li>
                        <li><a href="#about">About Us</a></li>
                    </ul>
                </div>

                <div class="footer-links">
                    <h4>Account</h4>
                    <ul>
                        <li><a href="<?php echo BASE_URL; ?>/login.php">Login</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/register.php">Register</a></li>
                        <li><a href="#">Track Order</a></li>
                    </ul>
                </div>

                <div class="footer-links">
                    <h4>Support</h4>
                    <ul>
                        <li><a href="#">Help Center</a></li>
                        <li><a href="#">Contact Us</a></li>
                        <li><a href="#">FAQs</a></li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy;
                    <?php echo date('Y'); ?>
                    <?php echo APP_NAME; ?>. All rights reserved.
                </p>
                <p>Academic Project - MVC Online Shopping System</p>
            </div>
        </div>
    </footer>

    <script src="<?php echo BASE_URL; ?>/public/js/main.js"></script>
</body>

</html>