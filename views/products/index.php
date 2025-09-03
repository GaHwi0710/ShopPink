<div class="container">
    <!-- Hero Slider -->
    <div class="hero-slider">
        <div class="slide active" style="background-image: url('https://via.placeholder.com/1200x500/f8bbd0/ffffff?text=Khuyen+Mai+Dac+Biet+2025');">
            <div class="slide-content">
                <h2>Khuyến mãi đặc biệt 2025</h2>
                <p>Khám phá những xu hướng mới nhất với nhiều ưu đãi đặc biệt lên đến 50%.</p>
                <a href="#" class="btn">Mua ngay</a>
            </div>
        </div>
        <div class="slide" style="background-image: url('https://via.placeholder.com/1200x500/e91e63/ffffff?text=Giam+Gia+Cuoi+Mua+He');">
            <div class="slide-content">
                <h2>Giảm giá cuối mùa hè</h2>
                <p>Ưu đãi đặc biệt cho các sản phẩm cuối mùa với mức giá cực kỳ hấp dẫn.</p>
                <a href="#" class="btn">Xem ngay</a>
            </div>
        </div>
        <div class="slide" style="background-image: url('https://via.placeholder.com/1200x500/9c27b0/ffffff?text=San+Pham+Moi+2025');">
            <div class="slide-content">
                <h2>Sản phẩm mới 2025</h2>
                <p>Làm mới phong cách của bạn với bộ sưu tập sản phẩm mới nhất từ các thương hiệu nổi tiếng.</p>
                <a href="#" class="btn">Khám phá</a>
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
        <div class="category">
            <i class="fas fa-tshirt"></i>
            <h3>Thời trang</h3>
        </div>
        <div class="category">
            <i class="fas fa-mobile-alt"></i>
            <h3>Điện tử</h3>
        </div>
        <div class="category">
            <i class="fas fa-home"></i>
            <h3>Đồ gia dụng</h3>
        </div>
        <div class="category">
            <i class="fas fa-laptop"></i>
            <h3>Laptop</h3>
        </div>
        <div class="category">
            <i class="fas fa-gem"></i>
            <h3>Trang sức</h3>
        </div>
        <div class="category">
            <i class="fas fa-pump-soap"></i>
            <h3>Mỹ phẩm</h3>
        </div>
    </div>
    
    <!-- Featured Products -->
    <h2 class="section-title animate-on-scroll">Sản phẩm nổi bật</h2>
    
    <!-- Filter and Sort Controls -->
    <div class="filter-sort-container animate-on-scroll">
        <div class="filter-controls">
            <label for="category-filter">Danh mục:</label>
            <select id="category-filter">
                <option value="all">Tất cả</option>
                <option value="thoi-trang">Thời trang</option>
                <option value="dien-tu">Điện tử</option>
                <option value="do-gia-dung">Đồ gia dụng</option>
            </select>
            
            <label for="price-filter">Khoảng giá:</label>
            <select id="price-filter">
                <option value="all">Tất cả</option>
                <option value="0-500000">Dưới 500.000đ</option>
                <option value="500000-1000000">500.000đ - 1.000.000đ</option>
                <option value="1000000-2000000">1.000.000đ - 2.000.000đ</option>
                <option value="2000000">Trên 2.000.000đ</option>
            </select>
        </div>
        
        <div class="sort-controls">
            <label for="sort-products">Sắp xếp:</label>
            <select id="sort-products">
                <option value="default">Mặc định</option>
                <option value="price-asc">Giá: Thấp đến cao</option>
                <option value="price-desc">Giá: Cao đến thấp</option>
                <option value="name">Tên: A-Z</option>
                <option value="newest">Mới nhất</option>
            </select>
            
            <div class="view-toggle">
                <button class="view-btn active" id="grid-view"><i class="fas fa-th"></i></button>
                <button class="view-btn" id="list-view"><i class="fas fa-list"></i></button>
            </div>
        </div>
    </div>
    
    <!-- Products Grid -->
    <div class="products-grid animate-on-scroll">
        <?php foreach ($products as $product): ?>
            <div class="product" data-category="thoi-trang" data-price="<?php echo $product['price']; ?>">
                <div class="product-badge">Hot</div>
                <div class="product-img" style="background-image: url('<?php echo $product['image'] ?: 'https://via.placeholder.com/300x300/f8bbd0/ffffff?text=Product'; ?>');"></div>
                <div class="product-actions">
                    <button class="quick-view-btn" title="Xem nhanh"><i class="fas fa-search"></i></button>
                    <button class="compare-btn" title="So sánh"><i class="fas fa-exchange-alt"></i></button>
                    <button class="wishlist-btn" title="Yêu thích"><i class="far fa-heart"></i></button>
                </div>
                <div class="product-info">
                    <div class="product-vendor"><?php echo $product['seller_name']; ?></div>
                    <div class="product-title"><?php echo $product['name']; ?></div>
                    <div class="product-price">
                        <span class="current-price"><?php echo number_format($product['price'], 0, ',', '.'); ?>đ</span>
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
                        <form action="/add-to-cart" method="POST">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <input type="hidden" name="quantity" value="1">
                            <button type="submit" class="add-to-cart">Thêm vào giỏ</button>
                        </form>
                        <button class="wishlist-btn"><i class="far fa-heart"></i></button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Pagination -->
    <div class="pagination animate-on-scroll">
        <button class="page-btn disabled"><i class="fas fa-chevron-left"></i></button>
        <button class="page-btn active">1</button>
        <button class="page-btn">2</button>
        <button class="page-btn">3</button>
        <button class="page-btn">...</button>
        <button class="page-btn">10</button>
        <button class="page-btn"><i class="fas fa-chevron-right"></i></button>
    </div>
</div>

<!-- Best Sellers -->
<div class="container">
    <h2 class="section-title animate-on-scroll">Sản phẩm bán chạy</h2>
    <div class="best-sellers-grid animate-on-scroll">
        <?php foreach (array_slice($products, 0, 4) as $product): ?>
            <div class="best-seller-item">
                <div class="best-seller-badge">Bán chạy</div>
                <div class="best-seller-img" style="background-image: url('<?php echo $product['image'] ?: 'https://via.placeholder.com/300x300/f8bbd0/ffffff?text=Product'; ?>');"></div>
                <div class="best-seller-info">
                    <div class="best-seller-title"><?php echo $product['name']; ?></div>
                    <div class="best-seller-price"><?php echo number_format($product['price'], 0, ',', '.'); ?>đ</div>
                    <div class="best-seller-rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star-half-alt"></i>
                        <span>(15 đánh giá)</span>
                    </div>
                    <div class="best-seller-sold">Đã bán: 120 sản phẩm</div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Brands Slider -->
<div class="container">
    <h2 class="section-title animate-on-scroll">Thương hiệu nổi bật</h2>
    <div class="brands-slider owl-carousel animate-on-scroll">
        <div class="brand-item">
            <img src="https://via.placeholder.com/150x80/f8bbd0/ffffff?text=Brand+1" alt="Brand 1">
        </div>
        <div class="brand-item">
            <img src="https://via.placeholder.com/150x80/e91e63/ffffff?text=Brand+2" alt="Brand 2">
        </div>
        <div class="brand-item">
            <img src="https://via.placeholder.com/150x80/9c27b0/ffffff?text=Brand+3" alt="Brand 3">
        </div>
        <div class="brand-item">
            <img src="https://via.placeholder.com/150x80/673ab7/ffffff?text=Brand+4" alt="Brand 4">
        </div>
        <div class="brand-item">
            <img src="https://via.placeholder.com/150x80/3f51b5/ffffff?text=Brand+5" alt="Brand 5">
        </div>
        <div class="brand-item">
            <img src="https://via.placeholder.com/150x80/2196f3/ffffff?text=Brand+6" alt="Brand 6">
        </div>
    </div>
</div>