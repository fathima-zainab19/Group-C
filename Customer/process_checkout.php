<?php
 include('../../includes/header.php'); 
session_start();
require_once('../../config/database.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $total = $_POST['total'];
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);

    // 1. Insert into orders table
    $order_sql = "INSERT INTO orders (customer_id, total, status, delivery_address, contact_number) 
                  VALUES ('$user_id', '$total', 'Pending', '$address', '$phone')";

    if (mysqli_query($conn, $order_sql)) {
        // 2. Clear the user's cart
        $clear_cart = "DELETE FROM cart WHERE user_id = '$user_id'";
        mysqli_query($conn, $clear_cart);

        // 3. Success! Redirect to My Orders
        header("Location: my_orders.php?msg=Order placed successfully!");
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
<?php include('../../includes/footer.php'); ?>