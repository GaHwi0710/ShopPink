<?php include 'views/layouts/header.php'; ?>

<div class="container">
    <div class="user-dashboard">
        <div class="user-sidebar">
            <div class="user-profile-card">
                <div class="user-avatar">
                    <?php if (!empty($user['avatar'])): ?>
                        <img src="<?php echo $user['avatar']; ?>" alt="<?php echo $user['full_name']; ?>">
                    <?php else: ?>
                        <i class="fas fa-user"></i>
                    <?php endif; ?>
                </div>
                <h3><?php echo $user['full_name']; ?></h3>
                <p><?php echo $user['email']; ?></p>
                <a href="/profile" class="btn-edit-profile">Chỉnh sửa hồ sơ</a>
            </div>
            
            <div class="user-menu">
                <h3>Tài khoản của tôi</h3>
                <ul>
                    <li><a href="/user/dashboard" class="active"><i class="fas fa-tachometer-alt"></i> Tổng quan</a></li>
                    <li><a href="/orders"><i class="fas fa-shopping-bag"></i> Đơn hàng của tôi</a></li>
                    <li><a href="/wishlist"><i class="fas fa-heart"></i> Sản phẩm yêu thích</a></li>
                    <li><a href="/addresses"><i class="fas fa-map-marker-alt"></i> Sổ địa chỉ</a></li>
                    <li><a href="/vouchers"><i class="fas fa-ticket-alt"></i> Voucher của tôi</a></li>
                    <li><a href="/notifications"><i class="fas fa-bell"></i> Thông báo</a></li>
                    <li><a href="/view-history"><i class="fas fa-history"></i> Lịch sử xem</a></li>
                    <li><a href="/profile"><i class="fas fa-user-cog"></i> Cài đặt tài khoản</a></li>
                </ul>
            </div>
        </div>
        
        <div class="user-content">
            <div class="dashboard-header">
                <h1>Tổng quan tài khoản</h1>
                <p>Chào mừng bạn trở lại, <?php echo $user['full_name']; ?>!</p>
            </div>
            
            <div class="dashboard-stats">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo count($orders); ?></h3>
                        <p>Đơn hàng</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo count($wishlist); ?></h3>
                        <p>Sản phẩm yêu thích</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo count($addresses); ?></h3>
                        <p>Địa chỉ</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-ticket-alt"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo count($vouchers); ?></h3>
                        <p>Voucher</p>
                    </div>
                </div>
            </div>
            
            <div class="dashboard-sections">
                <div class="dashboard-section recent-orders">
                    <div class="section-header">
                        <h2>Đơn hàng gần đây</h2>
                        <a href="/orders" class="view-all">Xem tất cả</a>
                    </div>
                    
                    <?php if (empty($recentOrders)): ?>
                        <div class="empty-state">
                            <i class="fas fa-shopping-bag"></i>
                            <h4>Bạn chưa có đơn hàng nào</h4>
                            <p>Hãy khám phá các sản phẩm của chúng tôi và đặt hàng ngay.</p>
                            <a href="/" class="btn">Khám phá ngay</a>
                        </div>
                    <?php else: ?>
                        <div class="orders-list">
                            <?php foreach ($recentOrders as $order): ?>
                                <div class="order-item">
                                    <div class="order-info">
                                        <div class="order-code">#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></div>
                                        <div class="order-date"><?php echo date('d/m/Y', strtotime($order['created_at'])); ?></div>
                                        <div class="order-status status-<?php echo $order['status']; ?>">
                                            <?php
                                            switch ($order['status']) {
                                                case 'pending': echo 'Chờ xử lý'; break;
                                                case 'processing': echo 'Đang xử lý'; break;
                                                case 'shipped': echo 'Đang giao'; break;
                                                case 'delivered': echo 'Đã giao'; break;
                                                case 'cancelled': echo 'Đã hủy'; break;
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    <div class="order-total">
                                        <?php echo number_format($order['total_amount'], 0, ',', '.'); ?>đ
                                    </div>
                                    <div class="order-actions">
                                        <a href="/orders/<?php echo $order['id']; ?>" class="btn-view">Xem chi tiết</a>
                                        
                                        <?php if ($order['status'] === 'pending'): ?>
                                            <a href="/cancel-order/<?php echo $order['id']; ?>" class="btn-cancel" onclick="return confirm('Bạn có chắc chắn muốn hủy đơn hàng này?');">Hủy đơn</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="dashboard-section recommended-products">
                    <div class="section-header">
                        <h2>Gợi ý cho bạn</h2>
                        <a href="/" class="view-all">Xem tất cả</a>
                    </div>
                    
                    <div class="products-grid">
                        <?php foreach ($recommendedProducts as $product): ?>
                            <div class="product">
                                <div class="product-img" style="background-image: url('<?php echo $product['image'] ?: 'https://via.placeholder.com/300x300/f8bbd0/ffffff?text=Product'; ?>');"></div>
                                <div class="product-info">
                                    <div class="product-title"><?php echo $product['name']; ?></div>
                                    <div class="product-price">
                                        <span class="current-price"><?php echo number_format($product['price'], 0, ',', '.'); ?>đ</span>
                                    </div>
                                    <div class="product-footer">
                                        <a href="/products/<?php echo $product['id']; ?>" class="btn">Xem chi tiết</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.user-dashboard {
    display: flex;
    gap: 30px;
    margin-top: 30px;
}

.user-sidebar {
    width: 280px;
    flex-shrink: 0;
}

.user-profile-card {
    background: white;
    border-radius: 15px;
    padding: 25px;
    text-align: center;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    margin-bottom: 20px;
}

.user-avatar {
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

.user-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.user-avatar i {
    font-size: 40px;
    color: var(--primary-color);
}

.user-profile-card h3 {
    font-size: 18px;
    margin-bottom: 5px;
    color: var(--dark-color);
}

.user-profile-card p {
    font-size: 14px;
    color: var(--gray);
    margin-bottom: 20px;
}

.btn-edit-profile {
    display: inline-block;
    padding: 8px 20px;
    background: var(--light-color);
    color: var(--primary-color);
    border-radius: 20px;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
}

.btn-edit-profile:hover {
    background: var(--primary-color);
    color: white;
}

.user-menu {
    background: white;
    border-radius: 15px;
    padding: 20px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.user-menu h3 {
    font-size: 16px;
    margin-bottom: 15px;
    color: var(--dark-color);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.user-menu ul {
    list-style: none;
}

.user-menu li {
    margin-bottom: 5px;
}

.user-menu li a {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 15px;
    color: var(--gray-dark);
    text-decoration: none;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.user-menu li a:hover,
.user-menu li a.active {
    background: var(--light-color);
    color: var(--primary-color);
}

.user-menu li a i {
    width: 20px;
    text-align: center;
}

.user-content {
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

.orders-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.order-item {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.order-info {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.order-code {
    font-weight: 600;
    color: var(--dark-color);
}

.order-date {
    font-size: 14px;
    color: var(--gray);
}

.order-status {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.status-pending {
    background: #fff8e1;
    color: #ff9800;
}

.status-processing {
    background: #e3f2fd;
    color: #2196f3;
}

.status-shipped {
    background: #e8f5e9;
    color: #4caf50;
}

.status-delivered {
    background: #f3e5f5;
    color: #9c27b0;
}

.status-cancelled {
    background: #ffebee;
    color: #f44336;
}

.order-total {
    font-weight: 600;
    color: var(--dark-color);
}

.order-actions {
    display: flex;
    gap: 10px;
}

.btn-view,
.btn-cancel {
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
}

.btn-view {
    background: var(--light-color);
    color: var(--primary-color);
}

.btn-view:hover {
    background: var(--primary-color);
    color: white;
}

.btn-cancel {
    background: #ffebee;
    color: #f44336;
}

.btn-cancel:hover {
    background: #f44336;
    color: white;
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
}

.product {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
}

.product:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.12);
}

.product-img {
    height: 180px;
    background-size: cover;
    background-position: center;
}

.product-info {
    padding: 15px;
}

.product-title {
    font-size: 14px;
    margin-bottom: 10px;
    height: 40px;
    overflow: hidden;
    font-weight: 600;
    color: var(--dark-color);
    line-height: 1.4;
}

.product-price {
    font-size: 16px;
    font-weight: bold;
    color: var(--primary-color);
    margin-bottom: 15px;
}

.product-footer .btn {
    display: block;
    width: 100%;
    padding: 8px 0;
    background: var(--primary-color);
    color: white;
    text-align: center;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
}

.product-footer .btn:hover {
    background: var(--secondary-color);
}

@media (max-width: 1200px) {
    .products-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 992px) {
    .user-dashboard {
        flex-direction: column;
    }
    
    .user-sidebar {
        width: 100%;
    }
    
    .dashboard-stats {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .products-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 768px) {
    .dashboard-stats {
        grid-template-columns: 1fr;
    }
    
    .order-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
    
    .order-actions {
        width: 100%;
        justify-content: flex-end;
    }
    
    .products-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>

<?php include 'views/layouts/footer.php'; ?>