<?php
class Payment {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // READ: View transaction logs
    public function viewPayments() {
        $query = "SELECT * FROM payments";
        return mysqli_query($this->conn, $query);
    }

    // UPDATE: Change payment status (Success/Fail)
    public function verifyTransaction($paymentId, $status) {
        $query = "UPDATE payments SET status = '$status' WHERE id = $paymentId";
        return mysqli_query($this->conn, $query);
    }
}
?>