<?php
// review.php
// Trang xác nhận đơn hàng
require_once 'config.php';
// Kiểm tra người dùng đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
// Lấy ID đơn hàng từ URL
$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
if ($order_id <= 0) {
    header('Location: index.php');
    exit;
}
// Lấy thông tin đơn hàng
$stmt = $conn->prepare("
    SELECT o.*, u.name as user_name, u.email as user_email 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    WHERE o.id = ? AND o.user_id = ?
");
$stmt->execute([$order_id, $_SESSION['user_id']]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$order) {
    header('Location: index.php');
    exit;
}
// Lấy chi tiết đơn hàng
$stmt = $conn->prepare("
    SELECT od.*, p.name as product_name, p.image as product_image 
    FROM order_details od 
    JOIN products p ON od.product_id = p.id 
    WHERE od.order_id = ?
");
$stmt->execute([$order_id]);
$order_details = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Kiểm tra người dùng đã đăng nhập chưa
$is_logged_in = isset($_SESSION['user_id']);
$user_role = $is_logged_in ? $_SESSION['user_role'] : '';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác nhận đơn hàng - ShopPink</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">
                <a href="index.php">ShopPink</a>
            </div>
            <nav>
                <ul>
                    <li><a href="index.php">Trang chủ</a></li>
                    <li><a href="products.php">Sản phẩm</a></li>
                    <?php if ($is_logged_in): ?>
                        <?php if ($user_role === 'seller'): ?>
                            <li><a href="seller.php">Quản lý</a></li>
                            <li><a href="report.php">Báo cáo</a></li>
                        <?php endif; ?>
                        <li><a href="cart.php"><i class="fas fa-shopping-cart"></i> Giỏ hàng</a></li>
                        <li><a href="logout.php">Đăng xuất</a></li>
                    <?php else: ?>
                        <li><a href="login.php">Đăng nhập</a></li>
                        <li><a href="register.php">Đăng ký</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
    <main>
        <section class="order-review">
            <div class="container">
                <div class="order-success">
                    <i class="fas fa-check-circle"></i>
                    <h1>Đặt hàng thành công!</h1>
                    <p>Cảm ơn bạn đã mua hàng tại ShopPink. Đơn hàng của bạn đang được xử lý.</p>
                </div>
                <div class="order-details">
                    <h2>Thông tin đơn hàng</h2>
                    <div class="order-info">
                        <div class="info-row">
                            <span>Mã đơn hàng:</span>
                            <span>#<?php echo $order['id']; ?></span>
                        </div>
                        <div class="info-row">
                            <span>Ngày đặt:</span>
                            <span><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></span>
                        </div>
                        <div class="info-row">
                            <span>Trạng thái:</span>
                            <span class="status <?php echo $order['status']; ?>">
                                <?php 
                                switch ($order['status']) {
                                    case 'pending':
                                        echo 'Chờ xử lý';
                                        break;
                                    case 'completed':
                                        echo 'Hoàn thành';
                                        break;
                                    case 'cancelled':
                                        echo 'Đã hủy';
                                        break;
                                    default:
                                        echo $order['status'];
                                }
                                ?>
                            </span>
                        </div>
                        <div class="info-row">
                            <span>Tổng tiền:</span>
                            <span class="total"><?php echo number_format($order['total'], 0, ',', '.'); ?>₫</span>
                        </div>
                    </div>
                    <h3>Sản phẩm đã đặt</h3>
                    <div class="order-items">
                        <?php foreach ($order_details as $item): ?>
                            <div class="order-item">
                                <div class="item-image cart-product-image">
                                    <img src="assets/images/products/<?php echo htmlspecialchars($item['product_image']); ?>" 
                                            alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                                    </div>
                                <div class="item-info">
                                    <h4><?php echo htmlspecialchars($item['product_name']); ?></h4>
                                    <p>Giá: <?php echo number_format($item['price'], 0, ',', '.'); ?>₫</p>
                                    <p>Số lượng: <?php echo $item['quantity']; ?></p>
                                </div>
                                <div class="item-total">
                                    <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?>₫
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="order-actions">
                        <a href="index.php" class="btn btn-primary">Tiếp tục mua sắm</a>
                        <a href="complaint.php?order_id=<?php echo $order['id']; ?>" class="btn">Khiếu nại</a>
                    </div>
                </div>
            </div>
        </section>
    </main>
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>ShopPink</h3>
                    <p>Nơi mua sắm trực tuyến đáng tin cậy với nhiều sản phẩm chất lượng.</p>
                </div>
                <div class="footer-section">
                    <h3>Liên kết nhanh</h3>
                    <ul>
                        <li><a href="index.php">Trang chủ</a></li>
                        <li><a href="products.php">Sản phẩm</a></li>
                        <li><a href="login.php">Đăng nhập</a></li>
                        <li><a href="register.php">Đăng ký</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Liên hệ</h3>
                    <p>Email: contact@shoppink.com</p>
                    <p>Điện thoại: 0123 456 789</p>
                    <p>Địa chỉ: Hà Nội</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> ShopPink.</p>
            </div>
        </div>
    </footer>
    <script src="assets/js/main.js"></script>
</body>
</html>