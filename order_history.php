<?php
session_start();
include('includes/config.php');
// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = intval($_SESSION['user_id']);
// Lấy danh sách đơn hàng của user (prepared statement)
$orders_stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$orders_stmt->bind_param("i", $user_id);
$orders_stmt->execute();
$orders_result = $orders_stmt->get_result();
// Map trạng thái sang tiếng Việt
$status_map = [
    'pending'   => 'Chờ xử lý',
    'confirmed' => 'Đã xác nhận',
    'shipping'  => 'Đang giao',
    'completed' => 'Hoàn thành',
    'cancelled' => 'Đã hủy'
];
// Map phương thức thanh toán sang tiếng Việt
$payment_map = [
    'cod' => 'COD',
    'bank' => 'Chuyển khoản',
    'card' => 'Thẻ tín dụng',
    'momo' => 'Ví điện tử'
];
?>
<?php include('includes/header.php'); ?>

<div class="container">
    <h1 class="section-title animate-on-scroll">Lịch sử đơn hàng</h1>
    
    <?php if ($orders_result->num_rows > 0): ?>
        <div class="order-history animate-on-scroll">
            <div class="order-filters">
                <div class="filter-group">
                    <label for="status-filter">Trạng thái:</label>
                    <select id="status-filter" class="filter-select">
                        <option value="">Tất cả</option>
                        <option value="pending">Chờ xử lý</option>
                        <option value="confirmed">Đã xác nhận</option>
                        <option value="shipping">Đang giao</option>
                        <option value="completed">Hoàn thành</option>
                        <option value="cancelled">Đã hủy</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="payment-filter">Thanh toán:</label>
                    <select id="payment-filter" class="filter-select">
                        <option value="">Tất cả</option>
                        <option value="cod">COD</option>
                        <option value="bank">Chuyển khoản</option>
                        <option value="card">Thẻ tín dụng</option>
                        <option value="momo">Ví điện tử</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="date-filter">Thời gian:</label>
                    <select id="date-filter" class="filter-select">
                        <option value="">Tất cả</option>
                        <option value="today">Hôm nay</option>
                        <option value="week">Tuần này</option>
                        <option value="month">Tháng này</option>
                        <option value="year">Năm nay</option>
                    </select>
                </div>
            </div>
            
            <div class="order-table-container">
                <table class="order-history-table">
                    <thead>
                        <tr>
                            <th>Mã đơn hàng</th>
                            <th>Ngày đặt</th>
                            <th>Tổng tiền</th>
                            <th>Phương thức TT</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($order = $orders_result->fetch_assoc()): ?>
                            <tr class="order-row" data-status="<?php echo $order['status']; ?>" data-payment="<?php echo $order['payment_method']; ?>">
                                <td class="order-id">
                                    <span class="order-code">#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></span>
                                </td>
                                <td class="order-date">
                                    <?php echo date('d/m/Y', strtotime($order['created_at'])); ?>
                                    <span class="order-time"><?php echo date('H:i', strtotime($order['created_at'])); ?></span>
                                </td>
                                <td class="order-total">
                                    <?php echo number_format($order['total'], 0, ',', '.'); ?>đ
                                </td>
                                <td class="order-payment">
                                    <span class="payment-method"><?php echo $payment_map[$order['payment_method']] ?? $order['payment_method']; ?></span>
                                </td>
                                <td class="order-status">
                                    <span class="status-badge <?php echo $order['status']; ?>">
                                        <?php echo $status_map[$order['status']] ?? ucfirst($order['status']); ?>
                                    </span>
                                </td>
                                <td class="order-actions">
                                    <a href="order_detail.php?id=<?php echo $order['id']; ?>" class="btn btn-sm">Xem chi tiết</a>
                                    <?php if ($order['status'] === 'pending'): ?>
                                        <a href="cancel_order.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline cancel-order" 
                                           onclick="return confirm('Bạn có chắc chắn muốn hủy đơn hàng này?');">Hủy</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="pagination">
                <button class="page-btn disabled"><i class="fas fa-chevron-left"></i></button>
                <button class="page-btn active">1</button>
                <button class="page-btn">2</button>
                <button class="page-btn">3</button>
                <button class="page-btn"><i class="fas fa-chevron-right"></i></button>
            </div>
        </div>
    <?php else: ?>
        <div class="empty-state animate-on-scroll">
            <img src="assets/images/no-orders.png" alt="Không có đơn hàng">
            <h3>Bạn chưa có đơn hàng nào</h3>
            <p>Hãy mua sắm ngay để trải nghiệm dịch vụ tuyệt vời tại ShopPink</p>
            <a href="index.php" class="btn">Mua sắm ngay</a>
        </div>
    <?php endif; ?>
</div>

<?php include('includes/footer.php'); ?>

<script>
// Order filters
document.getElementById('status-filter')?.addEventListener('change', function() {
    filterOrders();
});

document.getElementById('payment-filter')?.addEventListener('change', function() {
    filterOrders();
});

document.getElementById('date-filter')?.addEventListener('change', function() {
    filterOrders();
});

function filterOrders() {
    const statusFilter = document.getElementById('status-filter').value;
    const paymentFilter = document.getElementById('payment-filter').value;
    const dateFilter = document.getElementById('date-filter').value;
    const orderRows = document.querySelectorAll('.order-row');
    
    orderRows.forEach(row => {
        let show = true;
        
        // Status filter
        if (statusFilter && row.dataset.status !== statusFilter) {
            show = false;
        }
        
        // Payment filter
        if (paymentFilter && row.dataset.payment !== paymentFilter) {
            show = false;
        }
        
        // Date filter (simplified for demo)
        if (dateFilter) {
            const orderDate = new Date(row.querySelector('.order-date').textContent);
            const today = new Date();
            
            switch (dateFilter) {
                case 'today':
                    show = orderDate.toDateString() === today.toDateString();
                    break;
                case 'week':
                    const weekAgo = new Date(today);
                    weekAgo.setDate(today.getDate() - 7);
                    show = orderDate >= weekAgo;
                    break;
                case 'month':
                    show = orderDate.getMonth() === today.getMonth() && orderDate.getFullYear() === today.getFullYear();
                    break;
                case 'year':
                    show = orderDate.getFullYear() === today.getFullYear();
                    break;
            }
        }
        
        row.style.display = show ? '' : 'none';
    });
    
    // Check if any orders are visible
    const visibleOrders = Array.from(orderRows).filter(row => row.style.display !== 'none');
    
    if (visibleOrders.length === 0) {
        // Show no results message
        if (!document.querySelector('.no-results')) {
            const noResults = document.createElement('div');
            noResults.className = 'no-results';
            noResults.innerHTML = `
                <div class="empty-state">
                    <img src="assets/images/no-results.png" alt="Không có kết quả">
                    <h3>Không có đơn hàng nào phù hợp với bộ lọc</h3>
                    <button class="btn" onclick="resetFilters()">Xóa bộ lọc</button>
                </div>
            `;
            document.querySelector('.order-table-container').after(noResults);
        }
    } else {
        // Remove no results message if it exists
        const noResults = document.querySelector('.no-results');
        if (noResults) {
            noResults.remove();
        }
    }
}

function resetFilters() {
    document.getElementById('status-filter').value = '';
    document.getElementById('payment-filter').value = '';
    document.getElementById('date-filter').value = '';
    
    document.querySelectorAll('.order-row').forEach(row => {
        row.style.display = '';
    });
    
    const noResults = document.querySelector('.no-results');
    if (noResults) {
        noResults.remove();
    }
}

// Cancel order confirmation
document.querySelectorAll('.cancel-order')?.forEach(btn => {
    btn.addEventListener('click', function(e) {
        if (!confirm('Bạn có chắc chắn muốn hủy đơn hàng này?')) {
            e.preventDefault();
        }
    });
});
</script>