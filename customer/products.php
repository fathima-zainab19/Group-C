<?php
/**
 * CUSTOMER - PRODUCTS VIEW
 * MEMBER 2 - CUSTOMER ROLE
 */

$pageTitle = 'Products';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../controllers/CustomerController.php';

$controller = new CustomerController();

// Handle add to cart
if (isPost() && isset($_POST['action']) && $_POST['action'] === 'add_to_cart') {
    $result = $controller->handleCartAction();
    if ($result['success']) {
        setFlashMessage('success', $result['message']);
    } else {
        setFlashMessage('error', $result['message']);
    }
    redirect('/views/customer/products.php' . (isset($_GET['id']) ? '?id=' . $_GET['id'] : ''));
}

// Check if viewing single product
if (isset($_GET['id'])) {
    $product = $controller->getProduct((int) $_GET['id']);
    $reviews = $controller->getProductReviews((int) $_GET['id']);
}

// Get filters
$filters = [
    'category' => $_GET['category'] ?? '',
    'search' => $_GET['search'] ?? '',
    'sort' => $_GET['sort'] ?? 'newest',
    'min_price' => $_GET['min_price'] ?? '',
    'max_price' => $_GET['max_price'] ?? ''
];
$page = max(1, (int) ($_GET['page'] ?? 1));

$categories = $controller->getCategories();
$productsData = $controller->getProducts($filters, $page);
?>

<?php if (isset($product) && $product): ?>
    <!-- Single Product View -->
    <div class="page-header">
        <a href="<?php echo BASE_URL; ?>/views/customer/products.php" class="btn btn-secondary mb-2">← Back to Products</a>
    </div>

    <div class="card">
        <div class="card-body">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                <!-- Product Image -->
                <div class="product-image" style="height: 400px;">
                    <?php if ($product['image']): ?>
                        <img src="<?php echo BASE_URL; ?>/public/uploads/<?php echo escape($product['image']); ?>"
                            alt="<?php echo escape($product['name']); ?>">
                    <?php else: ?>
                        <div class="product-placeholder">📦</div>
                    <?php endif; ?>
                </div>

                <!-- Product Details -->
                <div>
                    <span class="badge badge-primary mb-2">
                        <?php echo escape($product['category_name']); ?>
                    </span>
                    <h1 style="font-size: 2rem; margin-bottom: 0.5rem;">
                        <?php echo escape($product['name']); ?>
                    </h1>
                    <p class="text-muted">Sold by:
                        <?php echo escape($product['seller_name']); ?>
                    </p>

                    <div style="font-size: 2rem; font-weight: 700; color: var(--primary-color); margin: 1rem 0;">
                        <?php echo formatPrice($product['price']); ?>
                    </div>

                    <p style="margin: 1rem 0;">
                        <?php echo nl2br(escape($product['description'])); ?>
                    </p>

                    <div style="margin: 1.5rem 0;">
                        <?php if ($product['stock'] > 0): ?>
                            <span class="badge badge-success">✓ In Stock (
                                <?php echo $product['stock']; ?> available)
                            </span>
                        <?php else: ?>
                            <span class="badge badge-danger">Out of Stock</span>
                        <?php endif; ?>
                    </div>

                    <?php if ($product['stock'] > 0): ?>
                        <form method="POST" action="" style="display: flex; gap: 1rem; align-items: flex-end;">
                            <input type="hidden" name="action" value="add_to_cart">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">

                            <div class="form-group" style="margin-bottom: 0; width: 100px;">
                                <label for="quantity">Quantity</label>
                                <input type="number" name="quantity" id="quantity" class="form-control" value="1" min="1"
                                    max="<?php echo $product['stock']; ?>">
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg">🛒 Add to Cart</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Reviews -->
    <div class="card mt-3">
        <div class="card-header">
            <h3>Customer Reviews</h3>
        </div>
        <div class="card-body">
            <?php if (!empty($reviews)): ?>
                <?php foreach ($reviews as $review): ?>
                    <div style="padding: 1rem; border-bottom: 1px solid var(--gray-200);">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                            <strong>
                                <?php echo escape($review['customer_name']); ?>
                            </strong>
                            <span class="text-muted">
                                <?php echo formatDate($review['created_at']); ?>
                            </span>
                        </div>
                        <div style="color: #f59e0b; margin-bottom: 0.5rem;">
                            <?php echo str_repeat('⭐', $review['rating']); ?>
                        </div>
                        <p style="margin: 0;">
                            <?php echo escape($review['comment']); ?>
                        </p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <p>No reviews yet. Be the first to review this product!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

<?php else: ?>
    <!-- Products Listing -->
    <div class="page-header">
        <h1>Browse Products</h1>
        <p>Find amazing products from trusted sellers</p>
    </div>

    <!-- Filters -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">
                <div class="form-group" style="margin-bottom: 0; flex: 2; min-width: 200px;">
                    <label for="search">Search</label>
                    <input type="text" name="search" id="search" class="form-control"
                        value="<?php echo escape($filters['search']); ?>" placeholder="Search products...">
                </div>

                <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 150px;">
                    <label for="category">Category</label>
                    <select name="category" id="category" class="form-control">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo $filters['category'] == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo escape($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 120px;">
                    <label for="sort">Sort By</label>
                    <select name="sort" id="sort" class="form-control">
                        <option value="newest" <?php echo $filters['sort'] === 'newest' ? 'selected' : ''; ?>>Newest</option>
                        <option value="price_low" <?php echo $filters['sort'] === 'price_low' ? 'selected' : ''; ?>>Price:
                            Low to High</option>
                        <option value="price_high" <?php echo $filters['sort'] === 'price_high' ? 'selected' : ''; ?>>Price:
                            High to Low</option>
                        <option value="name" <?php echo $filters['sort'] === 'name' ? 'selected' : ''; ?>>Name A-Z</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Search</button>
                <a href="<?php echo BASE_URL; ?>/views/customer/products.php" class="btn btn-secondary">Reset</a>
            </form>
        </div>
    </div>

    <!-- Products Grid -->
    <?php if (!empty($productsData['products'])): ?>
        <div class="products-grid">
            <?php foreach ($productsData['products'] as $product): ?>
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
                        <p class="product-seller">by
                            <?php echo escape($product['seller_name']); ?>
                        </p>
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

        <!-- Pagination -->
        <?php if ($productsData['pages'] > 1): ?>
            <div style="display: flex; justify-content: center; margin-top: 2rem;">
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>&<?php echo http_build_query($filters); ?>" class="pagination-btn">←
                            Previous</a>
                    <?php endif; ?>

                    <?php for ($i = max(1, $page - 2); $i <= min($productsData['pages'], $page + 2); $i++): ?>
                        <a href="?page=<?php echo $i; ?>&<?php echo http_build_query($filters); ?>"
                            class="pagination-btn <?php echo $i === $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($page < $productsData['pages']): ?>
                        <a href="?page=<?php echo $page + 1; ?>&<?php echo http_build_query($filters); ?>" class="pagination-btn">Next
                            →</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <div class="empty-state">
            <div class="empty-icon">🔍</div>
            <h3>No products found</h3>
            <p>Try adjusting your search or filters.</p>
            <a href="<?php echo BASE_URL; ?>/views/customer/products.php" class="btn btn-primary">View All Products</a>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>