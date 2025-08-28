<?php
session_start();
include('includes/config.php');
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
$details_stmt = $conn->prepare("
    SELECT od.*, p.name, p.image 
    FROM order_details od 
    JOIN products p ON od.product_id = p.id 
    WHERE od.order_id = ?
");
$details_stmt->bind_param("i", $order_id);
$details_stmt->execute();
$details_result = $details_stmt->get_result();
// Map trạng thái sang tiếng Việt
$status_map = [
    'pending'   => 'Chờ xử lý',
    'confirmed' => 'Đã xác nhận',
    'shipping'  => 'Đang giao',
    'completed' => 'Hoàn thành',
    'cancelled' => 'Đã hủy'
];
$status_text = $status_map[$order['status']] ?? ucfirst($order['status']);
// Map phương thức thanh toán sang tiếng Việt
$payment_map = [
    'cod' => 'Thanh toán khi nhận hàng (COD)',
    'bank' => 'Chuyển khoản ngân hàng',
    'card' => 'Thẻ tín dụng/ghi nợ',
    'momo' => 'Ví điện tử'
];
$payment_text = $payment_map[$order['payment_method']] ?? $order['payment_method'];
?>
<?php include('includes/header.php'); ?>

<div class="container">
    <div class="order-confirmation animate-on-scroll">
        <!-- Confirmation Header -->
        <div class="confirmation-header">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h1>Đặt hàng thành công!</h1>
            <p>Cảm ơn bạn đã mua hàng tại ShopPink. Đơn hàng của bạn đang được xử lý.</p>
            <p class="order-number">Mã đơn hàng: <strong>#<?php echo str_pad($order_id, 6, '0', STR_PAD_LEFT); ?></strong></p>
            <p class="notification-text">Bạn sẽ nhận được email xác nhận đơn hàng. Vui lòng kiểm tra hộp thư của bạn.</p>
        </div>
        
        <!-- Order Details -->
        <div class="order-details-container">
            <div class="order-info-section">
                <h2>Thông tin đơn hàng</h2>
                <div class="order-info-grid">
                    <div class="info-item">
                        <span class="info-label">Mã đơn hàng:</span>
                        <span class="info-value">#<?php echo str_pad($order_id, 6, '0', STR_PAD_LEFT); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Ngày đặt:</span>
                        <span class="info-value"><?php echo date("d/m/Y H:i", strtotime($order['created_at'])); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Tổng tiền:</span>
                        <span class="info-value price"><?php echo number_format($order['total'], 0, ',', '.'); ?>đ</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Phương thức thanh toán:</span>
                        <span class="info-value"><?php echo $payment_text; ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Trạng thái:</span>
                        <span class="info-value">
                            <span class="status-badge <?php echo htmlspecialchars($order['status']); ?>">
                                <?php echo $status_text; ?>
                            </span>
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="shipping-info-section">
                <h2>Thông tin giao hàng</h2>
                <div class="shipping-info">
                    <div class="shipping-address">
                        <h4>Địa chỉ giao hàng:</h4>
                        <p><?php echo htmlspecialchars($order['full_name']); ?></p>
                        <p><?php echo htmlspecialchars($order['address']); ?></p>
                        <p>Điện thoại: <?php echo htmlspecialchars($order['phone']); ?></p>
                    </div>
                    
                    <div class="shipping-timeline">
                        <h4>Thời gian giao hàng dự kiến:</h4>
                        <div class="timeline">
                            <div class="timeline-item active">
                                <div class="timeline-marker"></div>
                                <div class="timeline-content">
                                    <h5>Đã tiếp nhận</h5>
                                    <p><?php echo date("d/m/Y", strtotime($order['created_at'])); ?></p>
                                </div>
                            </div>
                            <div class="timeline-item">
                                <div class="timeline-marker"></div>
                                <div class="timeline-content">
                                    <h5>Đang xử lý</h5>
                                    <p>Dự kiến: <?php echo date("d/m/Y", strtotime('+1 day', strtotime($order['created_at']))); ?></p>
                                </div>
                            </div>
                            <div class="timeline-item">
                                <div class="timeline-marker"></div>
                                <div class="timeline-content">
                                    <h5>Đang giao hàng</h5>
                                    <p>Dự kiến: <?php echo date("d/m/Y", strtotime('+2 days', strtotime($order['created_at']))); ?></p>
                                </div>
                            </div>
                            <div class="timeline-item">
                                <div class="timeline-marker"></div>
                                <div class="timeline-content">
                                    <h5>Đã giao hàng</h5>
                                    <p>Dự kiến: <?php echo date("d/m/Y", strtotime('+3 days', strtotime($order['created_at']))); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Order Items -->
            <div class="order-items-section">
                <h2>Sản phẩm đã đặt</h2>
                <div class="order-items">
                    <?php while ($item = $details_result->fetch_assoc()) { ?>
                        <div class="order-item">
                            <div class="item-image">
                                <img src="assets/images/products/<?php echo htmlspecialchars($item['image'] ?? 'default.jpg'); ?>" 
                                     alt="<?php echo htmlspecialchars($item['name']); ?>">
                            </div>
                            <div class="item-details">
                                <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                                <div class="item-meta">
                                    <span class="item-quantity">Số lượng: <?php echo intval($item['quantity']); ?></span>
                                    <span class="item-price"><?php echo number_format($item['price'], 0, ',', '.'); ?>đ</span>
                                </div>
                                <div class="item-total">
                                    Thành tiền: <span><?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?>đ</span>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
            
            <!-- Order Summary -->
            <div class="order-summary-section">
                <h2>Tóm tắt đơn hàng</h2>
                <div class="order-summary">
                    <div class="summary-row">
                        <span>Tạm tính:</span>
                        <span><?php echo number_format($order['subtotal'], 0, ',', '.'); ?>đ</span>
                    </div>
                    <div class="summary-row">
                        <span>Phí vận chuyển:</span>
                        <span>
                            <?php if ($order['shipping_fee'] == 0): ?>
                                <span style="color: var(--success-color);">Miễn phí</span>
                            <?php else: ?>
                                <?php echo number_format($order['shipping_fee'], 0, ',', '.'); ?>đ
                            <?php endif; ?>
                        </span>
                    </div>
                    <?php if (!empty($order['discount'])): ?>
                    <div class="summary-row discount">
                        <span>Giảm giá:</span>
                        <span>-<?php echo number_format($order['discount'], 0, ',', '.'); ?>đ</span>
                    </div>
                    <?php endif; ?>
                    <div class="summary-row total">
                        <span>Tổng cộng:</span>
                        <span><?php echo number_format($order['total'], 0, ',', '.'); ?>đ</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="confirmation-actions">
            <a href="index.php" class="btn btn-outline">
                <i class="fas fa-home"></i> Tiếp tục mua sắm
            </a>
            <a href="order_detail.php?id=<?php echo $order_id; ?>" class="btn btn-primary">
                <i class="fas fa-receipt"></i> Xem chi tiết đơn hàng
            </a>
        </div>
        
        <!-- Recommendations -->
        <div class="recommendations">
            <h2>Sản phẩm gợi ý</h2>
            <div class="products-grid">
                <?php
                // Lấy sản phẩm gợi ý (ngẫu nhiên)
                $recommended_query = "SELECT * FROM products ORDER BY RAND() LIMIT 4";
                $recommended_result = mysqli_query($conn, $recommended_query);
                while ($product = mysqli_fetch_assoc($recommended_result)):
                ?>
                    <div class="product" data-category="<?php echo $product['category_id']; ?>" data-price="<?php echo $product['price']; ?>">
                        <div class="product-img" style="background-image: url('assets/images/products/<?php echo htmlspecialchars($product['image'] ?? 'default.jpg'); ?>');"></div>
                        <div class="product-info">
                            <div class="product-vendor"><?php echo htmlspecialchars($product['category_name'] ?? ''); ?></div>
                            <div class="product-title"><?php echo htmlspecialchars($product['name']); ?></div>
                            <div class="product-price">
                                <span class="current-price"><?php echo number_format($product['price'], 0, ',', '.'); ?>đ</span>
                            </div>
                            <div class="product-rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="far fa-star"></i>
                                <span>(<?php echo rand(5, 30); ?> đánh giá)</span>
                            </div>
                            <div class="product-footer">
                                <a href="product_detail.php?id=<?php echo $product['id']; ?>" class="btn">Xem chi tiết</a>
                                <button class="wishlist-btn"><i class="far fa-heart"></i></button>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>