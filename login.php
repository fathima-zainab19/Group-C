<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Pinky Petal</title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body style="background: #fff0f6; display: flex; justify-content: center; align-items: center; height: 100vh;">

    <div style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); width: 350px;">
        <h2 style="text-align: center; color: #d63384;">Pinky Petal Login</h2>
        
        <?php if(isset($_GET['error'])): ?>
            <p style="color: red; background: #ffdce0; padding: 10px; border-radius: 5px; text-align: center;">
                <?php echo htmlspecialchars($_GET['error']); ?>
            </p>
        <?php endif; ?>

        <form action="process_login.php" method="POST">
            <div style="margin-bottom: 15px;">
                <label>Username</label>
                <input type="text" name="username" required style="width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ddd; border-radius: 5px;">
            </div>
            <div style="margin-bottom: 15px;">
                <label>Password</label>
                <input type="password" name="password" required style="width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ddd; border-radius: 5px;">
            </div>
            <button type="submit" style="width: 100%; padding: 10px; background: #d63384; color: white; border: none; border-radius: 5px; cursor: pointer;">Login</button>
        </form>
    </div>

</body>
</html>