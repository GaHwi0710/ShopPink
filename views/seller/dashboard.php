<?php include 'views/layouts/header.php'; ?>

<div class="container">
    <div class="seller-dashboard">
        <div class="seller-sidebar">
            <div class="seller-profile-card">
                <div class="seller-avatar">
                    <?php if (!empty($seller['avatar'])): ?>
                        <img src="<?php echo $seller['avatar']; ?>" alt="<?php echo $seller['full_name']; ?>">
                    <?php else: ?>
                        <i class="fas fa-store"></i>
                    <?php endif; ?>
                </div>
                <h3><?php echo $seller['full_name']; ?></h3>
                <p>Người bán</p>
                <div class="seller-status">
                    <span class="status-badge active">Hoạt động</span>
                </div>
            </div>
            
            <div class="seller-menu">
                <h3>Kênh người bán</h3>
                <ul>
                    <li><a href="/seller/dashboard" class="active"><i class="fas fa-tachometer-alt"></i> Tổng quan</a></li>
                    <li><a href="/seller/products"><i class="fas fa-box"></i> Quản lý sản phẩm</a></li>
                    <li><a href="/seller/add-product"><i class="fas fa-plus-circle"></i> Thêm sản phẩm</a></li>
                    <li><a href="/seller/orders"><i class="fas fa-shopping-bag"></i> Quản lý đơn hàng</a></li>
                    <li><a href="/seller/revenue"><i class="fas fa-chart-line"></i> Doanh thu</a></li>
                    <li><a href="/seller/settings"><i class="fas fa-cog"></i> Cài đặt</a></li>
                </ul>
            </div>
        </div>
        
        <div class="seller-content">
            <div class="dashboard-header">
                <h1>Tổng quan cửa hàng</h1>
                <p>Chào mừng bạn trở lại, <?php echo $seller['full_name']; ?>!</p>
            </div>
            
            <div class="dashboard-stats">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['total_products']; ?></h3>
                        <p>Sản phẩm</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['total_orders']; ?></h3>
                        <p>Đơn hàng</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['total_revenue'], 0, ',', '.'); ?>đ</h3>
                        <p>Doanh thu</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['pending_orders']; ?></h3>
                        <p>Chờ xử lý</p>
                    </div>
                </div>
            </div>
            
            <div class="dashboard-sections">
                <div class="dashboard-section recent-orders">
                    <div class="section-header">
                        <h2>Đơn hàng mới</h2>
                        <a href="/seller/orders" class="view-all">Xem tất cả</a>
                    </div>
                    
                    <?php if (empty($recentOrders)): ?>
                        <div class="empty-state">
                            <i class="fas fa-shopping-bag"></i>
                            <h4>Bạn chưa có đơn hàng nào</h4>
                            <p>Đơn hàng của bạn sẽ hiển thị ở đây khi khách hàng đặt mua.</p>
                        </div>
                    <?php else: ?>
                        <div class="orders-table">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Mã đơn hàng</th>
                                        <th>Khách hàng</th>
                                        <th>Tổng tiền</th>
                                        <th>Ngày đặt</th>
                                        <th>Trạng thái</th>
                                        <th>Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentOrders as $order): ?>
                                        <tr>
                                            <td>#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                            <td><?php echo $order['customer_name']; ?></td>
                                            <td><?php echo number_format($order['total_amount'], 0, ',', '.'); ?>đ</td>
                                            <td><?php echo date('d/m/Y', strtotime($order['created_at'])); ?></td>
                                            <td>
                                                <span class="status-badge <?php echo $order['status']; ?>">
                                                    <?php
                                                    switch ($order['status']) {
                                                        case 'pending': echo 'Chờ xử lý'; break;
                                                        case 'processing': echo 'Đang xử lý'; break;
                                                        case 'shipped': echo 'Đang giao'; break;
                                                        case 'delivered': echo 'Đã giao'; break;
                                                        case 'cancelled': echo 'Đã hủy'; break;
                                                    }
                                                    ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="/seller/orders/<?php echo $order['id']; ?>" class="btn-view">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                
                                                <?php if ($order['status'] === 'pending'): ?>
                                                    <form action="/seller/update-order-status" method="POST" style="display: inline;">
                                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                        <input type="hidden" name="status" value="processing">
                                                        <button type="submit" class="btn-edit" title="Xác nhận">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                                
                                                <?php if ($order['status'] === 'processing'): ?>
                                                    <form action="/seller/update-order-status" method="POST" style="display: inline;">
                                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                        <input type="hidden" name="status" value="shipped">
                                                        <button type="submit" class="btn-edit" title="Giao hàng">
                                                            <i class="fas fa-shipping-fast"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                                
                                                <?php if ($order['status'] === 'shipped'): ?>
                                                    <form action="/seller/update-order-status" method="POST" style="display: inline;">
                                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                        <input type="hidden" name="status" value="delivered">
                                                        <button type="submit" class="btn-edit" title="Đã giao">
                                                            <i class="fas fa-check-double"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="dashboard-section top-products">
                    <div class="section-header">
                        <h2>Sản phẩm bán chạy</h2>
                        <a href="/seller/products" class="view-all">Xem tất cả</a>
                    </div>
                    
                    <?php if (empty($topProducts)): ?>
                        <div class="empty-state">
                            <i class="fas fa-box"></i>
                            <h4>Bạn chưa có sản phẩm nào</h4>
                            <p>Hãy thêm sản phẩm để bắt đầu kinh doanh.</p>
                            <a href="/seller/add-product" class="btn">Thêm sản phẩm</a>
                        </div>
                    <?php else: ?>
                        <div class="top-products-list">
                            <?php foreach ($topProducts as $product): ?>
                                <div class="top-product-item">
                                    <div class="product-img">
                                        <img src="<?php echo $product['image'] ?: 'https://via.placeholder.com/80x80/f8bbd0/ffffff?text=Product'; ?>" alt="<?php echo $product['name']; ?>">
                                    </div>
                                    <div class="product-info">
                                        <h4><?php echo $product['name']; ?></h4>
                                        <div class="product-price"><?php echo number_format($product['price'], 0, ',', '.'); ?>đ</div>
                                        <div class="product-stats">
                                            <span>Đã bán: <?php echo $product['sold_count'] ?? 0; ?></span>
                                            <span>Tồn kho: <?php echo $product['stock']; ?></span>
                                        </div>
                                    </div>
                                    <div class="product-actions">
                                        <a href="/seller/edit-product/<?php echo $product['id']; ?>" class="btn-edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="dashboard-section revenue-chart">
                    <div class="section-header">
                        <h2>Biểu đồ doanh thu</h2>
                        <div class="chart-filter">
                            <select id="revenue-period">
                                <option value="7">7 ngày qua</option>
                                <option value="30" selected>30 ngày qua</option>
                                <option value="90">90 ngày qua</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="chart-container">
                        <canvas id="revenue-chart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Revenue chart
    const ctx = document.getElementById('revenue-chart').getContext('2d');
    const revenueChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['01/01', '05/01', '10/01', '15/01', '20/01', '25/01', '30/01'],
            datasets: [{
                label: 'Doanh thu (đ)',
                data: [1200000, 1900000, 1500000, 2500000, 2200000, 3000000, 2800000],
                borderColor: '#e91e63',
                backgroundColor: 'rgba(233, 30, 99, 0.1)',
                borderWidth: 2,
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString('vi-VN') + 'đ';
                        }
                    }
                }
            }
        }
    });
    
    // Revenue period filter
    const revenuePeriod = document.getElementById('revenue-period');
    if (revenuePeriod) {
        revenuePeriod.addEventListener('change', function() {
            // In a real application, you would fetch new data based on the selected period
            // For demo purposes, we'll just show a toast
            showToast('info', 'Đã cập nhật biểu đồ theo ' + this.options[this.selectedIndex].text);
        });
    }
});
</script>

<style>
.seller-dashboard {
    display: flex;
    gap: 30px;
    margin-top: 30px;
}

.seller-sidebar {
    width: 280px;
    flex-shrink: 0;
}

.seller-profile-card {
    background: white;
    border-radius: 15px;
    padding: 25px;
    text-align: center;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    margin-bottom: 20px;
}

.seller-avatar {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    margin: 0 auto 15px;
    overflow: hidden;
    background: var(--light-color);
    display: flex;
    align-items: center;
    justify-content: center;
}

.seller-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.seller-avatar i {
    font-size: 40px;
    color: var(--primary-color);
}

.seller-profile-card h3 {
    font-size: 18px;
    margin-bottom: 5px;
    color: var(--dark-color);
}

.seller-profile-card p {
    font-size: 14px;
    color: var(--gray);
    margin-bottom: 15px;
}

.seller-status {
    margin-top: 10px;
}

.status-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.status-badge.active {
    background: #e8f5e9;
    color: #4caf50;
}

.seller-menu {
    background: white;
    border-radius: 15px;
    padding: 20px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.seller-menu h3 {
    font-size: 16px;
    margin-bottom: 15px;
    color: var(--dark-color);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.seller-menu ul {
    list-style: none;
}

.seller-menu li {
    margin-bottom: 5px;
}

.seller-menu li a {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 15px;
    color: var(--gray-dark);
    text-decoration: none;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.seller-menu li a:hover,
.seller-menu li a.active {
    background: var(--light-color);
    color: var(--primary-color);
}

.seller-menu li a i {
    width: 20px;
    text-align: center;
}

.seller-content {
    flex: 1;
}

.dashboard-header {
    margin-bottom: 30px;
}

.dashboard-header h1 {
    font-size: 28px;
    margin-bottom: 10px;
    color: var(--dark-color);
}

.dashboard-header p {
    color: var(--gray);
}

.dashboard-stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    display: flex;
    align-items: center;
    gap: 20px;
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: white;
}

.stat-card:nth-child(1) .stat-icon {
    background: linear-gradient(135deg, #667eea, #764ba2);
}

.stat-card:nth-child(2) .stat-icon {
    background: linear-gradient(135deg, #f093fb, #f5576c);
}

.stat-card:nth-child(3) .stat-icon {
    background: linear-gradient(135deg, #4facfe, #00f2fe);
}

.stat-card:nth-child(4) .stat-icon {
    background: linear-gradient(135deg, #43e97b, #38f9d7);
}

.stat-info h3 {
    font-size: 24px;
    margin-bottom: 5px;
    color: var(--dark-color);
}

.stat-info p {
    font-size: 14px;
    color: var(--gray);
}

.dashboard-sections {
    display: grid;
    grid-template-columns: 1fr;
    gap: 30px;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.section-header h2 {
    font-size: 20px;
    color: var(--dark-color);
}

.view-all {
    font-size: 14px;
    color: var(--primary-color);
    text-decoration: none;
    font-weight: 600;
}

.view-all:hover {
    text-decoration: underline;
}

.chart-filter {
    display: flex;
    align-items: center;
}

.chart-filter select {
    padding: 8px 15px;
    border: 1px solid #ddd;
    border-radius: 5px;
    background: white;
    font-size: 14px;
    cursor: pointer;
}

.orders-table {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.orders-table table {
    width: 100%;
    border-collapse: collapse;
}

.orders-table th,
.orders-table td {
    padding: 15px;
    text-align: left;
    border-bottom: 1px solid #f0f0f0;
}

.orders-table th {
    background: var(--light-color);
    font-weight: 600;
    color: var(--dark-color);
}

.orders-table tr:last-child td {
    border-bottom: none;
}

.status-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.status-badge.pending {
    background: #fff8e1;
    color: #ff9800;
}

.status-badge.processing {
    background: #e3f2fd;
    color: #2196f3;
}

.status-badge.shipped {
    background: #e8f5e9;
    color: #4caf50;
}

.status-badge.delivered {
    background: #f3e5f5;
    color: #9c27b0;
}

.status-badge.cancelled {
    background: #ffebee;
    color: #f44336;
}

.btn-view,
.btn-edit {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--light-color);
    color: var(--primary-color);
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-right: 5px;
}

.btn-view:hover,
.btn-edit:hover {
    background: var(--primary-color);
    color: white;
}

.top-products-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.top-product-item {
    background: white;
    border-radius: 12px;
    padding: 15px;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
    display: flex;
    align-items: center;
    gap: 15px;
}

.product-img {
    width: 60px;
    height: 60px;
    border-radius: 8px;
    overflow: hidden;
}

.product-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.product-info {
    flex: 1;
}

.product-info h4 {
    font-size: 16px;
    margin-bottom: 5px;
    color: var(--dark-color);
}

.product-price {
    font-size: 14px;
    font-weight: 600;
    color: var(--primary-color);
    margin-bottom: 5px;
}

.product-stats {
    display: flex;
    gap: 15px;
    font-size: 12px;
    color: var(--gray);
}

.chart-container {
    background: white;
    border-radius: 15px;
    padding: 20px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    height: 300px;
}

@media (max-width: 1200px) {
    .dashboard-stats {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 992px) {
    .seller-dashboard {
        flex-direction: column;
    }
    
    .seller-sidebar {
        width: 100%;
    }
    
    .dashboard-stats {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .dashboard-stats {
        grid-template-columns: 1fr;
    }
    
    .orders-table {
        overflow-x: auto;
    }
    
    .orders-table table {
        min-width: 600px;
    }
    
    .chart-container {
        height: 250px;
    }
}
</style>

<?php include 'views/layouts/footer.php'; ?>