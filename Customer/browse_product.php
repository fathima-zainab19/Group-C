<?php
include('../../includes/header.php'); 

session_start();
// Go up two levels to find the database config
require_once('../../config/database.php');

// Fetch all products
$sql = "SELECT * FROM products";
$result = mysqli_query($conn, $sql);

// ERROR CHECK: If the table is missing, this will catch it
if (!$result) {
    die("Database Error: " . mysqli_error($conn) . " <br>Please ensure the 'products' table is created.");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Browse Dresses - Pinky Petal</title>
    <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body>
    <h2>Our Collection</h2>
    <div style="display: flex; flex-wrap: wrap; gap: 20px;">
        <?php if(mysqli_num_rows($result) > 0): ?>
            <?php while($product = mysqli_fetch_assoc($result)): ?>
                <div style="border: 1px solid #ddd; padding: 15px; width: 200px; text-align: center;">
                    <img src="../../public/images/<?php echo $product['image']; ?>" width="100%">
                    <h3><?php echo $product['name']; ?></h3>
                    <p>$<?php echo $product['price']; ?></p>
                    <form action="add_to_cart.php" method="POST">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        <button type="submit">Add to Cart</button>
                    </form>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No products available yet.</p>
        <?php endif; ?>
    </div>
    <br>
    <a href="dashboard.php">Back to Dashboard</a>
</body>
</html>

<?php include('../../includes/footer.php'); ?>