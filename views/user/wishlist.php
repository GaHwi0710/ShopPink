<div class="container">
    <h1 class="page-title">Danh sách yêu thích</h1>
    
    <?php if (empty($wishlist)): ?>
        <div class="empty-state">
            <i class="fas fa-heart"></i>
            <h4>Bạn chưa có sản phẩm yêu thích nào</h4>
            <p>Hãy thêm sản phẩm vào danh sách yêu thích để không bỏ lỡ các ưu đãi tốt nhất.</p>
            <a href="/" class="btn">Khám phá ngay</a>
        </div>
    <?php else: ?>
        <div class="wishlist-grid">
            <?php foreach ($wishlist as $item): ?>
                <div class="product">
                    <div class="product-img" style="background-image: url('<?php echo $item['image'] ?: 'https://via.placeholder.com/300x300/f8bbd0/ffffff?text=Product'; ?>');"></div>
                    <div class="product-actions">
                        <button class="quick-view-btn" title="Xem nhanh"><i class="fas fa-search"></i></button>
                        <button class="compare-btn" title="So sánh"><i class="fas fa-exchange-alt"></i></button>
                        <a href="/remove-from-wishlist?id=<?php echo $item['product_id']; ?>" class="wishlist-btn active" title="Xóa khỏi yêu thích"><i class="fas fa-heart"></i></a>
                    </div>
                    <div class="product-info">
                        <div class="product-title"><?php echo $item['name']; ?></div>
                        <div class="product-price">
                            <span class="current-price"><?php echo number_format($item['price'], 0, ',', '.'); ?>đ</span>
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
                            <?php if ($item['stock'] > 0): ?>
                                <form action="/add-to-cart" method="POST">
                                    <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="add-to-cart">Thêm vào giỏ</button>
                                </form>
                            <?php else: ?>
                                <button class="out-of-stock" disabled>Hết hàng</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>