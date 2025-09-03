<?php
require_once BASE_PATH . '/config/database.php';

class User {
    private $conn;
    
    public function __construct() {
        $database = DatabaseConfig::getInstance();
        $this->conn = $database->getConnection();
    }
    
    // Đăng nhập
    public function login($username, $password) {
        try {
            $query = "SELECT * FROM users WHERE username = :username OR email = :username";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                // Cập nhật lần đăng nhập cuối cùng
                $query = "UPDATE users SET last_login = NOW() WHERE id = :id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':id', $user['id']);
                $stmt->execute();
                
                return $user;
            }
            
            return false;
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
    
    // Tạo người dùng mới
    public function create($data) {
        try {
            $query = "INSERT INTO users (username, email, password, full_name, phone, address, role)
                      VALUES (:username, :email, :password, :full_name, :phone, :address, :role)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $data['username']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':password', $data['password']);
            $stmt->bindParam(':full_name', $data['full_name']);
            $stmt->bindParam(':phone', $data['phone']);
            $stmt->bindParam(':address', $data['address']);
            $stmt->bindParam(':role', $data['role']);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
    
    // Lấy thông tin người dùng theo ID
    public function getById($id) {
        try {
            $query = "SELECT * FROM users WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
    
    // Lấy thông tin người dùng theo username
    public function getByUsername($username) {
        try {
            $query = "SELECT * FROM users WHERE username = :username";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
    
    // Lấy thông tin người dùng theo email
    public function getByEmail($email) {
        try {
            $query = "SELECT * FROM users WHERE email = :email";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
    
    // Cập nhật thông tin người dùng
    public function update($id, $data) {
        try {
            $query = "UPDATE users SET 
                      full_name = :full_name, 
                      phone = :phone, 
                      address = :address 
                      WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':full_name', $data['full_name']);
            $stmt->bindParam(':phone', $data['phone']);
            $stmt->bindParam(':address', $data['address']);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
    
    // Đổi mật khẩu
    public function changePassword($id, $newPassword) {
        try {
            $query = "UPDATE users SET password = :password WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
    
    // Lấy danh sách địa chỉ của người dùng
    public function getAddresses($userId) {
        try {
            $query = "SELECT * FROM user_addresses WHERE user_id = :user_id ORDER BY is_default DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
            return [];
        }
    }
    
    // Lấy địa chỉ mặc định của người dùng
    public function getDefaultAddress($userId) {
        try {
            $query = "SELECT * FROM user_addresses WHERE user_id = :user_id AND is_default = 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
    
    // Thêm địa chỉ mới
    public function addAddress($data) {
        try {
            // Nếu là địa chỉ mặc định, cập nhật tất cả các địa chỉ khác thành không mặc định
            if ($data['is_default']) {
                $query = "UPDATE user_addresses SET is_default = 0 WHERE user_id = :user_id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':user_id', $data['user_id'], PDO::PARAM_INT);
                $stmt->execute();
            }
            
            $query = "INSERT INTO user_addresses (user_id, recipient_name, phone, address, province, district, ward, is_default)
                      VALUES (:user_id, :recipient_name, :phone, :address, :province, :district, :ward, :is_default)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $data['user_id'], PDO::PARAM_INT);
            $stmt->bindParam(':recipient_name', $data['recipient_name']);
            $stmt->bindParam(':phone', $data['phone']);
            $stmt->bindParam(':address', $data['address']);
            $stmt->bindParam(':province', $data['province']);
            $stmt->bindParam(':district', $data['district']);
            $stmt->bindParam(':ward', $data['ward']);
            $stmt->bindParam(':is_default', $data['is_default'], PDO::PARAM_BOOL);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
    
    // Cập nhật địa chỉ
    public function updateAddress($id, $data) {
        try {
            // Nếu là địa chỉ mặc định, cập nhật tất cả các địa chỉ khác thành không mặc định
            if ($data['is_default']) {
                $query = "UPDATE user_addresses SET is_default = 0 WHERE user_id = :user_id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':user_id', $data['user_id'], PDO::PARAM_INT);
                $stmt->execute();
            }
            
            $query = "UPDATE user_addresses SET 
                      recipient_name = :recipient_name, 
                      phone = :phone, 
                      address = :address, 
                      province = :province, 
                      district = :district, 
                      ward = :ward, 
                      is_default = :is_default 
                      WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':recipient_name', $data['recipient_name']);
            $stmt->bindParam(':phone', $data['phone']);
            $stmt->bindParam(':address', $data['address']);
            $stmt->bindParam(':province', $data['province']);
            $stmt->bindParam(':district', $data['district']);
            $stmt->bindParam(':ward', $data['ward']);
            $stmt->bindParam(':is_default', $data['is_default'], PDO::PARAM_BOOL);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
    
    // Xóa địa chỉ
    public function deleteAddress($id) {
        try {
            $query = "DELETE FROM user_addresses WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
    
    // Đặt địa chỉ mặc định
    public function setDefaultAddress($id, $userId) {
        try {
            // Cập nhật tất cả các địa chỉ thành không mặc định
            $query = "UPDATE user_addresses SET is_default = 0 WHERE user_id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            // Đặt địa chỉ được chọn thành mặc định
            $query = "UPDATE user_addresses SET is_default = 1 WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
    
    // Lấy danh sách yêu thích của người dùng
    public function getWishlist($userId) {
        try {
            $query = "SELECT w.*, p.name, p.price, p.image 
                      FROM user_wishlist w
                      JOIN products p ON w.product_id = p.id
                      WHERE w.user_id = :user_id
                      ORDER BY w.created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
            return [];
        }
    }
    
    // Kiểm tra sản phẩm có trong danh sách yêu thích không
    public function isInWishlist($userId, $productId) {
        try {
            $query = "SELECT COUNT(*) as count FROM user_wishlist 
                      WHERE user_id = :user_id AND product_id = :product_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':product_id', $productId, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] > 0;
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
    
    // Thêm vào danh sách yêu thích
    public function addToWishlist($userId, $productId) {
        try {
            $query = "INSERT INTO user_wishlist (user_id, product_id) 
                      VALUES (:user_id, :product_id)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':product_id', $productId, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
    
    // Xóa khỏi danh sách yêu thích
    public function removeFromWishlist($userId, $productId) {
        try {
            $query = "DELETE FROM user_wishlist 
                      WHERE user_id = :user_id AND product_id = :product_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':product_id', $productId, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
    
    // Lấy danh sách voucher của người dùng
    public function getVouchers($userId) {
        try {
            $query = "SELECT uv.*, v.code, v.name, v.description, v.discount_type, 
                             v.discount_value, v.min_order_value, v.max_discount_amount, 
                             v.start_date, v.end_date, v.status
                      FROM user_vouchers uv
                      JOIN vouchers v ON uv.voucher_id = v.id
                      WHERE uv.user_id = :user_id AND uv.is_used = 0
                      ORDER BY uv.created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
            return [];
        }
    }
    
    // Lấy thông báo của người dùng
    public function getNotifications($userId, $limit = 20) {
        try {
            $query = "SELECT * FROM user_notifications 
                      WHERE user_id = :user_id 
                      ORDER BY created_at DESC 
                      LIMIT :limit";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
            return [];
        }
    }
    
    // Đánh dấu thông báo đã đọc
    public function markNotificationAsRead($notificationId) {
        try {
            $query = "UPDATE user_notifications SET is_read = 1 WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $notificationId, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
    
    // Đánh dấu tất cả thông báo đã đọc
    public function markAllNotificationsAsRead($userId) {
        try {
            $query = "UPDATE user_notifications SET is_read = 1 WHERE user_id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
    
    // Đếm số thông báo chưa đọc
    public function countUnreadNotifications($userId) {
        try {
            $query = "SELECT COUNT(*) as count FROM user_notifications
                      WHERE user_id = :user_id AND is_read = 0";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'];
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
            return 0;
        }
    }
    
    // Thêm sản phẩm vào lịch sử xem
    public function addToViewHistory($userId, $productId) {
        try {
            // Kiểm tra xem sản phẩm đã có trong lịch sử chưa
            $query = "SELECT id FROM user_view_history WHERE user_id = :user_id AND product_id = :product_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':product_id', $productId, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                // Cập nhật thời gian xem
                $query = "UPDATE user_view_history SET viewed_at = NOW() WHERE id = :id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':id', $result['id']);
                $stmt->execute();
            } else {
                // Thêm vào lịch sử
                $query = "INSERT INTO user_view_history (user_id, product_id) VALUES (:user_id, :product_id)";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
                $stmt->bindParam(':product_id', $productId, PDO::PARAM_INT);
                $stmt->execute();
            }
            
            return true;
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
    
    // Lấy lịch sử xem sản phẩm
    public function getViewHistory($userId, $limit = 10) {
        try {
            $query = "SELECT vh.*, p.name, p.price, p.image
                      FROM user_view_history vh
                      JOIN products p ON vh.product_id = p.id
                      WHERE vh.user_id = :user_id
                      ORDER BY vh.viewed_at DESC
                      LIMIT :limit";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
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