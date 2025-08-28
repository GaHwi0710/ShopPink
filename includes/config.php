<?php
// Cấu hình kết nối database
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_DATABASE', 'shoppink');

// Kết nối với MySQLi hướng đối tượng
try {
    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
    
    // Kiểm tra kết nối
    if ($conn->connect_error) {
        throw new Exception("❌ Lỗi kết nối Database: " . $conn->connect_error);
    }
    
    // Thiết lập charset để hỗ trợ tiếng Việt
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    // Hiển thị thông báo lỗi
    die("❌ Hệ thống đang gặp sự cố. Vui lòng thử lại sau!");
}

// Cấu hình hệ thống
define('SITE_NAME', 'ShopPink');
define('SITE_URL', 'http://localhost/ShopPink/');
define('SITE_EMAIL', 'info@shoppink.com');
define('ADMIN_EMAIL', 'admin@shoppink.com');

// Cấu hình thời gian
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Cấu hình giỏ hàng
define('CART_COOKIE_NAME', 'shoppink_cart');
define('CART_COOKIE_EXPIRE', time() + (86400 * 30)); // 30 days

// Cấu hình upload
define('UPLOAD_PATH', 'assets/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'gif']);

// Hàm định dạng giá tiền
function format_price($price) {
    return number_format($price, 0, ',', '.') . 'đ';
}

// Hàm chuyển hướng trang
function redirect($url) {
    // Kiểm tra xem URL đã có http/https chưa
    if (strpos($url, 'http://') === false && strpos($url, 'https://') === false) {
        // Thêm SITE_URL nếu là URL tương đối
        $url = SITE_URL . $url;
    }
    header("Location: $url");
    exit();
}

// Hàm kiểm tra đăng nhập
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Hàm lấy thông tin người dùng
function get_user_info($user_id) {
    global $conn;
    try {
        $user_id = (int)$user_id;
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ? AND status = 1");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        return null;
    } catch (Exception $e) {
        error_log($e->getMessage());
        return null;
    }
}

// Hàm tạo thông báo
function set_flash_message($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

// Hàm hiển thị thông báo
function display_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $type = $_SESSION['flash_message']['type'];
        $message = $_SESSION['flash_message']['message'];
        $class = '';
        switch ($type) {
            case 'success':
                $class = 'alert-success';
                $icon = 'fa-check-circle';
                break;
            case 'error':
                $class = 'alert-error';
                $icon = 'fa-exclamation-circle';
                break;
            case 'warning':
                $class = 'alert-warning';
                $icon = 'fa-exclamation-triangle';
                break;
            case 'info':
                $class = 'alert-info';
                $icon = 'fa-info-circle';
                break;
        }
        echo '<div class="alert ' . $class . ' active">
            <i class="fas ' . $icon . '"></i>
            <span>' . $message . '</span>
        </div>';
        // Xóa thông báo sau khi hiển thị
        unset($_SESSION['flash_message']);
    }
}

// Hàm bảo mật dữ liệu đầu vào
function clean_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $conn->real_escape_string($data);
}

// Hàm tạo slug từ chuỗi
function create_slug($string) {
    $slug = strtolower($string);
    $slug = preg_replace('/[^a-z0-9-\s]/', '', $slug);
    $slug = preg_replace('/[\s-]+/', '-', $slug);
    $slug = preg_replace('/-+$/', '', $slug);
    return $slug;
}

// Hàm kiểm tra email hợp lệ
function is_valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Hàm tạo mật khẩu ngẫu nhiên
function generate_random_password($length = 8) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[rand(0, strlen($chars) - 1)];
    }
    return $password;
}

// Hàm gửi email (cần cấu hình server email)
function send_email($to, $subject, $message) {
    $headers = "From: " . SITE_EMAIL . "\r\n";
    $headers .= "Reply-To: " . SITE_EMAIL . "\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    return mail($to, $subject, $message, $headers);
}

// Hàm ghi log lỗi
function log_error($message) {
    $log_file = 'logs/error_' . date('Y-m-d') . '.log';
    $log_message = date('Y-m-d H:i:s') . ' - ' . $message . "\n";
    // Tạo thư mục logs nếu chưa tồn tại
    if (!file_exists('logs')) {
        mkdir('logs', 0777, true);
    }
    file_put_contents($log_file, $log_message, FILE_APPEND);
}

// Hàm lấy địa chỉ IP của client
function get_client_ip() {
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if(isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}

// Hàm tạo token CSRF
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Hàm kiểm tra token CSRF
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>