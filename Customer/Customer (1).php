<?php
class Customer {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // CREATE: Place a new order
    public function placeOrder($customerId, $totalAmount) {
        $query = "INSERT INTO orders (customer_id, total, status) VALUES ('$customerId', '$totalAmount', 'Pending')";
        return mysqli_query($this->conn, $query);
    }

    // READ: View order history
    public function getMyOrders($customerId) {
        $query = "SELECT * FROM orders WHERE customer_id = $customerId";
        return mysqli_query($this->conn, $query);
    }
}
?>