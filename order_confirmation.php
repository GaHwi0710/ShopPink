<?php
session_start();
include('config.php');

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$order_id = $_GET['id'];

// Lấy thông tin đơn hàng
$order_query = "SELECT * FROM orders WHERE id = $order_id AND user_id = {$_SESSION['user_id']}";
$order_result = mysqli_query($conn, $order_query);
$order = mysqli_fetch_assoc($order_result);

if (!$order) {
    header("Location: user_home.php");
    exit();
}

// Lấy chi tiết đơn hàng
$details_query = "SELECT od.*, p.name, p.image 
                 FROM order_details od 
                 JOIN products p ON od.product_id = p.id 
                 WHERE od.order_id = $order_id";
$details_result = mysqli_query($conn, $details_query);
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
                        <span>#<?php echo $order_id; ?></span>
                    </div>
                    <div class="info-row">
                        <span>Ngày đặt:</span>
                        <span><?php echo $order['created_at']; ?></span>
                    </div>
                    <div class="info-row">
                        <span>Tổng tiền:</span>
                        <span><?php echo number_format($order['total'], 0, ',', '.'); ?> VNĐ</span>
                    </div>
                    <div class="info-row">
                        <span>Phương thức thanh toán:</span>
                        <span><?php echo ucfirst($order['payment_method']); ?></span>
                    </div>
                    <div class="info-row">
                        <span>Trạng thái:</span>
                        <span class="status pending">Đang xử lý</span>
                    </div>
                </div>
                
                <h3>Địa chỉ giao hàng</h3>
                <div class="shipping-address">
                    <p><?php echo $order['address']; ?></p>
                    <p>Điện thoại: <?php echo $order['phone']; ?></p>
                </div>
                
                <h3>Sản phẩm</h3>
                <div class="order-items">
                    <?php while ($item = mysqli_fetch_assoc($details_result)) { ?>
                        <div class="order-item">
                            <div class="item-info">
                                <img src="assets/images/<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>">
                                <div>
                                    <h4><?php echo $item['name']; ?></h4>
                                    <p>Số lượng: <?php echo $item['quantity']; ?></p>
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