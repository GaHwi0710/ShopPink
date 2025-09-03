<?php
require_once BASE_PATH . '/config/database.php';

class Product {
    private $conn;
    
    public function __construct() {
        $database = DatabaseConfig::getInstance();
        $this->conn = $database->getConnection();
    }
    
    // Lấy tất cả sản phẩm
    public function getAll($limit = null, $offset = 0) {
        try {
            $query = "SELECT p.*, u.full_name as seller_name, u.phone as seller_phone
                      FROM products p
                      JOIN users u ON p.seller_id = u.id
                      WHERE p.status = 'active'
                      ORDER BY p.created_at DESC";
            
            if ($limit) {
                $query .= " LIMIT :limit OFFSET :offset";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
                $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            } else {
                $stmt = $this->conn->prepare($query);
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
            return [];
        }
    }
    
    // Tìm kiếm sản phẩm
    public function search($keyword, $limit = null, $offset = 0) {
        try {
            $query = "SELECT p.*, u.full_name as seller_name, u.phone as seller_phone
                      FROM products p
                      JOIN users u ON p.seller_id = u.id
                      WHERE p.status = 'active' AND (p.name LIKE :keyword OR p.description LIKE :keyword)
                      ORDER BY p.created_at DESC";
            
            if ($limit) {
                $query .= " LIMIT :limit OFFSET :offset";
                $stmt = $this->conn->prepare($query);
                $keywordParam = "%$keyword%";
                $stmt->bindParam(':keyword', $keywordParam);
                $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
                $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            } else {
                $stmt = $this->conn->prepare($query);
                $keywordParam = "%$keyword%";
                $stmt->bindParam(':keyword', $keywordParam);
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
            return [];
        }
    }
    
    // Lấy sản phẩm theo ID
    public function getById($id) {
        try {
            $query = "SELECT p.*, u.full_name as seller_name, u.phone as seller_phone
                      FROM products p
                      JOIN users u ON p.seller_id = u.id
                      WHERE p.id = :id AND p.status = 'active'";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
    
    // Lấy sản phẩm theo người bán
    public function getBySeller($sellerId, $limit = null, $offset = 0) {
        try {
            $query = "SELECT * FROM products WHERE seller_id = :seller_id ORDER BY created_at DESC";
            
            if ($limit) {
                $query .= " LIMIT :limit OFFSET :offset";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':seller_id', $sellerId, PDO::PARAM_INT);
                $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
                $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            } else {
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':seller_id', $sellerId, PDO::PARAM_INT);
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
            return [];
        }
    }
    
    // Thêm sản phẩm mới
    public function create($data) {
        try {
            $query = "INSERT INTO products (seller_id, name, description, price, image, stock, status)
                      VALUES (:seller_id, :name, :description, :price, :image, :stock, 'active')";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':seller_id', $data['seller_id']);
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':description', $data['description']);
            $stmt->bindParam(':price', $data['price']);
            $stmt->bindParam(':image', $data['image']);
            $stmt->bindParam(':stock', $data['stock']);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
    
    // Cập nhật sản phẩm
    public function update($id, $data) {
        try {
            $query = "UPDATE products SET
                      name = :name,
                      description = :description,
                      price = :price,
                      image = :image,
                      stock = :stock
                      WHERE id = :id AND seller_id = :seller_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':description', $data['description']);
            $stmt->bindParam(':price', $data['price']);
            $stmt->bindParam(':image', $data['image']);
            $stmt->bindParam(':stock', $data['stock']);
            $stmt->bindParam(':seller_id', $data['seller_id'], PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
    
    // Xóa sản phẩm (đánh dấu là inactive)
    public function delete($id, $sellerId) {
        try {
            $query = "UPDATE products SET status = 'inactive' WHERE id = :id AND seller_id = :seller_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':seller_id', $sellerId, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
    
    // Lấy sản phẩm bán chạy theo người bán
    public function getTopSellingBySeller($sellerId, $limit = 5) {
        try {
            $query = "SELECT p.*, SUM(oi.quantity) as total_sold
                      FROM products p
                      LEFT JOIN order_items oi ON p.id = oi.product_id
                      LEFT JOIN orders o ON oi.order_id = o.id
                      WHERE p.seller_id = :seller_id AND o.status IN ('processing', 'shipped', 'delivered')
                      GROUP BY p.id
                      ORDER BY total_sold DESC
                      LIMIT :limit";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':seller_id', $sellerId, PDO::PARAM_INT);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
            return [];
        }
    }
}
?>