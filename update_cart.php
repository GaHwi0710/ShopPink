<?php
session_start();
include('config.php');

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$action = $_GET['action'];
$product_id = $_GET['id'];

// Khởi tạo giỏ hàng nếu chưa có
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

// Thực hiện hành động
switch ($action) {
    case 'add':
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]++;
        } else {
            $_SESSION['cart'][$product_id] = 1;
        }
        break;
        
    case 'increase':
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]++;
        }
        break;
        
    case 'decrease':
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]--;
            if ($_SESSION['cart'][$product_id] <= 0) {
                unset($_SESSION['cart'][$product_id]);
            }
        }
        break;
        
    case 'remove':
        if (isset($_SESSION['cart'][$product_id])) {
            unset($_SESSION['cart'][$product_id]);
        }
        break;
}

// Quay lại trang giỏ hàng
header("Location: cart.php");
exit();
?>