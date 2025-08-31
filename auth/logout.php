<?php
require_once '../includes/autoload.php';

// Xóa remember me token nếu có
if (isset($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];
    
    $delete_stmt = $conn->prepare("DELETE FROM user_tokens WHERE token = ?");
    $delete_stmt->bind_param("s", $token);
    $delete_stmt->execute();
    
    // Xóa cookie
    setcookie('remember_token', '', time() - 3600, '/');
}

// Xóa tất cả session
session_unset();
session_destroy();

// Xóa cart cookie nếu có
if (isset($_COOKIE['cart'])) {
    setcookie('cart', '', time() - 3600, '/');
}

// Chuyển về trang chủ
header("Location: ../index.php");
exit();
?>
