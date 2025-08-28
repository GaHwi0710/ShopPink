<?php
session_start();
include('includes/config.php');
// Nếu giỏ hàng chưa có, tạo mảng rỗng
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
$cart = $_SESSION['cart'];
$subtotal = 0;
$shipping_fee = 30000; // Phí vận chuyển cố định
foreach ($cart as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$total = $subtotal + $shipping_fee;
// Miễn phí vận chuyển cho đơn hàng từ 500.000đ
if ($subtotal >= 500000) {
    $shipping_fee = 0;
    $total = $subtotal;
}
?>
<?php include('includes/header.php'); ?>

<div class="container">
    <h1 class="section-title">Giỏ hàng của bạn</h1>
    
    <?php if (empty($cart)): ?>
        <div class="empty-cart animate-on-scroll">
            <img src="assets/images/empty-cart.png" alt="Giỏ hàng trống">
            <h3>Giỏ hàng của bạn đang trống</h3>
            <p>Hãy thêm sản phẩm vào giỏ hàng để tiếp tục mua sắm</p>
            <a href="index.php" class="btn">Tiếp tục mua sắm</a>
        </div>
    <?php else: ?>
        <div class="cart-container animate-on-scroll">
            <!-- Cart Table -->
            <div class="cart-table-container">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th>Giá</th>
                            <th>Số lượng</th>
                            <th>Thành tiền</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart as $id => $item): ?>
                            <tr>
                                <td data-label="Sản phẩm">
                                    <div class="cart-item-info">
                                        <div class="cart-item-img">
                                            <img src="assets/images/products/<?php echo htmlspecialchars($item['image'] ?? 'default.jpg'); ?>" 
                                                 alt="<?php echo htmlspecialchars($item['name']); ?>">
                                        </div>
                                        <div class="cart-item-details">
                                            <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                                            <p class="cart-item-category"><?php echo htmlspecialchars($item['category'] ?? ''); ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td data-label="Giá">
                                    <span class="cart-item-price"><?php echo number_format($item['price'], 0, ',', '.'); ?>đ</span>
                                </td>
                                <td data-label="Số lượng">
                                    <div class="quantity-control">
                                        <a href="update_cart.php?action=decrease&id=<?php echo $id; ?>" class="quantity-btn decrease">-</a>
                                        <input type="text" value="<?php echo $item['quantity']; ?>" readonly>
                                        <a href="update_cart.php?action=increase&id=<?php echo $id; ?>" class="quantity-btn increase">+</a>
                                    </div>
                                </td>
                                <td data-label="Thành tiền">
                                    <span class="cart-item-total"><?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?>đ</span>
                                </td>
                                <td data-label="">
                                    <a href="update_cart.php?action=remove&id=<?php echo $id; ?>" class="remove-item" title="Xóa sản phẩm">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Cart Summary -->
            <div class="cart-summary">
                <h3>Tóm tắt đơn hàng</h3>
                <div class="summary-item">
                    <span>Tạm tính:</span>
                    <span><?php echo number_format($subtotal, 0, ',', '.'); ?>đ</span>
                </div>
                <div class="summary-item">
                    <span>Phí vận chuyển:</span>
                    <span>
                        <?php if ($shipping_fee == 0): ?>
                            <span style="color: var(--success-color);">Miễn phí</span>
                        <?php else: ?>
                            <?php echo number_format($shipping_fee, 0, ',', '.'); ?>đ
                        <?php endif; ?>
                    </span>
                </div>
                <?php if ($subtotal < 500000): ?>
                    <div class="summary-item promo-info">
                        <span>Mua thêm <?php echo number_format(500000 - $subtotal, 0, ',', '.'); ?>đ để được miễn phí vận chuyển</span>
                    </div>
                <?php endif; ?>
                <div class="summary-item summary-total">
                    <span>Tổng cộng:</span>
                    <span><?php echo number_format($total, 0, ',', '.'); ?>đ</span>
                </div>
                <div class="cart-actions">
                    <a href="index.php" class="btn btn-outline">Tiếp tục mua sắm</a>
                    <a href="checkout.php" class="btn checkout-btn">Tiến hành thanh toán</a>
                </div>
                
                <!-- Coupon Code -->
                <div class="coupon-section">
                    <h4>Mã giảm giá</h4>
                    <form action="apply_coupon.php" method="post" class="coupon-form">
                        <input type="text" name="coupon_code" placeholder="Nhập mã giảm giá">
                        <button type="submit" class="btn">Áp dụng</button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Recommended Products -->
        <div class="section">
            <h2 class="section-title">Sản phẩm gợi ý</h2>
            <div class="products-grid">
                <?php
                // Lấy sản phẩm gợi ý (ngẫu nhiên)
                $recommended_query = "SELECT * FROM products ORDER BY RAND() LIMIT 4";
                $recommended_result = mysqli_query($conn, $recommended_query);
                while ($product = mysqli_fetch_assoc($recommended_result)):
                ?>
                    <div class="product" data-category="<?php echo $product['category_id']; ?>" data-price="<?php echo $product['price']; ?>">
                        <div class="product-img" style="background-image: url('assets/images/products/<?php echo htmlspecialchars($product['image'] ?? 'default.jpg'); ?>');"></div>
                        <div class="product-info">
                            <div class="product-vendor"><?php echo htmlspecialchars($product['category_name'] ?? ''); ?></div>
                            <div class="product-title"><?php echo htmlspecialchars($product['name']); ?></div>
                            <div class="product-price">
                                <span class="current-price"><?php echo number_format($product['price'], 0, ',', '.'); ?>đ</span>
                            </div>
                            <div class="product-rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="far fa-star"></i>
                                <span>(<?php echo rand(5, 30); ?> đánh giá)</span>
                            </div>
                            <div class="product-footer">
                                <a href="product_detail.php?id=<?php echo $product['id']; ?>" class="btn">Xem chi tiết</a>
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
// Cart item quantity update
document.querySelectorAll('.quantity-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        window.location.href = this.getAttribute('href');
    });
});

// Remove item confirmation
document.querySelectorAll('.remove-item').forEach(btn => {
    btn.addEventListener('click', function(e) {
        if (!confirm('Bạn có chắc chắn muốn xóa sản phẩm này khỏi giỏ hàng?')) {
            e.preventDefault();
        }
    });
});

// Coupon form submission
document.querySelector('.coupon-form')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const couponCode = this.querySelector('input[name="coupon_code"]').value.trim();
    
    if (!couponCode) {
        showToast('error', 'Vui lòng nhập mã giảm giá');
        return;
    }
    
    // Simulate API call
    this.querySelector('button[type="submit"]').textContent = 'Đang xử lý...';
    this.querySelector('button[type="submit"]').disabled = true;
    
    setTimeout(() => {
        // Reset button
        this.querySelector('button[type="submit"]').textContent = 'Áp dụng';
        this.querySelector('button[type="submit"]').disabled = false;
        
        // Show message (in real app, this would be based on server response)
        if (couponCode === 'SHOPPINK10') {
            showToast('success', 'Áp dụng mã giảm giá thành công! Giảm 10% tổng đơn hàng.');
            // In real app, update the cart summary
        } else {
            showToast('error', 'Mã giảm giá không hợp lệ hoặc đã hết hạn.');
        }
    }, 1000);
});
</script>