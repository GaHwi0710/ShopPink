<?php
session_start();
include('config.php');

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Lấy dữ liệu từ URL và ép kiểu
$action = isset($_GET['action']) ? $_GET['action'] : '';
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Nếu id sản phẩm không hợp lệ thì quay về giỏ hàng
if ($product_id <= 0) {
    header("Location: cart.php");
    exit();
}

// Kiểm tra sản phẩm có tồn tại trong DB không
$product_stmt = $conn->prepare("SELECT id FROM products WHERE id = ?");
$product_stmt->bind_param("i", $product_id);
$product_stmt->execute();
$product_result = $product_stmt->get_result();

if ($product_result->num_rows == 0) {
    // Nếu sản phẩm không tồn tại thì không cho thao tác
    header("Location: cart.php");
    exit();
}

// Khởi tạo giỏ hàng nếu chưa có
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

// Thực hiện hành động
switch ($action) {
    case 'add':
        $_SESSION['cart'][$product_id] = ($_SESSION['cart'][$product_id] ?? 0) + 1;
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
        unset($_SESSION['cart'][$product_id]);
        break;
}

// Quay lại trang giỏ hàng
header("Location: cart.php");
exit();
?>
