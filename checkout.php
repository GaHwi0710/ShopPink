<?php
// checkout.php
// Trang thanh toán
require_once 'config.php';
// Kiểm tra người dùng đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
// Kiểm tra giỏ hàng có sản phẩm không
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}
// Lấy thông tin người dùng
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
// Xử lý đặt hàng
$message = '';
$message_type = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $address = isset($_POST['address']) ? trim($_POST['address']) : '';
    $payment_method = isset($_POST['payment_method']) ? $_POST['payment_method'] : 'cod';
    
    if (empty($address)) {
        $message = 'Vui lòng nhập địa chỉ giao hàng';
        $message_type = 'error';
    } else {
        try {
            // Bắt đầu transaction
            $conn->beginTransaction();
            
            // Tính tổng tiền
            $total = 0;
            foreach ($_SESSION['cart'] as $item) {
                $total += $item['price'] * $item['quantity'];
            }
            
            // Tạo đơn hàng
            $stmt = $conn->prepare("INSERT INTO orders (user_id, total, status) VALUES (?, ?, 'pending')");
            $stmt->execute([$_SESSION['user_id'], $total]);
            $order_id = $conn->lastInsertId();
            
            // Thêm chi tiết đơn hàng và cập nhật tồn kho
            foreach ($_SESSION['cart'] as $item) {
                // Thêm chi tiết đơn hàng
                $stmt = $conn->prepare("INSERT INTO order_details (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
                $stmt->execute([$order_id, $item['id'], $item['quantity'], $item['price']]);
                
                // Cập nhật tồn kho
                $stmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
                $stmt->execute([$item['quantity'], $item['id']]);
            }
            
            // Commit transaction
            $conn->commit();
            
            // Xóa giỏ hàng
            $_SESSION['cart'] = [];
            
            // Chuyển hướng đến trang xác nhận đơn hàng
            header("Location: review.php?order_id=$order_id");
            exit;
        } catch (PDOException $e) {
            // Rollback transaction nếu có lỗi
            $conn->rollBack();
            $message = 'Lỗi: ' . $e->getMessage();
            $message_type = 'error';
        }
    }
}
// Tính tổng tiền
$cart_total = 0;
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_total += $item['price'] * $item['quantity'];
    }
}
// Kiểm tra người dùng đã đăng nhập chưa
$is_logged_in = isset($_SESSION['user_id']);
$user_role = $is_logged_in ? $_SESSION['user_role'] : '';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán - ShopPink</title>
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
        <section class="checkout-page">
            <div class="container">
                <h1>Thanh toán</h1>
                
                <?php if (!empty($message)): ?>
                    <div class="alert alert-<?php echo $message_type; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>
                
                <div class="checkout-container">
                    <div class="checkout-form">
                        <h2>Thông tin giao hàng</h2>
                        <form action="checkout.php" method="post">
                            <div class="form-group">
                                <label for="name">Họ và tên</label>
                                <input type="text" id="name" value="<?php echo htmlspecialchars($user['name']); ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label for="address">Địa chỉ giao hàng</label>
                                <textarea id="address" name="address" rows="3" required></textarea>
                            </div>
                            <div class="form-group">
                                <label>Phương thức thanh toán</label>
                                <div class="payment-methods">
                                    <div class="payment-method">
                                        <input type="radio" id="payment_cod" name="payment_method" value="cod" checked>
                                        <label for="payment_cod">
                                            <i class="fas fa-money-bill-wave"></i> Thanh toán khi nhận hàng (COD)
                                        </label>
                                    </div>
                                    <div class="payment-method">
                                        <input type="radio" id="payment_bank" name="payment_method" value="bank">
                                        <label for="payment_bank">
                                            <i class="fas fa-credit-card"></i> Chuyển khoản ngân hàng
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <button type="submit" name="place_order" class="btn btn-primary">Đặt hàng</button>
                            </div>
                        </form>
                    </div>
                    
                    <div class="order-summary">
                        <h2>Đơn hàng của bạn</h2>
                        <div class="order-items">
                            <?php foreach ($_SESSION['cart'] as $item): ?>
                                <div class="order-item">
                                    <div class="item-image cart-product-image">
                                        <img src="assets/images/products/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>"> 
                                    </div>
                                    <div class="item-info">
                                        <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                                        <p>Giá: <?php echo number_format($item['price'], 0, ',', '.'); ?>₫</p>
                                        <p>Số lượng: <?php echo $item['quantity']; ?></p>
                                    </div>
                                    <div class="item-total">
                                        <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?>₫
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="order-total">
                            <div class="total-row">
                                <span>Tạm tính:</span>
                                <span><?php echo number_format($cart_total, 0, ',', '.'); ?>₫</span>
                            </div>
                            <div class="total-row">
                                <span>Phí vận chuyển:</span>
                                <span>0₫</span>
                            </div>
                            <div class="total-row final">
                                <span>Tổng cộng:</span>
                                <span><?php echo number_format($cart_total, 0, ',', '.'); ?>₫</span>
                            </div>
                        </div>
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