<?php
/**
 * ============================================================================
 * DELIVERY MODEL
 * ============================================================================
 * Model for Delivery Staff-specific database operations.
 * 
 * MEMBER 4 - DELIVERY ROLE
 * ============================================================================
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/User.php';

class Delivery extends User
{

    /**
     * Get dashboard data for delivery staff
     */
    public function getDashboardData(int $deliveryId): array
    {
        $data = [];

        // Pending deliveries
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM deliveries WHERE delivery_person_id = ? AND status IN ('assigned', 'picked_up', 'in_transit')");
        $stmt->execute([$deliveryId]);
        $data['pending_deliveries'] = $stmt->fetchColumn();

        // Completed today
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM deliveries WHERE delivery_person_id = ? AND status = 'delivered' AND DATE(delivered_date) = CURDATE()");
        $stmt->execute([$deliveryId]);
        $data['completed_today'] = $stmt->fetchColumn();

        // Total completed
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM deliveries WHERE delivery_person_id = ? AND status = 'delivered'");
        $stmt->execute([$deliveryId]);
        $data['total_completed'] = $stmt->fetchColumn();

        // Available for pickup (unassigned)
        $stmt = $this->db->query("SELECT COUNT(*) FROM deliveries WHERE delivery_person_id IS NULL AND status = 'pending'");
        $data['available_pickups'] = $stmt->fetchColumn();

        // Current assigned deliveries
        $stmt = $this->db->prepare("
            SELECT d.*, o.shipping_address, o.shipping_phone, o.total_amount, u.full_name as customer_name
            FROM deliveries d
            JOIN orders o ON d.order_id = o.id
            JOIN users u ON o.customer_id = u.id
            WHERE d.delivery_person_id = ? AND d.status IN ('assigned', 'picked_up', 'in_transit')
            ORDER BY d.id DESC
        ");
        $stmt->execute([$deliveryId]);
        $data['current_deliveries'] = $stmt->fetchAll();

        return $data;
    }

    /**
     * Get assigned deliveries
     */
    public function getAssignedDeliveries(int $deliveryId): array
    {
        $stmt = $this->db->prepare("
            SELECT d.*, o.shipping_address, o.shipping_phone, o.total_amount, o.payment_method, o.payment_status,
                   u.full_name as customer_name, u.email as customer_email
            FROM deliveries d
            JOIN orders o ON d.order_id = o.id
            JOIN users u ON o.customer_id = u.id
            WHERE d.delivery_person_id = ? AND d.status IN ('assigned', 'picked_up', 'in_transit')
            ORDER BY d.id DESC
        ");
        $stmt->execute([$deliveryId]);
        return $stmt->fetchAll();
    }

    /**
     * Get available deliveries for pickup
     */
    public function getAvailableDeliveries(): array
    {
        $stmt = $this->db->query("
            SELECT d.*, o.shipping_address, o.shipping_phone, o.total_amount, u.full_name as customer_name
            FROM deliveries d
            JOIN orders o ON d.order_id = o.id
            JOIN users u ON o.customer_id = u.id
            WHERE d.delivery_person_id IS NULL AND d.status = 'pending'
            ORDER BY d.id ASC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Accept delivery
     */
    public function acceptDelivery(int $deliveryId, int $deliveryPersonId): bool
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE deliveries SET delivery_person_id = ?, status = 'assigned' 
                WHERE id = ? AND delivery_person_id IS NULL AND status = 'pending'
            ");
            $result = $stmt->execute([$deliveryPersonId, $deliveryId]);

            if ($stmt->rowCount() > 0) {
                // Update order status
                $this->updateOrderStatus($deliveryId, 'processing');
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Accept Delivery Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update delivery status
     */
    public function updateDeliveryStatus(int $deliveryId, int $deliveryPersonId, string $status, string $notes = ''): bool
    {
        try {
            $sql = "UPDATE deliveries SET status = ?, notes = ?";
            $params = [$status, $notes];

            if ($status === 'delivered') {
                $sql .= ", delivered_date = NOW()";
            }

            $sql .= " WHERE id = ? AND delivery_person_id = ?";
            $params[] = $deliveryId;
            $params[] = $deliveryPersonId;

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($params);

            if ($stmt->rowCount() > 0) {
                // Update order status based on delivery status
                $orderStatus = match ($status) {
                    'picked_up', 'in_transit' => 'shipped',
                    'delivered' => 'delivered',
                    default => null
                };
                if ($orderStatus) {
                    $this->updateOrderStatus($deliveryId, $orderStatus);
                }
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Update Delivery Status Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update order status
     */
    private function updateOrderStatus(int $deliveryId, string $status): bool
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE orders SET status = ? 
                WHERE id = (SELECT order_id FROM deliveries WHERE id = ?)
            ");
            return $stmt->execute([$status, $deliveryId]);
        } catch (PDOException $e) {
            error_log("Update Order Status Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get delivery history
     */
    public function getDeliveryHistory(int $deliveryId): array
    {
        $stmt = $this->db->prepare("
            SELECT d.*, o.shipping_address, o.total_amount, u.full_name as customer_name
            FROM deliveries d
            JOIN orders o ON d.order_id = o.id
            JOIN users u ON o.customer_id = u.id
            WHERE d.delivery_person_id = ? AND d.status = 'delivered'
            ORDER BY d.delivered_date DESC
        ");
        $stmt->execute([$deliveryId]);
        return $stmt->fetchAll();
    }

    /**
     * Get delivery statistics
     */
    public function getStats(int $deliveryId): array
    {
        $stats = [];

        // This week
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM deliveries 
            WHERE delivery_person_id = ? AND status = 'delivered' 
            AND YEARWEEK(delivered_date) = YEARWEEK(CURDATE())
        ");
        $stmt->execute([$deliveryId]);
        $stats['this_week'] = $stmt->fetchColumn();

        // This month
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM deliveries 
            WHERE delivery_person_id = ? AND status = 'delivered' 
            AND MONTH(delivered_date) = MONTH(CURDATE()) AND YEAR(delivered_date) = YEAR(CURDATE())
        ");
        $stmt->execute([$deliveryId]);
        $stats['this_month'] = $stmt->fetchColumn();

        return $stats;
    }
}
?>