<div class="quick-view-container">
    <div class="quick-view-image">
        <img src="<?php echo SITE_URL . '/' . ($product['image'] ?: 'assets/images/products/default.jpg'); ?>" alt="<?php echo $product['name']; ?>">
        <?php if ($product['stock'] <= 0): ?>
            <div class="out-of-stock-label">Hết hàng</div>
        <?php endif; ?>
    </div>
    
    <div class="quick-view-details">
        <h3><?php echo $product['name']; ?></h3>
        
        <div class="product-rating">
            <?php
            $avgRating = $this->productModel->getAverageRating($product['id']);
            $rating = round($avgRating['average_rating'], 1);
            ?>
            <div class="stars">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <i class="fas fa-star<?php echo $i <= $rating ? '' : '-o'; ?>"></i>
                <?php endfor; ?>
            </div>
            <span>(<?php echo $avgRating['total_reviews']; ?> đánh giá)</span>
        </div>
        
        <div class="product-price">
            <?php if ($product['discount_price'] > 0): ?>
                <span class="current-price"><?php echo number_format($product['discount_price'], 0, ',', '.'); ?>đ</span>
                <span class="old-price"><?php echo number_format($product['price'], 0, ',', '.'); ?>đ</span>
                <span class="discount-percent"><?php echo round((1 - $product['discount_price'] / $product['price']) * 100); ?>%</span>
            <?php else: ?>
                <span class="current-price"><?php echo number_format($product['price'], 0, ',', '.'); ?>đ</span>
            <?php endif; ?>
        </div>
        
        <div class="product-description">
            <?php echo nl2br(substr($product['description'], 0, 200)) . '...'; ?>
        </div>
        
        <div class="product-meta">
            <div class="seller-info">
                <span>Người bán: </span>
                <span><?php echo $product['seller_name']; ?></span>
            </div>
            
            <div class="stock-info">
                <span>Tình trạng: </span>
                <?php if ($product['stock'] > 0): ?>
                    <span class="in-stock">Còn hàng (<?php echo $product['stock']; ?>)</span>
                <?php else: ?>
                    <span class="out-of-stock">Hết hàng</span>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="product-actions">
            <div class="quantity-selector">
                <button type="button" class="quantity-btn minus">-</button>
                <input type="number" class="quantity-input" value="1" min="1" max="<?php echo $product['stock']; ?>" data-product-id="<?php echo $product['id']; ?>">
                <button type="button" class="quantity-btn plus">+</button>
            </div>
            
            <div class="action-buttons">
                <?php if ($product['stock'] > 0): ?>
                    <button type="button" class="add-to-cart-btn" data-product-id="<?php echo $product['id']; ?>">
                        <i class="fas fa-shopping-cart"></i> Thêm vào giỏ
                    </button>
                <?php else: ?>
                    <button type="button" class="out-of-stock-btn" disabled>Hết hàng</button>
                <?php endif; ?>
                
                <button type="button" class="wishlist-btn <?php echo $isInWishlist ? 'active' : ''; ?>" data-product-id="<?php echo $product['id']; ?>">
                    <i class="fas fa-heart"></i>
                </button>
            </div>
        </div>
        
        <div class="product-links">
            <a href="<?php echo SITE_URL; ?>/products/<?php echo $product['id']; ?>" class="view-details-btn">
                <i class="fas fa-eye"></i> Xem chi tiết
            </a>
        </div>
    </div>
</div>