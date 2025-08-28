<?php
// Include autoload để tự động nạp các file cần thiết
require_once 'includes/autoload.php';
// ...

// Kiểm tra đăng nhập (nếu cần)
// Nếu không yêu cầu đăng nhập, có thể bỏ qua phần này

// Lấy dữ liệu từ form và ép kiểu
$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
$quantity   = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

// Nếu dữ liệu không hợp lệ thì quay về trang chủ
if ($product_id <= 0 || $quantity <= 0) {
    // Nếu là AJAX request, trả về JSON
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Dữ liệu không hợp lệ!',
            'cart_count' => isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0
        ]);
        exit();
    }
    
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => 'Dữ liệu không hợp lệ!'
    ];
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
}

// Kiểm tra sản phẩm có tồn tại không
$product_stmt = $conn->prepare("SELECT id, name, price, stock FROM products WHERE id = ? AND status = 1");
$product_stmt->bind_param("i", $product_id);
$product_stmt->execute();
$product_result = $product_stmt->get_result();

if ($product_result->num_rows == 0) {
    // Nếu là AJAX request, trả về JSON
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Sản phẩm không tồn tại!',
            'cart_count' => isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0
        ]);
        exit();
    }
    
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => 'Sản phẩm không tồn tại!'
    ];
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
}

$product = $product_result->fetch_assoc();

// Kiểm tra số lượng tồn kho
if ($quantity > $product['stock']) {
    // Nếu là AJAX request, trả về JSON
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Số lượng sản phẩm không đủ trong kho!',
            'cart_count' => isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0
        ]);
        exit();
    }
    
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => 'Số lượng sản phẩm không đủ trong kho!'
    ];
    header("Location: " . $_SERVER['HTTP_REFERER']);
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

// Kiểm tra lại tổng số lượng không vượt quá tồn kho
if ($_SESSION['cart'][$product_id] > $product['stock']) {
    $_SESSION['cart'][$product_id] = $product['stock'];
    
    // Nếu là AJAX request, trả về JSON
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Đã thêm sản phẩm vào giỏ hàng! (Tối đa ' . $product['stock'] . ' sản phẩm)',
            'cart_count' => array_sum($_SESSION['cart']),
            'product_name' => $product['name']
        ]);
        exit();
    }
    
    $_SESSION['flash_message'] = [
        'type' => 'warning',
        'message' => 'Đã thêm sản phẩm vào giỏ hàng! (Tối đa ' . $product['stock'] . ' sản phẩm)'
    ];
} else {
    // Nếu là AJAX request, trả về JSON
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Đã thêm sản phẩm vào giỏ hàng thành công!',
            'cart_count' => array_sum($_SESSION['cart']),
            'product_name' => $product['name']
        ]);
        exit();
    }
    
    $_SESSION['flash_message'] = [
        'type' => 'success',
        'message' => 'Đã thêm sản phẩm vào giỏ hàng thành công!'
    ];
}

// Lưu sản phẩm vào danh sách đã xem
if (!isset($_SESSION['recently_viewed'])) {
    $_SESSION['recently_viewed'] = array();
}

// Xóa sản phẩm nếu đã tồn tại
if (($key = array_search($product_id, $_SESSION['recently_viewed'])) !== false) {
    unset($_SESSION['recently_viewed'][$key]);
}

// Thêm sản phẩm vào đầu danh sách
array_unshift($_SESSION['recently_viewed'], $product_id);

// Giới hạn số lượng sản phẩm đã xem
if (count($_SESSION['recently_viewed']) > 20) {
    array_pop($_SESSION['recently_viewed']);
}

// Nếu là AJAX request, đã xử lý ở trên
// Nếu không phải AJAX, chuyển hướng đến trang giỏ hàng hoặc trang trước đó
$redirect = isset($_POST['redirect']) ? $_POST['redirect'] : $_SERVER['HTTP_REFERER'];
if (empty($redirect) || strpos($redirect, 'add_to_cart.php') !== false) {
    $redirect = 'cart.php';
}

header("Location: " . $redirect);
exit();
?>