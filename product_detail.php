<?php
session_start();
include('includes/config.php');
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($product_id <= 0) {
    header("Location: index.php");
    exit;
}
// Lấy thông tin sản phẩm
$product_stmt = $conn->prepare("
    SELECT p.*, c.name as category_name, c.id as category_id
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    WHERE p.id = ?
");
$product_stmt->bind_param("i", $product_id);
$product_stmt->execute();
$product = $product_stmt->get_result()->fetch_assoc();
if (!$product) {
    include('includes/header.php');
    echo "<div class='container'><h2>❌ Sản phẩm không tồn tại.</h2></div>";
    include('includes/footer.php');
    exit;
}
// Lấy sản phẩm liên quan
$related_stmt = $conn->prepare("SELECT * FROM products WHERE category_id = ? AND id != ? LIMIT 4");
$related_stmt->bind_param("ii", $product['category_id'], $product_id);
$related_stmt->execute();
$related_result = $related_stmt->get_result();
// Lấy review
$reviews_stmt = $conn->prepare("
    SELECT r.*, u.username 
    FROM reviews r 
    JOIN users u ON r.user_id = u.id 
    WHERE r.product_id = ? 
    ORDER BY r.created_at DESC
");
$reviews_stmt->bind_param("i", $product_id);
$reviews_stmt->execute();
$reviews_result = $reviews_stmt->get_result();
// Trung bình rating
$avg_stmt = $conn->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as total FROM reviews WHERE product_id = ?");
$avg_stmt->bind_param("i", $product_id);
$avg_stmt->execute();
$avg_data = $avg_stmt->get_result()->fetch_assoc();
$avg_rating = round($avg_data['avg_rating'], 1);
$total_reviews = $avg_data['total'];
// Nếu user login → check xem đã mua sp này chưa
$can_review = false;
if (isset($_SESSION['user_id'])) {
    $check_stmt = $conn->prepare("
        SELECT COUNT(*) as cnt 
        FROM order_details od 
        JOIN orders o ON od.order_id = o.id 
        WHERE o.user_id = ? AND o.status = 'completed' AND od.product_id = ?
    ");
    $check_stmt->bind_param("ii", $_SESSION['user_id'], $product_id);
    $check_stmt->execute();
    $cnt = $check_stmt->get_result()->fetch_assoc()['cnt'];
    if ($cnt > 0) $can_review = true;
}
// Xử lý submit review
if ($_SERVER["REQUEST_METHOD"] === "POST" && $can_review) {
    $rating = intval($_POST['rating']);
    $comment = trim($_POST['comment']);
    if ($rating >= 1 && $rating <= 5) {
        $insert_stmt = $conn->prepare("
            INSERT INTO reviews (user_id, product_id, rating, comment, created_at) 
            VALUES (?, ?, ?, ?, NOW())
        ");
        $insert_stmt->bind_param("iiis", $_SESSION['user_id'], $product_id, $rating, $comment);
        $insert_stmt->execute();
        header("Location: product_detail.php?id=".$product_id."#reviews");
        exit();
    }
}
?>
<?php include('includes/header.php'); ?>

<div class="container">
    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="index.php">Trang chủ</a> &raquo;
        <a href="category.php?id=<?php echo $product['category_id']; ?>">
            <?php echo htmlspecialchars($product['category_name']); ?>
        </a> &raquo;
        <span><?php echo htmlspecialchars($product['name']); ?></span>
    </div>
    
    <!-- Product Detail -->
    <div class="product-detail-container">
        <div class="product-gallery">
            <div class="gallery-main" style="background-image: url('assets/images/products/<?php echo htmlspecialchars($product['image'] ?? 'default.jpg'); ?>');"></div>
            <div class="gallery-thumbnails">
                <div class="gallery-thumbnail active" style="background-image: url('assets/images/products/<?php echo htmlspecialchars($product['image'] ?? 'default.jpg'); ?>');"></div>
                <!-- Thêm các hình ảnh khác nếu có -->
                <div class="gallery-thumbnail" style="background-image: url('assets/images/products/<?php echo htmlspecialchars($product['image'] ?? 'default.jpg'); ?>');"></div>
                <div class="gallery-thumbnail" style="background-image: url('assets/images/products/<?php echo htmlspecialchars($product['image'] ?? 'default.jpg'); ?>');"></div>
            </div>
        </div>
        
        <div class="product-info">
            <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>
            <div class="product-vendor"><?php echo htmlspecialchars($product['category_name']); ?></div>
            
            <div class="product-rating">
                <div class="product-rating-stars">
                    <?php 
                    for ($i = 1; $i <= 5; $i++) {
                        if ($i <= round($avg_rating)) {
                            echo '<i class="fas fa-star"></i>';
                        } elseif ($i - 0.5 <= $avg_rating) {
                            echo '<i class="fas fa-star-half-alt"></i>';
                        } else {
                            echo '<i class="far fa-star"></i>';
                        }
                    }
                    ?>
                </div>
                <span>(<?php echo $total_reviews; ?> đánh giá)</span>
            </div>
            
            <div class="product-price">
                <span class="current-price"><?php echo number_format($product['price'], 0, ',', '.'); ?>đ</span>
                <?php if (!empty($product['old_price'])): ?>
                    <span class="old-price"><?php echo number_format($product['old_price'], 0, ',', '.'); ?>đ</span>
                <?php endif; ?>
            </div>
            
            <div class="product-description">
                <?php echo nl2br(htmlspecialchars($product['description'])); ?>
            </div>
            
            <!-- Product Variants -->
            <div class="product-variants">
                <div class="variant-title">Màu sắc:</div>
                <div class="variant-options">
                    <div class="variant-option active">Đen</div>
                    <div class="variant-option">Trắng</div>
                    <div class="variant-option">Xanh</div>
                </div>
            </div>
            
            <div class="product-variants">
                <div class="variant-title">Kích thước:</div>
                <div class="variant-options">
                    <div class="variant-option">S</div>
                    <div class="variant-option active">M</div>
                    <div class="variant-option">L</div>
                </div>
            </div>
            
            <!-- Stock Status -->
            <div class="stock-status">
                <div class="stock-indicator <?php echo $product['stock'] > 0 ? 'in-stock' : 'out-of-stock'; ?>"></div>
                <div class="stock-text"><?php echo $product['stock'] > 0 ? 'Còn hàng' : 'Hết hàng'; ?></div>
            </div>
            
            <!-- Quantity and Add to Cart -->
            <div class="product-actions">
                <div class="quantity-control">
                    <button id="decrease-quantity">-</button>
                    <input type="number" id="quantity" value="1" min="1" max="<?php echo $product['stock'] > 0 ? $product['stock'] : 1; ?>">
                    <button id="increase-quantity">+</button>
                </div>
                
                <form action="add_to_cart.php" method="post" class="add-to-cart-form">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    <input type="hidden" name="quantity" id="cart-quantity" value="1">
                    <button type="submit" class="add-to-cart-btn" <?php echo $product['stock'] <= 0 ? 'disabled' : ''; ?>>
                        <?php echo $product['stock'] <= 0 ? 'Hết hàng' : 'Thêm vào giỏ hàng'; ?>
                    </button>
                </form>
                
                <button class="wishlist-btn" title="Yêu thích">
                    <i class="far fa-heart"></i>
                </button>
            </div>
            
            <!-- Product Info -->
            <div class="product-meta">
                <div class="meta-item">
                    <i class="fas fa-truck"></i>
                    <span>Miễn phí vận chuyển cho đơn hàng từ 500.000đ</span>
                </div>
                <div class="meta-item">
                    <i class="fas fa-shield-alt"></i>
                    <span>Bảo hành chính hãng 12 tháng</span>
                </div>
                <div class="meta-item">
                    <i class="fas fa-sync-alt"></i>
                    <span>Đổi trả trong vòng 30 ngày</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Product Tabs -->
    <div class="product-tabs" id="product-tabs">
        <div class="tab-nav">
            <button class="tab-btn active" data-tab="description">Mô tả</button>
            <button class="tab-btn" data-tab="specifications">Thông số</button>
            <button class="tab-btn" data-tab="reviews">Đánh giá (<?php echo $total_reviews; ?>)</button>
            <button class="tab-btn" data-tab="shipping">Vận chuyển</button>
        </div>
        
        <div class="tab-content active" id="description">
            <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
        </div>
        
        <div class="tab-content" id="specifications">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: 600;">SKU</td>
                    <td style="padding: 10px; border-bottom: 1px solid #eee;"><?php echo htmlspecialchars($product['sku'] ?? 'N/A'); ?></td>
                </tr>
                <tr>
                    <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: 600;">Thương hiệu</td>
                    <td style="padding: 10px; border-bottom: 1px solid #eee;"><?php echo htmlspecialchars($product['brand'] ?? 'N/A'); ?></td>
                </tr>
                <tr>
                    <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: 600;">Xuất xứ</td>
                    <td style="padding: 10px; border-bottom: 1px solid #eee;"><?php echo htmlspecialchars($product['origin'] ?? 'N/A'); ?></td>
                </tr>
                <tr>
                    <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: 600;">Chất liệu</td>
                    <td style="padding: 10px; border-bottom: 1px solid #eee;"><?php echo htmlspecialchars($product['material'] ?? 'N/A'); ?></td>
                </tr>
                <tr>
                    <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: 600;">Bảo hành</td>
                    <td style="padding: 10px; border-bottom: 1px solid #eee;"><?php echo htmlspecialchars($product['warranty'] ?? '12 tháng'); ?></td>
                </tr>
            </table>
        </div>
        
        <div class="tab-content" id="reviews">
            <!-- Product Reviews -->
            <div class="product-reviews" id="reviews">
                <div class="review-header">
                    <div class="review-summary">
                        <div class="review-average"><?php echo $avg_rating; ?></div>
                        <div>
                            <div class="review-stars">
                                <?php 
                                for ($i = 1; $i <= 5; $i++) {
                                    if ($i <= round($avg_rating)) {
                                        echo '<i class="fas fa-star"></i>';
                                    } elseif ($i - 0.5 <= $avg_rating) {
                                        echo '<i class="fas fa-star-half-alt"></i>';
                                    } else {
                                        echo '<i class="far fa-star"></i>';
                                    }
                                }
                                ?>
                            </div>
                            <div class="review-count">(<?php echo $total_reviews; ?> đánh giá)</div>
                        </div>
                    </div>
                    <div class="review-filters">
                        <button class="review-filter active">Tất cả</button>
                        <button class="review-filter">5 sao</button>
                        <button class="review-filter">4 sao</button>
                        <button class="review-filter">3 sao</button>
                    </div>
                </div>
                
                <?php if ($total_reviews > 0): ?>
                    <div class="review-list">
                        <?php while ($review = $reviews_result->fetch_assoc()): ?>
                            <div class="review-item">
                                <div class="review-meta">
                                    <div class="review-author"><?php echo htmlspecialchars($review['username']); ?></div>
                                    <div class="review-date"><?php echo date("d/m/Y", strtotime($review['created_at'])); ?></div>
                                </div>
                                <div class="review-rating">
                                    <?php 
                                    for ($i = 1; $i <= 5; $i++) {
                                        if ($i <= $review['rating']) {
                                            echo '<i class="fas fa-star"></i>';
                                        } else {
                                            echo '<i class="far fa-star"></i>';
                                        }
                                    }
                                    ?>
                                </div>
                                <div class="review-content">
                                    <?php echo nl2br(htmlspecialchars($review['comment'])); ?>
                                </div>
                                <div class="review-actions">
                                    <div class="review-action">
                                        <i class="fas fa-thumbs-up"></i>
                                        <span>Hữu ích</span>
                                    </div>
                                    <div class="review-action">
                                        <i class="fas fa-reply"></i>
                                        <span>Trả lời</span>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p>Chưa có đánh giá nào cho sản phẩm này.</p>
                <?php endif; ?>
                
                <!-- Review Form -->
                <?php if ($can_review): ?>
                    <div class="review-form">
                        <h3>Viết đánh giá của bạn</h3>
                        <form method="post">
                            <div class="rating-input">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="far fa-star" data-rating="<?php echo $i; ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <div class="form-group">
                                <textarea name="comment" placeholder="Viết đánh giá của bạn..." rows="4" required></textarea>
                            </div>
                            <input type="hidden" name="rating" id="rating-value" value="5">
                            <button type="submit" class="btn-primary">Gửi đánh giá</button>
                        </form>
                    </div>
                <?php elseif(isset($_SESSION['user_id'])): ?>
                    <p><i>Bạn chỉ có thể đánh giá khi đã mua sản phẩm này.</i></p>
                <?php else: ?>
                    <p><a href="login.php">Đăng nhập</a> để viết đánh giá.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="tab-content" id="shipping">
            <p><strong>Thời gian giao hàng:</strong> 2-5 ngày làm việc</p>
            <p><strong>Phí vận chuyển:</strong> Miễn phí cho đơn hàng từ 500.000đ</p>
            <p><strong>Phương thức thanh toán:</strong></p>
            <ul>
                <li>Thanh toán khi nhận hàng (COD)</li>
                <li>Chuyển khoản ngân hàng</li>
                <li>Thẻ tín dụng/ghi nợ</li>
                <li>Ví điện tử (Momo, ZaloPay, VNPay)</li>
            </ul>
            <p><strong>Chính sách đổi trả:</strong> Đổi trả trong vòng 30 ngày nếu sản phẩm có lỗi từ nhà sản xuất</p>
        </div>
    </div>
    
    <!-- Related Products -->
    <?php if ($related_result->num_rows > 0): ?>
    <div class="section">
        <h2 class="section-title">Sản phẩm liên quan</h2>
        <div class="products-grid">
            <?php 
            // Reset pointer để sử dụng lại kết quả
            mysqli_data_seek($related_result, 0);
            while ($rp = $related_result->fetch_assoc()): 
            ?>
                <div class="product" data-category="<?php echo $rp['category_id']; ?>" data-price="<?php echo $rp['price']; ?>">
                    <div class="product-img" style="background-image: url('assets/images/products/<?php echo htmlspecialchars($rp['image'] ?? 'default.jpg'); ?>');"></div>
                    <div class="product-info">
                        <div class="product-vendor"><?php echo htmlspecialchars($rp['category_name']); ?></div>
                        <div class="product-title"><?php echo htmlspecialchars($rp['name']); ?></div>
                        <div class="product-price">
                            <span class="current-price"><?php echo number_format($rp['price'], 0, ',', '.'); ?>đ</span>
                        </div>
                        <div class="product-rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="far fa-star"></i>
                            <span>(0 đánh giá)</span>
                        </div>
                        <div class="product-footer">
                            <a href="product_detail.php?id=<?php echo $rp['id']; ?>" class="btn">Xem chi tiết</a>
                            <button class="wishlist-btn"><i class="far fa-heart"></i></button>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include('includes/footer.php'); ?>

<script>
// Quantity controls
document.getElementById('decrease-quantity').addEventListener('click', function() {
    const input = document.getElementById('quantity');
    if (input.value > 1) {
        input.value = parseInt(input.value) - 1;
        document.getElementById('cart-quantity').value = input.value;
    }
});

document.getElementById('increase-quantity').addEventListener('click', function() {
    const input = document.getElementById('quantity');
    const max = parseInt(input.getAttribute('max'));
    if (input.value < max) {
        input.value = parseInt(input.value) + 1;
        document.getElementById('cart-quantity').value = input.value;
    }
});

document.getElementById('quantity').addEventListener('change', function() {
    document.getElementById('cart-quantity').value = this.value;
});

// Product tabs
const tabBtns = document.querySelectorAll('.tab-btn');
const tabContents = document.querySelectorAll('.tab-content');

tabBtns.forEach(btn => {
    btn.addEventListener('click', () => {
        const tabId = btn.getAttribute('data-tab');
        
        tabBtns.forEach(b => b.classList.remove('active'));
        tabContents.forEach(c => c.classList.remove('active'));
        
        btn.classList.add('active');
        document.getElementById(tabId).classList.add('active');
    });
});

// Rating input
const ratingStars = document.querySelectorAll('.rating-input i');
const ratingValue = document.getElementById('rating-value');

ratingStars.forEach(star => {
    star.addEventListener('click', () => {
        const rating = parseInt(star.getAttribute('data-rating'));
        ratingValue.value = rating;
        
        ratingStars.forEach((s, index) => {
            if (index < rating) {
                s.classList.remove('far');
                s.classList.add('fas');
            } else {
                s.classList.remove('fas');
                s.classList.add('far');
            }
        });
    });
    
    star.addEventListener('mouseover', () => {
        const rating = parseInt(star.getAttribute('data-rating'));
        
        ratingStars.forEach((s, index) => {
            if (index < rating) {
                s.classList.remove('far');
                s.classList.add('fas');
            } else {
                s.classList.remove('fas');
                s.classList.add('far');
            }
        });
    });
});

// Gallery thumbnails
const thumbnails = document.querySelectorAll('.gallery-thumbnail');
const mainImage = document.querySelector('.gallery-main');

thumbnails.forEach(thumbnail => {
    thumbnail.addEventListener('click', () => {
        thumbnails.forEach(t => t.classList.remove('active'));
        thumbnail.classList.add('active');
        
        // Update main image background
        const bgImage = thumbnail.style.backgroundImage;
        mainImage.style.backgroundImage = bgImage;
    });
});
</script>