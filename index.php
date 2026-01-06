<?php 
// 1. Start session to check user status
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include('includes/header.php'); 
?>

<div class="hero-section" style="text-align: center; padding: 100px 20px; background: #fff0f6;">
    <h1 style="font-size: 3rem; color: #d63384; margin-bottom: 10px;">Pinky Petal Boutique 🌸</h1>
    <p style="font-size: 1.2rem; color: #555; margin-bottom: 30px;">Manage your orders, payments, and deliveries in one elegant place.</p>

    <div class="cta-buttons">
        <?php if(isset($_SESSION['user_id'])): ?>
            <div class="card" style="max-width: 400px; margin: 0 auto; padding: 20px;">
                <p>Logged in as: <strong><?php echo $_SESSION['username']; ?></strong> (<?php echo $_SESSION['role']; ?>)</p>
                
                <div style="display: flex; gap: 10px; justify-content: center; margin-top: 15px;">
                    <a href="login.php"><button style="background: #d63384; border: none; padding: 12px 25px; border-radius: 5px; color: white; cursor: pointer;">Go to My Dashboard</button></a>
                    
                    <a href="logout.php"><button style="background: #2c3e50; border: none; padding: 12px 25px; border-radius: 5px; color: white; cursor: pointer;">Logout</button></a>
                </div>
            </div>
        <?php else: ?>
            <div style="display: flex; gap: 15px; justify-content: center;">
                <a href="login.php">
                    <button style="background: #d63384; border: none; padding: 15px 40px; border-radius: 5px; color: white; font-size: 1.1rem; cursor: pointer;">Sign In</button>
                </a>
                <a href="register.php">
                    <button style="background: #2c3e50; border: none; padding: 15px 40px; border-radius: 5px; color: white; font-size: 1.1rem; cursor: pointer;">Create Account</button>
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<div style="display: flex; justify-content: space-around; padding: 50px; background: white;">
    <div style="text-align: center;">
        <h3>👗 Latest Fashion</h3>
        <p>Browse unique collections from top sellers.</p>
    </div>
    <div style="text-align: center;">
        <h3>💳 Secure Payments</h3>
        <p>Verified by our Finance Management team.</p>
    </div>
    <div style="text-align: center;">
        <h3>🚚 Fast Delivery</h3>
        <p>Real-time tracking for every order.</p>
    </div>
</div>

<?php include('includes/footer.php'); ?>