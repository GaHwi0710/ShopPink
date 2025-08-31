<?php
require_once '../includes/autoload.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if (!$order_id) {
    header("Location: orders.php");
    exit();
}

// Lấy thông tin đơn hàng
$order_stmt = $conn->prepare("
    SELECT * FROM orders 
    WHERE id = ? AND user_id = ?
");
$order_stmt->bind_param("ii", $_SESSION['user_id'], $order_id);
$order_stmt->execute();
$order = $order_stmt->get_result()->fetch_assoc();

if (!$order) {
    header("Location: orders.php");
    exit();
}

// Lấy chi tiết đơn hàng
$details_stmt = $conn->prepare("
    SELECT od.*, p.image as product_image 
    FROM order_details od 
    JOIN products p ON od.product_id = p.id 
    WHERE od.order_id = ?
");
$details_stmt->bind_param("i", $order_id);
$details_stmt->execute();
$order_details = $details_stmt->get_result();

include('../includes/header.php');
?>

<div class="container">
    <div class="confirmation-page">
        <div class="confirmation-header">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h1>Đặt hàng thành công!</h1>
            <p>Cảm ơn bạn đã đặt hàng. Đơn hàng của bạn đã được xác nhận.</p>
        </div>
        
        <div class="confirmation-container">
            <!-- Thông tin đơn hàng -->
            <div class="order-summary">
                <h3>Thông tin đơn hàng</h3>
                <div class="order-info">
                    <div class="info-row">
                        <span class="label">Mã đơn hàng:</span>
                        <span class="value">#<?php echo $order['id']; ?></span>
                    </div>
                    
                    <div class="info-row">
                        <span class="label">Ngày đặt hàng:</span>
                        <span class="value"><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></span>
                    </div>
                    
                    <div class="info-row">
                        <span class="label">Trạng thái:</span>
                        <span class="value status-pending">Chờ xử lý</span>
                    </div>
                    
                    <div class="info-row">
                        <span class="label">Phương thức thanh toán:</span>
                        <span class="value">
                            <?php
                            $payment_labels = [
                                'cod' => 'Thanh toán khi nhận hàng (COD)',
                                'bank' => 'Chuyển khoản ngân hàng',
                                'momo' => 'Ví MoMo',
                                'vnpay' => 'VNPay'
                            ];
                            echo $payment_labels[$order['payment_method']] ?? $order['payment_method'];
                            ?>
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Thông tin giao hàng -->
            <div class="shipping-info">
                <h3>Thông tin giao hàng</h3>
                <div class="shipping-details">
                    <div class="info-row">
                        <span class="label">Người nhận:</span>
                        <span class="value"><?php echo htmlspecialchars($order['full_name']); ?></span>
                    </div>
                    
                    <div class="info-row">
                        <span class="label">Số điện thoại:</span>
                        <span class="value"><?php echo htmlspecialchars($order['phone']); ?></span>
                    </div>
                    
                    <?php if ($order['email']): ?>
                        <div class="info-row">
                            <span class="label">Email:</span>
                            <span class="value"><?php echo htmlspecialchars($order['email']); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <div class="info-row">
                        <span class="label">Địa chỉ:</span>
                        <span class="value"><?php echo htmlspecialchars($order['address'] . ', ' . $order['city']); ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Sản phẩm đã đặt -->
            <div class="ordered-products">
                <h3>Sản phẩm đã đặt</h3>
                <div class="products-list">
                    <?php while ($detail = $order_details->fetch_assoc()): ?>
                        <div class="product-item">
                            <div class="product-image">
                                <img src="../assets/images/products/<?php echo htmlspecialchars($detail['product_image']); ?>" 
                                     alt="<?php echo htmlspecialchars($detail['product_name']); ?>">
                            </div>
                            
                            <div class="product-info">
                                <h4 class="product-name">
                                    <a href="../product_detail.php?id=<?php echo $detail['product_id']; ?>">
                                        <?php echo htmlspecialchars($detail['product_name']); ?>
                                    </a>
                                </h4>
                                <p class="product-price"><?php echo format_price($detail['price']); ?></p>
                                <p class="product-quantity">Số lượng: <?php echo $detail['quantity']; ?></p>
                            </div>
                            
                            <div class="product-total">
                                <span class="total-price"><?php echo format_price($detail['subtotal']); ?></span>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
            
            <!-- Tổng kết -->
            <div class="order-total">
                <h3>Tổng kết đơn hàng</h3>
                <div class="total-breakdown">
                    <div class="total-row">
                        <span class="label">Tổng tiền hàng:</span>
                        <span class="value"><?php echo format_price($order['subtotal']); ?></span>
                    </div>
                    
                    <div class="total-row">
                        <span class="label">Phí vận chuyển:</span>
                        <span class="value"><?php echo $order['shipping_fee'] > 0 ? format_price($order['shipping_fee']) : 'Miễn phí'; ?></span>
                    </div>
                    
                    <div class="total-row final-total">
                        <span class="label">Tổng cộng:</span>
                        <span class="value"><?php echo format_price($order['total_amount']); ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Ghi chú -->
            <?php if ($order['notes']): ?>
                <div class="order-notes">
                    <h3>Ghi chú</h3>
                    <div class="notes-content">
                        <?php echo nl2br(htmlspecialchars($order['notes'])); ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Thông tin bổ sung -->
            <div class="additional-info">
                <h3>Thông tin bổ sung</h3>
                <div class="info-content">
                    <div class="info-item">
                        <i class="fas fa-clock"></i>
                        <div class="info-text">
                            <h4>Thời gian xử lý</h4>
                            <p>Đơn hàng của bạn sẽ được xử lý trong vòng 24-48 giờ làm việc.</p>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <i class="fas fa-truck"></i>
                        <div class="info-text">
                            <h4>Thời gian giao hàng</h4>
                            <p>Thời gian giao hàng dự kiến: 2-5 ngày làm việc tùy theo địa chỉ giao hàng.</p>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <i class="fas fa-phone"></i>
                        <div class="info-text">
                            <h4>Liên hệ hỗ trợ</h4>
                            <p>Nếu có thắc mắc, vui lòng liên hệ: <strong>1900-xxxx</strong> hoặc email: <strong>support@shoppink.com</strong></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Hành động -->
            <div class="confirmation-actions">
                <a href="order_detail.php?id=<?php echo $order['id']; ?>" class="btn btn-primary">
                    <i class="fas fa-eye"></i> Xem chi tiết đơn hàng
                </a>
                
                <a href="orders.php" class="btn btn-secondary">
                    <i class="fas fa-list"></i> Xem tất cả đơn hàng
                </a>
                
                <a href="../products.php" class="btn btn-outline-primary">
                    <i class="fas fa-shopping-bag"></i> Tiếp tục mua sắm
                </a>
            </div>
        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>