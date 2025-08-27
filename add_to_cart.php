<?php
session_start();
include('config.php');

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$product_id = $_POST['product_id'];
$quantity = $_POST['quantity'];

// Kiểm tra sản phẩm có tồn tại không
$product_query = "SELECT * FROM products WHERE id = $product_id";
$product_result = mysqli_query($conn, $product_query);

if (mysqli_num_rows($product_result) == 0) {
    header("Location: index.php");
    exit();
}

// Khởi tạo giỏ hàng nếu chưa có
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

// Thêm sản phẩm vào giỏ hàng
if (isset($_SESSION['cart'][$product_id])) {
    $_SESSION['cart'][$product_id] += $quantity;
} else {
    $_SESSION['cart'][$product_id] = $quantity;
}

// Chuyển hướng đến trang giỏ hàng
header("Location: cart.php");
exit();
?>