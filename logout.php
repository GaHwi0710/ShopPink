<?php
// Bắt đầu session
session_start();

// Kiểm tra nếu có dữ liệu POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Include functions
    require_once 'includes/functions.php';
    
    // Lấy dữ liệu từ form
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    // Kiểm tra product_id hợp lệ
    if ($product_id <= 0) {
        $_SESSION['message'] = [
            'type' => 'error',
            'text' => 'Sản phẩm không hợp lệ!'
        ];
        header('Location: cart.php');
        exit();
    }
    
    // Kiểm tra giỏ hàng có tồn tại
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        $_SESSION['message'] = [
            'type' => 'error',
            'text' => 'Giỏ hàng của bạn đang trống!'
        ];
        header('Location: cart.php');
        exit();
    }
    
    // Kiểm tra sản phẩm có trong giỏ hàng không
    if (!isset($_SESSION['cart'][$product_id])) {
        $_SESSION['message'] = [
            'type' => 'error',
            'text' => 'Sản phẩm không có trong giỏ hàng!'
        ];
        header('Location: cart.php');
        exit();
    }
    
    // Xử lý theo action
    switch ($action) {
        case 'update':
            // Cập nhật số lượng
            if ($quantity <= 0) {
                // Xóa sản phẩm khỏi giỏ hàng
                unset($_SESSION['cart'][$product_id]);
                $_SESSION['message'] = [
                    'type' => 'success',
                    'text' => 'Đã xóa sản phẩm khỏi giỏ hàng!'
                ];
            } else {
                // Kiểm tra số lượng tồn kho
                $conn = connectDB();
                $sql = "SELECT stock FROM products WHERE id = $product_id";
                $result = mysqli_query($conn, $sql);
                $product = mysqli_fetch_assoc($result);
                
                if ($product && $quantity <= $product['stock']) {
                    $_SESSION['cart'][$product_id] = $quantity;
                    $_SESSION['message'] = [
                        'type' => 'success',
                        'text' => 'Đã cập nhật số lượng sản phẩm!'
                    ];
                } else {
                    $_SESSION['message'] = [
                        'type' => 'error',
                        'text' => 'Số lượng sản phẩm không đủ trong kho!'
                    ];
                }
            }
            break;
            
        case 'remove':
            // Xóa sản phẩm khỏi giỏ hàng
            unset($_SESSION['cart'][$product_id]);
            $_SESSION['message'] = [
                'type' => 'success',
                'text' => 'Đã xóa sản phẩm khỏi giỏ hàng!'
            ];
            break;
            
        case 'increase':
            // Tăng số lượng
            $new_quantity = $_SESSION['cart'][$product_id] + 1;
            
            // Kiểm tra số lượng tồn kho
            $conn = connectDB();
            $sql = "SELECT stock FROM products WHERE id = $product_id";
            $result = mysqli_query($conn, $sql);
            $product = mysqli_fetch_assoc($result);
            
            if ($product && $new_quantity <= $product['stock']) {
                $_SESSION['cart'][$product_id] = $new_quantity;
                $_SESSION['message'] = [
                    'type' => 'success',
                    'text' => 'Đã tăng số lượng sản phẩm!'
                ];
            } else {
                $_SESSION['message'] = [
                    'type' => 'error',
                    'text' => 'Số lượng sản phẩm không đủ trong kho!'
                ];
            }
            break;
            
        case 'decrease':
            // Giảm số lượng
            $new_quantity = $_SESSION['cart'][$product_id] - 1;
            
            if ($new_quantity <= 0) {
                // Xóa sản phẩm khỏi giỏ hàng
                unset($_SESSION['cart'][$product_id]);
                $_SESSION['message'] = [
                    'type' => 'success',
                    'text' => 'Đã xóa sản phẩm khỏi giỏ hàng!'
                ];
            } else {
                $_SESSION['cart'][$product_id] = $new_quantity;
                $_SESSION['message'] = [
                    'type' => 'success',
                    'text' => 'Đã giảm số lượng sản phẩm!'
                ];
            }
            break;
            
        default:
            $_SESSION['message'] = [
                'type' => 'error',
                'text' => 'Hành động không hợp lệ!'
            ];
            break;
    }
    
    // Chuyển hướng về trang giỏ hàng
    header('Location: cart.php');
    exit();
} else {
    // Nếu không phải POST, chuyển hướng về trang giỏ hàng
    header('Location: cart.php');
    exit();
}
?>