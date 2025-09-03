<?php
require_once BASE_PATH . '/config/database.php';

class Order {
    private $conn;
    
    public function __construct() {
        $database = DatabaseConfig::getInstance();
        $this->conn = $database->getConnection();
    }
    
    // Tạo đơn hàng mới
    public function create($data) {
        try {
            $this->conn->beginTransaction();
            
            // Tạo đơn hàng
            $query = "INSERT INTO orders (customer_id, total_amount, status, shipping_address, payment_method)
                      VALUES (:customer_id, :total_amount, 'pending', :shipping_address, :payment_method)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':customer_id', $data['customer_id'], PDO::PARAM_INT);
            $stmt->bindParam(':total_amount', $data['total_amount']);
            $stmt->bindParam(':shipping_address', $data['shipping_address']);
            $stmt->bindParam(':payment_method', $data['payment_method']);
            $stmt->execute();
            
            $orderId = $this->conn->lastInsertId();
            
            // Thêm các sản phẩm vào đơn hàng
            foreach ($data['items'] as $item) {
                $query = "INSERT INTO order_items (order_id, product_id, quantity, price)
                          VALUES (:order_id, :product_id, :quantity, :price)";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':order_id', $orderId, PDO::PARAM_INT);
                $stmt->bindParam(':product_id', $item['product_id'], PDO::PARAM_INT);
                $stmt->bindParam(':quantity', $item['quantity'], PDO::PARAM_INT);
                $stmt->bindParam(':price', $item['price']);
                $stmt->execute();
                
                // Cập nhật số lượng tồn kho
                $query = "UPDATE products SET stock = stock - :quantity WHERE id = :product_id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':quantity', $item['quantity'], PDO::PARAM_INT);
                $stmt->bindParam(':product_id', $item['product_id'], PDO::PARAM_INT);
                $stmt->execute();
            }
            
            $this->conn->commit();
            return $orderId;
        } catch(PDOException $e) {
            $this->conn->rollback();
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
    
    // Lấy đơn hàng theo ID
    public function getById($id) {
        try {
            $query = "SELECT * FROM orders WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
    
    // Lấy đơn hàng của khách hàng
    public function getByCustomer($customerId) {
        try {
            $query = "SELECT * FROM orders WHERE customer_id = :customer_id ORDER BY created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':customer_id', $customerId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
            return [];
        }
    }
    
    // Lấy đơn hàng của người bán
    public function getBySeller($sellerId) {
        try {
            $query = "SELECT o.*, u.full_name as customer_name
                      FROM orders o
                      JOIN order_items oi ON o.id = oi.order_id
                      JOIN products p ON oi.product_id = p.id
                      JOIN users u ON o.customer_id = u.id
                      WHERE p.seller_id = :seller_id
                      GROUP BY o.id
                      ORDER BY o.created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':seller_id', $sellerId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
            return [];
        }
    }
    
    // Lấy chi tiết đơn hàng
    public function getOrderItems($orderId) {
        try {
            $query = "SELECT oi.*, p.name as product_name, p.image as product_image
                      FROM order_items oi
                      JOIN products p ON oi.product_id = p.id
                      WHERE oi.order_id = :order_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':order_id', $orderId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
            return [];
        }
    }
    
    // Cập nhật trạng thái đơn hàng
    public function updateStatus($orderId, $status, $sellerId = null) {
        try {
            $query = "UPDATE orders SET status = :status WHERE id = :id";
            if ($sellerId) {
                // Nếu là người bán, kiểm tra xem đơn hàng có thuộc về người bán này không
                $query .= " AND id IN (
                    SELECT order_id FROM order_items
                    JOIN products ON order_items.product_id = products.id
                    WHERE products.seller_id = :seller_id
                )";
            }
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':id', $orderId, PDO::PARAM_INT);
            if ($sellerId) {
                $stmt->bindParam(':seller_id', $sellerId, PDO::PARAM_INT);
            }
            
            return $stmt->execute();
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
    
    // Lấy doanh thu theo người bán và khoảng thời gian
    public function getRevenueBySeller($sellerId, $period = 'month') {
        try {
            $query = "";
            
            switch ($period) {
                case 'day':
                    $query = "SELECT DATE(created_at) as date, SUM(total_amount) as revenue
                              FROM orders o
                              JOIN order_items oi ON o.id = oi.order_id
                              JOIN products p ON oi.product_id = p.id
                              WHERE p.seller_id = :seller_id AND o.status IN ('processing', 'shipped', 'delivered')
                              AND DATE(created_at) = CURDATE()
                              GROUP BY DATE(created_at)
                              ORDER BY date";
                    break;
                    
                case 'week':
                    $query = "SELECT DATE(created_at) as date, SUM(total_amount) as revenue
                              FROM orders o
                              JOIN order_items oi ON o.id = oi.order_id
                              JOIN products p ON oi.product_id = p.id
                              WHERE p.seller_id = :seller_id AND o.status IN ('processing', 'shipped', 'delivered')
                              AND YEARWEEK(created_at) = YEARWEEK(CURDATE())
                              GROUP BY DATE(created_at)
                              ORDER BY date";
                    break;
                    
                case 'month':
                    $query = "SELECT DATE(created_at) as date, SUM(total_amount) as revenue
                              FROM orders o
                              JOIN order_items oi ON o.id = oi.order_id
                              JOIN products p ON oi.product_id = p.id
                              WHERE p.seller_id = :seller_id AND o.status IN ('processing', 'shipped', 'delivered')
                              AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())
                              GROUP BY DATE(created_at)
                              ORDER BY date";
                    break;
                    
                case 'year':
                    $query = "SELECT MONTH(created_at) as month, SUM(total_amount) as revenue
                              FROM orders o
                              JOIN order_items oi ON o.id = oi.order_id
                              JOIN products p ON oi.product_id = p.id
                              WHERE p.seller_id = :seller_id AND o.status IN ('processing', 'shipped', 'delivered')
                              AND YEAR(created_at) = YEAR(CURDATE())
                              GROUP BY MONTH(created_at)
                              ORDER BY month";
                    break;
                    
                default:
                    $query = "SELECT DATE(created_at) as date, SUM(total_amount) as revenue
                              FROM orders o
                              JOIN order_items oi ON o.id = oi.order_id
                              JOIN products p ON oi.product_id = p.id
                              WHERE p.seller_id = :seller_id AND o.status IN ('processing', 'shipped', 'delivered')
                              AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())
                              GROUP BY DATE(created_at)
                              ORDER BY date";
            }
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':seller_id', $sellerId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
            return [];
        }
    }
}
?>