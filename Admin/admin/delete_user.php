<?php
 include('../../includes/header.php'); 
session_start();
require_once('../../config/database.php');
require_once('../../models/Admin.php');

if ($_SESSION['role'] === 'Admin' && isset($_GET['id'])) {
    $adminModel = new Admin($conn);
    $userId = $_GET['id'];

    if ($adminModel->deleteUser($userId)) {
        header("Location: manage_users.php?msg=UserDeleted");
    } else {
        echo "Error deleting record.";
    }
}
?>

<?php include('../../includes/footer.php'); ?>