<?php
require_once('../config/database.php');

class CustomerController {
    // CREATE: Add item to shopping cart
    public function addToCart($conn, $customerId, $productId, $quantity) {
        $sql = "INSERT INTO cart (user_id, product_id, quantity) VALUES ('$customerId', '$productId', '$quantity')";
        return mysqli_query($conn, $sql);
    }

    // READ: View order status
    public function getOrderStatus($conn, $customerId) {
        $sql = "SELECT * FROM orders WHERE customer_id = '$customerId'";
        return mysqli_query($conn, $sql);
    }
}
?>