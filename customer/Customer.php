<?php
/**
 * ============================================================================
 * CUSTOMER MODEL
 * ============================================================================
 * Model for Customer-specific database operations.
 * 
 * MEMBER 2 - CUSTOMER ROLE
 * ============================================================================
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/User.php';

class Customer extends User
{

    /**
     * Get dashboard data for customer
     */
    public function getDashboardData(int $customerId): array
    {
        $data = [];

        // Get order count
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM orders WHERE customer_id = ?");
        $stmt->execute([$customerId]);
        $data['total_orders'] = $stmt->fetchColumn();

        // Get cart items count
        $stmt = $this->db->prepare("SELECT COALESCE(SUM(quantity), 0) FROM cart WHERE customer_id = ?");
        $stmt->execute([$customerId]);
        $data['cart_items'] = $stmt->fetchColumn();

        // Get pending orders
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM orders WHERE customer_id = ? AND status NOT IN ('delivered', 'cancelled')");
        $stmt->execute([$customerId]);
        $data['pending_orders'] = $stmt->fetchColumn();

        // Get open tickets
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM support_tickets WHERE customer_id = ? AND status != 'closed'");
        $stmt->execute([$customerId]);
        $data['open_tickets'] = $stmt->fetchColumn();

        // Featured products
        $stmt = $this->db->query("SELECT p.*, c.name as category_name FROM products p 
                                  JOIN categories c ON p.category_id = c.id 
                                  WHERE p.status = 'active' ORDER BY RAND() LIMIT 4");
        $data['featured_products'] = $stmt->fetchAll();

        // Recent orders
        $stmt = $this->db->prepare("SELECT * FROM orders WHERE customer_id = ? ORDER BY order_date DESC LIMIT 5");
        $stmt->execute([$customerId]);
        $data['recent_orders'] = $stmt->fetchAll();

        return $data;
    }

    // ========================================================================
    // PRODUCT BROWSING
    // ========================================================================

    /**
     * Get products with filters
     */
    public function getProducts(array $filters = [], int $page = 1, int $limit = 12): array
    {
        $offset = ($page - 1) * $limit;

        $sql = "SELECT p.*, c.name as category_name, u.full_name as seller_name 
                FROM products p 
                JOIN categories c ON p.category_id = c.id 
                JOIN users u ON p.seller_id = u.id 
                WHERE p.status = 'active'";
        $countSql = "SELECT COUNT(*) FROM products p WHERE p.status = 'active'";
        $params = [];

        if (!empty($filters['category'])) {
            $sql .= " AND p.category_id = ?";
            $countSql .= " AND p.category_id = ?";
            $params[] = $filters['category'];
        }

        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
            $countSql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
            $params[] = $search;
            $params[] = $search;
        }

        if (!empty($filters['min_price'])) {
            $sql .= " AND p.price >= ?";
            $countSql .= " AND p.price >= ?";
            $params[] = $filters['min_price'];
        }

        if (!empty($filters['max_price'])) {
            $sql .= " AND p.price <= ?";
            $countSql .= " AND p.price <= ?";
            $params[] = $filters['max_price'];
        }

        // Get total count
        $stmt = $this->db->prepare($countSql);
        $stmt->execute($params);
        $total = $stmt->fetchColumn();

        // Add sorting
        $sort = $filters['sort'] ?? 'newest';
        switch ($sort) {
            case 'price_low':
                $sql .= " ORDER BY p.price ASC";
                break;
            case 'price_high':
                $sql .= " ORDER BY p.price DESC";
                break;
            case 'name':
                $sql .= " ORDER BY p.name ASC";
                break;
            default:
                $sql .= " ORDER BY p.created_at DESC";
        }

        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return [
            'products' => $stmt->fetchAll(),
            'total' => $total,
            'pages' => ceil($total / $limit),
            'current_page' => $page
        ];
    }

    /**
     * Get single product by ID
     */
    public function getProductById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT p.*, c.name as category_name, u.full_name as seller_name 
            FROM products p 
            JOIN categories c ON p.category_id = c.id 
            JOIN users u ON p.seller_id = u.id 
            WHERE p.id = ? AND p.status = 'active'
        ");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Get product reviews
     */
    public function getProductReviews(int $productId): array
    {
        $stmt = $this->db->prepare("
            SELECT r.*, u.full_name as customer_name 
            FROM reviews r 
            JOIN users u ON r.customer_id = u.id 
            WHERE r.product_id = ? AND r.status = 'approved'
            ORDER BY r.created_at DESC
        ");
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }

    /**
     * Get all categories
     */
    public function getCategories(): array
    {
        $stmt = $this->db->query("SELECT * FROM categories WHERE status = 'active' ORDER BY name");
        return $stmt->fetchAll();
    }

    // ========================================================================
    // CART MANAGEMENT
    // ========================================================================

    /**
     * Get cart items
     */
    public function getCartItems(int $customerId): array
    {
        $stmt = $this->db->prepare("
            SELECT c.*, p.name, p.price, p.image, p.stock, u.full_name as seller_name 
            FROM cart c 
            JOIN products p ON c.product_id = p.id 
            JOIN users u ON p.seller_id = u.id 
            WHERE c.customer_id = ? AND p.status = 'active'
        ");
        $stmt->execute([$customerId]);
        return $stmt->fetchAll();
    }

    /**
     * Get cart total
     */
    public function getCartTotal(int $customerId): float
    {
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(c.quantity * p.price), 0) 
            FROM cart c 
            JOIN products p ON c.product_id = p.id 
            WHERE c.customer_id = ?
        ");
        $stmt->execute([$customerId]);
        return (float) $stmt->fetchColumn();
    }

    /**
     * Add item to cart
     */
    public function addToCart(int $customerId, int $productId, int $quantity = 1): bool
    {
        try {
            // Check if item already in cart
            $stmt = $this->db->prepare("SELECT id, quantity FROM cart WHERE customer_id = ? AND product_id = ?");
            $stmt->execute([$customerId, $productId]);
            $existing = $stmt->fetch();

            if ($existing) {
                // Update quantity
                $stmt = $this->db->prepare("UPDATE cart SET quantity = quantity + ? WHERE id = ?");
                return $stmt->execute([$quantity, $existing['id']]);
            } else {
                // Insert new item
                $stmt = $this->db->prepare("INSERT INTO cart (customer_id, product_id, quantity) VALUES (?, ?, ?)");
                return $stmt->execute([$customerId, $productId, $quantity]);
            }
        } catch (PDOException $e) {
            error_log("Add to Cart Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update cart item quantity
     */
    public function updateCartItem(int $customerId, int $productId, int $quantity): bool
    {
        try {
            if ($quantity <= 0) {
                return $this->removeFromCart($customerId, $productId);
            }

            $stmt = $this->db->prepare("UPDATE cart SET quantity = ? WHERE customer_id = ? AND product_id = ?");
            return $stmt->execute([$quantity, $customerId, $productId]);
        } catch (PDOException $e) {
            error_log("Update Cart Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Remove item from cart
     */
    public function removeFromCart(int $customerId, int $productId): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM cart WHERE customer_id = ? AND product_id = ?");
            return $stmt->execute([$customerId, $productId]);
        } catch (PDOException $e) {
            error_log("Remove from Cart Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Clear entire cart
     */
    public function clearCart(int $customerId): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM cart WHERE customer_id = ?");
            return $stmt->execute([$customerId]);
        } catch (PDOException $e) {
            error_log("Clear Cart Error: " . $e->getMessage());
            return false;
        }
    }

    // ========================================================================
    // ORDER MANAGEMENT
    // ========================================================================

    /**
     * Place an order from cart
     */
    public function placeOrder(int $customerId, array $orderData): int|false
    {
        try {
            $this->db->beginTransaction();

            // Get cart items
            $cartItems = $this->getCartItems($customerId);
            if (empty($cartItems)) {
                throw new Exception("Cart is empty");
            }

            // Calculate total
            $total = 0;
            foreach ($cartItems as $item) {
                $total += $item['price'] * $item['quantity'];
            }

            // Create order
            $stmt = $this->db->prepare("
                INSERT INTO orders (customer_id, total_amount, shipping_address, shipping_phone, payment_method, notes) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $customerId,
                $total,
                $orderData['shipping_address'],
                $orderData['shipping_phone'],
                $orderData['payment_method'] ?? 'cod',
                $orderData['notes'] ?? null
            ]);
            $orderId = $this->db->lastInsertId();

            // Add order items
            $stmt = $this->db->prepare("
                INSERT INTO order_items (order_id, product_id, seller_id, quantity, price) 
                VALUES (?, ?, ?, ?, ?)
            ");

            foreach ($cartItems as $item) {
                // Get seller_id from product
                $productStmt = $this->db->prepare("SELECT seller_id FROM products WHERE id = ?");
                $productStmt->execute([$item['product_id']]);
                $sellerId = $productStmt->fetchColumn();

                $stmt->execute([
                    $orderId,
                    $item['product_id'],
                    $sellerId,
                    $item['quantity'],
                    $item['price']
                ]);

                // Update stock
                $updateStock = $this->db->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
                $updateStock->execute([$item['quantity'], $item['product_id']]);
            }

            // Create delivery record
            $stmt = $this->db->prepare("
                INSERT INTO deliveries (order_id, delivery_address, contact_phone) VALUES (?, ?, ?)
            ");
            $stmt->execute([$orderId, $orderData['shipping_address'], $orderData['shipping_phone']]);

            // Clear cart
            $this->clearCart($customerId);

            $this->db->commit();
            return $orderId;

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Place Order Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get customer orders
     */
    public function getOrders(int $customerId): array
    {
        $stmt = $this->db->prepare("
            SELECT o.*, 
                   (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count
            FROM orders o 
            WHERE o.customer_id = ? 
            ORDER BY o.order_date DESC
        ");
        $stmt->execute([$customerId]);
        return $stmt->fetchAll();
    }

    /**
     * Get order details
     */
    public function getOrderDetails(int $orderId, int $customerId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM orders WHERE id = ? AND customer_id = ?");
        $stmt->execute([$orderId, $customerId]);
        $order = $stmt->fetch();

        if (!$order)
            return null;

        // Get order items
        $stmt = $this->db->prepare("
            SELECT oi.*, p.name, p.image 
            FROM order_items oi 
            JOIN products p ON oi.product_id = p.id 
            WHERE oi.order_id = ?
        ");
        $stmt->execute([$orderId]);
        $order['items'] = $stmt->fetchAll();

        // Get delivery info
        $stmt = $this->db->prepare("SELECT * FROM deliveries WHERE order_id = ?");
        $stmt->execute([$orderId]);
        $order['delivery'] = $stmt->fetch();

        return $order;
    }

    // ========================================================================
    // SUPPORT TICKETS
    // ========================================================================

    /**
     * Create support ticket
     */
    public function createTicket(int $customerId, array $data): int|false
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO support_tickets (customer_id, order_id, subject, message, priority) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $customerId,
                $data['order_id'] ?: null,
                $data['subject'],
                $data['message'],
                $data['priority'] ?? 'medium'
            ]);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Create Ticket Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get customer tickets
     */
    public function getTickets(int $customerId): array
    {
        $stmt = $this->db->prepare("
            SELECT t.*, 
                   (SELECT COUNT(*) FROM ticket_responses WHERE ticket_id = t.id) as response_count
            FROM support_tickets t 
            WHERE t.customer_id = ? 
            ORDER BY t.created_at DESC
        ");
        $stmt->execute([$customerId]);
        return $stmt->fetchAll();
    }

    /**
     * Get ticket details with responses
     */
    public function getTicketDetails(int $ticketId, int $customerId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM support_tickets WHERE id = ? AND customer_id = ?");
        $stmt->execute([$ticketId, $customerId]);
        $ticket = $stmt->fetch();

        if (!$ticket)
            return null;

        // Get responses
        $stmt = $this->db->prepare("
            SELECT tr.*, u.full_name, u.role 
            FROM ticket_responses tr 
            JOIN users u ON tr.user_id = u.id 
            WHERE tr.ticket_id = ? AND tr.is_internal = 0
            ORDER BY tr.created_at ASC
        ");
        $stmt->execute([$ticketId]);
        $ticket['responses'] = $stmt->fetchAll();

        return $ticket;
    }

    /**
     * Add response to ticket
     */
    public function addTicketResponse(int $ticketId, int $userId, string $message): bool
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO ticket_responses (ticket_id, user_id, message) VALUES (?, ?, ?)
            ");
            return $stmt->execute([$ticketId, $userId, $message]);
        } catch (PDOException $e) {
            error_log("Add Ticket Response Error: " . $e->getMessage());
            return false;
        }
    }
}
?>