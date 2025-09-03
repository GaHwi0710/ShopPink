<div class="container">
    <div class="product-detail">
        <div class="row">
            <div class="col-md-5">
                <div class="product-gallery">
                    <div class="gallery-main" style="background-image: url('<?php echo $product['image'] ?: 'https://via.placeholder.com/400x400/f8bbd0/ffffff?text=Product'; ?>');"></div>
                    <div class="gallery-thumbnails">
                        <div class="gallery-thumbnail active" style="background-image: url('<?php echo $product['image'] ?: 'https://via.placeholder.com/80x80/f8bbd0/ffffff?text=Product'; ?>');"></div>
                        <div class="gallery-thumbnail" style="background-image: url('https://via.placeholder.com/80x80/e91e63/ffffff?text=Image+2');"></div>
                        <div class="gallery-thumbnail" style="background-image: url('https://via.placeholder.com/80x80/9c27b0/ffffff?text=Image+3');"></div>
                        <div class="gallery-thumbnail" style="background-image: url('https://via.placeholder.com/80x80/673ab7/ffffff?text=Image+4');"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-7">
                <div class="product-info">
                    <h1 class="product-title"><?php echo $product['name']; ?></h1>
                    <div class="product-meta">
                        <div class="product-vendor">
                            <i class="fas fa-store"></i> 
                            <span>Bán bởi: <?php echo $product['seller_name']; ?></span>
                        </div>
                        <div class="product-rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                            <span>(15 đánh giá)</span>
                        </div>
                    </div>
                    
                    <div class="product-price">
                        <span class="current-price"><?php echo number_format($product['price'], 0, ',', '.'); ?>đ</span>
                    </div>
                    
                    <div class="product-description">
                        <p><?php echo $product['description']; ?></p>
                    </div>
                    
                    <div class="product-actions">
                        <div class="product-quantity">
                            <label for="quantity">Số lượng:</label>
                            <div class="quantity-control">
                                <button id="decrease-quantity">-</button>
                                <input type="number" id="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>">
                                <button id="increase-quantity">+</button>
                            </div>
                        </div>
                        
                        <form action="/add-to-cart" method="POST" class="add-to-cart-form">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <input type="hidden" name="quantity" id="cart-quantity" value="1">
                            <button type="submit" class="btn-primary add-to-cart-btn">
                                <i class="fas fa-shopping-cart"></i> Thêm vào giỏ hàng
                            </button>
                        </form>
                        
                        <button class="wishlist-btn">
                            <i class="far fa-heart"></i> Yêu thích
                        </button>
                    </div>
                    
                    <div class="product-meta-info">
                        <div class="meta-item">
                            <i class="fas fa-truck"></i>
                            <span>Miễn phí vận chuyển cho đơn hàng từ 500.000đ</span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-shield-alt"></i>
                            <span>Bảo hành chính hãng 12 tháng</span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-undo-alt"></i>
                            <span>Đổi trả trong vòng 30 ngày</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Product Tabs -->
    <div class="product-tabs">
        <div class="tab-nav">
            <button class="tab-btn active" data-tab="description">Mô tả</button>
            <button class="tab-btn" data-tab="specifications">Thông số</button>
            <button class="tab-btn" data-tab="reviews">Đánh giá</button>
            <button class="tab-btn" data-tab="shipping">Vận chuyển</button>
        </div>
        
        <div class="tab-content active" id="description">
            <h3>Mô tả sản phẩm</h3>
            <p><?php echo $product['description']; ?></p>
            <p>Sản phẩm được cam kết chất lượng, chính hãng 100%. Với thiết kế hiện đại, chất liệu cao cấp, sản phẩm mang lại sự thoải mái và phong cách cho người sử dụng.</p>
        </div>
        
        <div class="tab-content" id="specifications">
            <h3>Thông số kỹ thuật</h3>
            <table class="spec-table">
                <tr>
                    <th>Tên sản phẩm</th>
                    <td><?php echo $product['name']; ?></td>
                </tr>
                <tr>
                    <th>Xuất xứ</th>
                    <td>Việt Nam</td>
                </tr>
                <tr>
                    <th>Bảo hành</th>
                    <td>12 tháng</td>
                </tr>
                <tr>
                    <th>Tình trạng</th>
                    <td>Mới 100%</td>
                </tr>
            </table>
        </div>
        
        <div class="tab-content" id="reviews">
            <h3>Đánh giá sản phẩm</h3>
            
            <div class="review-summary">
                <div class="review-average">4.5</div>
                <div>
                    <div class="review-stars">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star-half-alt"></i>
                    </div>
                    <div class="review-count">(15 đánh giá)</div>
                </div>
            </div>
            
            <div class="review-list">
                <div class="review-item">
                    <div class="review-meta">
                        <div class="review-author">Nguyễn Văn A</div>
                        <div class="review-date">15/05/2025</div>
                    </div>
                    <div class="review-rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="review-content">
                        Sản phẩm chất lượng tốt, thiết kế đẹp, màu sắc như hình. Rất hài lòng với mua sắm này.
                    </div>
                </div>
                
                <div class="review-item">
                    <div class="review-meta">
                        <div class="review-author">Trần Thị B</div>
                        <div class="review-date">10/05/2025</div>
                    </div>
                    <div class="review-rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="far fa-star"></i>
                    </div>
                    <div class="review-content">
                        Sản phẩm tốt nhưng giao hàng hơi chậm. Chất liệu tốt, mặc thoải mái.
                    </div>
                </div>
            </div>
            
            <?php if (Auth::isCustomer()): ?>
                <div class="review-form">
                    <h3>Viết đánh giá của bạn</h3>
                    <form action="/review" method="POST">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        <div class="rating-input">
                            <i class="far fa-star" data-rating="1"></i>
                            <i class="far fa-star" data-rating="2"></i>
                            <i class="far fa-star" data-rating="3"></i>
                            <i class="far fa-star" data-rating="4"></i>
                            <i class="far fa-star" data-rating="5"></i>
                        </div>
                        <div class="form-group">
                            <textarea name="comment" placeholder="Viết đánh giá của bạn..." rows="4"></textarea>
                        </div>
                        <button type="submit" class="btn-primary">Gửi đánh giá</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="tab-content" id="shipping">
            <h3>Thông tin vận chuyển</h3>
            <p>Thời gian giao hàng: 2-5 ngày làm việc</p>
            <p>Phí vận chuyển: Miễn phí cho đơn hàng từ 500.000đ</p>
            <p>Phạm vi giao hàng: Toàn quốc</p>
            <p>Hình thức thanh toán: COD, Chuyển khoản</p>
        </div>
    </div>
    
    <!-- Related Products -->
    <div class="related-products">
        <h3 class="section-title">Sản phẩm liên quan</h3>
        <div class="products-grid">
            <?php foreach (array_slice($products, 0, 4) as $relatedProduct): ?>
                <div class="product">
                    <div class="product-img" style="background-image: url('<?php echo $relatedProduct['image'] ?: 'https://via.placeholder.com/300x300/f8bbd0/ffffff?text=Product'; ?>');"></div>
                    <div class="product-info">
                        <div class="product-title"><?php echo $relatedProduct['name']; ?></div>
                        <div class="product-price">
                            <span class="current-price"><?php echo number_format($relatedProduct['price'], 0, ',', '.'); ?>đ</span>
                        </div>
                        <div class="product-footer">
                            <a href="/products/<?php echo $relatedProduct['id']; ?>" class="btn">Xem chi tiết</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>