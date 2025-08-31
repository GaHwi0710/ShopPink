<?php
// Include autoload để tự động nạp các file cần thiết
require_once 'includes/autoload.php';

// Lấy danh mục chính
$main_categories = getCategories(0);

// Lấy sản phẩm nổi bật
$featured_products = getFeaturedProducts(8);

// Lấy sản phẩm bán chạy
$bestseller_products = getBestsellerProducts(8);

// Include header
include('includes/header.php');
?>
<!-- Hero Slider -->
<div class="container">
    <div class="hero-slider">
        <div class="slide active" style="background-image: url('assets/images/banners/banner1.jpg');">
            <div class="slide-content">
                <h2>Bộ sưu tập mùa hè 2023</h2>
                <p>Khám phá những xu hướng thời trang mới nhất với nhiều ưu đãi đặc biệt lên đến 50%.</p>
                <a href="category.php" class="btn">Mua ngay</a>
            </div>
        </div>
        <div class="slide" style="background-image: url('assets/images/banners/banner2.jpg');">
            <div class="slide-content">
                <h2>Giảm giá cuối mùa</h2>
                <p>Ưu đãi đặc biệt cho các sản phẩm cuối mùa với mức giá cực kỳ hấp dẫn.</p>
                <a href="category.php" class="btn">Xem ngay</a>
            </div>
        </div>
        <div class="slide" style="background-image: url('assets/images/banners/banner3.jpg');">
            <div class="slide-content">
                <h2>Phụ kiện cao cấp</h2>
                <p>Làm mới phong cách của bạn với bộ sưu tập phụ kiện mới nhất từ các thương hiệu nổi tiếng.</p>
                <a href="category.php" class="btn">Khám phá</a>
            </div>
        </div>
        <div class="slider-nav">
            <div class="slider-dot active"></div>
            <div class="slider-dot"></div>
            <div class="slider-dot"></div>
        </div>
    </div>
    
    <!-- Quick Stats -->
    <div class="quick-stats animate-on-scroll">
        <div class="stat-box">
            <i class="fas fa-truck"></i>
            <h3>Giao hàng miễn phí</h3>
            <p>Cho đơn hàng từ 500.000đ</p>
        </div>
        <div class="stat-box">
            <i class="fas fa-sync-alt"></i>
            <h3>Đổi trả dễ dàng</h3>
            <p>Trong vòng 30 ngày</p>
        </div>
        <div class="stat-box">
            <i class="fas fa-shield-alt"></i>
            <h3>Bảo mật thông tin</h3>
            <p>Thanh toán an toàn</p>
        </div>
        <div class="stat-box">
            <i class="fas fa-headset"></i>
            <h3>Hỗ trợ 24/7</h3>
            <p>Luôn sẵn sàng hỗ trợ</p>
        </div>
    </div>
    
    <!-- Categories -->
    <h2 class="section-title animate-on-scroll">Danh mục sản phẩm</h2>
    <div class="categories animate-on-scroll">
        <?php foreach ($main_categories as $category) { ?>
            <div class="category">
                <i class="fas fa-tshirt"></i>
                <h3><?php echo htmlspecialchars($category['name']); ?></h3>
            </div>
        <?php } ?>
    </div>
    
    <!-- Featured Products -->
    <h2 class="section-title animate-on-scroll">Sản phẩm nổi bật</h2>
    <div class="products-grid animate-on-scroll" id="products-grid">
        <?php foreach ($featured_products as $product) { ?>
            <div class="product" data-category="women" data-price="<?php echo $product['price']; ?>">
                <div class="product-badge">Hot</div>
                <div class="product-img" style="background-image: url('assets/images/products/<?php echo htmlspecialchars($product['image'] ?? 'default.jpg'); ?>');"></div>
                <div class="product-actions">
                    <button class="quick-view-btn" title="Xem nhanh"><i class="fas fa-search"></i></button>
                    <button class="compare-btn" title="So sánh"><i class="fas fa-exchange-alt"></i></button>
                    <button class="wishlist-btn" title="Yêu thích"><i class="far fa-heart"></i></button>
                </div>
                <div class="product-info">
                    <div class="product-vendor"><?php echo htmlspecialchars($product['category_name']); ?></div>
                    <div class="product-title"><?php echo htmlspecialchars($product['name']); ?></div>
                    <div class="product-price">
                        <span class="current-price"><?php echo format_price($product['price']); ?></span>
                        <span class="old-price"><?php echo format_price($product['price'] * 1.2); ?></span>
                    </div>
                    <div class="product-rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star-half-alt"></i>
                        <span>(15 đánh giá)</span>
                    </div>
                    <div class="product-footer">
                        <button class="add-to-cart">Thêm vào giỏ</button>
                        <button class="wishlist-btn"><i class="far fa-heart"></i></button>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
    
    <!-- Best Sellers -->
    <h2 class="section-title animate-on-scroll">Sản phẩm bán chạy</h2>
    <div class="best-sellers-grid animate-on-scroll">
        <?php foreach ($bestseller_products as $product) { ?>
            <div class="best-seller-item">
                <div class="best-seller-badge">Bán chạy</div>
                <div class="best-seller-img" style="background-image: url('assets/images/products/<?php echo htmlspecialchars($product['image'] ?? 'default.jpg'); ?>');"></div>
                <div class="best-seller-info">
                    <div class="best-seller-title"><?php echo htmlspecialchars($product['name']); ?></div>
                    <div class="best-seller-price"><?php echo format_price($product['price']); ?></div>
                    <div class="best-seller-rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star-half-alt"></i>
                        <span>(15 đánh giá)</span>
                    </div>
                    <div class="best-seller-sold">Đã bán: <?php echo isset($product['total_sold']) ? $product['total_sold'] : '0'; ?> sản phẩm</div>
                </div>
            </div>
        <?php } ?>
    </div>
    
    <!-- Recently Viewed Products -->
    <h2 class="section-title animate-on-scroll">Sản phẩm đã xem</h2>
    <div class="recently-viewed-grid animate-on-scroll">
        <?php
        // Lấy sản phẩm đã xem gần đây
        $recently_viewed = getRecentlyViewedProducts(6);
        foreach ($recently_viewed as $product) {
        ?>
            <div class="recently-viewed-item">
                <div class="recently-viewed-img" style="background-image: url('assets/images/products/<?php echo htmlspecialchars($product['image'] ?? 'default.jpg'); ?>');"></div>
                <div class="recently-viewed-info">
                    <div class="recently-viewed-title"><?php echo htmlspecialchars($product['name']); ?></div>
                    <div class="recently-viewed-price"><?php echo format_price($product['price']); ?></div>
                </div>
            </div>
        <?php } ?>
        
        <!-- Nếu không có sản phẩm đã xem, hiển thị sản phẩm mẫu -->
        <?php if (empty($recently_viewed)) { ?>
            <div class="recently-viewed-item">
                <div class="recently-viewed-img" style="background-image: url('assets/images/products/product1.jpg');"></div>
                <div class="recently-viewed-info">
                    <div class="recently-viewed-title">Áo thun cổ tròn chất liệu cotton thoáng mát</div>
                    <div class="recently-viewed-price">299.000đ</div>
                </div>
            </div>
            <div class="recently-viewed-item">
                <div class="recently-viewed-img" style="background-image: url('assets/images/products/product2.jpg');"></div>
                <div class="recently-viewed-info">
                    <div class="recently-viewed-title">Đầm dự tiệc sang trọng, thiết kế hiện đại</div>
                    <div class="recently-viewed-price">1.299.000đ</div>
                </div>
            </div>
            <div class="recently-viewed-item">
                <div class="recently-viewed-img" style="background-image: url('assets/images/products/product3.jpg');"></div>
                <div class="recently-viewed-info">
                    <div class="recently-viewed-title">Giày cao gót quai mảnh, chất liệu da cao cấp</div>
                    <div class="recently-viewed-price">799.000đ</div>
                </div>
            </div>
            <div class="recently-viewed-item">
                <div class="recently-viewed-img" style="background-image: url('assets/images/products/product4.jpg');"></div>
                <div class="recently-viewed-info">
                    <div class="recently-viewed-title">Túi xách da thật, thiết kế trẻ trung, đa năng</div>
                    <div class="recently-viewed-price">1.599.000đ</div>
                </div>
            </div>
            <div class="recently-viewed-item">
                <div class="recently-viewed-img" style="background-image: url('assets/images/products/product5.jpg');"></div>
                <div class="recently-viewed-info">
                    <div class="recently-viewed-title">Ví da thật thiết kế sang trọng</div>
                    <div class="recently-viewed-price">599.000đ</div>
                </div>
            </div>
            <div class="recently-viewed-item">
                <div class="recently-viewed-img" style="background-image: url('assets/images/products/product6.jpg');"></div>
                <div class="recently-viewed-info">
                    <div class="recently-viewed-title">Đồng hồ nữ thiết kế tinh tế</div>
                    <div class="recently-viewed-price">899.000đ</div>
                </div>
            </div>
        <?php } ?>
    </div>
    
    <!-- Brands Slider -->
    <h2 class="section-title animate-on-scroll">Thương hiệu nổi bật</h2>
    <div class="brands-slider owl-carousel animate-on-scroll">
        <?php
        // Lấy danh sách thương hiệu
        $brands = getBrands();
        foreach ($brands as $brand) {
        ?>
            <div class="brand-item">
                <img src="assets/images/brands/<?php echo htmlspecialchars($brand['logo'] ?? 'brand.png'); ?>" alt="<?php echo htmlspecialchars($brand['name']); ?>">
            </div>
        <?php } ?>
        
        <!-- Nếu không có thương hiệu, hiển thị thương hiệu mẫu -->
        <?php if (empty($brands)) { ?>
            <div class="brand-item">
                <img src="assets/images/brands/brand1.png" alt="Brand 1">
            </div>
            <div class="brand-item">
                <img src="assets/images/brands/brand2.png" alt="Brand 2">
            </div>
            <div class="brand-item">
                <img src="assets/images/brands/brand3.png" alt="Brand 3">
            </div>
            <div class="brand-item">
                <img src="assets/images/brands/brand4.png" alt="Brand 4">
            </div>
            <div class="brand-item">
                <img src="assets/images/brands/brand5.png" alt="Brand 5">
            </div>
            <div class="brand-item">
                <img src="assets/images/brands/brand6.png" alt="Brand 6">
            </div>
        <?php } ?>
    </div>
    
    <!-- Newsletter -->
    <div class="newsletter animate-on-scroll">
        <h2>Đăng ký nhận thông tin</h2>
        <p>Hãy đăng ký để nhận thông tin về các sản phẩm mới và chương trình khuyến mãi đặc biệt từ chúng tôi.</p>
        <form class="newsletter-form">
            <input type="email" placeholder="Email của bạn...">
            <button type="submit">Đăng ký</button>
        </form>
    </div>
</div>

<?php include('includes/footer.php'); ?>