<?php
/**
 * ============================================================================
 * APPLICATION CONFIGURATION
 * ============================================================================
 * This file contains all application-wide configuration constants and settings.
 * 
 * SHARED FILE - Used by all team members
 * 
 * Include this file at the top of every PHP file:
 *   require_once __DIR__ . '/config/config.php';
 * ============================================================================
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    // Configure session security settings
    ini_set('session.cookie_httponly', 1);    // Prevent JavaScript access to session cookie
    ini_set('session.use_only_cookies', 1);   // Only use cookies for sessions
    ini_set('session.cookie_secure', 0);      // Set to 1 in production with HTTPS

    session_start();
}

// ============================================================================
// PATH CONFIGURATION
// ============================================================================

// Base directory of the application
define('BASE_PATH', dirname(__DIR__));

// URL Configuration - UPDATE FOR YOUR ENVIRONMENT
define('BASE_URL', 'http://localhost/tech_world/');  // No trailing slash

// Directory paths
define('CONFIG_PATH', BASE_PATH . '/config');
define('CONTROLLERS_PATH', BASE_PATH . '/controllers');
define('MODELS_PATH', BASE_PATH . '/models');
define('VIEWS_PATH', BASE_PATH . '/views');
define('PUBLIC_PATH', BASE_PATH . '/public');
define('INCLUDES_PATH', BASE_PATH . '/includes');
define('UPLOADS_PATH', PUBLIC_PATH . '/uploads');

// ============================================================================
// APPLICATION SETTINGS
// ============================================================================

// Application name
define('APP_NAME', 'ShopEase');
define('APP_TAGLINE', 'Your One-Stop Shopping Destination');
define('APP_VERSION', '1.0.0');

// Pagination settings
define('ITEMS_PER_PAGE', 12);
define('ADMIN_ITEMS_PER_PAGE', 20);

// File upload settings
define('MAX_FILE_SIZE', 5 * 1024 * 1024);  // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// ============================================================================
// USER ROLES
// ============================================================================

define('ROLE_ADMIN', 'admin');
define('ROLE_CUSTOMER', 'customer');
define('ROLE_SELLER', 'seller');
define('ROLE_DELIVERY', 'delivery');
define('ROLE_SUPPORT', 'support');

// Role display names
define('ROLE_NAMES', [
    'admin' => 'Administrator',
    'customer' => 'Customer',
    'seller' => 'Seller',
    'delivery' => 'Delivery Staff',
    'support' => 'Support Staff'
]);

// Role dashboard URLs
define('ROLE_DASHBOARDS', [
    'admin' => '/views/admin/dashboard.php',
    'customer' => '/views/customer/dashboard.php',
    'seller' => '/views/seller/dashboard.php',
    'delivery' => '/views/delivery/dashboard.php',
    'support' => '/views/support/dashboard.php'
]);

// ============================================================================
// STATUS DEFINITIONS
// ============================================================================

// User statuses
define('USER_STATUS_ACTIVE', 'active');
define('USER_STATUS_PENDING', 'pending');
define('USER_STATUS_SUSPENDED', 'suspended');

// Order statuses
define('ORDER_STATUSES', [
    'pending' => 'Pending',
    'confirmed' => 'Confirmed',
    'processing' => 'Processing',
    'shipped' => 'Shipped',
    'delivered' => 'Delivered',
    'cancelled' => 'Cancelled'
]);

// Order status colors for UI
define('ORDER_STATUS_COLORS', [
    'pending' => '#f59e0b',
    'confirmed' => '#3b82f6',
    'processing' => '#8b5cf6',
    'shipped' => '#06b6d4',
    'delivered' => '#10b981',
    'cancelled' => '#ef4444'
]);

// Delivery statuses
define('DELIVERY_STATUSES', [
    'pending' => 'Pending Assignment',
    'assigned' => 'Assigned',
    'picked_up' => 'Picked Up',
    'in_transit' => 'In Transit',
    'delivered' => 'Delivered',
    'failed' => 'Delivery Failed'
]);

// Ticket statuses
define('TICKET_STATUSES', [
    'open' => 'Open',
    'in_progress' => 'In Progress',
    'resolved' => 'Resolved',
    'closed' => 'Closed'
]);

// Ticket priorities
define('TICKET_PRIORITIES', [
    'low' => 'Low',
    'medium' => 'Medium',
    'high' => 'High',
    'urgent' => 'Urgent'
]);

// ============================================================================
// HELPER FUNCTIONS
// ============================================================================

/**
 * Redirect to a URL
 * @param string $url URL to redirect to
 */
function redirect(string $url): void
{
    header("Location: " . BASE_URL . $url);
    exit();
}

/**
 * Escape HTML output to prevent XSS
 * @param string|null $string String to escape
 * @return string Escaped string
 */
function escape(?string $string): string
{
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Check if user is logged in
 * @return bool
 */
function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current user's role
 * @return string|null
 */
function getCurrentUserRole(): ?string
{
    return $_SESSION['user_role'] ?? null;
}

/**
 * Get current user's ID
 * @return int|null
 */
function getCurrentUserId(): ?int
{
    return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
}

/**
 * Check if current user has specific role
 * @param string|array $roles Role(s) to check
 * @return bool
 */
function hasRole($roles): bool
{
    if (!isLoggedIn())
        return false;

    $currentRole = getCurrentUserRole();
    if (is_array($roles)) {
        return in_array($currentRole, $roles);
    }
    return $currentRole === $roles;
}

/**
 * Require user to be logged in with specific role
 * @param string|array $roles Required role(s)
 */
function requireRole($roles): void
{
    if (!isLoggedIn()) {
        $_SESSION['error'] = 'Please login to access this page.';
        redirect('/login.php');
    }

    if (!hasRole($roles)) {
        $_SESSION['error'] = 'You do not have permission to access this page.';
        redirect('/login.php');
    }
}

/**
 * Set flash message
 * @param string $type Message type (success, error, warning, info)
 * @param string $message Message content
 */
function setFlashMessage(string $type, string $message): void
{
    $_SESSION[$type] = $message;
}

/**
 * Get and clear flash message
 * @param string $type Message type
 * @return string|null
 */
function getFlashMessage(string $type): ?string
{
    $message = $_SESSION[$type] ?? null;
    unset($_SESSION[$type]);
    return $message;
}

/**
 * Format price with currency
 * @param float $price Price value
 * @return string Formatted price
 */
function formatPrice(float $price): string
{
    return '$' . number_format($price, 2);
}

/**
 * Format date for display
 * @param string $date Date string
 * @param string $format Date format
 * @return string Formatted date
 */
function formatDate(string $date, string $format = 'M d, Y'): string
{
    return date($format, strtotime($date));
}

/**
 * Format datetime for display
 * @param string $datetime DateTime string
 * @return string Formatted datetime
 */
function formatDateTime(string $datetime): string
{
    return date('M d, Y h:i A', strtotime($datetime));
}

/**
 * Generate CSRF token
 * @return string CSRF token
 */
function generateCsrfToken(): string
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 * @param string $token Token to verify
 * @return bool
 */
function verifyCsrfToken(string $token): bool
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Get CSRF token input field
 * @return string HTML input field
 */
function csrfField(): string
{
    return '<input type="hidden" name="csrf_token" value="' . generateCsrfToken() . '">';
}

/**
 * Validate and sanitize input
 * @param string $input Input to sanitize
 * @return string Sanitized input
 */
function sanitizeInput(string $input): string
{
    return trim(strip_tags($input));
}

/**
 * Check if request is POST
 * @return bool
 */
function isPost(): bool
{
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

/**
 * Check if request is AJAX
 * @return bool
 */
function isAjax(): bool
{
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Return JSON response
 * @param array $data Data to encode
 * @param int $status HTTP status code
 */
function jsonResponse(array $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

// ============================================================================
// AUTO-LOAD MODELS AND DATABASE
// ============================================================================

// Include database configuration
require_once CONFIG_PATH . '/database.php';
?>