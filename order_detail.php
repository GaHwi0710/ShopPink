<?php
session_start();
include('config.php');

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Lấy order_id và ép kiểu int
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($order_id <= 0) {
    header("Location: user_home.php");
    exit();
}

// Lấy thông tin đơn hàng
$order_stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$order_stmt->bind_param("ii", $order_id, $_SESSION['user_id']);
$order_stmt->execute();
$order = $order_stmt->get_result()->fetch_assoc();

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
    <title>Chi tiết đơn hàng #<?php echo htmlspecialchars($order_id); ?> - ShopPink</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/form.css">
</head>
<body>
    <?php include('includes/header.php'); ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Chi tiết đơn hàng #<?php echo htmlspecialchars($order_id); ?></h1>
        </div>
        
        <div class="order-detail-container">
            <div class="order-info-section">
                <div class="order-status">
                    <h2>Trạng thái đơn hàng</h2>
                    <div class="status-container">
                        <div class="status-step <?php echo $order['status'] == 'pending' ? 'active' : ''; ?>">
                            <div class="step-icon">1</div>
                            <div class="step-text">Đang xử lý</div>
                        </div>
                        <div class="status-step <?php echo in_array($order['status'], ['confirmed','shipping','completed']) ? 'active' : ''; ?>">
                            <div class="step-icon">2</div>
                            <div class="step-text">Đã xác nhận</div>
                        </div>
                        <div class="status-step <?php echo in_array($order['status'], ['shipping','completed']) ? 'active' : ''; ?>">
                            <div class="step-icon">3</div>
                            <div class="step-text">Đang giao hàng</div>
                        </div>
                        <div class="status-step <?php echo $order['status'] == 'completed' ? 'active' : ''; ?>">
                            <div class="step-icon">4</div>
                            <div class="step-text">Hoàn thành</div>
                        </div>
                    </div>
                </div>
                
                <div class="order-info">
                    <h2>Thông tin đơn hàng</h2>
                    <div class="info-row">
                        <span>Mã đơn hàng:</span>
                        <span>#<?php echo htmlspecialchars($order_id); ?></span>
                    </div>
                    <div class="info-row">
                        <span>Ngày đặt:</span>
                        <span><?php echo htmlspecialchars($order['created_at']); ?></span>
                    </div>
                    <div class="info-row">
                        <span>Phương thức thanh toán:</span>
                        <span><?php echo ucfirst(htmlspecialchars($order['payment_method'])); ?></span>
                    </div>
                    <div class="info-row">
                        <span>Tổng tiền:</span>
                        <span class="total-price"><?php echo number_format($order['total'], 0, ',', '.'); ?> VNĐ</span>
                    </div>
                </div>
                
                <div class="shipping-info">
                    <h2>Thông tin giao hàng</h2>
                    <p><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($order['address']); ?></p>
                    <p><strong>Điện thoại:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
                </div>
            </div>
            
            <div class="order-items-section">
                <h2>Sản phẩm</h2>
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
                                <?php echo number_format($item['price'], 0, ',', '.'); ?> VNĐ
                            </div>
                            <div class="item-total">
                                <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?> VNĐ
                            </div>
                        </div>
                    <?php } ?>
                </div>
                
                <div class="order-totals">
                    <div class="total-row">
                        <span>Tạm tính:</span>
                        <span><?php echo number_format($order['total'] - 30000, 0, ',', '.'); ?> VNĐ</span>
                    </div>
                    <div class="total-row">
                        <span>Phí vận chuyển:</span>
                        <span>30,000 VNĐ</span>
                    </div>
                    <div class="total-row grand-total">
                        <span>Tổng cộng:</span>
                        <span><?php echo number_format($order['total'], 0, ',', '.'); ?> VNĐ</span>
                    </div>
                </div>
                
                <div class="order-actions">
                    <a href="user_home.php" class="btn">Tiếp tục mua sắm</a>
                    <?php if ($order['status'] == 'completed') { ?>
                        <a href="review.php?id=<?php echo htmlspecialchars($order_id); ?>" class="btn btn-outline">Đánh giá sản phẩm</a>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
    
    <?php include('includes/footer.php'); ?>
</body>
</html>
