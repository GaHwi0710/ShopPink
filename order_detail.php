<?php
// Include autoload để tự động nạp các file cần thiết
require_once 'includes/autoload.php';

// Đảm bảo các hàm flash message đã được khai báo
if (!function_exists('setFlashMessage')) {
    function setFlashMessage($type, $message) {
        $_SESSION['flash'][$type] = $message;
    }
}
if (!function_exists('displayFlashMessage')) {
    function displayFlashMessage() {
        if (!empty($_SESSION['flash'])) {
            foreach ($_SESSION['flash'] as $type => $msg) {
                echo '<div class="alert alert-' . htmlspecialchars($type) . '">' . htmlspecialchars($msg) . '</div>';
            }
            unset($_SESSION['flash']);
        }
    }
}

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    setFlashMessage('error', 'Vui lòng đăng nhập để xem chi tiết đơn hàng!');
    redirect('login.php');
}

// Lấy order_id từ URL
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Kiểm tra order_id hợp lệ
if ($order_id <= 0) {
    setFlashMessage('error', 'ID đơn hàng không hợp lệ!');
    redirect('order_history.php');
}

// Lấy thông tin user
$user_id = $_SESSION['user_id'];

// Define a fallback getOrder() if it's not provided by autoload/includes.
// This tries common globals ($pdo or $conn) and returns false if no DB available,
// allowing the existing error handling to run.
if (!function_exists('getOrder')) {
    function getOrder($order_id, $user_id) {
        // Try PDO instance stored in $GLOBALS['pdo']
        if (isset($GLOBALS['pdo']) && $GLOBALS['pdo'] instanceof PDO) {
            $stmt = $GLOBALS['pdo']->prepare('SELECT * FROM orders WHERE id = ? AND user_id = ? LIMIT 1');
            $stmt->execute([$order_id, $user_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        // Try mysqli instance stored in $GLOBALS['conn']
        if (isset($GLOBALS['conn']) && $GLOBALS['conn'] instanceof mysqli) {
            $stmt = $GLOBALS['conn']->prepare('SELECT * FROM orders WHERE id = ? AND user_id = ? LIMIT 1');
            if ($stmt) {
                $stmt->bind_param('ii', $order_id, $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                return $result ? $result->fetch_assoc() : false;
            }
            return false;
        }

        // No known DB connection available; return false so calling code handles it.
        return false;
    }
}

// Lấy thông tin đơn hàng
$order = getOrder($order_id, $user_id);

// Kiểm tra đơn hàng có tồn tại và thuộc về user hiện tại
if (!$order) {
    setFlashMessage('error', 'Đơn hàng không tồn tại hoặc bạn không có quyền xem!');
    redirect('order_history.php');
}

// Lấy chi tiết đơn hàng
$order_items = getOrderDetails($order_id);

// Lấy thông tin user để hiển thị
$user_info = getUser($user_id);

// Include header
include('includes/header.php');
?>

<div class="container">
    <div class="order-detail-page">
        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <a href="index.php">Trang chủ</a> / 
            <a href="user_home.php">Tài khoản</a> / 
            <a href="order_history.php">Đơn hàng của tôi</a> / 
            <span>Chi tiết đơn hàng #<?php echo str_pad($order_id, 6, '0', STR_PAD_LEFT); ?></span>
        </div>
        
        <!-- Hiển thị thông báo flash -->
        <?php displayFlashMessage(); ?>
        
        <!-- Thông tin đơn hàng -->
        <div class="order-info">
            <h2>Chi tiết đơn hàng #<?php echo str_pad($order_id, 6, '0', STR_PAD_LEFT); ?></h2>
            
            <div class="order-status">
                <span class="status-label">Trạng thái:</span>
                <span class="status-badge <?php echo strtolower($order['status']); ?>">
                    <?php 
                    switch($order['status']) {
                        case 'pending':
                            echo '<i class="fas fa-clock"></i> Chờ xử lý';
                            break;
                        case 'processing':
                            echo '<i class="fas fa-cog"></i> Đang xử lý';
                            break;
                        case 'shipping':
                            echo '<i class="fas fa-truck"></i> Đang giao hàng';
                            break;
                        case 'completed':
                            echo '<i class="fas fa-check-circle"></i> Hoàn thành';
                            break;
                        case 'cancelled':
                            echo '<i class="fas fa-times-circle"></i> Đã hủy';
                            break;
                        default:
                            echo $order['status'];
                    }
                    ?>
                </span>
            </div>
            
            <div class="order-meta">
                <div class="meta-item">
                    <span class="label">Ngày đặt:</span>
                    <span class="value"><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></span>
                </div>
                <div class="meta-item">
                    <span class="label">Phương thức thanh toán:</span>
                    <span class="value">
                        <?php 
                        switch($order['payment_method']) {
                            case 'cod':
                                echo '<i class="fas fa-money-bill-wave"></i> Thanh toán khi nhận hàng (COD)';
                                break;
                            case 'bank':
                                echo '<i class="fas fa-university"></i> Chuyển khoản ngân hàng';
                                break;
                            case 'momo':
                                echo '<i class="fas fa-mobile-alt"></i> Ví MoMo';
                                break;
                            default:
                                echo $order['payment_method'];
                        }
                        ?>
                    </span>
                </div>
                <div class="meta-item">
                    <span class="label">Tổng tiền:</span>
                    <span class="value total"><?php echo formatPrice($order['total']); ?></span>
                </div>
            </div>
        </div>
        
        <!-- Timeline đơn hàng -->
        <div class="order-timeline">
            <h3>Tiến trình đơn hàng</h3>
            <div class="timeline">
                <div class="timeline-item <?php echo in_array($order['status'], ['pending', 'processing', 'shipping', 'completed']) ? 'active' : ''; ?>">
                    <div class="timeline-marker">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="timeline-content">
                        <h4>Đặt hàng thành công</h4>
                        <p><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></p>
                    </div>
                </div>
                
                <div class="timeline-item <?php echo in_array($order['status'], ['processing', 'shipping', 'completed']) ? 'active' : ''; ?>">
                    <div class="timeline-marker">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="timeline-content">
                        <h4>Đang xử lý</h4>
                        <p>Đơn hàng của bạn đang được xử lý</p>
                    </div>
                </div>
                
                <div class="timeline-item <?php echo in_array($order['status'], ['shipping', 'completed']) ? 'active' : ''; ?>">
                    <div class="timeline-marker">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="timeline-content">
                        <h4>Đang giao hàng</h4>
                        <p>Đơn hàng đang được giao đến bạn</p>
                    </div>
                </div>
                
                <div class="timeline-item <?php echo $order['status'] == 'completed' ? 'active' : ''; ?>">
                    <div class="timeline-marker">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="timeline-content">
                        <h4>Giao hàng thành công</h4>
                        <p>Đơn hàng đã được giao thành công</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Thông tin giao hàng -->
        <div class="shipping-info">
            <h3>Thông tin giao hàng</h3>
            <div class="info-card">
                <div class="info-row">
                    <span class="label">Họ và tên:</span>
                    <span class="value"><?php echo htmlspecialchars($order['full_name']); ?></span>
                </div>
                <div class="info-row">
                    <span class="label">Số điện thoại:</span>
                    <span class="value"><?php echo htmlspecialchars($order['phone']); ?></span>
                </div>
                <div class="info-row">
                    <span class="label">Email:</span>
                    <span class="value"><?php echo htmlspecialchars($order['email']); ?></span>
                </div>
                <div class="info-row">
                    <span class="label">Địa chỉ:</span>
                    <span class="value"><?php echo htmlspecialchars($order['address']); ?></span>
                </div>
                <div class="info-row">
                    <span class="label">Thành phố:</span>
                    <span class="value"><?php echo htmlspecialchars($order['city']); ?></span>
                </div>
                <div class="info-row">
                    <span class="label">Quận/Huyện:</span>
                    <span class="value"><?php echo htmlspecialchars($order['district']); ?></span>
                </div>
            </div>
        </div>
        
        <!-- Chi tiết sản phẩm -->
        <div class="order-items">
            <h3>Sản phẩm đã đặt</h3>
            <div class="items-table">
                <table class="order-items-table">
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th>Giá</th>
                            <th>Số lượng</th>
                            <th>Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order_items as $item): ?>
                        <tr>
                            <td class="product-info">
                                <div class="product-image">
                                    <img src="assets/images/products/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                </div>
                                <div class="product-details">
                                    <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                                    <p>Mã SP: <?php echo str_pad($item['product_id'], 6, '0', STR_PAD_LEFT); ?></p>
                                </div>
                            </td>
                            <td class="product-price"><?php echo formatPrice($item['price']); ?></td>
                            <td class="product-quantity"><?php echo $item['quantity']; ?></td>
                            <td class="product-total"><?php echo formatPrice($item['price'] * $item['quantity']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Tổng thanh toán -->
        <div class="order-summary">
            <h3>Tổng thanh toán</h3>
            <div class="summary-card">
                <div class="summary-row">
                    <span>Tạm tính:</span>
                    <span><?php echo formatPrice($order['total'] - 30000); ?></span>
                </div>
                <div class="summary-row">
                    <span>Phí vận chuyển:</span>
                    <span><?php echo formatPrice(30000); ?></span>
                </div>
                <div class="summary-row total">
                    <span>Tổng cộng:</span>
                    <span><?php echo formatPrice($order['total']); ?></span>
                </div>
            </div>
        </div>
        
        <!-- Hành động -->
        <div class="order-actions">
            <a href="order_history.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Quay lại danh sách đơn hàng
            </a>
            
            <?php if ($order['status'] == 'completed'): ?>
                <button class="btn-review" onclick="showReviewModal()">
                    <i class="fas fa-star"></i> Đánh giá sản phẩm
                </button>
            <?php endif; ?>
            
            <?php if ($order['status'] == 'pending'): ?>
                <button class="btn-cancel" onclick="confirmCancel()">
                    <i class="fas fa-times"></i> Hủy đơn hàng
                </button>
            <?php endif; ?>
            
            <button class="btn-print" onclick="window.print()">
                <i class="fas fa-print"></i> In đơn hàng
            </button>
        </div>
    </div>
</div>

<!-- Modal đánh giá sản phẩm -->
<div class="modal" id="review-modal">
    <div class="modal-content">
        <span class="modal-close" onclick="closeReviewModal()">&times;</span>
        <h2>Đánh giá sản phẩm</h2>
        
        <form id="review-form" method="POST" action="add_review.php">
            <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
            
            <div class="review-products">
                <?php foreach ($order_items as $item): ?>
                <div class="review-product">
                    <div class="product-info">
                        <img src="assets/images/products/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                        <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                    </div>
                    
                    <div class="rating-input">
                        <input type="hidden" name="ratings[<?php echo $item['product_id']; ?>]" value="0">
                        <div class="stars" data-product-id="<?php echo $item['product_id']; ?>">
                            <i class="far fa-star" data-rating="1"></i>
                            <i class="far fa-star" data-rating="2"></i>
                            <i class="far fa-star" data-rating="3"></i>
                            <i class="far fa-star" data-rating="4"></i>
                            <i class="far fa-star" data-rating="5"></i>
                        </div>
                    </div>
                    
                    <div class="comment-input">
                        <textarea name="comments[<?php echo $item['product_id']; ?>]" placeholder="Nhận xét của bạn về sản phẩm này..."></textarea>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <button type="submit" class="btn-primary">Gửi đánh giá</button>
        </form>
    </div>
</div>

<style>
/* Order Detail Styles */
.order-detail-page {
    padding: 30px 0;
}
.breadcrumb {
    margin-bottom: 30px;
    font-size: 14px;
}
.breadcrumb a {
    color: var(--primary-color);
    text-decoration: none;
}
.breadcrumb span {
    color: var(--gray);
}
.order-info {
    background: white;
    border-radius: 15px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}
.order-info h2 {
    margin-bottom: 20px;
    color: var(--dark-color);
}
.order-status {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 20px;
}
.status-label {
    font-weight: 600;
    color: var(--gray-dark);
}
.status-badge {
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 5px;
}
.status-badge.pending {
    background: #fff3cd;
    color: #856404;
}
.status-badge.processing {
    background: #d1ecf1;
    color: #0c5460;
}
.status-badge.shipping {
    background: #d4edda;
    color: #155724;
}
.status-badge.completed {
    background: #cce5ff;
    color: #004085;
}
.status-badge.cancelled {
    background: #f8d7da;
    color: #721c24;
}
.order-meta {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}
.meta-item {
    display: flex;
    flex-direction: column;
    gap: 5px;
}
.meta-item .label {
    font-weight: 600;
    color: var(--gray-dark);
}
.meta-item .value {
    color: var(--dark-color);
}
.meta-item .value.total {
    font-size: 20px;
    font-weight: bold;
    color: var(--primary-color);
}
.order-timeline {
    background: white;
    border-radius: 15px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}
.order-timeline h3 {
    margin-bottom: 25px;
    color: var(--dark-color);
}
.timeline {
    position: relative;
    padding-left: 30px;
}
.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e0e0e0;
}
.timeline-item {
    position: relative;
    padding-bottom: 40px;
}
.timeline-item:last-child {
    padding-bottom: 0;
}
.timeline-marker {
    position: absolute;
    left: -30px;
    top: 0;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #e0e0e0;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}
.timeline-item.active .timeline-marker {
    background: var(--primary-color);
}
.timeline-content h4 {
    margin-bottom: 5px;
    color: var(--dark-color);
}
.timeline-content p {
    color: var(--gray);
    font-size: 14px;
}
.shipping-info {
    background: white;
    border-radius: 15px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}
.shipping-info h3 {
    margin-bottom: 25px;
    color: var(--dark-color);
}
.info-card {
    background: var(--light-color);
    border-radius: 10px;
    padding: 20px;
}
.info-row {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid #f0f0f0;
}
.info-row:last-child {
    border-bottom: none;
}
.info-row .label {
    font-weight: 600;
    color: var(--gray-dark);
}
.info-row .value {
    color: var(--dark-color);
}
.order-items {
    background: white;
    border-radius: 15px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}
.order-items h3 {
    margin-bottom: 25px;
    color: var(--dark-color);
}
.order-items-table {
    width: 100%;
    border-collapse: collapse;
}
.order-items-table th,
.order-items-table td {
    padding: 15px;
    text-align: left;
    border-bottom: 1px solid #f0f0f0;
}
.order-items-table th {
    background: var(--light-color);
    font-weight: 600;
    color: var(--dark-color);
}
.product-info {
    display: flex;
    align-items: center;
    gap: 15px;
}
.product-image {
    width: 60px;
    height: 60px;
    border-radius: 10px;
    overflow: hidden;
}
.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.product-details h4 {
    margin-bottom: 5px;
    color: var(--dark-color);
}
.product-details p {
    font-size: 12px;
    color: var(--gray);
}
.product-price,
.product-quantity,
.product-total {
    font-weight: 600;
}
.order-summary {
    background: white;
    border-radius: 15px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}
.order-summary h3 {
    margin-bottom: 25px;
    color: var(--dark-color);
}
.summary-card {
    background: var(--light-color);
    border-radius: 10px;
    padding: 20px;
}
.summary-row {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
}
.summary-row.total {
    font-size: 18px;
    font-weight: bold;
    color: var(--primary-color);
    border-top: 2px solid #f0f0f0;
    margin-top: 10px;
    padding-top: 20px;
}
.order-actions {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}
.btn-back,
.btn-review,
.btn-cancel,
.btn-print {
    padding: 12px 25px;
    border: none;
    border-radius: 25px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
}
.btn-back {
    background: #f0f0f0;
    color: var(--dark-color);
}
.btn-back:hover {
    background: #e0e0e0;
}
.btn-review {
    background: var(--primary-color);
    color: white;
}
.btn-review:hover {
    background: var(--dark-color);
}
.btn-cancel {
    background: var(--error-color);
    color: white;
}
.btn-cancel:hover {
    background: #d32f2f;
}
.btn-print {
    background: var(--gray-dark);
    color: white;
}
.btn-print:hover {
    background: #424242;
}
/* Review Modal Styles */
.review-products {
    margin-bottom: 20px;
}
.review-product {
    border: 1px solid #e0e0e0;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
}
.review-product .product-info {
    margin-bottom: 15px;
}
.review-product .product-info img {
    width: 50px;
    height: 50px;
}
.rating-input {
    margin-bottom: 15px;
}
.stars {
    display: flex;
    gap: 5px;
}
.stars i {
    font-size: 20px;
    color: #ddd;
    cursor: pointer;
    transition: color 0.3s ease;
}
.stars i:hover,
.stars i.active {
    color: #ffc107;
}
.comment-input textarea {
    width: 100%;
    min-height: 80px;
    padding: 10px;
    border: 1px solid #e0e0e0;
    border-radius: 5px;
    resize: vertical;
}
@media (max-width: 768px) {
    .order-meta {
        grid-template-columns: 1fr;
    }
    
    .order-items-table {
        font-size: 14px;
    }
    
    .product-info {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .order-actions {
        flex-direction: column;
    }
    
    .btn-back,
    .btn-review,
    .btn-cancel,
    .btn-print {
        width: 100%;
        justify-content: center;
    }
}
</style>

<script>
// JavaScript cho order detail page
function showReviewModal() {
    document.getElementById('review-modal').classList.add('active');
}
function closeReviewModal() {
    document.getElementById('review-modal').classList.remove('active');
}
function confirmCancel() {
    if (confirm('Bạn có chắc chắn muốn hủy đơn hàng này?')) {
        window.location.href = 'cancel_order.php?id=<?php echo $order_id; ?>';
    }
}
// Rating stars
document.querySelectorAll('.stars').forEach(starsContainer => {
    const stars = starsContainer.querySelectorAll('i');
    const productId = starsContainer.dataset.productId;
    const input = document.querySelector(`input[name="ratings[${productId}]"]`);
    
    stars.forEach((star, index) => {
        star.addEventListener('click', () => {
            const rating = parseInt(star.dataset.rating);
            input.value = rating;
            
            stars.forEach((s, i) => {
                if (i < rating) {
                    s.classList.remove('far');
                    s.classList.add('fas', 'active');
                } else {
                    s.classList.remove('fas', 'active');
                    s.classList.add('far');
                }
            });
        });
        
        star.addEventListener('mouseenter', () => {
            const rating = parseInt(star.dataset.rating);
            
            stars.forEach((s, i) => {
                if (i < rating) {
                    s.classList.remove('far');
                    s.classList.add('fas');
                } else {
                    s.classList.remove('fas');
                    s.classList.add('far');
                }
            });
        });
    });
    
    starsContainer.addEventListener('mouseleave', () => {
        const currentRating = parseInt(input.value);
        
        stars.forEach((s, i) => {
            if (i < currentRating) {
                s.classList.remove('far');
                s.classList.add('fas', 'active');
            } else {
                s.classList.remove('fas', 'active');
                s.classList.add('far');
            }
        });
    });
});
// Close modal when clicking outside
window.addEventListener('click', (e) => {
    const modal = document.getElementById('review-modal');
    if (e.target === modal) {
        closeReviewModal();
    }
});
</script>

<?php
// Include footer
require_once 'includes/footer.php';
?>