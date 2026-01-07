<?php 
include('../../includes/header.php'); 
require_once('../../config/database.php');

// Security: Ensure only Customers can see this
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Customer') {
    header("Location: ../../login.php");
    exit();
}

// Fetch 3 random products for the "Featured" section
$sql = "SELECT * FROM products LIMIT 3";
$featured = mysqli_query($conn, $sql);
?>

<div class="dashboard-container">
    <div class="sidebar">
        <h3>Customer Menu</h3>
        <a href="dashboard.php">🏠 Home</a>
        <a href="browse_products.php">👗 Browse Dresses</a>
        <a href="cart.php">🛒 My Shopping Cart</a>
        <a href="my_orders.php">📦 Track My Orders</a>
        <a href="contact_admin.php">💬 Help & Support</a>
        <a href="../../logout.php" style="color: #ff69b4; margin-top: 20px;">🚪 Logout</a>
    </div>

    <div class="main-content">
        <div class="card">
            <h1>Welcome to Pinky Petal, <?php echo $_SESSION['username']; ?>! 🌸</h1>
            <p>Explore our latest dress collections and manage your orders below.</p>
        </div>

        <h2 style="margin-top: 30px; color: var(--dark-pink);">Featured Collections</h2>
        
        <div style="display: flex; gap: 20px; margin-top: 15px;">
            <?php if(mysqli_num_rows($featured) > 0): ?>
                <?php while($product = mysqli_fetch_assoc($featured)): ?>
                    <div class="card" style="flex: 1; text-align: center; padding: 15px;">
                        <img src="../../public/uploads/<?php echo $product['image']; ?>" style="width: 100%; height: 200px; object-fit: cover; border-radius: 10px;">
                        <h3 style="margin: 10px 0;"><?php echo $product['name']; ?></h3>
                        <p style="color: var(--main-pink); font-weight: bold; font-size: 1.2rem;">$<?php echo number_format($product['price'], 2); ?></p>
                        <a href="add_to_cart.php?id=<?php echo $product['id']; ?>">
                            <button type="button">Add to Cart</button>
                        </a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="card" style="width: 100%; text-align: center; padding: 40px;">
                    <p>No dresses available right now. Check back soon!</p>
                </div>
            <?php endif; ?>
        </div>

        <div style="display: flex; gap: 15px; margin-top: 30px;">
            <a href="browse_products.php" style="flex: 1;"><button style="background: #2c3e50;">Browse All Dresses</button></a>
            <a href="my_orders.php" style="flex: 1;"><button style="background: #2c3e50;">Track My Orders</button></a>
        </div>
    </div>
</div>

<?php include('../../includes/footer.php'); ?>