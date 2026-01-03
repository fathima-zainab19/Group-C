<?php
/**
 * ============================================================================
 * CUSTOMER CONTROLLER
 * ============================================================================
 * Controller for Customer role handling products, cart, orders, and support.
 * 
 * MEMBER 2 - CUSTOMER ROLE
 * ============================================================================
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Customer.php';

class CustomerController
{
    private $customerModel;
    private $customerId;

    public function __construct()
    {
        requireRole('customer');
        $this->customerModel = new Customer();
        $this->customerId = getCurrentUserId();
    }

    /**
     * Get dashboard data
     */
    public function dashboard(): array
    {
        return $this->customerModel->getDashboardData($this->customerId);
    }

    /**
     * Get products with filters
     */
    public function getProducts(array $filters = [], int $page = 1): array
    {
        return $this->customerModel->getProducts($filters, $page);
    }

    /**
     * Get single product
     */
    public function getProduct(int $id): ?array
    {
        return $this->customerModel->getProductById($id);
    }

    /**
     * Get product reviews
     */
    public function getProductReviews(int $productId): array
    {
        return $this->customerModel->getProductReviews($productId);
    }

    /**
     * Get all categories
     */
    public function getCategories(): array
    {
        return $this->customerModel->getCategories();
    }

    // ========================================================================
    // CART METHODS
    // ========================================================================

    /**
     * Get cart items
     */
    public function getCart(): array
    {
        return $this->customerModel->getCartItems($this->customerId);
    }

    /**
     * Get cart total
     */
    public function getCartTotal(): float
    {
        return $this->customerModel->getCartTotal($this->customerId);
    }

    /**
     * Get cart count
     */
    public function getCartCount(): int
    {
        $items = $this->getCart();
        return array_sum(array_column($items, 'quantity'));
    }

    /**
     * Handle cart actions
     */
    public function handleCartAction(): array
    {
        if (!isPost()) {
            return ['success' => false, 'message' => 'Invalid request'];
        }

        $action = $_POST['action'] ?? '';
        $productId = (int) ($_POST['product_id'] ?? 0);
        $quantity = (int) ($_POST['quantity'] ?? 1);

        switch ($action) {
            case 'add':
                $result = $this->customerModel->addToCart($this->customerId, $productId, $quantity);
                $message = $result ? 'Added to cart!' : 'Failed to add to cart';
                break;

            case 'update':
                $result = $this->customerModel->updateCartItem($this->customerId, $productId, $quantity);
                $message = $result ? 'Cart updated!' : 'Failed to update cart';
                break;

            case 'remove':
                $result = $this->customerModel->removeFromCart($this->customerId, $productId);
                $message = $result ? 'Item removed!' : 'Failed to remove item';
                break;

            case 'clear':
                $result = $this->customerModel->clearCart($this->customerId);
                $message = $result ? 'Cart cleared!' : 'Failed to clear cart';
                break;

            default:
                return ['success' => false, 'message' => 'Invalid action'];
        }

        return [
            'success' => $result,
            'message' => $message,
            'cartCount' => $this->getCartCount()
        ];
    }

    // ========================================================================
    // ORDER METHODS
    // ========================================================================

    /**
     * Place order
     */
    public function placeOrder(): array
    {
        if (!isPost()) {
            return ['success' => false, 'message' => 'Invalid request'];
        }

        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            return ['success' => false, 'message' => 'Invalid security token'];
        }

        $orderData = [
            'shipping_address' => sanitizeInput($_POST['shipping_address'] ?? ''),
            'shipping_phone' => sanitizeInput($_POST['shipping_phone'] ?? ''),
            'payment_method' => sanitizeInput($_POST['payment_method'] ?? 'cod'),
            'notes' => sanitizeInput($_POST['notes'] ?? '')
        ];

        // Validate
        if (empty($orderData['shipping_address'])) {
            return ['success' => false, 'message' => 'Shipping address is required'];
        }
        if (empty($orderData['shipping_phone'])) {
            return ['success' => false, 'message' => 'Phone number is required'];
        }

        $orderId = $this->customerModel->placeOrder($this->customerId, $orderData);

        if ($orderId) {
            return ['success' => true, 'message' => 'Order placed successfully!', 'order_id' => $orderId];
        }
        return ['success' => false, 'message' => 'Failed to place order. Please try again.'];
    }

    /**
     * Get customer orders
     */
    public function getOrders(): array
    {
        return $this->customerModel->getOrders($this->customerId);
    }

    /**
     * Get order details
     */
    public function getOrderDetails(int $orderId): ?array
    {
        return $this->customerModel->getOrderDetails($orderId, $this->customerId);
    }

    // ========================================================================
    // SUPPORT METHODS
    // ========================================================================

    /**
     * Handle support ticket actions
     */
    public function handleTicketAction(): array
    {
        if (!isPost()) {
            return ['success' => false, 'message' => 'Invalid request'];
        }

        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            return ['success' => false, 'message' => 'Invalid security token'];
        }

        $action = $_POST['action'] ?? 'create';

        if ($action === 'create') {
            $data = [
                'subject' => sanitizeInput($_POST['subject'] ?? ''),
                'message' => sanitizeInput($_POST['message'] ?? ''),
                'order_id' => (int) ($_POST['order_id'] ?? 0) ?: null,
                'priority' => sanitizeInput($_POST['priority'] ?? 'medium')
            ];

            if (empty($data['subject']) || empty($data['message'])) {
                return ['success' => false, 'message' => 'Subject and message are required'];
            }

            $ticketId = $this->customerModel->createTicket($this->customerId, $data);
            if ($ticketId) {
                return ['success' => true, 'message' => 'Ticket created successfully!', 'ticket_id' => $ticketId];
            }
            return ['success' => false, 'message' => 'Failed to create ticket'];
        }

        if ($action === 'reply') {
            $ticketId = (int) ($_POST['ticket_id'] ?? 0);
            $message = sanitizeInput($_POST['message'] ?? '');

            if (!$ticketId || empty($message)) {
                return ['success' => false, 'message' => 'Message is required'];
            }

            $result = $this->customerModel->addTicketResponse($ticketId, $this->customerId, $message);
            if ($result) {
                return ['success' => true, 'message' => 'Reply sent!'];
            }
            return ['success' => false, 'message' => 'Failed to send reply'];
        }

        return ['success' => false, 'message' => 'Invalid action'];
    }

    /**
     * Get customer tickets
     */
    public function getTickets(): array
    {
        return $this->customerModel->getTickets($this->customerId);
    }

    /**
     * Get ticket details
     */
    public function getTicketDetails(int $ticketId): ?array
    {
        return $this->customerModel->getTicketDetails($ticketId, $this->customerId);
    }
}
?>