<?php
session_start();
include('includes/config.php');
// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
// Kiểm tra giỏ hàng
if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit();
}
$user_id = intval($_SESSION['user_id']);
// Lấy thông tin user
$user_stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();
if (!$user) {
    header("Location: logout.php");
    exit();
}
$cart = $_SESSION['cart'];
$product_ids = array_keys($cart);
$products = [];
$subtotal = 0;
if (!empty($product_ids)) {
    $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
    $types = str_repeat('i', count($product_ids));
    $product_stmt = $conn->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
    $product_stmt->bind_param($types, ...$product_ids);
    $product_stmt->execute();
    $products_result = $product_stmt->get_result();
    while ($row = $products_result->fetch_assoc()) {
        $quantity = intval($cart[$row['id']]['quantity'] ?? $cart[$row['id']]);
        $item_total = $row['price'] * $quantity;
        $subtotal += $item_total;
        $products[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'image' => $row['image'],
            'price' => $row['price'],
            'quantity' => $quantity,
            'total' => $item_total
        ];
    }
}
// Tính phí vận chuyển
$shipping_fee = 30000;
// Miễn phí vận chuyển cho đơn hàng từ 500.000đ
if ($subtotal >= 500000) {
    $shipping_fee = 0;
}
$total = $subtotal + $shipping_fee;
// Xử lý đặt hàng
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = trim($_POST['full_name']);
    $address = trim($_POST['address']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $payment_method = $_POST['payment_method'] ?? 'cod';
    $note = trim($_POST['note']);
    
    if (empty($full_name) || empty($address) || empty($phone) || empty($email)) {
        $error = "Vui lòng nhập đầy đủ thông tin.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Email không hợp lệ.";
    } elseif (!preg_match('/^[0-9]{9,11}$/', $phone)) {
        $error = "Số điện thoại không hợp lệ.";
    } else {
        $order_stmt = $conn->prepare("
            INSERT INTO orders (user_id, full_name, email, address, phone, subtotal, shipping_fee, total, payment_method, note, status, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
        ");
        $order_stmt->bind_param("issssiddsss", $user_id, $full_name, $email, $address, $phone, $subtotal, $shipping_fee, $total, $payment_method, $note);
        
        if ($order_stmt->execute()) {
            $order_id = $order_stmt->insert_id;
            $detail_stmt = $conn->prepare("
                INSERT INTO order_details (order_id, product_id, quantity, price) 
                VALUES (?, ?, ?, ?)
            ");
            foreach ($products as $item) {
                $detail_stmt->bind_param("iiid", $order_id, $item['id'], $item['quantity'], $item['price']);
                $detail_stmt->execute();
            }
            unset($_SESSION['cart']);
            header("Location: order_confirmation.php?id=" . $order_id);
            exit();
        } else {
            $error = "Lỗi đặt hàng: " . $conn->error;
        }
    }
}
?>
<?php include('includes/header.php'); ?>

<div class="container">
    <h1 class="section-title">Thanh toán</h1>
    
    <?php if (!empty($error)): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
        </div>
    <?php endif; ?>
    
    <div class="checkout-container">
        <!-- Checkout Form -->
        <div class="checkout-form-container animate-on-scroll">
            <h2>Thông tin giao hàng</h2>
            <form action="" method="post" class="checkout-form" id="checkout-form">
                <div class="form-group">
                    <label for="full_name">Họ và tên <span class="required">*</span></label>
                    <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name'] ?? $user['username']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email <span class="required">*</span></label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="phone">Số điện thoại <span class="required">*</span></label>
                    <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="address">Địa chỉ giao hàng <span class="required">*</span></label>
                    <textarea id="address" name="address" rows="3" required><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="note">Ghi chú (tùy chọn)</label>
                    <textarea id="note" name="note" rows="3" placeholder="Ghi chú thêm về đơn hàng, ví dụ: thời gian giao hàng cụ thể..."></textarea>
                </div>
                
                <h2>Phương thức thanh toán</h2>
                <div class="payment-methods">
                    <div class="payment-method">
                        <input type="radio" id="payment-cod" name="payment_method" value="cod" checked>
                        <label for="payment-cod">
                            <i class="fas fa-money-bill-wave"></i>
                            <span>Thanh toán khi nhận hàng (COD)</span>
                        </label>
                        <p>Nhân viên giao hàng sẽ thu tiền khi bạn nhận được hàng</p>
                    </div>
                    
                    <div class="payment-method">
                        <input type="radio" id="payment-bank" name="payment_method" value="bank">
                        <label for="payment-bank">
                            <i class="fas fa-university"></i>
                            <span>Chuyển khoản ngân hàng</span>
                        </label>
                        <p>Chuyển khoản trước khi nhận hàng. Thông tin tài khoản sẽ được gửi sau khi đặt hàng thành công.</p>
                    </div>
                    
                    <div class="payment-method">
                        <input type="radio" id="payment-card" name="payment_method" value="card">
                        <label for="payment-card">
                            <i class="fas fa-credit-card"></i>
                            <span>Thẻ tín dụng/ghi nợ</span>
                        </label>
                        <p>Thanh toán an toàn qua cổng thanh toán trực tuyến</p>
                    </div>
                    
                    <div class="payment-method">
                        <input type="radio" id="payment-momo" name="payment_method" value="momo">
                        <label for="payment-momo">
                            <i class="fas fa-wallet"></i>
                            <span>Ví điện tử (Momo, ZaloPay, VNPay)</span>
                        </label>
                        <p>Thanh toán nhanh chóng qua ví điện tử</p>
                    </div>
                </div>
                
                <div class="form-actions">
                    <a href="cart.php" class="btn btn-outline">
                        <i class="fas fa-arrow-left"></i> Quay lại giỏ hàng
                    </a>
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-lock"></i> Đặt hàng
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Order Summary -->
        <div class="checkout-summary animate-on-scroll">
            <h2>Đơn hàng của bạn</h2>
            
            <div class="order-items">
                <?php foreach ($products as $item): ?>
                    <div class="order-item">
                        <div class="order-item-img">
                            <img src="assets/images/products/<?php echo htmlspecialchars($item['image'] ?? 'default.jpg'); ?>" 
                                 alt="<?php echo htmlspecialchars($item['name']); ?>">
                        </div>
                        <div class="order-item-info">
                            <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                            <p>Số lượng: <?php echo $item['quantity']; ?></p>
                            <p class="order-item-price"><?php echo number_format($item['price'], 0, ',', '.'); ?>đ</p>
                        </div>
                        <div class="order-item-total">
                            <?php echo number_format($item['total'], 0, ',', '.'); ?>đ
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="order-summary-details">
                <div class="summary-row">
                    <span>Tạm tính:</span>
                    <span><?php echo number_format($subtotal, 0, ',', '.'); ?>đ</span>
                </div>
                <div class="summary-row">
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
                    <div class="promo-info">
                        <i class="fas fa-info-circle"></i>
                        <span>Mua thêm <?php echo number_format(500000 - $subtotal, 0, ',', '.'); ?>đ để được miễn phí vận chuyển</span>
                    </div>
                <?php endif; ?>
                <div class="summary-row summary-total">
                    <span>Tổng cộng:</span>
                    <span><?php echo number_format($total, 0, ',', '.'); ?>đ</span>
                </div>
            </div>
            
            <!-- Coupon Code -->
            <div class="coupon-section">
                <h4>Mã giảm giá</h4>
                <div class="coupon-form">
                    <input type="text" placeholder="Nhập mã giảm giá">
                    <button type="button" class="btn">Áp dụng</button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>

<script>
// Form validation
document.getElementById('checkout-form')?.addEventListener('submit', function(e) {
    const phone = document.getElementById('phone').value;
    const phoneRegex = /^[0-9]{9,11}$/;
    
    if (!phoneRegex.test(phone)) {
        e.preventDefault();
        showToast('error', 'Số điện thoại không hợp lệ. Vui lòng nhập lại.');
    }
});

// Payment method selection
document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.querySelectorAll('.payment-method').forEach(method => {
            method.classList.remove('selected');
        });
        this.closest('.payment-method').classList.add('selected');
    });
});

// Initialize first payment method as selected
document.querySelector('input[name="payment_method"]:checked')?.closest('.payment-method').classList.add('selected');

// Coupon code application
document.querySelector('.coupon-form button')?.addEventListener('click', function() {
    const couponInput = this.previousElementSibling;
    const couponCode = couponInput.value.trim();
    
    if (!couponCode) {
        showToast('error', 'Vui lòng nhập mã giảm giá');
        return;
    }
    
    // Simulate API call
    this.textContent = 'Đang xử lý...';
    this.disabled = true;
    
    setTimeout(() => {
        // Reset button
        this.textContent = 'Áp dụng';
        this.disabled = false;
        
        // Show message (in real app, this would be based on server response)
        if (couponCode === 'SHOPPINK10') {
            showToast('success', 'Áp dụng mã giảm giá thành công! Giảm 10% tổng đơn hàng.');
            // In real app, update the order summary
        } else {
            showToast('error', 'Mã giảm giá không hợp lệ hoặc đã hết hạn.');
        }
    }, 1000);
});
</script>