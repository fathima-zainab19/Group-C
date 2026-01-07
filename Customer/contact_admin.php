<?php 
include('../../includes/header.php'); 
require_once('../../config/database.php');

if(isset($_POST['send_msg'])) {
    $user_id = $_SESSION['user_id'];
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);

    $sql = "INSERT INTO complaints (user_id, subject, message) VALUES ('$user_id', '$subject', '$message')";
    if(mysqli_query($conn, $sql)) {
        $msg = "Message sent to Admin successfully!";
    }
}
?>

<div class="dashboard-container">
    <div class="sidebar">
        <h3>Customer Menu</h3>
        <a href="dashboard.php">🏠 Home</a>
        <a href="browse_products.php">👗 Browse Dresses</a>
        <a href="my_orders.php">📦 Track My Orders</a>
        <a href="contact_admin.php" style="background: var(--main-pink);">💬 Help & Support</a>
        <a href="../../logout.php" style="color: #ff69b4; margin-top: 20px;">🚪 Logout</a>
    </div>

    <div class="main-content">
        <div class="card" style="max-width: 600px;">
            <h2>Contact Support</h2>
            <?php if(isset($msg)) echo "<p style='color:green;'>$msg</p>"; ?>
            <form action="" method="POST">
                <input type="text" name="subject" placeholder="What is the issue?" required>
                <textarea name="message" rows="5" placeholder="Describe your problem here..." style="width:100%; border-radius:8px; border:1px solid #ddd; padding:10px;"></textarea>
                <button type="submit" name="send_msg" style="margin-top:15px;">Send Message</button>
            </form>
        </div>
    </div>
</div>

<?php include('../../includes/footer.php'); ?>