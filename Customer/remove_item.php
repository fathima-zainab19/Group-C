<?php
include('../../includes/header.php'); 
session_start();
require_once('../../config/database.php');
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $user_id = $_SESSION['user_id'];
    mysqli_query($conn, "DELETE FROM cart WHERE id = '$id' AND user_id = '$user_id'");
}
header("Location: cart.php");
exit();
?>
<?php include('../../includes/footer.php'); ?>