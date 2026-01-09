<?php
 include('../../includes/header.php');
require_once('../../config/database.php');
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $order_id = $_POST['order_id'];

    // Update status to 'Paid'
    $sql = "UPDATE orders SET status = 'Paid' WHERE id = '$order_id'";
    
    if (mysqli_query($conn, $sql)) {
        // Go back to the dashboard to see the change
        header("Location: dashboard.php");
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
<?php include('../../includes/footer.php'); ?>