<?php
session_start();
include('config.php');

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Lấy thông tin giỏ hàng từ session
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : array();
$cart_items = array();
$total = 0;

if (!empty($cart)) {
    $placeholders = implode(',', array_fill(0, count($cart), '?'));
    $types = str_repeat('i', count($cart)); // tất cả là INT
    $product_ids = array_keys($cart);

    $stmt = $conn->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
    $stmt->bind_param($types, ...$product_ids);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $quantity = intval($cart[$row['id']]);
        $subtotal = $row['price'] * $quantity;
        $total += $subtotal;

        $cart_items[] = array(
            'id' => $row['id'],
            'name' => $row['name'],
            'price' => $row['price'],
            'image' => $row['image'],
            'quantity' => $quantity,
            'subtotal' => $subtotal
        );
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ hàng - ShopPink</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/form.css">
</head>
<body>
    <?php include('includes/header.php'); ?>
    
    <div class="container">
        <h1>Giỏ hàng của bạn</h1>
        
        <?php if (empty($cart_items)) { ?>
            <div class="empty-cart">
                <img src="assets/images/empty-cart.png" alt="Giỏ hàng trống">
                <p>Giỏ hàng của bạn đang trống</p>
                <a href="index.php" class="btn">Tiếp tục mua sắm</a>
            </div>
        <?php } else { ?>
            <div class="cart-container">
                <div class="cart-items">
                    <table>
                        <thead>
                            <tr>
                                <th>Sản phẩm</th>
                                <th>Giá</th>
                                <th>Số lượng</th>
                                <th>Thành tiền</th>
                                <th>Xóa</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cart_items as $item) { ?>
                                <tr>
                                    <td class="product-info">
                                        <img src="assets/images/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                        <div>
                                            <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                        </div>
                                    </td>
                                    <td><?php echo number_format($item['price'], 0, ',', '.'); ?> VNĐ</td>
                                    <td>
                                        <div class="quantity-control">
                                            <a href="update_cart.php?action=decrease&id=<?php echo $item['id']; ?>">-</a>
                                            <span><?php echo $item['quantity']; ?></span>
                                            <a href="update_cart.php?action=increase&id=<?php echo $item['id']; ?>">+</a>
                                        </div>
                                    </td>
                                    <td><?php echo number_format($item['subtotal'], 0, ',', '.'); ?> VNĐ</td>
                                    <td>
                                        <a href="update_cart.php?action=remove&id=<?php echo $item['id']; ?>" class="remove-btn">
                                            <img src="assets/images/delete-icon.png" alt="Xóa">
                                        </a>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="cart-summary">
                    <h2>Tóm tắt đơn hàng</h2>
                    <div class="summary-row">
                        <span>Tạm tính:</span>
                        <span><?php echo number_format($total, 0, ',', '.'); ?> VNĐ</span>
                    </div>
                    <div class="summary-row">
                        <span>Phí vận chuyển:</span>
                        <span>30,000 VNĐ</span>
                    </div>
                    <div class="summary-row total">
                        <span>Tổng cộng:</span>
                        <span><?php echo number_format($total + 30000, 0, ',', '.'); ?> VNĐ</span>
                    </div>
                    <a href="checkout.php" class="btn btn-primary btn-block">Thanh toán</a>
                    <a href="index.php" class="btn btn-outline btn-block">Tiếp tục mua sắm</a>
                </div>
            </div>
        <?php } ?>
    </div>
    
    <?php include('includes/footer.php'); ?>
</body>
</html>
