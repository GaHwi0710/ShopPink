<div class="container">
    <h1 class="page-title">Kết quả tìm kiếm: "<?php echo htmlspecialchars($_GET['keyword']); ?>"</h1>
    
    <?php if (empty($products)): ?>
        <div class="empty-state">
            <i class="fas fa-search"></i>
            <h4>Không tìm thấy sản phẩm</h4>
            <p>Không có sản phẩm nào phù hợp với từ khóa "<?php echo htmlspecialchars($_GET['keyword']); ?>"</p>
            <a href="/" class="btn">Tiếp tục mua sắm</a>
        </div>
    <?php else: ?>
        <div class="search-results">
            <p>Tìm thấy <?php echo count($products); ?> sản phẩm phù hợp</p>
        </div>
        
        <!-- Filter and Sort Controls -->
        <div class="filter-sort-container">
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
        <div class="products-grid">
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
        <div class="pagination">
            <button class="page-btn disabled"><i class="fas fa-chevron-left"></i></button>
            <button class="page-btn active">1</button>
            <button class="page-btn">2</button>
            <button class="page-btn">3</button>
            <button class="page-btn">...</button>
            <button class="page-btn">10</button>
            <button class="page-btn"><i class="fas fa-chevron-right"></i></button>
        </div>
    <?php endif; ?>
</div>