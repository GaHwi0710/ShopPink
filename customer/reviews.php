<?php
require_once '../includes/autoload.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Xử lý thêm/sửa đánh giá
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = intval($_POST['product_id']);
    $rating = intval($_POST['rating']);
    $comment = trim($_POST['comment']);
    $action = $_POST['action'] ?? 'add';
    
    if ($rating < 1 || $rating > 5) {
        $error = "Đánh giá phải từ 1-5 sao!";
    } elseif (empty($comment)) {
        $error = "Vui lòng nhập nội dung đánh giá!";
    } else {
        // Kiểm tra xem user đã mua sản phẩm này chưa
        $check_order_stmt = $conn->prepare("
            SELECT od.id FROM order_details od 
            JOIN orders o ON od.order_id = o.id 
            WHERE o.user_id = ? AND od.product_id = ? AND o.status = 'delivered'
        ");
        $check_order_stmt->bind_param("ii", $user_id, $product_id);
        $check_order_stmt->execute();
        
        if ($check_order_stmt->get_result()->num_rows == 0) {
            $error = "Bạn chỉ có thể đánh giá sản phẩm đã mua và nhận hàng!";
        } else {
            if ($action == 'add') {
                // Thêm đánh giá mới
                $insert_stmt = $conn->prepare("
                    INSERT INTO product_reviews (user_id, product_id, rating, comment, created_at) 
                    VALUES (?, ?, ?, ?, NOW())
                ");
                $insert_stmt->bind_param("iiis", $user_id, $product_id, $rating, $comment);
                
                if ($insert_stmt->execute()) {
                    $success = "Đánh giá đã được gửi thành công!";
                } else {
                    $error = "Có lỗi xảy ra khi gửi đánh giá!";
                }
            } elseif ($action == 'edit') {
                // Sửa đánh giá
                $review_id = intval($_POST['review_id']);
                $update_stmt = $conn->prepare("
                    UPDATE product_reviews 
                    SET rating = ?, comment = ?, updated_at = NOW()
                    WHERE id = ? AND user_id = ?
                ");
                $update_stmt->bind_param("isii", $rating, $comment, $review_id, $user_id);
                
                if ($update_stmt->execute()) {
                    $success = "Đánh giá đã được cập nhật thành công!";
                } else {
                    $error = "Có lỗi xảy ra khi cập nhật đánh giá!";
                }
            }
        }
    }
}

// Xử lý xóa đánh giá
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $review_id = intval($_GET['id']);
    
    $delete_stmt = $conn->prepare("DELETE FROM product_reviews WHERE id = ? AND user_id = ?");
    $delete_stmt->bind_param("ii", $review_id, $user_id);
    
    if ($delete_stmt->execute()) {
        $success = "Đánh giá đã được xóa thành công!";
    } else {
        $error = "Có lỗi xảy ra khi xóa đánh giá!";
    }
}

// Lấy đánh giá của user
$user_reviews_stmt = $conn->prepare("
    SELECT pr.*, p.name as product_name, p.image as product_image 
    FROM product_reviews pr 
    JOIN products p ON pr.product_id = p.id 
    WHERE pr.user_id = ? 
    ORDER BY pr.created_at DESC
");
$user_reviews_stmt->bind_param("i", $user_id);
$user_reviews_stmt->execute();
$user_reviews = $user_reviews_stmt->get_result();

// Lấy sản phẩm đã mua để đánh giá
$purchased_products_stmt = $conn->prepare("
    SELECT DISTINCT p.id, p.name, p.image, p.price 
    FROM products p 
    JOIN order_details od ON p.id = od.product_id 
    JOIN orders o ON od.order_id = o.id 
    WHERE o.user_id = ? AND o.status = 'delivered' 
    AND p.id NOT IN (
        SELECT product_id FROM product_reviews WHERE user_id = ?
    )
    ORDER BY o.created_at DESC
");
$purchased_products_stmt->bind_param("ii", $user_id, $user_id);
$purchased_products_stmt->execute();
$purchased_products = $purchased_products_stmt->get_result();

include('../includes/header.php');
?>

<div class="container">
    <div class="reviews-page">
        <h1 class="page-title">
            <i class="fas fa-star"></i> Đánh giá sản phẩm
        </h1>
        
        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <div class="reviews-container">
            <!-- Đánh giá sản phẩm mới -->
            <?php if ($purchased_products->num_rows > 0): ?>
                <div class="review-section">
                    <h3>Đánh giá sản phẩm mới</h3>
                    <p>Bạn có thể đánh giá các sản phẩm đã mua và nhận hàng:</p>
                    
                    <div class="products-to-review">
                        <?php while ($product = $purchased_products->fetch_assoc()): ?>
                            <div class="product-review-item">
                                <div class="product-info">
                                    <img src="../assets/images/products/<?php echo htmlspecialchars($product['image']); ?>" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>">
                                    <div class="product-details">
                                        <h4><?php echo htmlspecialchars($product['name']); ?></h4>
                                        <p class="price"><?php echo format_price($product['price']); ?></p>
                                    </div>
                                </div>
                                
                                <button class="btn btn-primary" onclick="showReviewForm(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars($product['name']); ?>')">
                                    <i class="fas fa-star"></i> Viết đánh giá
                                </button>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Đánh giá đã viết -->
            <div class="review-section">
                <h3>Đánh giá của tôi</h3>
                <?php if ($user_reviews->num_rows > 0): ?>
                    <div class="user-reviews">
                        <?php while ($review = $user_reviews->fetch_assoc()): ?>
                            <div class="review-item">
                                <div class="review-header">
                                    <div class="product-info">
                                        <img src="../assets/images/products/<?php echo htmlspecialchars($review['product_image']); ?>" 
                                             alt="<?php echo htmlspecialchars($review['product_name']); ?>">
                                        <div class="product-details">
                                            <h4><?php echo htmlspecialchars($review['product_name']); ?></h4>
                                            <div class="rating">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="fas fa-star <?php echo $i <= $review['rating'] ? 'active' : ''; ?>"></i>
                                                <?php endfor; ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="review-actions">
                                        <button class="btn btn-small btn-warning" 
                                                onclick="editReview(<?php echo $review['id']; ?>, <?php echo $review['rating']; ?>, '<?php echo htmlspecialchars($review['comment']); ?>')">
                                            <i class="fas fa-edit"></i> Sửa
                                        </button>
                                        <a href="?delete=1&id=<?php echo $review['id']; ?>" 
                                           class="btn btn-small btn-danger"
                                           onclick="return confirm('Bạn có chắc muốn xóa đánh giá này?')">
                                            <i class="fas fa-trash"></i> Xóa
                                        </a>
                                    </div>
                                </div>
                                
                                <div class="review-content">
                                    <p><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                                </div>
                                
                                <div class="review-footer">
                                    <small>Đánh giá vào: <?php echo date('d/m/Y H:i', strtotime($review['created_at'])); ?></small>
                                    <?php if ($review['updated_at']): ?>
                                        <small> - Cập nhật: <?php echo date('d/m/Y H:i', strtotime($review['updated_at'])); ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="no-reviews">
                        <p>Bạn chưa có đánh giá nào.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal đánh giá -->
<div id="reviewModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h3 id="modalTitle">Đánh giá sản phẩm</h3>
        
        <form method="POST" action="" id="reviewForm">
            <input type="hidden" name="product_id" id="modalProductId">
            <input type="hidden" name="action" id="modalAction" value="add">
            <input type="hidden" name="review_id" id="modalReviewId">
            
            <div class="form-group">
                <label>Sản phẩm:</label>
                <p id="modalProductName" class="product-name"></p>
            </div>
            
            <div class="form-group">
                <label for="modalRating">Đánh giá:</label>
                <div class="rating-input">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <input type="radio" name="rating" id="star<?php echo $i; ?>" value="<?php echo $i; ?>" required>
                        <label for="star<?php echo $i; ?>"><i class="fas fa-star"></i></label>
                    <?php endfor; ?>
                </div>
            </div>
            
            <div class="form-group">
                <label for="modalComment">Nội dung đánh giá:</label>
                <textarea name="comment" id="modalComment" rows="4" required 
                          placeholder="Hãy chia sẻ trải nghiệm của bạn về sản phẩm này..."></textarea>
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Hủy</button>
                <button type="submit" class="btn btn-primary">Gửi đánh giá</button>
            </div>
        </form>
    </div>
</div>

<script>
// Modal functions
function showReviewForm(productId, productName) {
    document.getElementById('modalProductId').value = productId;
    document.getElementById('modalProductName').textContent = productName;
    document.getElementById('modalAction').value = 'add';
    document.getElementById('modalRating').value = '';
    document.getElementById('modalComment').value = '';
    document.getElementById('modalTitle').textContent = 'Đánh giá sản phẩm';
    
    // Reset rating stars
    document.querySelectorAll('.rating-input input[type="radio"]').forEach(radio => {
        radio.checked = false;
    });
    
    document.getElementById('reviewModal').style.display = 'block';
}

function editReview(reviewId, rating, comment) {
    document.getElementById('modalAction').value = 'edit';
    document.getElementById('modalReviewId').value = reviewId;
    document.getElementById('modalTitle').textContent = 'Sửa đánh giá';
    
    // Set rating
    document.getElementById('star' + rating).checked = true;
    
    // Set comment
    document.getElementById('modalComment').value = comment;
    
    document.getElementById('reviewModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('reviewModal').style.display = 'none';
}

// Close modal when clicking on X or outside
document.querySelector('.close').onclick = closeModal;
window.onclick = function(event) {
    if (event.target == document.getElementById('reviewModal')) {
        closeModal();
    }
}
</script>

<?php include('../includes/footer.php'); ?>