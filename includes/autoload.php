<?php
// Khởi tạo session
session_start();

// Include các file cần thiết
require_once 'config.php';
require_once 'functions.php';

// Thiết lập timezone
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Thiết lập encoding
mb_internal_encoding('UTF-8');

// Xử lý lỗi (chỉ hiển thị trong development)
if (defined('DEVELOPMENT_MODE') && DEVELOPMENT_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Hàm helper để format giá
if (!function_exists('format_price')) {
    function format_price($price) {
        return number_format($price, 0, ',', '.') . ' ₫';
    }
}

// Hàm helper để kiểm tra đăng nhập
if (!function_exists('is_logged_in')) {
    function is_logged_in() {
        return isset($_SESSION['user_id']);
    }
}

// Hàm helper để lấy thông tin user hiện tại
if (!function_exists('get_current_user')) {
    function get_current_user() {
        global $conn;
        if (is_logged_in()) {
            $user_id = $_SESSION['user_id'];
            $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            return $stmt->get_result()->fetch_assoc();
        }
        return null;
    }
}
?>