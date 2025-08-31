<?php
require_once '../includes/autoload.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$order_id) {
    header("Location: orders.php");
    exit();
}

// Lấy thông tin đơn hàng
$order_stmt = $conn->prepare("
    SELECT * FROM orders 
    WHERE id = ? AND user_id = ?
");
$order_stmt->bind_param("ii", $order_id, $user_id);
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
    <div class="order-detail-page">
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-file-alt"></i> Chi tiết đơn hàng #<?php echo $order['id']; ?>
            </h1>
            <a href="orders.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại danh sách đơn hàng
            </a>
        </div>
        
        <div class="order-detail-container">
            <!-- Thông tin đơn hàng -->
            <div class="order-info-section">
                <h3>Thông tin đơn hàng</h3>
                <div class="order-info-grid">
                    <div class="info-item">
                        <label>Mã đơn hàng:</label>
                        <span>#<?php echo $order['id']; ?></span>
                    </div>
                    
                    <div class="info-item">
                        <label>Ngày đặt hàng:</label>
                        <span><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></span>
                    </div>
                    
                    <div class="info-item">
                        <label>Trạng thái:</label>
                        <span class="status-badge status-<?php echo $order['status']; ?>">
                            <?php
                            $status_labels = [
                                'pending' => 'Chờ xử lý',
                                'processing' => 'Đang xử lý',
                                'shipped' => 'Đang giao hàng',
                                'delivered' => 'Đã giao hàng',
                                'cancelled' => 'Đã hủy',
                                'returned' => 'Đã trả hàng'
                            ];
                            echo $status_labels[$order['status']] ?? $order['status'];
                            ?>
                        </span>
                    </div>
                    
                    <div class="info-item">
                        <label>Phương thức thanh toán:</label>
                        <span>
                            <?php
                            $payment_labels = [
                                'cod' => 'Thanh toán khi nhận hàng',
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
            <div class="shipping-info-section">
                <h3>Thông tin giao hàng</h3>
                <div class="shipping-info">
                    <div class="info-item">
                        <label>Người nhận:</label>
                        <span><?php echo htmlspecialchars($order['full_name']); ?></span>
                    </div>
                    
                    <div class="info-item">
                        <label>Số điện thoại:</label>
                        <span><?php echo htmlspecialchars($order['phone']); ?></span>
                    </div>
                    
                    <div class="info-item">
                        <label>Email:</label>
                        <span><?php echo htmlspecialchars($order['email'] ?: 'Không có'); ?></span>
                    </div>
                    
                    <div class="info-item">
                        <label>Địa chỉ:</label>
                        <span><?php echo htmlspecialchars($order['address'] . ', ' . $order['city']); ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Chi tiết sản phẩm -->
            <div class="products-section">
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
            
            <!-- Tổng kết đơn hàng -->
            <div class="order-summary-section">
                <h3>Tổng kết đơn hàng</h3>
                <div class="summary-table">
                    <div class="summary-row">
                        <span>Tổng tiền hàng:</span>
                        <span><?php echo format_price($order['subtotal']); ?></span>
                    </div>
                    
                    <div class="summary-row">
                        <span>Phí vận chuyển:</span>
                        <span><?php echo $order['shipping_fee'] > 0 ? format_price($order['shipping_fee']) : 'Miễn phí'; ?></span>
                    </div>
                    
                    <div class="summary-row total">
                        <span>Tổng cộng:</span>
                        <span class="total-amount"><?php echo format_price($order['total_amount']); ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Ghi chú -->
            <?php if ($order['notes']): ?>
                <div class="notes-section">
                    <h3>Ghi chú</h3>
                    <div class="notes-content">
                        <?php echo nl2br(htmlspecialchars($order['notes'])); ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Hành động -->
            <div class="order-actions">
                <?php if ($order['status'] == 'pending'): ?>
                    <form method="POST" action="orders.php" class="cancel-form" style="display: inline;">
                        <input type="hidden" name="cancel_order" value="1">
                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                        <button type="submit" class="btn btn-danger" 
                                onclick="return confirm('Bạn có chắc muốn hủy đơn hàng này?')">
                            <i class="fas fa-times"></i> Hủy đơn hàng
                        </button>
                    </form>
                <?php endif; ?>
                
                <?php if ($order['status'] == 'delivered'): ?>
                    <a href="reviews.php" class="btn btn-primary">
                        <i class="fas fa-star"></i> Đánh giá sản phẩm
                    </a>
                <?php endif; ?>
                
                <a href="orders.php" class="btn btn-secondary">
                    <i class="fas fa-list"></i> Xem tất cả đơn hàng
                </a>
            </div>
        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>