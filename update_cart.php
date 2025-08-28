<?php
// Include autoload để tự động nạp các file cần thiết
require_once 'includes/autoload.php';
// ...
include('includes/functions.php');

// Lấy action và product_id
$action = $_GET['action'] ?? '';
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Nếu id không hợp lệ → về cart
if ($product_id <= 0) {
    setFlashMessage('error', 'ID sản phẩm không hợp lệ!');
    header("Location: cart.php");
    exit;
}

// Lấy thông tin sản phẩm từ DB
$conn = connectDB();
$product_stmt = $conn->prepare("SELECT id, name, price, image, stock FROM products WHERE id = ? AND status = 1");
$product_stmt->bind_param("i", $product_id);
$product_stmt->execute();
$product_result = $product_stmt->get_result();
$product = $product_result->fetch_assoc();

if (!$product) {
    setFlashMessage('error', 'Sản phẩm không tồn tại!');
    header("Location: cart.php");
    exit;
}

// Khởi tạo giỏ hàng nếu chưa có
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Thực hiện hành động
switch ($action) {
    case 'add':
        if (isset($_SESSION['cart'][$product_id])) {
            // Kiểm tra số lượng tồn kho
            if ($_SESSION['cart'][$product_id]['quantity'] < $product['stock']) {
                $_SESSION['cart'][$product_id]['quantity']++;
                setFlashMessage('success', 'Đã tăng số lượng sản phẩm!');
            } else {
                setFlashMessage('error', 'Số lượng sản phẩm không đủ trong kho!');
            }
        } else {
            $_SESSION['cart'][$product_id] = [
                'id' => $product['id'],
                'name' => $product['name'],
                'price' => $product['price'],
                'image' => $product['image'],
                'quantity' => 1
            ];
            setFlashMessage('success', 'Đã thêm sản phẩm vào giỏ hàng!');
        }
        break;
        
    case 'increase':
        if (isset($_SESSION['cart'][$product_id])) {
            // Kiểm tra số lượng tồn kho
            if ($_SESSION['cart'][$product_id]['quantity'] < $product['stock']) {
                $_SESSION['cart'][$product_id]['quantity']++;
                setFlashMessage('success', 'Đã tăng số lượng sản phẩm!');
            } else {
                setFlashMessage('error', 'Số lượng sản phẩm không đủ trong kho!');
            }
        }
        break;
        
    case 'decrease':
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]['quantity']--;
            if ($_SESSION['cart'][$product_id]['quantity'] <= 0) {
                unset($_SESSION['cart'][$product_id]);
                setFlashMessage('success', 'Đã xóa sản phẩm khỏi giỏ hàng!');
            } else {
                setFlashMessage('success', 'Đã giảm số lượng sản phẩm!');
            }
        }
        break;
        
    case 'remove':
        if (isset($_SESSION['cart'][$product_id])) {
            unset($_SESSION['cart'][$product_id]);
            setFlashMessage('success', 'Đã xóa sản phẩm khỏi giỏ hàng!');
        }
        break;
        
    case 'update':
        $quantity = isset($_GET['quantity']) ? intval($_GET['quantity']) : 1;
        
        if ($quantity <= 0) {
            unset($_SESSION['cart'][$product_id]);
            setFlashMessage('success', 'Đã xóa sản phẩm khỏi giỏ hàng!');
        } elseif ($quantity > $product['stock']) {
            $_SESSION['cart'][$product_id]['quantity'] = $product['stock'];
            setFlashMessage('warning', 'Chỉ có thể thêm tối đa ' . $product['stock'] . ' sản phẩm!');
        } else {
            $_SESSION['cart'][$product_id]['quantity'] = $quantity;
            setFlashMessage('success', 'Đã cập nhật số lượng sản phẩm!');
        }
        break;
        
    default:
        setFlashMessage('error', 'Hành động không hợp lệ!');
        break;
}

// Quay lại giỏ hàng
header("Location: cart.php");
exit;
?>