<?php
 include('../../includes/header.php'); 
session_start();
require_once('../../config/database.php');

if (isset($_POST['status']) && $_SESSION['role'] === 'Payment Manager') {
    $payment_id = $_POST['payment_id'];
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];

    // UPDATE Operation: Update Payment Status
    $sql_payment = "UPDATE payments SET status = '$status' WHERE id = '$payment_id'";
    mysqli_query($conn, $sql_payment);

    // UPDATE Operation: If success, update Order Status so Seller can see it
    if ($status === 'Success') {
        $sql_order = "UPDATE orders SET status = 'Paid' WHERE id = '$order_id'";
        mysqli_query($conn, $sql_order);
    }

    header("Location: verify_payments.php?update=done");
}
?>
<?php include('../../includes/footer.php'); ?>