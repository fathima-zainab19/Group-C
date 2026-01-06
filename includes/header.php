<?php 
if (session_status() == PHP_SESSION_NONE) {
    session_start(); 
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pinky Petal | Boutique</title>
    <link rel="stylesheet" href="http://localhost/pinky_petal/public/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
</head>
<body>

<nav>
    <a href="http://localhost/pinky_petal/index.php" class="logo">🌸 Pinky Petal</a>
    <ul>
        <li><a href="http://localhost/pinky_petal/index.php">Home</a></li>
        <?php if(isset($_SESSION['username'])): ?>
            <li><a href="http://localhost/pinky_petal/logout.php">Logout</a></li>
        <?php else: ?>
            <li><a href="http://localhost/pinky_petal/login.php">Login</a></li>
            <li><a href="http://localhost/pinky_petal/register.php">Register</a></li>
        <?php endif; ?>
    </ul>
</nav>