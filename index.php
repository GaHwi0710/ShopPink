<?php
require_once 'includes/autoload.php';

// Lấy danh mục
$categories_stmt = $conn->prepare("
    SELECT c.*, COUNT(p.id) as product_count 
    FROM categories c 
    LEFT JOIN products p ON c.id = p.category_id AND p.status = 'active'
    WHERE c.status = 'active' AND c.parent_id IS NULL
    GROUP BY c.id 
    ORDER BY c.sort_order, c.name
");
$categories_stmt->execute();
$categories = $categories_stmt->get_result();

// Lấy sản phẩm nổi bật
$featured_products = getFeaturedProducts(8);

// Lấy sản phẩm bán chạy
$bestseller_products = getBestsellerProducts(8);

// Lấy sản phẩm gần đây nếu user đã đăng nhập
$recent_products = null;
if (is_logged_in()) {
    $recent_products = getRecentlyViewedProducts($_SESSION['user_id'], 4);
}

$page_title = 'Trang chủ';
include('includes/header.php');
?>

<div class="hero-section">
    <div class="hero-slider">
        <div class="hero-slide active">
            <div class="hero-content">
                <h1>Chào mừng đến với ShopPink</h1>
                <p>Khám phá bộ sưu tập mỹ phẩm chất lượng cao với giá cả hợp lý</p>
                <a href="products.php" class="btn btn-primary btn-large">
                    <i class="fas fa-shopping-bag"></i> Mua sắm ngay
                </a>
            </div>
            <div class="hero-image">
                <img src="assets/images/hero-1.jpg" alt="Mỹ phẩm chất lượng cao">
            </div>
        </div>
    </div>
</div>

<div class="container">
    <!-- Danh mục sản phẩm -->
    <section class="categories-section">
        <h2 class="section-title">
            <i class="fas fa-th-large"></i> Danh mục sản phẩm
        </h2>
        
        <div class="categories-grid">
            <?php while ($category = $categories->fetch_assoc()): ?>
                <div class="category-card">
                    <div class="category-image">
                        <img src="assets/images/categories/<?php echo htmlspecialchars($category['image'] ?? 'default.jpg'); ?>" 
                             alt="<?php echo htmlspecialchars($category['name']); ?>">
                    </div>
                    
                    <div class="category-info">
                        <h3><?php echo htmlspecialchars($category['name']); ?></h3>
                        <p><?php echo $category['product_count']; ?> sản phẩm</p>
                        <a href="products.php?category=<?php echo $category['id']; ?>" class="btn btn-outline-primary">
                            Xem tất cả
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </section>
    
    <!-- Sản phẩm nổi bật -->
    <section class="featured-section">
        <h2 class="section-title">
            <i class="fas fa-star"></i> Sản phẩm nổi bật
        </h2>
        
        <div class="products-grid">
            <?php while ($product = $featured_products->fetch_assoc()): ?>
                <div class="product-card">
                    <div class="product-image">
                        <a href="product_detail.php?id=<?php echo $product['id']; ?>">
                            <img src="assets/images/products/<?php echo htmlspecialchars($product['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>">
                        </a>
                        
                        <?php if ($product['discount'] > 0): ?>
                            <div class="discount-badge">
                                -<?php echo $product['discount']; ?>%
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="product-info">
                        <h3 class="product-name">
                            <a href="product_detail.php?id=<?php echo $product['id']; ?>">
                                <?php echo htmlspecialchars($product['name']); ?>
                            </a>
                        </h3>
                        
                        <div class="product-category">
                            <a href="products.php?category=<?php echo $product['category_id']; ?>">
                                <?php echo htmlspecialchars($product['category_name']); ?>
                            </a>
                        </div>
                        
                        <div class="product-rating">
                            <div class="stars">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star <?php echo $i <= $product['avg_rating'] ? 'active' : ''; ?>"></i>
                                <?php endfor; ?>
                            </div>
                        </div>
                        
                        <div class="product-price">
                            <?php if ($product['discount'] > 0): ?>
                                <span class="old-price"><?php echo format_price($product['price']); ?></span>
                                <span class="new-price">
                                    <?php echo format_price($product['price'] * (1 - $product['discount'] / 100)); ?>
                                </span>
                            <?php else: ?>
                                <span class="price"><?php echo format_price($product['price']); ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="product-actions">
                            <button class="btn btn-primary btn-block add-to-cart-btn" 
                                    onclick="addToCart(<?php echo $product['id']; ?>)">
                                <i class="fas fa-shopping-cart"></i> Thêm vào giỏ hàng
                            </button>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
        
        <div class="section-actions">
            <a href="products.php?featured=1" class="btn btn-outline-primary">
                <i class="fas fa-star"></i> Xem tất cả sản phẩm nổi bật
            </a>
        </div>
    </section>
    
    <!-- Sản phẩm bán chạy -->
    <section class="bestseller-section">
        <h2 class="section-title">
            <i class="fas fa-fire"></i> Sản phẩm bán chạy
        </h2>
        
        <div class="products-grid">
            <?php while ($product = $bestseller_products->fetch_assoc()): ?>
                <div class="product-card">
                    <div class="product-image">
                        <a href="product_detail.php?id=<?php echo $product['id']; ?>">
                            <img src="assets/images/products/<?php echo htmlspecialchars($product['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>">
                        </a>
                        
                        <?php if ($product['discount'] > 0): ?>
                            <div class="discount-badge">
                                -<?php echo $product['discount']; ?>%
                            </div>
                        <?php endif; ?>
                        
                        <div class="bestseller-badge">
                            <i class="fas fa-fire"></i> Bán chạy
                        </div>
                    </div>
                    
                    <div class="product-info">
                        <h3 class="product-name">
                            <a href="product_detail.php?id=<?php echo $product['id']; ?>">
                                <?php echo htmlspecialchars($product['name']); ?>
                            </a>
                        </h3>
                        
                        <div class="product-category">
                            <a href="products.php?category=<?php echo $product['category_id']; ?>">
                                <?php echo htmlspecialchars($product['category_name']); ?>
                            </a>
                        </div>
                        
                        <div class="product-rating">
                            <div class="stars">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star <?php echo $i <= $product['avg_rating'] ? 'active' : ''; ?>"></i>
                                <?php endfor; ?>
                            </div>
                        </div>
                        
                        <div class="product-price">
                            <?php if ($product['discount'] > 0): ?>
                                <span class="old-price"><?php echo format_price($product['price']); ?></span>
                                <span class="new-price">
                                    <?php echo format_price($product['price'] * (1 - $product['discount'] / 100)); ?>
                                </span>
                            <?php else: ?>
                                <span class="price"><?php echo format_price($product['price']); ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="product-actions">
                            <button class="btn btn-primary btn-block add-to-cart-btn" 
                                    onclick="addToCart(<?php echo $product['id']; ?>)">
                                <i class="fas fa-shopping-cart"></i> Thêm vào giỏ hàng
                            </button>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
        
        <div class="section-actions">
            <a href="products.php?bestseller=1" class="btn btn-outline-primary">
                <i class="fas fa-fire"></i> Xem tất cả sản phẩm bán chạy
            </a>
        </div>
    </section>
    
    <!-- Sản phẩm gần đây (nếu user đã đăng nhập) -->
    <?php if ($recent_products && $recent_products->num_rows > 0): ?>
        <section class="recent-section">
            <h2 class="section-title">
                <i class="fas fa-clock"></i> Sản phẩm gần đây
            </h2>
            
            <div class="products-grid">
                <?php while ($product = $recent_products->fetch_assoc()): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <a href="product_detail.php?id=<?php echo $product['id']; ?>">
                                <img src="assets/images/products/<?php echo htmlspecialchars($product['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>">
                            </a>
                        </div>
                        
                        <div class="product-info">
                            <h3 class="product-name">
                                <a href="product_detail.php?id=<?php echo $product['id']; ?>">
                                    <?php echo htmlspecialchars($product['name']); ?>
                                </a>
                            </h3>
                            
                            <div class="product-category">
                                <a href="products.php?category=<?php echo $product['category_id']; ?>">
                                    <?php echo htmlspecialchars($product['category_name']); ?>
                                </a>
                            </div>
                            
                            <div class="product-price">
                                <?php if ($product['discount'] > 0): ?>
                                    <span class="old-price"><?php echo format_price($product['price']); ?></span>
                                    <span class="new-price">
                                        <?php echo format_price($product['price'] * (1 - $product['discount'] / 100)); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="price"><?php echo format_price($product['price']); ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="product-actions">
                                <button class="btn btn-primary btn-block add-to-cart-btn" 
                                        onclick="addToCart(<?php echo $product['id']; ?>)">
                                    <i class="fas fa-shopping-cart"></i> Thêm vào giỏ hàng
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </section>
    <?php endif; ?>
</div>

<script>
function addToCart(productId) {
    <?php if (is_logged_in()): ?>
        // Nếu đã đăng nhập, thêm vào giỏ hàng
        fetch('customer/cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=add&product_id=' + productId + '&quantity=1'
        })
        .then(response => response.text())
        .then(data => {
            // Reload trang để cập nhật giỏ hàng
            location.reload();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Có lỗi xảy ra khi thêm vào giỏ hàng!');
        });
    <?php else: ?>
        // Nếu chưa đăng nhập, chuyển đến trang đăng nhập
        if (confirm('Bạn cần đăng nhập để thêm sản phẩm vào giỏ hàng. Đăng nhập ngay?')) {
            window.location.href = 'auth/login.php';
        }
    <?php endif; ?>
}
</script>

<?php include('includes/footer.php'); ?>