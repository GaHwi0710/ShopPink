<?php
require_once '../includes/autoload.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header("Location: ../auth/login.php");
    exit();
}

$error = '';
$success = '';

// Xử lý các action
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add':
            $product_id = intval($_POST['product_id']);
            $quantity = intval($_POST['quantity']);
            
            if ($quantity > 0) {
                // Kiểm tra tồn kho
                $stock_stmt = $conn->prepare("SELECT stock FROM products WHERE id = ? AND status = 'active'");
                $stock_stmt->bind_param("i", $product_id);
                $stock_stmt->execute();
                $product = $stock_stmt->get_result()->fetch_assoc();
                
                if ($product && $product['stock'] >= $quantity) {
                    addToCart($product_id, $quantity);
                    $success = "Sản phẩm đã được thêm vào giỏ hàng!";
                } else {
                    $error = "Sản phẩm không đủ số lượng trong kho!";
                }
            }
            break;
            
        case 'update':
            $product_id = intval($_POST['product_id']);
            $quantity = intval($_POST['quantity']);
            
            if ($quantity > 0) {
                // Kiểm tra tồn kho
                $stock_stmt = $conn->prepare("SELECT stock FROM products WHERE id = ? AND status = 'active'");
                $stock_stmt->bind_param("i", $product_id);
                $stock_stmt->execute();
                $product = $stock_stmt->get_result()->fetch_assoc();
                
                if ($product && $product['stock'] >= $quantity) {
                    updateCart($product_id, $quantity);
                    $success = "Giỏ hàng đã được cập nhật!";
                } else {
                    $error = "Sản phẩm không đủ số lượng trong kho!";
                }
            } else {
                removeFromCart($product_id);
                $success = "Sản phẩm đã được xóa khỏi giỏ hàng!";
            }
            break;
            
        case 'remove':
            $product_id = intval($_POST['product_id']);
            removeFromCart($product_id);
            $success = "Sản phẩm đã được xóa khỏi giỏ hàng!";
            break;
            
        case 'clear':
            clearCart();
            $success = "Giỏ hàng đã được làm trống!";
            break;
    }
}

// Lấy danh sách sản phẩm trong giỏ hàng
$cart_items = getCartItems();
$cart_total = getCartTotal();

include('../includes/header.php');
?>

<div class="container">
    <div class="cart-page">
        <h1 class="page-title">
            <i class="fas fa-shopping-cart"></i> Giỏ hàng
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
        
        <?php if (empty($cart_items)): ?>
            <div class="empty-cart">
                <div class="empty-cart-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <h3>Giỏ hàng trống</h3>
                <p>Bạn chưa có sản phẩm nào trong giỏ hàng.</p>
                <a href="../products.php" class="btn btn-primary">
                    <i class="fas fa-shopping-bag"></i> Tiếp tục mua sắm
                </a>
            </div>
        <?php else: ?>
            <div class="cart-container">
                <div class="cart-items">
                    <h3>Sản phẩm trong giỏ hàng</h3>
                    
                    <?php foreach ($cart_items as $item): ?>
                        <div class="cart-item">
                            <div class="item-image">
                                <img src="../assets/images/products/<?php echo htmlspecialchars($item['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($item['name']); ?>">
                            </div>
                            
                            <div class="item-info">
                                <h4 class="item-name">
                                    <a href="../product_detail.php?id=<?php echo $item['id']; ?>">
                                        <?php echo htmlspecialchars($item['name']); ?>
                                    </a>
                                </h4>
                                <p class="item-category"><?php echo htmlspecialchars($item['category_name']); ?></p>
                                <p class="item-price"><?php echo format_price($item['price']); ?></p>
                            </div>
                            
                            <div class="item-quantity">
                                <form method="POST" action="" class="quantity-form">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                    
                                    <div class="quantity-controls">
                                        <button type="button" class="qty-btn" onclick="changeQuantity(<?php echo $item['id']; ?>, -1)">-</button>
                                        <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" 
                                               min="1" max="<?php echo $item['stock']; ?>" class="qty-input"
                                               onchange="updateQuantity(<?php echo $item['id']; ?>, this.value)">
                                        <button type="button" class="qty-btn" onclick="changeQuantity(<?php echo $item['id']; ?>, 1)">+</button>
                                    </div>
                                </form>
                            </div>
                            
                            <div class="item-subtotal">
                                <span class="subtotal-price"><?php echo format_price($item['subtotal']); ?></span>
                            </div>
                            
                            <div class="item-actions">
                                <form method="POST" action="" class="remove-form" style="display: inline;">
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-small" 
                                            onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="cart-summary">
                    <h3>Tổng kết giỏ hàng</h3>
                    
                    <div class="summary-item">
                        <span>Tổng tiền hàng:</span>
                        <span><?php echo format_price($cart_total); ?></span>
                    </div>
                    
                    <div class="summary-item">
                        <span>Phí vận chuyển:</span>
                        <span>Miễn phí</span>
                    </div>
                    
                    <div class="summary-total">
                        <span>Tổng cộng:</span>
                        <span><?php echo format_price($cart_total); ?></span>
                    </div>
                    
                    <div class="cart-actions">
                        <a href="../products.php" class="btn btn-outline-primary btn-block">
                            <i class="fas fa-arrow-left"></i> Tiếp tục mua sắm
                        </a>
                        
                        <form method="POST" action="" class="clear-form" style="display: inline;">
                            <input type="hidden" name="action" value="clear">
                            <button type="submit" class="btn btn-outline-danger btn-block"
                                    onclick="return confirm('Bạn có chắc muốn làm trống giỏ hàng?')">
                                <i class="fas fa-trash"></i> Làm trống giỏ hàng
                            </button>
                        </form>
                        
                        <a href="checkout.php" class="btn btn-primary btn-block">
                            <i class="fas fa-credit-card"></i> Tiến hành thanh toán
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function changeQuantity(productId, change) {
    const input = document.querySelector(`input[name="quantity"][onchange*="${productId}"]`);
    const newValue = parseInt(input.value) + change;
    
    if (newValue >= 1) {
        input.value = newValue;
        updateQuantity(productId, newValue);
    }
}

function updateQuantity(productId, quantity) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.innerHTML = `
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="product_id" value="${productId}">
        <input type="hidden" name="quantity" value="${quantity}">
    `;
    document.body.appendChild(form);
    form.submit();
}
</script>

<?php include('../includes/footer.php'); ?>