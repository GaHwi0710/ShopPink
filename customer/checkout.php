<?php
require_once '../includes/autoload.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header("Location: ../auth/login.php");
    exit();
}

// Kiểm tra giỏ hàng
$cart_items = getCartItems();
if (empty($cart_items)) {
    header("Location: cart.php");
    exit();
}

$error = '';
$success = '';

// Lấy thông tin user
$user_stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$user_stmt->bind_param("i", $_SESSION['user_id']);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();

// Xử lý checkout
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $payment_method = $_POST['payment_method'];
    $notes = trim($_POST['notes']);
    
    // Validation
    if (empty($full_name) || empty($phone) || empty($address) || empty($city)) {
        $error = "Vui lòng nhập đầy đủ thông tin bắt buộc!";
    } elseif (!preg_match('/^[0-9]{10,11}$/', $phone)) {
        $error = "Số điện thoại không hợp lệ!";
    } elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Email không hợp lệ!";
    } else {
        // Kiểm tra tồn kho
        $stock_ok = true;
        foreach ($cart_items as $item) {
            $stock_stmt = $conn->prepare("SELECT stock FROM products WHERE id = ?");
            $stock_stmt->bind_param("i", $item['id']);
            $stock_stmt->execute();
            $product = $stock_stmt->get_result()->fetch_assoc();
            
            if (!$product || $product['stock'] < $item['quantity']) {
                $stock_ok = false;
                $error = "Sản phẩm " . $item['name'] . " không đủ số lượng trong kho!";
                break;
            }
        }
        
        if ($stock_ok) {
            // Tạo đơn hàng
            $shipping_info = [
                'full_name' => $full_name,
                'phone' => $phone,
                'email' => $email,
                'address' => $address,
                'city' => $city
            ];
            
            $order_id = createOrder($_SESSION['user_id'], $shipping_info, $cart_items, $payment_method, $notes);
            
            if ($order_id) {
                // Xóa giỏ hàng
                clearCart();
                
                // Chuyển đến trang xác nhận đơn hàng
                header("Location: order_confirmation.php?order_id=" . $order_id);
                exit();
            } else {
                $error = "Có lỗi xảy ra khi tạo đơn hàng!";
            }
        }
    }
}

$cart_total = getCartTotal();

include('../includes/header.php');
?>

<div class="container">
    <div class="checkout-page">
        <h1 class="page-title">
            <i class="fas fa-credit-card"></i> Thanh toán
        </h1>
        
        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <div class="checkout-container">
            <div class="checkout-form">
                <h3>Thông tin giao hàng</h3>
                
                <form method="POST" action="" class="checkout-form-content">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="full_name">Họ và tên *</label>
                            <input type="text" id="full_name" name="full_name" required 
                                   value="<?php echo htmlspecialchars($full_name ?? $user['full_name'] ?? ''); ?>"
                                   placeholder="Nhập họ và tên đầy đủ">
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Số điện thoại *</label>
                            <input type="tel" id="phone" name="phone" required 
                                   value="<?php echo htmlspecialchars($phone ?? $user['phone'] ?? ''); ?>"
                                   placeholder="Nhập số điện thoại"
                                   pattern="[0-9]{10,11}">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($email ?? $user['email'] ?? ''); ?>"
                                   placeholder="Nhập email (không bắt buộc)">
                        </div>
                        
                        <div class="form-group">
                            <label for="city">Thành phố *</label>
                            <select id="city" name="city" required>
                                <option value="">Chọn thành phố</option>
                                <option value="Hà Nội" <?php echo (isset($city) && $city == 'Hà Nội') ? 'selected' : ''; ?>>Hà Nội</option>
                                <option value="TP. Hồ Chí Minh" <?php echo (isset($city) && $city == 'TP. Hồ Chí Minh') ? 'selected' : ''; ?>>TP. Hồ Chí Minh</option>
                                <option value="Đà Nẵng" <?php echo (isset($city) && $city == 'Đà Nẵng') ? 'selected' : ''; ?>>Đà Nẵng</option>
                                <option value="Hải Phòng" <?php echo (isset($city) && $city == 'Hải Phòng') ? 'selected' : ''; ?>>Hải Phòng</option>
                                <option value="Cần Thơ" <?php echo (isset($city) && $city == 'Cần Thơ') ? 'selected' : ''; ?>>Cần Thơ</option>
                                <option value="Khác" <?php echo (isset($city) && $city == 'Khác') ? 'selected' : ''; ?>>Khác</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Địa chỉ chi tiết *</label>
                        <textarea id="address" name="address" rows="3" required 
                                  placeholder="Nhập địa chỉ chi tiết (số nhà, đường, phường/xã, quận/huyện)"><?php echo htmlspecialchars($address ?? $user['address'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="payment_method">Phương thức thanh toán *</label>
                        <select id="payment_method" name="payment_method" required>
                            <option value="">Chọn phương thức thanh toán</option>
                            <option value="cod" <?php echo (isset($payment_method) && $payment_method == 'cod') ? 'selected' : ''; ?>>Thanh toán khi nhận hàng (COD)</option>
                            <option value="bank" <?php echo (isset($payment_method) && $payment_method == 'bank') ? 'selected' : ''; ?>>Chuyển khoản ngân hàng</option>
                            <option value="momo" <?php echo (isset($payment_method) && $payment_method == 'momo') ? 'selected' : ''; ?>>Ví MoMo</option>
                            <option value="vnpay" <?php echo (isset($payment_method) && $payment_method == 'vnpay') ? 'selected' : ''; ?>>VNPay</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="notes">Ghi chú</label>
                        <textarea id="notes" name="notes" rows="3" 
                                  placeholder="Ghi chú thêm về đơn hàng (không bắt buộc)"><?php echo htmlspecialchars($notes ?? ''); ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-credit-card"></i> Xác nhận đặt hàng
                    </button>
                </form>
            </div>
            
            <div class="order-summary">
                <h3>Thông tin đơn hàng</h3>
                
                <div class="order-items">
                    <?php foreach ($cart_items as $item): ?>
                        <div class="order-item">
                            <div class="item-image">
                                <img src="../assets/images/products/<?php echo htmlspecialchars($item['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($item['name']); ?>">
                            </div>
                            
                            <div class="item-info">
                                <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                                <p class="item-price"><?php echo format_price($item['price']); ?></p>
                                <p class="item-quantity">Số lượng: <?php echo $item['quantity']; ?></p>
                            </div>
                            
                            <div class="item-subtotal">
                                <?php echo format_price($item['subtotal']); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="order-total">
                    <div class="total-row">
                        <span>Tổng tiền hàng:</span>
                        <span><?php echo format_price($cart_total); ?></span>
                    </div>
                    
                    <div class="total-row">
                        <span>Phí vận chuyển:</span>
                        <span>Miễn phí</span>
                    </div>
                    
                    <div class="total-row final-total">
                        <span>Tổng cộng:</span>
                        <span><?php echo format_price($cart_total); ?></span>
                    </div>
                </div>
                
                <div class="order-actions">
                    <a href="cart.php" class="btn btn-outline-primary btn-block">
                        <i class="fas fa-arrow-left"></i> Quay lại giỏ hàng
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>