<?php
// get_order_details.php
// Lấy chi tiết đơn hàng bằng AJAX
require_once 'config.php';
// Kiểm tra người dùng đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    echo '<p>Bạn cần đăng nhập để xem chi tiết đơn hàng</p>';
    exit;
}
// Kiểm tra vai trò người dùng
if ($_SESSION['user_role'] !== 'seller') {
    echo '<p>Bạn không có quyền xem chi tiết đơn hàng</p>';
    exit;
}
// Lấy ID đơn hàng từ URL
$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
if ($order_id <= 0) {
    echo '<p>ID đơn hàng không hợp lệ</p>';
    exit;
}
// Lấy thông tin đơn hàng
$stmt = $conn->prepare("
    SELECT o.*, u.name as user_name, u.email as user_email 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    WHERE o.id = ?
");
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$order) {
    echo '<p>Không tìm thấy đơn hàng</p>';
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
?>
<style>
/* CSS cho chi tiết đơn hàng */
.order-detail-info {
    margin-bottom: 25px;
    padding: 20px;
    background-color: #f9f9f9;
    border-radius: 8px;
    border-left: 4px solid #e91e63;
}
.info-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 12px;
    padding-bottom: 12px;
    border-bottom: 1px solid #eee;
}
.info-row:last-child {
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: none;
}
.info-row span:first-child {
    font-weight: 600;
    color: #555;
    min-width: 120px;
}
.info-row span:last-child {
    font-weight: 500;
    color: #333;
}
.status {
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 600;
    display: inline-block;
}
.status.pending {
    background-color: #fff8e1;
    color: #ff8f00;
}
.status.completed {
    background-color: #e8f5e9;
    color: #2e7d32;
}
.status.cancelled {
    background-color: #ffebee;
    color: #c62828;
}
.total {
    font-size: 18px;
    font-weight: 700;
    color: #e91e63;
}
h4 {
    font-size: 18px;
    margin: 25px 0 15px;
    color: #333;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
}
.order-items {
    display: flex;
    flex-direction: column;
    gap: 15px;
}
.order-item {
    display: flex;
    align-items: center;
    padding: 15px;
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
}
.order-item:hover {
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}
.item-image {
    width: 80px;
    height: 80px;
    margin-right: 15px;
    border-radius: 8px;
    overflow: hidden;
    flex-shrink: 0;
}
.item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: all 0.3s ease;
}
.order-item:hover .item-image img {
    transform: scale(1.05);
}
.item-info {
    flex-grow: 1;
}
.item-info h5 {
    font-size: 16px;
    margin: 0 0 8px 0;
    color: #333;
}
.item-info p {
    margin: 5px 0;
    color: #777;
    font-size: 14px;
}
.item-total {
    font-size: 16px;
    font-weight: 700;
    color: #e91e63;
    margin-left: 15px;
    flex-shrink: 0;
}
@media (max-width: 768px) {
    .order-item {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .item-image {
        margin-right: 0;
        margin-bottom: 15px;
    }
    
    .item-total {
        margin-left: 0;
        margin-top: 10px;
        align-self: flex-end;
    }
    
    .info-row {
        flex-direction: column;
        gap: 5px;
    }
}
</style>
<div class="order-detail-info">
    <div class="info-row">
        <span>Mã đơn hàng:</span>
        <span>#<?php echo $order['id']; ?></span>
    </div>
    <div class="info-row">
        <span>Khách hàng:</span>
        <span><?php echo htmlspecialchars($order['user_name']); ?> (<?php echo htmlspecialchars($order['user_email']); ?>)</span>
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
<h4>Sản phẩm đã đặt</h4>
<div class="order-items">
    <?php foreach ($order_details as $item): ?>
        <div class="order-item">
            <div class="item-image cart-product-image">
                <img src="assets/images/products/<?php echo htmlspecialchars($item['product_image']); ?>" 
                    alt="<?php echo htmlspecialchars($item['product_name']); ?>">
            </div>
            <div class="item-info">
                <h5><?php echo htmlspecialchars($item['product_name']); ?></h5>
                <p>Giá: <?php echo number_format($item['price'], 0, ',', '.'); ?>₫</p>
                <p>Số lượng: <?php echo $item['quantity']; ?></p>
            </div>
            <div class="item-total">
                <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?>₫
            </div>
        </div>
    <?php endforeach; ?>
</div>