<?php
/**
 * ============================================================================
 * ADMIN CONTROLLER
 * ============================================================================
 * Controller for Admin role handling user management, categories, and reports.
 * 
 * MEMBER 1 - ADMIN ROLE
 * ============================================================================
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Admin.php';

class AdminController
{
    private $adminModel;

    public function __construct()
    {
        // Ensure only admin can access
        requireRole('admin');
        $this->adminModel = new Admin();
    }

    /**
     * Get dashboard data
     */
    public function dashboard(): array
    {
        return $this->adminModel->getDashboardStats();
    }

    /**
     * Get users with pagination and filters
     */
    public function getUsers(int $page = 1, array $filters = []): array
    {
        return $this->adminModel->getAllUsers($page, ADMIN_ITEMS_PER_PAGE, $filters);
    }

    /**
     * Handle user actions (approve, suspend, delete)
     */
    public function handleUserAction(): array
    {
        if (!isPost()) {
            return ['success' => false, 'message' => 'Invalid request method'];
        }

        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            return ['success' => false, 'message' => 'Invalid security token'];
        }

        $action = $_POST['action'] ?? '';
        $userId = (int) ($_POST['user_id'] ?? 0);

        if (!$userId) {
            return ['success' => false, 'message' => 'Invalid user ID'];
        }

        // Prevent self-action
        if ($userId === getCurrentUserId()) {
            return ['success' => false, 'message' => 'Cannot perform action on yourself'];
        }

        switch ($action) {
            case 'approve':
                $result = $this->adminModel->approveUser($userId);
                $message = $result ? 'User approved successfully' : 'Failed to approve user';
                break;

            case 'suspend':
                $result = $this->adminModel->suspendUser($userId);
                $message = $result ? 'User suspended successfully' : 'Failed to suspend user';
                break;

            case 'delete':
                $result = $this->adminModel->delete($userId);
                $message = $result ? 'User deleted successfully' : 'Failed to delete user';
                break;

            case 'update':
                $data = [
                    'full_name' => sanitizeInput($_POST['full_name'] ?? ''),
                    'email' => sanitizeInput($_POST['email'] ?? ''),
                    'phone' => sanitizeInput($_POST['phone'] ?? ''),
                    'status' => sanitizeInput($_POST['status'] ?? 'active')
                ];
                $result = $this->adminModel->update($userId, $data);
                $message = $result ? 'User updated successfully' : 'Failed to update user';
                break;

            default:
                return ['success' => false, 'message' => 'Invalid action'];
        }

        return ['success' => $result, 'message' => $message];
    }

    /**
     * Get user by ID
     */
    public function getUser(int $id): ?array
    {
        return $this->adminModel->findById($id);
    }

    // ========================================================================
    // CATEGORY METHODS
    // ========================================================================

    /**
     * Get all categories
     */
    public function getCategories(): array
    {
        return $this->adminModel->getCategories();
    }

    /**
     * Get category by ID
     */
    public function getCategory(int $id): ?array
    {
        return $this->adminModel->getCategoryById($id);
    }

    /**
     * Handle category actions
     */
    public function handleCategoryAction(): array
    {
        if (!isPost()) {
            return ['success' => false, 'message' => 'Invalid request method'];
        }

        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            return ['success' => false, 'message' => 'Invalid security token'];
        }

        $action = $_POST['action'] ?? '';
        $categoryId = (int) ($_POST['category_id'] ?? 0);

        $data = [
            'name' => sanitizeInput($_POST['name'] ?? ''),
            'description' => sanitizeInput($_POST['description'] ?? ''),
            'status' => sanitizeInput($_POST['status'] ?? 'active')
        ];

        // Validate name
        if (empty($data['name'])) {
            return ['success' => false, 'message' => 'Category name is required'];
        }

        switch ($action) {
            case 'create':
                $result = $this->adminModel->createCategory($data);
                $message = $result ? 'Category created successfully' : 'Failed to create category';
                $success = $result !== false;
                break;

            case 'update':
                if (!$categoryId) {
                    return ['success' => false, 'message' => 'Invalid category ID'];
                }
                $result = $this->adminModel->updateCategory($categoryId, $data);
                $message = $result ? 'Category updated successfully' : 'Failed to update category';
                $success = $result;
                break;

            case 'delete':
                if (!$categoryId) {
                    return ['success' => false, 'message' => 'Invalid category ID'];
                }
                $result = $this->adminModel->deleteCategory($categoryId);
                $message = $result ? 'Category deleted successfully' : 'Failed to delete category (may have products)';
                $success = $result;
                break;

            default:
                return ['success' => false, 'message' => 'Invalid action'];
        }

        return ['success' => $success, 'message' => $message];
    }

    // ========================================================================
    // REPORT METHODS
    // ========================================================================

    /**
     * Get sales report
     */
    public function getSalesReport(string $startDate = null, string $endDate = null): array
    {
        return $this->adminModel->getSalesReport($startDate, $endDate);
    }

    /**
     * Get top sellers report
     */
    public function getTopSellers(int $limit = 10): array
    {
        return $this->adminModel->getTopSellers($limit);
    }

    /**
     * Get top products report
     */
    public function getTopProducts(int $limit = 10): array
    {
        return $this->adminModel->getTopProducts($limit);
    }

    /**
     * Get user registration statistics
     */
    public function getUserStats(int $days = 30): array
    {
        return $this->adminModel->getUserRegistrationStats($days);
    }
}
?>