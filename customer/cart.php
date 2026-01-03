<?php
/**
 * CUSTOMER - CART VIEW
 * MEMBER 2 - CUSTOMER ROLE
 */

$pageTitle = 'Shopping Cart';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../controllers/CustomerController.php';

$controller = new CustomerController();

// Handle cart actions
if (isPost()) {
    $action = $_POST['action'] ?? '';

    if ($action === 'checkout') {
        $result = $controller->placeOrder();
        if ($result['success']) {
            setFlashMessage('success', $result['message']);
            redirect('/views/customer/orders.php?id=' . $result['order_id']);
        } else {
            setFlashMessage('error', $result['message']);
        }
    } else {
        $result = $controller->handleCartAction();
        if ($result['success']) {
            setFlashMessage('success', $result['message']);
        } else {
            setFlashMessage('error', $result['message']);
        }
    }
    redirect('/views/customer/cart.php');
}

$cartItems = $controller->getCart();
$cartTotal = $controller->getCartTotal();
?>

<div class="page-header">
    <h1>Shopping Cart</h1>
    <p>Review your items before checkout</p>
</div>

<?php if (!empty($cartItems)): ?>
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">
        <!-- Cart Items -->
        <div class="card">
            <div class="card-header">
                <h3>Cart Items (
                    <?php echo count($cartItems); ?>)
                </h3>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="clear">
                    <button type="submit" class="btn btn-sm btn-secondary" data-confirm="Clear all items from cart?">Clear
                        Cart</button>
                </form>
            </div>
            <div class="card-body">
                <?php foreach ($cartItems as $item): ?>
                    <div
                        style="display: flex; gap: 1rem; padding: 1rem; border-bottom: 1px solid var(--gray-200); align-items: center;">
                        <!-- Product Image -->
                        <div
                            style="width: 80px; height: 80px; background: var(--gray-100); border-radius: var(--radius); display: flex; align-items: center; justify-content: center; font-size: 2rem;">
                            📦
                        </div>

                        <!-- Product Details -->
                        <div style="flex: 1;">
                            <h4 style="margin: 0 0 0.25rem 0;">
                                <?php echo escape($item['name']); ?>
                            </h4>
                            <p class="text-muted" style="margin: 0 0 0.5rem 0;">Sold by:
                                <?php echo escape($item['seller_name']); ?>
                            </p>
                            <span style="font-weight: 600; color: var(--primary-color);">
                                <?php echo formatPrice($item['price']); ?>
                            </span>
                        </div>

                        <!-- Quantity -->
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <form method="POST" style="display: flex; gap: 0.5rem; align-items: center;">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1"
                                    max="<?php echo $item['stock']; ?>" class="form-control" style="width: 70px;"
                                    onchange="this.form.submit()">
                            </form>
                        </div>

                        <!-- Subtotal -->
                        <div style="min-width: 100px; text-align: right;">
                            <strong>
                                <?php echo formatPrice($item['price'] * $item['quantity']); ?>
                            </strong>
                        </div>

                        <!-- Remove -->
                        <form method="POST">
                            <input type="hidden" name="action" value="remove">
                            <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                            <button type="submit" class="btn btn-sm btn-danger" title="Remove">🗑️</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Order Summary & Checkout -->
        <div>
            <div class="card">
                <div class="card-header">
                    <h3>Order Summary</h3>
                </div>
                <div class="card-body">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                        <span>Subtotal</span>
                        <span>
                            <?php echo formatPrice($cartTotal); ?>
                        </span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                        <span>Shipping</span>
                        <span class="text-success">Free</span>
                    </div>
                    <hr style="margin: 1rem 0;">
                    <div style="display: flex; justify-content: space-between; font-size: 1.25rem; font-weight: 700;">
                        <span>Total</span>
                        <span style="color: var(--primary-color);">
                            <?php echo formatPrice($cartTotal); ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Checkout Form -->
            <div class="card mt-3">
                <div class="card-header">
                    <h3>Checkout</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <?php echo csrfField(); ?>
                        <input type="hidden" name="action" value="checkout">

                        <div class="form-group">
                            <label for="shipping_address">Shipping Address <span class="required">*</span></label>
                            <textarea name="shipping_address" id="shipping_address" class="form-control" rows="3" required
                                placeholder="Enter your complete shipping address"><?php echo escape($_SESSION['address'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="shipping_phone">Phone Number <span class="required">*</span></label>
                            <input type="tel" name="shipping_phone" id="shipping_phone" class="form-control" required
                                placeholder="Your contact number">
                        </div>

                        <div class="form-group">
                            <label for="payment_method">Payment Method</label>
                            <select name="payment_method" id="payment_method" class="form-control">
                                <option value="cod">Cash on Delivery</option>
                                <option value="card">Credit/Debit Card</option>
                                <option value="upi">UPI</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="notes">Order Notes (Optional)</label>
                            <textarea name="notes" id="notes" class="form-control" rows="2"
                                placeholder="Any special instructions..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block btn-lg">
                            Place Order -
                            <?php echo formatPrice($cartTotal); ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

<?php else: ?>
    <div class="empty-state">
        <div class="empty-icon">🛒</div>
        <h3>Your cart is empty</h3>
        <p>Looks like you haven't added any items to your cart yet.</p>
        <a href="<?php echo BASE_URL; ?>/views/customer/products.php" class="btn btn-primary btn-lg">Start Shopping</a>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>