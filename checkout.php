<?php
session_start();
include('config.php');

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Kiểm tra giỏ hàng
if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit();
}

$user_id = intval($_SESSION['user_id']);

// Lấy thông tin user
$user_stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();

if (!$user) {
    header("Location: logout.php"); // user không tồn tại
    exit();
}

$cart = $_SESSION['cart'];
$product_ids = array_keys($cart);
$placeholders = implode(',', array_fill(0, count($product_ids), '?'));
$types = str_repeat('i', count($product_ids));

// Query sản phẩm một lần duy nhất
$product_stmt = $conn->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
$product_stmt->bind_param($types, ...$product_ids);
$product_stmt->execute();
$products_result = $product_stmt->get_result();

$products = [];
$subtotal = 0;
while ($row = $products_result->fetch_assoc()) {
    $quantity = intval($cart[$row['id']]);
    $item_total = $row['price'] * $quantity;
    $subtotal += $item_total;

    $products[] = [
        'id' => $row['id'],
        'name' => $row['name'],
        'image' => $row['image'],
        'price' => $row['price'],
        'quantity' => $quantity,
        'total' => $item_total
    ];
}

// Xử lý đặt hàng
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $address = trim($_POST['address']);
    $phone = trim($_POST['phone']);
    $payment_method = $_POST['payment_method'] ?? 'cod';

    if (empty($address) || empty($phone)) {
        $error = "Vui lòng nhập đầy đủ thông tin.";
    } elseif (!preg_match('/^[0-9]{9,11}$/', $phone)) {
        $error = "Số điện thoại không hợp lệ.";
    } else {
        $shipping_fee = 30000;
        $total = $subtotal + $shipping_fee;

        // Thêm đơn hàng
        $order_stmt = $conn->prepare("INSERT INTO orders (user_id, total, address, phone, payment_method, status) 
                                      VALUES (?, ?, ?, ?, ?, 'pending')");
        $order_stmt->bind_param("idsss", $user_id, $total, $address, $phone, $payment_method);

        if ($order_stmt->execute()) {
            $order_id = $order_stmt->insert_id;

            // Thêm chi tiết đơn hàng
            $detail_stmt = $conn->prepare("INSERT INTO order_details (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            foreach ($products as $item) {
                $detail_stmt->bind_param("iiid", $order_id, $item['id'], $item['quantity'], $item['price']);
                $detail_stmt->execute();
            }

            // Xóa giỏ hàng
            unset($_SESSION['cart']);

            // Chuyển hướng
            header("Location: order_confirmation.php?id=" . $order_id);
            exit();
        } else {
            $error = "Lỗi đặt hàng: " . $conn->error;
        }
    }
}
?>
