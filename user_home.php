<?php
require_once 'includes/autoload.php';
// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = intval($_SESSION['user_id']);
// Lấy thông tin người dùng
$user_stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();
if (!$user) {
    header("Location: logout.php");
    exit();
}
// Lấy danh sách đơn hàng gần đây
$orders_stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 3");
$orders_stmt->bind_param("i", $user_id);
$orders_stmt->execute();
$orders_result = $orders_stmt->get_result();
// Lấy danh mục sản phẩm
$category_query = "SELECT * FROM categories WHERE parent_id = 0";
$category_result = mysqli_query($conn, $category_query);
// Lấy sản phẩm nổi bật
$featured_query = "SELECT * FROM products ORDER BY created_at DESC LIMIT 8";
$featured_result = mysqli_query($conn, $featured_query);
// Lấy sản phẩm theo sở thích
$preferred_products = [];
if (!empty($user['preferences'])) {
    $preferences = json_decode($user['preferences'], true);
    if (!empty($preferences)) {
        $pref_placeholders = implode(',', array_fill(0, count($preferences), '?'));
        $pref_types = str_repeat('i', count($preferences));
        $pref_query = "SELECT * FROM products WHERE category_id IN ($pref_placeholders) ORDER BY created_at DESC LIMIT 4";
        $pref_stmt = $conn->prepare($pref_query);
        $pref_stmt->bind_param($pref_types, ...$preferences);
        $pref_stmt->execute();
        $preferred_result = $pref_stmt->get_result();
        $preferred_products = $preferred_result->fetch_all(MYSQLI_ASSOC);
    }
}
// Lấy sản phẩm đã xem gần đây (giả lập)
$recently_viewed = [];
$recent_query = "SELECT * FROM products ORDER BY RAND() LIMIT 6";
$recent_result = mysqli_query($conn, $recent_query);
$recently_viewed = $recent_result->fetch_all(MYSQLI_ASSOC);
?>
<?php include('includes/header.php'); ?>

<div class="container">
    <!-- User Welcome Section -->
    <div class="user-welcome animate-on-scroll">
        <div class="user-avatar">
            <div class="avatar-circle">
                <?php if (!empty($user['avatar'])): ?>
                    <img src="assets/images/avatars/<?php echo htmlspecialchars($user['avatar']); ?>" alt="<?php echo htmlspecialchars($user['username']); ?>">
                <?php else: ?>
                    <i class="fas fa-user"></i>
                <?php endif; ?>
            </div>
        </div>
        <div class="user-info">
            <h1>Xin chào, <strong><?php echo htmlspecialchars($user['full_name'] ?? $user['username']); ?></strong> 👋</h1>
            <p>Khám phá các sản phẩm được đề xuất riêng cho bạn</p>
            <div class="user-stats">
                <div class="stat-item">
                    <i class="fas fa-shopping-bag"></i>
                    <span><?php echo rand(5, 20); ?></span>
                    <span>Đơn hàng</span>
                </div>
                <div class="stat-item">
                    <i class="fas fa-heart"></i>
                    <span><?php echo rand(10, 50); ?></span>
                    <span>Yêu thích</span>
                </div>
                <div class="stat-item">
                    <i class="fas fa-calendar-check"></i>
                    <span><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></span>
                    <span>Ngày tham gia</span>
                </div>
            </div>
        </div>
        <div class="user-actions">
            <a href="order_history.php" class="btn btn-outline">
                <i class="fas fa-receipt"></i> Lịch sử đơn hàng
            </a>
            <a href="edit_profile.php" class="btn btn-outline">
                <i class="fas fa-user-edit"></i> Chỉnh sửa hồ sơ
            </a>
        </div>
    </div>
    
    <!-- Recent Orders -->
    <?php if ($orders_result->num_rows > 0): ?>
    <div class="user-section animate-on-scroll">
        <div class="section-header">
            <h2>Đơn hàng gần đây</h2>
            <a href="order_history.php" class="view-all">Xem tất cả</a>
        </div>
        <div class="recent-orders">
            <?php while ($order = $orders_result->fetch_assoc()): ?>
                <div class="order-card">
                    <div class="order-info">
                        <div class="order-number">#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></div>
                        <div class="order-date"><?php echo date('d/m/Y', strtotime($order['created_at'])); ?></div>
                    </div>
                    <div class="order-status">
                        <span class="status-badge <?php echo $order['status']; ?>">
                            <?php 
                            switch ($order['status']) {
                                case 'pending': echo 'Chờ xử lý'; break;
                                case 'confirmed': echo 'Đã xác nhận'; break;
                                case 'shipping': echo 'Đang giao'; break;
                                case 'completed': echo 'Hoàn thành'; break;
                                case 'cancelled': echo 'Đã hủy'; break;
                                default: echo ucfirst($order['status']);
                            }
                            ?>
                        </span>
                    </div>
                    <div class="order-total"><?php echo number_format($order['total'], 0, ',', '.'); ?>đ</div>
                    <div class="order-actions">
                        <a href="order_detail.php?id=<?php echo $order['id']; ?>" class="btn btn-sm">Xem chi tiết</a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Recommended Products -->
    <?php if (!empty($preferred_products)): ?>
    <div class="user-section animate-on-scroll">
        <div class="section-header">
            <h2>Gợi ý cho bạn</h2>
            <a href="category.php" class="view-all">Xem tất cả</a>
        </div>
        <div class="products-grid">
            <?php foreach ($preferred_products as $product): ?>
                <div class="product" data-category="<?php echo $product['category_id']; ?>" data-price="<?php echo $product['price']; ?>">
                    <?php if (!empty($product['old_price']) && $product['old_price'] > $product['price']): ?>
                        <div class="product-badge sale">-<?php echo round(($product['old_price'] - $product['price']) / $product['old_price'] * 100); ?>%</div>
                    <?php endif; ?>
                    
                    <div class="product-img" style="background-image: url('assets/images/products/<?php echo htmlspecialchars($product['image'] ?? 'default.jpg'); ?>');"></div>
                    <div class="product-actions">
                        <button class="quick-view-btn" title="Xem nhanh"><i class="fas fa-search"></i></button>
                        <button class="compare-btn" title="So sánh"><i class="fas fa-exchange-alt"></i></button>
                        <button class="wishlist-btn" title="Yêu thích"><i class="far fa-heart"></i></button>
                    </div>
                    <div class="product-info">
                        <div class="product-vendor"><?php echo htmlspecialchars($product['category_name'] ?? ''); ?></div>
                        <div class="product-title"><?php echo htmlspecialchars($product['name']); ?></div>
                        <div class="product-price">
                            <span class="current-price"><?php echo number_format($product['price'], 0, ',', '.'); ?>đ</span>
                            <?php if (!empty($product['old_price']) && $product['old_price'] > $product['price']): ?>
                                <span class="old-price"><?php echo number_format($product['old_price'], 0, ',', '.'); ?>đ</span>
                            <?php endif; ?>
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
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Categories -->
    <div class="user-section animate-on-scroll">
        <div class="section-header">
            <h2>Danh mục sản phẩm</h2>
            <a href="category.php" class="view-all">Xem tất cả</a>
        </div>
        <div class="categories">
            <?php 
            mysqli_data_seek($category_result, 0);
            while ($category = mysqli_fetch_assoc($category_result)): 
            ?>
                <div class="category">
                    <i class="fas fa-tshirt"></i>
                    <h3><?php echo htmlspecialchars($category['name']); ?></h3>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
    
    <!-- Featured Products -->
    <div class="user-section animate-on-scroll">
        <div class="section-header">
            <h2>Sản phẩm nổi bật</h2>
            <a href="category.php" class="view-all">Xem tất cả</a>
        </div>
        <div class="products-grid">
            <?php 
            mysqli_data_seek($featured_result, 0);
            while ($product = mysqli_fetch_assoc($featured_result)): 
            ?>
                <div class="product" data-category="<?php echo $product['category_id']; ?>" data-price="<?php echo $product['price']; ?>">
                    <?php if (!empty($product['old_price']) && $product['old_price'] > $product['price']): ?>
                        <div class="product-badge new">Mới</div>
                    <?php endif; ?>
                    
                    <div class="product-img" style="background-image: url('assets/images/products/<?php echo htmlspecialchars($product['image'] ?? 'default.jpg'); ?>');"></div>
                    <div class="product-actions">
                        <button class="quick-view-btn" title="Xem nhanh"><i class="fas fa-search"></i></button>
                        <button class="compare-btn" title="So sánh"><i class="fas fa-exchange-alt"></i></button>
                        <button class="wishlist-btn" title="Yêu thích"><i class="far fa-heart"></i></button>
                    </div>
                    <div class="product-info">
                        <div class="product-vendor"><?php echo htmlspecialchars($product['category_name'] ?? ''); ?></div>
                        <div class="product-title"><?php echo htmlspecialchars($product['name']); ?></div>
                        <div class="product-price">
                            <span class="current-price"><?php echo number_format($product['price'], 0, ',', '.'); ?>đ</span>
                            <?php if (!empty($product['old_price']) && $product['old_price'] > $product['price']): ?>
                                <span class="old-price"><?php echo number_format($product['old_price'], 0, ',', '.'); ?>đ</span>
                            <?php endif; ?>
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
    
    <!-- Recently Viewed -->
    <div class="user-section animate-on-scroll">
        <div class="section-header">
            <h2>Sản phẩm đã xem</h2>
            <a href="category.php" class="view-all">Xem tất cả</a>
        </div>
        <div class="recently-viewed-grid">
            <?php foreach ($recently_viewed as $product): ?>
                <div class="recently-viewed-item">
                    <div class="recently-viewed-img" style="background-image: url('assets/images/products/<?php echo htmlspecialchars($product['image'] ?? 'default.jpg'); ?>');"></div>
                    <div class="recently-viewed-info">
                        <div class="recently-viewed-title"><?php echo htmlspecialchars($product['name']); ?></div>
                        <div class="recently-viewed-price"><?php echo number_format($product['price'], 0, ',', '.'); ?>đ</div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>