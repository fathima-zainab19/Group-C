<?php
 include('../../includes/header.php'); 
session_start();
require_once('../../config/database.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

if (isset($_GET['id'])) {
    $product_id = mysqli_real_escape_string($conn, $_GET['id']);
    $user_id = $_SESSION['user_id'];

    // Check if item is already in cart
    $check = mysqli_query($conn, "SELECT * FROM cart WHERE user_id = '$user_id' AND product_id = '$product_id'");

    if (mysqli_num_rows($check) > 0) {
        mysqli_query($conn, "UPDATE cart SET quantity = quantity + 1 WHERE user_id = '$user_id' AND product_id = '$product_id'");
    } else {
        mysqli_query($conn, "INSERT INTO cart (user_id, product_id, quantity) VALUES ('$user_id', '$product_id', 1)");
    }
    
    // Redirect to cart.php so the user sees their item was added
    header("Location: cart.php");
    exit();
}
?>
<?php include('../../includes/footer.php'); ?>