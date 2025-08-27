<?php
session_start();
include('config.php');

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Lấy id đơn hàng, ép kiểu int
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($order_id <= 0) {
    header("Location: user_home.php");
    exit();
}

// Lấy thông tin đơn hàng
$order_stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$order_stmt->bind_param("ii", $order_id, $_SESSION['user_id']);
$order_stmt->execute();
$order_result = $order_stmt->get_result();
$order = $order_result->fetch_assoc();

if (!$order) {
    header("Location: user_home.php");
    exit();
}

// Lấy chi tiết đơn hàng
$details_stmt = $conn->prepare("SELECT od.*, p.name, p.image 
                                FROM order_details od 
                                JOIN products p ON od.product_id = p.id 
                                WHERE od.order_id = ?");
$details_stmt->bind_param("i", $order_id);
$details_stmt->execute();
$details_result = $details_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác nhận đơn hàng - ShopPink</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/form.css">
</head>
<body>
    <?php include('includes/header.php'); ?>
    
    <div class="container">
        <div class="order-confirmation">
            <div class="confirmation-header">
                <img src="assets/images/success-icon.png" alt="Thành công">
                <h1>Đặt hàng thành công!</h1>
                <p>Cảm ơn bạn đã mua hàng tại ShopPink. Đơn hàng của bạn đang được xử lý.</p>
            </div>
            
            <div class="order-details">
                <h2>Thông tin đơn hàng</h2>
                <div class="order-info">
                    <div class="info-row">
                        <span>Mã đơn hàng:</span>
                        <span>#<?php echo htmlspecialchars($order_id); ?></span>
                    </div>
                    <div class="info-row">
                        <span>Ngày đặt:</span>
                        <span><?php echo htmlspecialchars($order['created_at']); ?></span>
                    </div>
                    <div class="info-row">
                        <span>Tổng tiền:</span>
                        <span><?php echo number_format($order['total'], 0, ',', '.'); ?> VNĐ</span>
                    </div>
                    <div class="info-row">
                        <span>Phương thức thanh toán:</span>
                        <span><?php echo ucfirst(htmlspecialchars($order['payment_method'])); ?></span>
                    </div>
                    <div class="info-row">
                        <span>Trạng thái:</span>
                        <span class="status <?php echo htmlspecialchars($order['status']); ?>">
                            <?php echo ucfirst($order['status']); ?>
                        </span>
                    </div>
                </div>
                
                <h3>Địa chỉ giao hàng</h3>
                <div class="shipping-address">
                    <p><?php echo htmlspecialchars($order['address']); ?></p>
                    <p>Điện thoại: <?php echo htmlspecialchars($order['phone']); ?></p>
                </div>
                
                <h3>Sản phẩm</h3>
                <div class="order-items">
                    <?php while ($item = $details_result->fetch_assoc()) { ?>
                        <div class="order-item">
                            <div class="item-info">
                                <img src="assets/images/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                <div>
                                    <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                                    <p>Số lượng: <?php echo intval($item['quantity']); ?></p>
                                </div>
                            </div>
                            <div class="item-price">
                                <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?> VNĐ
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
            
            <div class="confirmation-actions">
                <a href="user_home.php" class="btn">Về trang chủ</a>
                <a href="order_detail.php?id=<?php echo $order_id; ?>" class="btn btn-outline">Xem chi tiết đơn hàng</a>
            </div>
        </div>
    </div>
    
    <?php include('includes/footer.php'); ?>
</body>
</html>
