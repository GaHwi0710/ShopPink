<?php
// logout.php
// Trang đăng xuất

// Bắt đầu session
session_start();

// Xóa tất cả các biến session
$_SESSION = array();

// Hủy session
session_destroy();

// Chuyển hướng về trang chủ
header('Location: index.php');
exit;
?>