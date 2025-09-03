<?php
require_once BASE_PATH . '/config/database.php';

class Review {
    private $conn;
    
    public function __construct() {
        $database = DatabaseConfig::getInstance();
        $this->conn = $database->getConnection();
    }
    
    // Tạo đánh giá mới
    public function create($data) {
        try {
            $query = "INSERT INTO reviews (product_id, customer_id, rating, comment)
                      VALUES (:product_id, :customer_id, :rating, :comment)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':product_id', $data['product_id'], PDO::PARAM_INT);
            $stmt->bindParam(':customer_id', $data['customer_id'], PDO::PARAM_INT);
            $stmt->bindParam(':rating', $data['rating'], PDO::PARAM_INT);
            $stmt->bindParam(':comment', $data['comment']);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
    
    // Lấy đánh giá theo sản phẩm
    public function getByProduct($productId) {
        try {
            $query = "SELECT r.*, u.full_name as customer_name
                      FROM reviews r
                      JOIN users u ON r.customer_id = u.id
                      WHERE r.product_id = :product_id
                      ORDER BY r.created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':product_id', $productId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
            return [];
        }
    }
    
    // Lấy đánh giá theo khách hàng
    public function getByCustomer($customerId) {
        try {
            $query = "SELECT r.*, p.name as product_name, p.image as product_image
                      FROM reviews r
                      JOIN products p ON r.product_id = p.id
                      WHERE r.customer_id = :customer_id
                      ORDER BY r.created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':customer_id', $customerId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
            return [];
        }
    }
    
    // Kiểm tra xem khách hàng đã mua sản phẩm chưa
    public function hasPurchased($customerId, $productId) {
        try {
            $query = "SELECT COUNT(*) as count
                      FROM orders o
                      JOIN order_items oi ON o.id = oi.order_id
                      WHERE o.customer_id = :customer_id AND oi.product_id = :product_id
                      AND o.status IN ('processing', 'shipped', 'delivered')";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':customer_id', $customerId, PDO::PARAM_INT);
            $stmt->bindParam(':product_id', $productId, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] > 0;
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
    
    // Kiểm tra xem khách hàng đã đánh giá sản phẩm chưa
    public function hasReviewed($customerId, $productId) {
        try {
            $query = "SELECT COUNT(*) as count
                      FROM reviews
                      WHERE customer_id = :customer_id AND product_id = :product_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':customer_id', $customerId, PDO::PARAM_INT);
            $stmt->bindParam(':product_id', $productId, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] > 0;
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
    
    // Tính điểm đánh giá trung bình của sản phẩm
    public function getAverageRating($productId) {
        try {
            $query = "SELECT AVG(rating) as average_rating, COUNT(*) as total_reviews
                      FROM reviews
                      WHERE product_id = :product_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':product_id', $productId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
            return ['average_rating' => 0, 'total_reviews' => 0];
        }
    }
}
?>