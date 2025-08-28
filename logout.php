<?php
require_once 'includes/autoload.php';

// Xóa tất cả session
session_unset();
session_destroy();

// Xóa cookie ghi nhớ đăng nhập nếu có
if (isset($_COOKIE['remember_token'])) {
    // Xóa token khỏi database
    $token = $_COOKIE['remember_token'];
    $stmt = $conn->prepare("DELETE FROM user_tokens WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    
    // Xóa cookie
    setcookie('remember_token', '', time() - 3600, '/');
}

// Xóa cookie giỏ hàng nếu có
if (isset($_COOKIE['shoppink_cart'])) {
    setcookie('shoppink_cart', '', time() - 3600, '/');
}

// Chuyển hướng về trang chủ
header("Location: index.php");
exit();
?>