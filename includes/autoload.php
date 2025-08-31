<?php
// Khởi tạo session nếu chưa có
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include các file cần thiết
require_once 'config.php';
require_once 'functions.php';

// Thiết lập timezone
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Thiết lập encoding
ini_set('default_charset', 'UTF-8');

// Helper functions
function format_price($price) {
    return number_format($price, 0, ',', '.') . ' ₫';
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function get_current_user() {
    if (is_logged_in()) {
        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'full_name' => $_SESSION['user_name'] ?? '',
            'role' => $_SESSION['user_role'] ?? 'customer'
        ];
    }
    return null;
}
?>