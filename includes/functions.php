<?php
// Các hàm hỗ trợ cho ShopPink

// Hàm kết nối database
function connectDB() {
    include('config.php');
    return $conn;
}

// Hàm kiểm tra đăng nhập
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Hàm chuyển hướng
function redirect($url) {
    header("Location: $url");
    exit();
}

// Hàm lấy thông tin user
function getUser($user_id) {
    $conn = connectDB();
    $sql = "SELECT * FROM users WHERE id = $user_id";
    $result = mysqli_query($conn, $sql);
    return mysqli_fetch_assoc($result);
}

// Hàm lấy danh mục sản phẩm
function getCategories($parent_id = null) {
    $conn = connectDB();
    
    if ($parent_id === null) {
        $sql = "SELECT * FROM categories WHERE parent_id IS NULL";
    } else {
        $sql = "SELECT * FROM categories WHERE parent_id = $parent_id";
    }
    
    $result = mysqli_query($conn, $sql);
    $categories = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $categories[] = $row;
    }
    
    return $categories;
}

// Hàm lấy sản phẩm theo danh mục
function getProductsByCategory($category_id, $limit = null) {
    $conn = connectDB();
    
    $sql = "SELECT p.*, c.name as category_name 
            FROM products p 
            JOIN categories c ON p.category_id = c.id 
            WHERE p.category_id = $category_id";
    
    if ($limit) {
        $sql .= " LIMIT $limit";
    }
    
    $result = mysqli_query($conn, $sql);
    $products = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }
    
    return $products;
}

// Hàm lấy sản phẩm nổi bật
function getFeaturedProducts($limit = 8) {
    $conn = connectDB();
    
    $sql = "SELECT p.*, c.name as category_name 
            FROM products p 
            JOIN categories c ON p.category_id = c.id 
            ORDER BY p.created_at DESC 
            LIMIT $limit";
    
    $result = mysqli_query($conn, $sql);
    $products = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }
    
    return $products;
}

// Hàm lấy sản phẩm bán chạy
function getBestsellerProducts($limit = 8) {
    $conn = connectDB();
    
    $sql = "SELECT p.*, c.name as category_name, SUM(od.quantity) as total_sold 
            FROM products p 
            JOIN categories c ON p.category_id = c.id 
            JOIN order_details od ON p.id = od.product_id 
            GROUP BY p.id 
            ORDER BY total_sold DESC 
            LIMIT $limit";
    
    $result = mysqli_query($conn, $sql);
    $products = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }
    
    return $products;
}

// Hàm lấy chi tiết sản phẩm
function getProduct($product_id) {
    $conn = connectDB();
    
    $sql = "SELECT p.*, c.name as category_name 
            FROM products p 
            JOIN categories c ON p.category_id = c.id 
            WHERE p.id = $product_id";
    
    $result = mysqli_query($conn, $sql);
    return mysqli_fetch_assoc($result);
}

// Hàm lấy sản phẩm liên quan
function getRelatedProducts($product_id, $category_id, $limit = 4) {
    $conn = connectDB();
    
    $sql = "SELECT * FROM products 
            WHERE category_id = $category_id AND id != $product_id 
            LIMIT $limit";
    
    $result = mysqli_query($conn, $sql);
    $products = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }
    
    return $products;
}

// Hàm tìm kiếm sản phẩm
function searchProducts($query) {
    $conn = connectDB();
    
    $sql = "SELECT p.*, c.name as category_name 
            FROM products p 
            JOIN categories c ON p.category_id = c.id 
            WHERE p.name LIKE '%$query%' OR p.description LIKE '%$query%'";
    
    $result = mysqli_query($conn, $sql);
    $products = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }
    
    return $products;
}

// Hàm thêm sản phẩm vào giỏ hàng
function addToCart($product_id, $quantity = 1) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }
}

// Hàm cập nhật giỏ hàng
function updateCart($product_id, $quantity) {
    if ($quantity <= 0) {
        unset($_SESSION['cart'][$product_id]);
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }
}

// Hàm xóa sản phẩm khỏi giỏ hàng
function removeFromCart($product_id) {
    unset($_SESSION['cart'][$product_id]);
}

// Hàm lấy thông tin giỏ hàng
function getCartItems() {
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return [];
    }
    
    $conn = connectDB();
    $product_ids = array_keys($_SESSION['cart']);
    $ids = implode(',', $product_ids);
    
    $sql = "SELECT * FROM products WHERE id IN ($ids)";
    $result = mysqli_query($conn, $sql);
    
    $cart_items = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $product_id = $row['id'];
        $quantity = $_SESSION['cart'][$product_id];
        $subtotal = $row['price'] * $quantity;
        
        $cart_items[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'price' => $row['price'],
            'image' => $row['image'],
            'quantity' => $quantity,
            'subtotal' => $subtotal
        ];
    }
    
    return $cart_items;
}

// Hàm tính tổng tiền giỏ hàng
function getCartTotal() {
    $cart_items = getCartItems();
    $total = 0;
    
    foreach ($cart_items as $item) {
        $total += $item['subtotal'];
    }
    
    return $total;
}

// Hàm tạo đơn hàng
function createOrder($user_id, $address, $phone, $payment_method) {
    $conn = connectDB();
    
    // Tính tổng tiền
    $cart_items = getCartItems();
    $total = getCartTotal();
    
    // Thêm phí vận chuyển
    $shipping_fee = 30000;
    $total += $shipping_fee;
    
    // Thêm đơn hàng vào database
    $sql = "INSERT INTO orders (user_id, total, address, phone, payment_method, status) 
            VALUES ($user_id, $total, '$address', '$phone', '$payment_method', 'pending')";
    
    if (mysqli_query($conn, $sql)) {
        $order_id = mysqli_insert_id($conn);
        
        // Thêm chi tiết đơn hàng
        foreach ($cart_items as $item) {
            $product_id = $item['id'];
            $quantity = $item['quantity'];
            $price = $item['price'];
            
            $detail_sql = "INSERT INTO order_details (order_id, product_id, quantity, price) 
                          VALUES ($order_id, $product_id, $quantity, $price)";
            mysqli_query($conn, $detail_sql);
        }
        
        // Xóa giỏ hàng
        unset($_SESSION['cart']);
        
        return $order_id;
    }
    
    return false;
}

// Hàm lấy thông tin đơn hàng
function getOrder($order_id, $user_id = null) {
    $conn = connectDB();
    
    $sql = "SELECT * FROM orders WHERE id = $order_id";
    
    if ($user_id) {
        $sql .= " AND user_id = $user_id";
    }
    
    $result = mysqli_query($conn, $sql);
    return mysqli_fetch_assoc($result);
}

// Hàm lấy chi tiết đơn hàng
function getOrderDetails($order_id) {
    $conn = connectDB();
    
    $sql = "SELECT od.*, p.name, p.image 
            FROM order_details od 
            JOIN products p ON od.product_id = p.id 
            WHERE od.order_id = $order_id";
    
    $result = mysqli_query($conn, $sql);
    $details = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $details[] = $row;
    }
    
    return $details;
}

// Hàm lấy danh sách đơn hàng của user
function getUserOrders($user_id) {
    $conn = connectDB();
    
    $sql = "SELECT * FROM orders WHERE user_id = $user_id ORDER BY created_at DESC";
    $result = mysqli_query($conn, $sql);
    $orders = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $orders[] = $row;
    }
    
    return $orders;
}

// Hàm định dạng giá tiền
function formatPrice($price) {
    return number_format($price, 0, ',', '.') . ' VNĐ';
}

// Hàm kiểm tra email hợp lệ
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Hàm tạo slug từ chuỗi
function createSlug($str) {
    $str = trim(mb_strtolower($str));
    $str = preg_replace('/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/', 'a', $str);
    $str = preg_replace('/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/', 'e', $str);
    $str = preg_replace('/(ì|í|ị|ỉ|ĩ)/', 'i', $str);
    $str = preg_replace('/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/', 'o', $str);
    $str = preg_replace('/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/', 'u', $str);
    $str = preg_replace('/(ỳ|ý|ỵ|ỷ|ỹ)/', 'y', $str);
    $str = preg_replace('/(đ)/', 'd', $str);
    $str = preg_replace('/[^a-z0-9-\s]/', '', $str);
    $str = preg_replace('/([\s]+)/', '-', $str);
    return $str;
}

// Hàm cắt chuỗi
function truncate($str, $length = 100, $append = '...') {
    if (strlen($str) <= $length) {
        return $str;
    }
    
    return substr($str, 0, $length) . $append;
}

// Hàm upload file
function uploadFile($file, $target_dir) {
    $target_file = $target_dir . basename($file["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
    // Kiểm tra file có phải là hình ảnh không
    $check = getimagesize($file["tmp_name"]);
    if ($check !== false) {
        $uploadOk = 1;
    } else {
        $uploadOk = 0;
    }
    
    // Kiểm tra kích thước file
    if ($file["size"] > 5000000) { // 5MB
        $uploadOk = 0;
    }
    
    // Kiểm tra định dạng file
    if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
        $uploadOk = 0;
    }
    
    // Kiểm tra nếu uploadOk = 0
    if ($uploadOk == 0) {
        return false;
    } else {
        if (move_uploaded_file($file["tmp_name"], $target_file)) {
            return basename($file["name"]);
        } else {
            return false;
        }
    }
}

// Hàm gửi email
function sendEmail($to, $subject, $message, $headers = '') {
    if (empty($headers)) {
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: ShopPink <no-reply@shoppink.com>" . "\r\n";
    }
    
    return mail($to, $subject, $message, $headers);
}

// Hàm tạo mã ngẫu nhiên
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    
    return $randomString;
}

// Hàm phân trang
function paginate($total_records, $per_page = 10, $current_page = 1) {
    $total_pages = ceil($total_records / $per_page);
    
    $pagination = [
        'total_records' => $total_records,
        'per_page' => $per_page,
        'total_pages' => $total_pages,
        'current_page' => $current_page,
        'offset' => ($current_page - 1) * $per_page
    ];
    
    return $pagination;
}

// Hàm hiển thị phân trang
function renderPagination($pagination, $url_pattern) {
    $output = '<div class="pagination">';
    
    // Previous button
    if ($pagination['current_page'] > 1) {
        $prev_page = $pagination['current_page'] - 1;
        $output .= '<a href="' . sprintf($url_pattern, $prev_page) . '" class="prev">Trước</a>';
    }
    
    // Page numbers
    for ($i = 1; $i <= $pagination['total_pages']; $i++) {
        $active = ($i == $pagination['current_page']) ? 'active' : '';
        $output .= '<a href="' . sprintf($url_pattern, $i) . '" class="' . $active . '">' . $i . '</a>';
    }
    
    // Next button
    if ($pagination['current_page'] < $pagination['total_pages']) {
        $next_page = $pagination['current_page'] + 1;
        $output .= '<a href="' . sprintf($url_pattern, $next_page) . '" class="next">Sau</a>';
    }
    
    $output .= '</div>';
    
    return $output;
}
?>