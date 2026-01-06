<?php
// Base URL for the project - update this if your folder name changes
define('BASE_URL', 'http://localhost/pinky_petal/');

// System Name
define('APP_NAME', 'Pinky Petal Online Shopping');

// Session Start - Required for role-based authentication
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Error reporting (Turn off for production, keep on for development)
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>