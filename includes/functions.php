<?php
// File này chứa các hàm bổ sung cho ShopPink
// KHÔNG include config.php ở đây để tránh vòng lặp
// KHÔNG định nghĩa lại các hàm đã có trong config.php

// Hàm kết nối database - sử dụng kết nối đã có từ config.php
function connectDB() {
    global $conn;
    return $conn;
}

// Hàm kiểm tra đăng nhập - sử dụng hàm từ config.php
function isLoggedIn() {
    return is_logged_in();
}

// Hàm lấy thông tin user
function getUser($user_id) {
    $conn = connectDB();
    $user_id = (int)$user_id;
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Hàm lấy danh mục sản phẩm
function getCategories($parent_id = null) {
    $conn = connectDB();
    
    if ($parent_id === null) {
        $sql = "SELECT * FROM categories WHERE parent_id IS NULL";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
    } else {
        $sql = "SELECT * FROM categories WHERE parent_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $parent_id);
        $stmt->execute();
    }
    
    $result = $stmt->get_result();
    $categories = [];
    
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
    
    return $categories;
}

// Hàm lấy sản phẩm nổi bật
function getFeaturedProducts($limit = 8) {
    $conn = connectDB();
    $sql = "SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.status = 1 
            ORDER BY p.created_at DESC 
            LIMIT ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    
    return $products;
}

// Hàm lấy sản phẩm bán chạy
function getBestsellerProducts($limit = 8) {
    $conn = connectDB();
    $sql = "SELECT p.*, c.name as category_name, SUM(od.quantity) as total_sold
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            LEFT JOIN order_details od ON p.id = od.product_id
            GROUP BY p.id
            ORDER BY total_sold DESC 
            LIMIT ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    
    return $products;
}

// Hàm lấy sản phẩm đã xem
function getRecentlyViewedProducts($limit = 6) {
    if (!isset($_SESSION['recently_viewed']) || empty($_SESSION['recently_viewed'])) {
        return [];
    }
    
    $conn = connectDB();
    $product_ids = array_slice($_SESSION['recently_viewed'], -$limit);
    $ids = implode(',', $product_ids);
    $sql = "SELECT * FROM products WHERE id IN ($ids) ORDER BY FIELD(id, $ids)";
    $result = $conn->query($sql);
    
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    
    return $products;
}

// Hàm lấy danh sách thương hiệu
function getBrands() {
    $conn = connectDB();
    $sql = "SELECT * FROM brands ORDER BY name ASC";
    $result = $conn->query($sql);
    
    $brands = [];
    while ($row = $result->fetch_assoc()) {
        $brands[] = $row;
    }
    
    return $brands;
}

// Hàm định dạng giá tiền - sử dụng hàm từ config.php
function formatPrice($price) {
    return format_price($price);
}

// Hàm kiểm tra email hợp lệ - sử dụng hàm từ config.php
function isValidEmail($email) {
    return is_valid_email($email);
}

// Hàm tạo slug từ chuỗi - sử dụng hàm từ config.php
function createSlug($str) {
    return create_slug($str);
}

// Hàm tạo thông báo flash - sử dụng hàm từ config.php
function setFlashMessage($type, $message) {
    set_flash_message($type, $message);
}

// Hàm hiển thị thông báo flash - sử dụng hàm từ config.php
function displayFlashMessage() {
    display_flash_message();
}

// Các hàm khác giữ nguyên nhưng sử dụng prepared statements để bảo mật
function getProductsByCategory($category_id, $limit = null) {
    $conn = connectDB();
    
    $sql = "SELECT p.*, c.name as category_name 
            FROM products p 
            JOIN categories c ON p.category_id = c.id 
            WHERE p.category_id = ?";
    
    if ($limit) {
        $sql .= " LIMIT ?";
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $category_id);
    
    if ($limit) {
        $stmt->bind_param("i", $limit);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $products = [];
    
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    
    return $products;
}

function getProduct($product_id) {
    $conn = connectDB();
    
    $sql = "SELECT p.*, c.name as category_name 
            FROM products p 
            JOIN categories c ON p.category_id = c.id 
            WHERE p.id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

// Thêm các hàm còn lại từ file functions.php của bạn, nhưng sửa lại để:
// 1. Không include config.php
// 2. Không định nghĩa lại các hàm đã có trong config.php
// 3. Sử dụng prepared statements thay vì truy vấn trực tiếp
?>