<?php
/**
 * ============================================================================
 * LOGOUT SCRIPT
 * ============================================================================
 * Securely destroys user session and redirects to login page.
 * 
 * SHARED FILE - Used by all team members
 * ============================================================================
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/models/User.php';

// Destroy session
$userModel = new User();
$userModel->destroySession();

// Set logout message
session_start();
setFlashMessage('success', 'You have been logged out successfully.');

// Redirect to login
redirect('/login.php');
?>