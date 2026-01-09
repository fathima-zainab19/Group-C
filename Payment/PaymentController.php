<?php
require_once('../config/database.php');

class PaymentController {
    // READ: View all payments
    public function getPaymentLogs($conn) {
        $sql = "SELECT * FROM payments";
        return mysqli_query($conn, $sql);
    }

    // UPDATE: Verify payment status
    public function verifyPayment($conn, $paymentId, $status) {
        // status could be 'Successful' or 'Failed'
        $sql = "UPDATE payments SET status = '$status' WHERE id = '$paymentId'";
        return mysqli_query($conn, $sql);
    }
}
?>