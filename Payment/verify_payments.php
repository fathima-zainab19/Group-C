<?php
include('../../includes/header.php'); 
session_start();
require_once('../../config/database.php');

// 1. Check if ID is provided in the URL
if (isset($_GET['id'])) {
    $order_id = mysqli_real_escape_string($conn, $_GET['id']);

    // 2. Update the status from 'Pending' to 'Paid'
    $sql = "UPDATE orders SET status = 'Paid' WHERE id = '$order_id'";

    if (mysqli_query($conn, $sql)) {
        // 3. Success! Go back to dashboard with a message
        header("Location: dashboard.php?msg=Order #" . $order_id . " verified and marked as PAID.");
        exit();
    } else {
        // Error handling
        echo "Error updating record: " . mysqli_error($conn);
    }
} else {
    header("Location: dashboard.php");
    exit();
}
?>
<?php include('../../includes/footer.php'); ?>