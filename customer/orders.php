<?php
require_once '../includes/autoload.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Xử lý hủy đơn hàng
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cancel_order'])) {
    $order_id = intval($_POST['order_id']);
    
    // Kiểm tra đơn hàng thuộc về user này và có thể hủy
    $check_stmt = $conn->prepare("
        SELECT * FROM orders 
        WHERE id = ? AND user_id = ? AND status = 'pending'
    ");
    $check_stmt->bind_param("ii", $order_id, $user_id);
    $check_stmt->execute();
    $order = $check_stmt->get_result()->fetch_assoc();
    
    if ($order) {
        $cancel_stmt = $conn->prepare("
            UPDATE orders SET status = 'cancelled', updated_at = NOW() WHERE id = ?
        ");
        $cancel_stmt->bind_param("i", $order_id);
        
        if ($cancel_stmt->execute()) {
            $success = "Đơn hàng đã được hủy thành công!";
        } else {
            $error = "Có lỗi xảy ra khi hủy đơn hàng!";
        }
    } else {
        $error = "Không thể hủy đơn hàng này!";
    }
}

// Lấy danh sách đơn hàng của user
$orders_stmt = $conn->prepare("
    SELECT o.*, COUNT(od.id) as item_count 
    FROM orders o 
    LEFT JOIN order_details od ON o.id = od.order_id 
    WHERE o.user_id = ? 
    GROUP BY o.id 
    ORDER BY o.created_at DESC
");
$orders_stmt->bind_param("i", $user_id);
$orders_stmt->execute();
$orders = $orders_stmt->get_result();

include('../includes/header.php');
?>

<div class="container">
    <div class="orders-page">
        <h1 class="page-title">
            <i class="fas fa-shopping-bag"></i> Đơn hàng của tôi
        </h1>
        
        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($orders->num_rows > 0): ?>
            <div class="orders-list">
                <?php while ($order = $orders->fetch_assoc()): ?>
                    <div class="order-item">
                        <div class="order-header">
                            <div class="order-info">
                                <h3 class="order-id">
                                    Đơn hàng #<?php echo $order['id']; ?>
                                </h3>
                                <p class="order-date">
                                    <i class="fas fa-calendar"></i>
                                    <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?>
                                </p>
                                <p class="order-status status-<?php echo $order['status']; ?>">
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
                                </p>
                            </div>
                            
                            <div class="order-summary">
                                <div class="summary-item">
                                    <span class="label">Số sản phẩm:</span>
                                    <span class="value"><?php echo $order['item_count']; ?></span>
                                </div>
                                
                                <div class="summary-item">
                                    <span class="label">Tổng tiền:</span>
                                    <span class="value total-amount"><?php echo format_price($order['total_amount']); ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="order-details">
                            <div class="detail-row">
                                <span class="label">Người nhận:</span>
                                <span class="value"><?php echo htmlspecialchars($order['full_name']); ?></span>
                            </div>
                            
                            <div class="detail-row">
                                <span class="label">Số điện thoại:</span>
                                <span class="value"><?php echo htmlspecialchars($order['phone']); ?></span>
                            </div>
                            
                            <div class="detail-row">
                                <span class="label">Địa chỉ:</span>
                                <span class="value"><?php echo htmlspecialchars($order['address'] . ', ' . $order['city']); ?></span>
                            </div>
                            
                            <div class="detail-row">
                                <span class="label">Phương thức thanh toán:</span>
                                <span class="value">
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
                        
                        <div class="order-actions">
                            <a href="order_detail.php?id=<?php echo $order['id']; ?>" 
                               class="btn btn-primary">
                                <i class="fas fa-eye"></i> Xem chi tiết
                            </a>
                            
                            <?php if ($order['status'] == 'pending'): ?>
                                <form method="POST" action="" class="cancel-form" style="display: inline;">
                                    <input type="hidden" name="cancel_order" value="1">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <button type="submit" class="btn btn-danger" 
                                            onclick="return confirm('Bạn có chắc muốn hủy đơn hàng này?')">
                                        <i class="fas fa-times"></i> Hủy đơn hàng
                                    </button>
                                </form>
                            <?php endif; ?>
                            
                            <?php if ($order['status'] == 'delivered'): ?>
                                <a href="reviews.php" class="btn btn-warning">
                                    <i class="fas fa-star"></i> Đánh giá sản phẩm
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="no-orders">
                <div class="no-orders-icon">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <h3>Bạn chưa có đơn hàng nào</h3>
                <p>Hãy mua sắm để có đơn hàng đầu tiên!</p>
                <a href="../products.php" class="btn btn-primary">
                    <i class="fas fa-shopping-bag"></i> Mua sắm ngay
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include('../includes/footer.php'); ?>