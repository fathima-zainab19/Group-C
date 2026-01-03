<?php
/**
 * ============================================================================
 * ADMIN MODEL
 * ============================================================================
 * Model for Admin-specific database operations.
 * 
 * MEMBER 1 - ADMIN ROLE
 * ============================================================================
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/User.php';

class Admin extends User
{

    /**
     * Get dashboard statistics
     */
    public function getDashboardStats(): array
    {
        $stats = [];

        // Total users by role
        $stmt = $this->db->query("SELECT role, COUNT(*) as count FROM users GROUP BY role");
        $stats['users_by_role'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        // Pending approvals
        $stmt = $this->db->query("SELECT COUNT(*) FROM users WHERE status = 'pending'");
        $stats['pending_approvals'] = $stmt->fetchColumn();

        // Total products
        $stmt = $this->db->query("SELECT COUNT(*) FROM products");
        $stats['total_products'] = $stmt->fetchColumn();

        // Total orders
        $stmt = $this->db->query("SELECT COUNT(*) FROM orders");
        $stats['total_orders'] = $stmt->fetchColumn();

        // Total revenue
        $stmt = $this->db->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE status != 'cancelled'");
        $stats['total_revenue'] = $stmt->fetchColumn();

        // Recent orders
        $stmt = $this->db->query("SELECT o.*, u.full_name as customer_name FROM orders o 
                                  JOIN users u ON o.customer_id = u.id 
                                  ORDER BY o.order_date DESC LIMIT 5");
        $stats['recent_orders'] = $stmt->fetchAll();

        // Open tickets
        $stmt = $this->db->query("SELECT COUNT(*) FROM support_tickets WHERE status IN ('open', 'in_progress')");
        $stats['open_tickets'] = $stmt->fetchColumn();

        return $stats;
    }

    /**
     * Get all users with pagination
     */
    public function getAllUsers(int $page = 1, int $limit = 20, array $filters = []): array
    {
        $offset = ($page - 1) * $limit;

        $sql = "SELECT id, username, email, role, status, phone, full_name, created_at FROM users WHERE 1=1";
        $countSql = "SELECT COUNT(*) FROM users WHERE 1=1";
        $params = [];

        if (!empty($filters['role'])) {
            $sql .= " AND role = ?";
            $countSql .= " AND role = ?";
            $params[] = $filters['role'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND status = ?";
            $countSql .= " AND status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $sql .= " AND (username LIKE ? OR email LIKE ? OR full_name LIKE ?)";
            $countSql .= " AND (username LIKE ? OR email LIKE ? OR full_name LIKE ?)";
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        // Get total count
        $stmt = $this->db->prepare($countSql);
        $stmt->execute($params);
        $total = $stmt->fetchColumn();

        // Get users
        $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $users = $stmt->fetchAll();

        return [
            'users' => $users,
            'total' => $total,
            'pages' => ceil($total / $limit),
            'current_page' => $page
        ];
    }

    /**
     * Approve user account
     */
    public function approveUser(int $userId): bool
    {
        return $this->updateStatus($userId, 'active');
    }

    /**
     * Suspend user account
     */
    public function suspendUser(int $userId): bool
    {
        return $this->updateStatus($userId, 'suspended');
    }

    // ========================================================================
    // CATEGORY MANAGEMENT
    // ========================================================================

    /**
     * Get all categories
     */
    public function getCategories(): array
    {
        $stmt = $this->db->query("SELECT c.*, COUNT(p.id) as product_count 
                                  FROM categories c 
                                  LEFT JOIN products p ON c.id = p.category_id 
                                  GROUP BY c.id 
                                  ORDER BY c.name");
        return $stmt->fetchAll();
    }

    /**
     * Get category by ID
     */
    public function getCategoryById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Create category
     */
    public function createCategory(array $data): int|false
    {
        try {
            $stmt = $this->db->prepare("INSERT INTO categories (name, description, status) VALUES (?, ?, ?)");
            $stmt->execute([
                $data['name'],
                $data['description'] ?? null,
                $data['status'] ?? 'active'
            ]);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Create Category Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update category
     */
    public function updateCategory(int $id, array $data): bool
    {
        try {
            $stmt = $this->db->prepare("UPDATE categories SET name = ?, description = ?, status = ? WHERE id = ?");
            return $stmt->execute([
                $data['name'],
                $data['description'] ?? null,
                $data['status'] ?? 'active',
                $id
            ]);
        } catch (PDOException $e) {
            error_log("Update Category Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete category
     */
    public function deleteCategory(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM categories WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Delete Category Error: " . $e->getMessage());
            return false;
        }
    }

    // ========================================================================
    // REPORTS
    // ========================================================================

    /**
     * Get sales report
     */
    public function getSalesReport(string $startDate = null, string $endDate = null): array
    {
        $params = [];
        $sql = "SELECT DATE(order_date) as date, COUNT(*) as orders, SUM(total_amount) as revenue 
                FROM orders WHERE status != 'cancelled'";

        if ($startDate) {
            $sql .= " AND order_date >= ?";
            $params[] = $startDate;
        }
        if ($endDate) {
            $sql .= " AND order_date <= ?";
            $params[] = $endDate . ' 23:59:59';
        }

        $sql .= " GROUP BY DATE(order_date) ORDER BY date DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get top sellers
     */
    public function getTopSellers(int $limit = 10): array
    {
        $stmt = $this->db->prepare("
            SELECT u.id, u.full_name, u.email, COUNT(DISTINCT oi.order_id) as total_orders, 
                   SUM(oi.quantity * oi.price) as total_revenue
            FROM users u
            JOIN order_items oi ON u.id = oi.seller_id
            GROUP BY u.id
            ORDER BY total_revenue DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    /**
     * Get top products
     */
    public function getTopProducts(int $limit = 10): array
    {
        $stmt = $this->db->prepare("
            SELECT p.id, p.name, p.price, SUM(oi.quantity) as total_sold, 
                   SUM(oi.quantity * oi.price) as total_revenue
            FROM products p
            JOIN order_items oi ON p.id = oi.product_id
            GROUP BY p.id
            ORDER BY total_sold DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    /**
     * Get user registration stats
     */
    public function getUserRegistrationStats(int $days = 30): array
    {
        $stmt = $this->db->prepare("
            SELECT DATE(created_at) as date, role, COUNT(*) as count
            FROM users
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
            GROUP BY DATE(created_at), role
            ORDER BY date
        ");
        $stmt->execute([$days]);
        return $stmt->fetchAll();
    }
}
?>