<?php
session_start();
include('config.php');

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Lấy dữ liệu từ form và ép kiểu
$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
$quantity   = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

// Nếu dữ liệu không hợp lệ thì quay về trang chủ
if ($product_id <= 0 || $quantity <= 0) {
    header("Location: index.php");
    exit();
}

// Kiểm tra sản phẩm có tồn tại không
$product_stmt = $conn->prepare("SELECT id FROM products WHERE id = ?");
$product_stmt->bind_param("i", $product_id);
$product_stmt->execute();
$product_result = $product_stmt->get_result();

if ($product_result->num_rows == 0) {
    // Nếu sản phẩm không tồn tại thì quay về trang chủ
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
