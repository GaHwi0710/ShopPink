<?php
require_once 'includes/autoload.php';

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$product_id) {
    header("Location: products.php");
    exit();
}

// Lấy thông tin sản phẩm
$product = getProductById($product_id);

if (!$product) {
    header("Location: products.php");
    exit();
}

// Lưu lịch sử xem sản phẩm nếu user đã đăng nhập
if (is_logged_in()) {
    $view_stmt = $conn->prepare("
        INSERT INTO user_views (user_id, product_id, viewed_at) 
        VALUES (?, ?, NOW()) 
        ON DUPLICATE KEY UPDATE viewed_at = NOW()
    ");
    $view_stmt->bind_param("ii", $_SESSION['user_id'], $product_id);
    $view_stmt->execute();
}

// Lấy đánh giá sản phẩm
$reviews_stmt = $conn->prepare("
    SELECT pr.*, u.full_name, u.username 
    FROM product_reviews pr 
    JOIN users u ON pr.user_id = u.id 
    WHERE pr.product_id = ? 
    ORDER BY pr.created_at DESC 
    LIMIT 10
");
$reviews_stmt->bind_param("i", $product_id);
$reviews_stmt->execute();
$reviews = $reviews_stmt->get_result();

// Lấy sản phẩm liên quan
$related_stmt = $conn->prepare("
    SELECT p.*, COALESCE(AVG(pr.rating), 0) as avg_rating 
    FROM products p 
    LEFT JOIN product_reviews pr ON p.id = pr.product_id 
    WHERE p.category_id = ? AND p.id != ? AND p.status = 'active' 
    GROUP BY p.id 
    ORDER BY p.sold_count DESC 
    LIMIT 4
");
$related_stmt->bind_param("ii", $product['category_id'], $product_id);
$related_stmt->execute();
$related_products = $related_stmt->get_result();

$page_title = $product['name'];
$page_description = substr(strip_tags($product['description']), 0, 160);
include('includes/header.php');
?>

<div class="container">
    <div class="product-detail-page">
        <!-- Breadcrumb -->
        <nav class="breadcrumb">
            <a href="index.php">Trang chủ</a>
            <span>/</span>
            <a href="products.php">Sản phẩm</a>
            <span>/</span>
            <a href="category.php?id=<?php echo $product['category_id']; ?>">
                <?php echo htmlspecialchars($product['category_name']); ?>
            </a>
            <span>/</span>
            <span><?php echo htmlspecialchars($product['name']); ?></span>
        </nav>
        
        <div class="product-detail-container">
            <!-- Hình ảnh sản phẩm -->
            <div class="product-images">
                <div class="main-image">
                    <img id="mainImage" src="assets/images/products/<?php echo htmlspecialchars($product['image']); ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>">
                    
                    <?php if ($product['discount'] > 0): ?>
                        <div class="discount-badge">
                            -<?php echo $product['discount']; ?>%
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Thumbnail images (nếu có nhiều ảnh) -->
                <div class="thumbnail-images">
                    <img src="assets/images/products/<?php echo htmlspecialchars($product['image']); ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                         onclick="changeMainImage(this.src)" class="active">
                    
                    <?php
                    // Giả sử có thêm ảnh phụ (image_2, image_3, v.v.)
                    for ($i = 2; $i <= 4; $i++) {
                        $img_field = 'image_' . $i;
                        if (!empty($product[$img_field])) {
                            echo '<img src="assets/images/products/' . htmlspecialchars($product[$img_field]) . '" 
                                      alt="' . htmlspecialchars($product['name']) . '" 
                                      onclick="changeMainImage(this.src)">';
                        }
                    }
                    ?>
                </div>
            </div>
            
            <!-- Thông tin sản phẩm -->
            <div class="product-info">
                <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>
                
                <div class="product-rating">
                    <div class="stars">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star <?php echo $i <= $product['avg_rating'] ? 'active' : ''; ?>"></i>
                        <?php endfor; ?>
                    </div>
                    <span class="rating-text">
                        (<?php echo $product['review_count']; ?> đánh giá)
                    </span>
                    <span class="sold-count">
                        Đã bán: <?php echo $product['sold_count']; ?>
                    </span>
                </div>
                
                <div class="product-price">
                    <?php if ($product['discount'] > 0): ?>
                        <span class="old-price"><?php echo format_price($product['price']); ?></span>
                        <span class="new-price">
                            <?php echo format_price($product['price'] * (1 - $product['discount'] / 100)); ?>
                        </span>
                        <span class="discount-percent">-<?php echo $product['discount']; ?>%</span>
                    <?php else: ?>
                        <span class="current-price"><?php echo format_price($product['price']); ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="product-stock">
                    <?php if ($product['stock'] > 0): ?>
                        <span class="in-stock">
                            <i class="fas fa-check-circle"></i> Còn hàng (<?php echo $product['stock']; ?> sản phẩm)
                        </span>
                    <?php else: ?>
                        <span class="out-of-stock">
                            <i class="fas fa-times-circle"></i> Hết hàng
                        </span>
                    <?php endif; ?>
                </div>
                
                <!-- Mô tả ngắn -->
                <div class="product-summary">
                    <?php echo nl2br(htmlspecialchars($product['summary'] ?? '')); ?>
                </div>
                
                <!-- Form thêm vào giỏ hàng -->
                <?php if ($product['stock'] > 0): ?>
                    <form class="add-to-cart-form" onsubmit="return addToCartForm(event)">
                        <div class="quantity-selector">
                            <label for="quantity">Số lượng:</label>
                            <div class="quantity-controls">
                                <button type="button" onclick="changeQuantity(-1)">-</button>
                                <input type="number" id="quantity" name="quantity" value="1" 
                                       min="1" max="<?php echo $product['stock']; ?>">
                                <button type="button" onclick="changeQuantity(1)">+</button>
                            </div>
                        </div>
                        
                        <div class="product-actions">
                            <button type="submit" class="btn btn-primary btn-large">
                                <i class="fas fa-shopping-cart"></i> Thêm vào giỏ hàng
                            </button>
                            
                            <button type="button" class="btn btn-outline-secondary" onclick="addToWishlist(<?php echo $product_id; ?>)">
                                <i class="fas fa-heart"></i> Yêu thích
                            </button>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="out-of-stock-actions">
                        <button class="btn btn-secondary btn-large" disabled>
                            <i class="fas fa-times"></i> Hết hàng
                        </button>
                        
                        <button class="btn btn-outline-primary" onclick="notifyWhenAvailable(<?php echo $product_id; ?>)">
                            <i class="fas fa-bell"></i> Báo khi có hàng
                        </button>
                    </div>
                <?php endif; ?>
                
                <!-- Thông tin thêm -->
                <div class="product-meta">
                    <div class="meta-item">
                        <strong>Danh mục:</strong>
                        <a href="category.php?id=<?php echo $product['category_id']; ?>">
                            <?php echo htmlspecialchars($product['category_name']); ?>
                        </a>
                    </div>
                    
                    <div class="meta-item">
                        <strong>Mã sản phẩm:</strong>
                        <span><?php echo htmlspecialchars($product['sku'] ?? 'SP' . $product['id']); ?></span>
                    </div>
                    
                    <?php if (!empty($product['brand'])): ?>
                        <div class="meta-item">
                            <strong>Thương hiệu:</strong>
                            <span><?php echo htmlspecialchars($product['brand']); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($product['tags'])): ?>
                        <div class="meta-item">
                            <strong>Tags:</strong>
                            <div class="product-tags">
                                <?php 
                                $tags = explode(',', $product['tags']);
                                foreach ($tags as $tag): 
                                    $tag = trim($tag);
                                    if (!empty($tag)):
                                ?>
                                    <span class="tag"><?php echo htmlspecialchars($tag); ?></span>
                                <?php 
                                    endif;
                                endforeach; 
                                ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Tab content -->
        <div class="product-tabs">
            <div class="tab-buttons">
                <button class="tab-btn active" onclick="openTab(event, 'description')">Mô tả</button>
                <button class="tab-btn" onclick="openTab(event, 'specifications')">Thông số</button>
                <button class="tab-btn" onclick="openTab(event, 'reviews')">Đánh giá (<?php echo $product['review_count']; ?>)</button>
            </div>
            
            <div id="description" class="tab-content active">
                <div class="description-content">
                    <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                </div>
            </div>
            
            <div id="specifications" class="tab-content">
                <div class="specifications-content">
                    <table class="specs-table">
                        <tr>
                            <td>Tên sản phẩm</td>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                        </tr>
                        <?php if (!empty($product['brand'])): ?>
                            <tr>
                                <td>Thương hiệu</td>
                                <td><?php echo htmlspecialchars($product['brand']); ?></td>
                            </tr>
                        <?php endif; ?>
                        <tr>
                            <td>Danh mục</td>
                            <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                        </tr>
                        <tr>
                            <td>Xuất xứ</td>
                            <td><?php echo htmlspecialchars($product['origin'] ?? 'Đang cập nhật'); ?></td>
                        </tr>
                        <tr>
                            <td>Trọng lượng</td>
                            <td><?php echo htmlspecialchars($product['weight'] ?? 'Đang cập nhật'); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <div id="reviews" class="tab-content">
                <div class="reviews-section">
                    <?php if ($reviews->num_rows > 0): ?>
                        <div class="reviews-list">
                            <?php while ($review = $reviews->fetch_assoc()): ?>
                                <div class="review-item">
                                    <div class="review-header">
                                        <div class="reviewer-info">
                                            <strong><?php echo htmlspecialchars($review['full_name']); ?></strong>
                                            <div class="review-rating">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="fas fa-star <?php echo $i <= $review['rating'] ? 'active' : ''; ?>"></i>
                                                <?php endfor; ?>
                                            </div>
                                        </div>
                                        <div class="review-date">
                                            <?php echo date('d/m/Y', strtotime($review['created_at'])); ?>
                                        </div>
                                    </div>
                                    
                                    <div class="review-content">
                                        <?php echo nl2br(htmlspecialchars($review['comment'])); ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                        
                        <div class="reviews-actions">
                            <a href="search.php?search=<?php echo urlencode($product['name']); ?>#reviews" 
                               class="btn btn-outline-primary">
                                Xem tất cả đánh giá
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="no-reviews">
                            <p>Chưa có đánh giá nào cho sản phẩm này.</p>
                            <?php if (is_logged_in()): ?>
                                <a href="customer/reviews.php" class="btn btn-primary">
                                    <i class="fas fa-star"></i> Viết đánh giá đầu tiên
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Sản phẩm liên quan -->
        <?php if ($related_products->num_rows > 0): ?>
            <section class="related-products">
                <h3 class="section-title">
                    <i class="fas fa-heart"></i> Sản phẩm liên quan
                </h3>
                
                <div class="products-grid">
                    <?php while ($related = $related_products->fetch_assoc()): ?>
                        <div class="product-card">
                            <div class="product-image">
                                <a href="product_detail.php?id=<?php echo $related['id']; ?>">
                                    <img src="assets/images/products/<?php echo htmlspecialchars($related['image']); ?>" 
                                         alt="<?php echo htmlspecialchars($related['name']); ?>">
                                </a>
                                
                                <?php if ($related['discount'] > 0): ?>
                                    <div class="discount-badge">
                                        -<?php echo $related['discount']; ?>%
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="product-info">
                                <h4 class="product-name">
                                    <a href="product_detail.php?id=<?php echo $related['id']; ?>">
                                        <?php echo htmlspecialchars($related['name']); ?>
                                    </a>
                                </h4>
                                
                                <div class="product-rating">
                                    <div class="stars">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star <?php echo $i <= $related['avg_rating'] ? 'active' : ''; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                
                                <div class="product-price">
                                    <?php if ($related['discount'] > 0): ?>
                                        <span class="old-price"><?php echo format_price($related['price']); ?></span>
                                        <span class="new-price">
                                            <?php echo format_price($related['price'] * (1 - $related['discount'] / 100)); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="price"><?php echo format_price($related['price']); ?></span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="product-actions">
                                    <button class="btn btn-primary btn-block add-to-cart-btn" 
                                            onclick="addToCart(<?php echo $related['id']; ?>)">
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
</div>

<script>
// Tab functionality
function openTab(evt, tabName) {
    var i, tabcontent, tablinks;
    tabcontent = document.getElementsByClassName("tab-content");
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].classList.remove("active");
    }
    tablinks = document.getElementsByClassName("tab-btn");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].classList.remove("active");
    }
    document.getElementById(tabName).classList.add("active");
    evt.currentTarget.classList.add("active");
}

// Image gallery
function changeMainImage(src) {
    document.getElementById('mainImage').src = src;
    
    // Update active thumbnail
    var thumbnails = document.querySelectorAll('.thumbnail-images img');
    thumbnails.forEach(function(img) {
        img.classList.remove('active');
        if (img.src === src) {
            img.classList.add('active');
        }
    });
}

// Quantity controls
function changeQuantity(change) {
    var quantityInput = document.getElementById('quantity');
    var currentValue = parseInt(quantityInput.value);
    var newValue = currentValue + change;
    var maxValue = parseInt(quantityInput.max);
    
    if (newValue >= 1 && newValue <= maxValue) {
        quantityInput.value = newValue;
    }
}

// Add to cart
function addToCartForm(event) {
    event.preventDefault();
    
    var quantity = document.getElementById('quantity').value;
    
    <?php if (is_logged_in()): ?>
        // Nếu đã đăng nhập, thêm vào giỏ hàng
        fetch('customer/cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=add&product_id=<?php echo $product_id; ?>&quantity=' + quantity
        })
        .then(response => response.text())
        .then(data => {
            alert('Sản phẩm đã được thêm vào giỏ hàng!');
            // Cập nhật số lượng giỏ hàng trên header
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
    
    return false;
}

function addToCart(productId) {
    <?php if (is_logged_in()): ?>
        fetch('customer/cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=add&product_id=' + productId + '&quantity=1'
        })
        .then(response => response.text())
        .then(data => {
            alert('Sản phẩm đã được thêm vào giỏ hàng!');
            location.reload();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Có lỗi xảy ra khi thêm vào giỏ hàng!');
        });
    <?php else: ?>
        if (confirm('Bạn cần đăng nhập để thêm sản phẩm vào giỏ hàng. Đăng nhập ngay?')) {
            window.location.href = 'auth/login.php';
        }
    <?php endif; ?>
}

function addToWishlist(productId) {
    <?php if (is_logged_in()): ?>
        alert('Tính năng yêu thích đang được phát triển!');
    <?php else: ?>
        if (confirm('Bạn cần đăng nhập để sử dụng tính năng yêu thích. Đăng nhập ngay?')) {
            window.location.href = 'auth/login.php';
        }
    <?php endif; ?>
}

function notifyWhenAvailable(productId) {
    <?php if (is_logged_in()): ?>
        alert('Chúng tôi sẽ thông báo khi sản phẩm có hàng trở lại!');
    <?php else: ?>
        if (confirm('Bạn cần đăng nhập để sử dụng tính năng này. Đăng nhập ngay?')) {
            window.location.href = 'auth/login.php';
        }
    <?php endif; ?>
}
</script>

<?php include('includes/footer.php'); ?>
