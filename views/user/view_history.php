<div class="container">
    <h1 class="page-title">Lịch sử xem sản phẩm</h1>
    
    <?php if (empty($viewHistory)): ?>
        <div class="empty-state">
            <i class="fas fa-history"></i>
            <h4>Bạn chưa xem sản phẩm nào</h4>
            <p>Hãy khám phá các sản phẩm của chúng tôi.</p>
            <a href="/" class="btn">Khám phá ngay</a>
        </div>
    <?php else: ?>
        <div class="view-history-grid">
            <?php foreach ($viewHistory as $item): ?>
                <div class="product">
                    <div class="product-img" style="background-image: url('<?php echo $item['image'] ?: 'https://via.placeholder.com/300x300/f8bbd0/ffffff?text=Product'; ?>');"></div>
                    <div class="product-info">
                        <div class="product-title"><?php echo $item['name']; ?></div>
                        <div class="product-price">
                            <span class="current-price"><?php echo number_format($item['price'], 0, ',', '.'); ?>đ</span>
                        </div>
                        <div class="product-footer">
                            <a href="/products/<?php echo $item['product_id']; ?>" class="btn">Xem chi tiết</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>