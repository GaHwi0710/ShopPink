<?php
// cart.php
// Trang giỏ hàng
require_once 'config.php';
// Kiểm tra người dùng đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
// Xử lý các hành động với giỏ hàng
$message = '';
$message_type = '';
// Xóa sản phẩm khỏi giỏ hàng
if (isset($_GET['action']) && $_GET['action'] === 'remove' && isset($_GET['id'])) {
    $product_id = (int)$_GET['id'];
    
    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $key => $item) {
            if ($item['id'] == $product_id) {
                unset($_SESSION['cart'][$key]);
                $_SESSION['cart'] = array_values($_SESSION['cart']); // Reindex array
                $message = 'Đã xóa sản phẩm khỏi giỏ hàng';
                $message_type = 'success';
                break;
            }
        }
    }
    
    header("Location: cart.php?message=" . urlencode($message) . "&type=" . $message_type);
    exit;
}
// Cập nhật số lượng sản phẩm
if (isset($_POST['update_cart']) && isset($_SESSION['cart'])) {
    $quantities = $_POST['quantity'] ?? [];
    
    foreach ($_SESSION['cart'] as $key => $item) {
        $product_id = $item['id'];
        $new_quantity = isset($quantities[$product_id]) ? (int)$quantities[$product_id] : $item['quantity'];
        
        if ($new_quantity <= 0) {
            unset($_SESSION['cart'][$key]);
        } else {
            // Kiểm tra tồn kho
            $stmt = $conn->prepare("SELECT stock FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($product && $new_quantity <= $product['stock']) {
                $_SESSION['cart'][$key]['quantity'] = $new_quantity;
            } else {
                $_SESSION['cart'][$key]['quantity'] = $product['stock'];
                $message = 'Số lượng sản phẩm "' . $item['name'] . '" đã được điều chỉnh theo tồn kho';
                $message_type = 'warning';
            }
        }
    }
    
    $_SESSION['cart'] = array_values($_SESSION['cart']); // Reindex array
    
    if (empty($message)) {
        $message = 'Đã cập nhật giỏ hàng';
        $message_type = 'success';
    }
    
    header("Location: cart.php?message=" . urlencode($message) . "&type=" . $message_type);
    exit;
}
// Lấy thông báo từ URL
$message = isset($_GET['message']) ? $_GET['message'] : '';
$message_type = isset($_GET['type']) ? $_GET['type'] : '';
// Tính tổng tiền
$cart_total = 0;
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_total += $item['price'] * $item['quantity'];
    }
}
// Kiểm tra người dùng đã đăng nhập chưa
$is_logged_in = isset($_SESSION['user_id']);
$user_role = $is_logged_in ? $_SESSION['user_role'] : '';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ hàng - ShopPink</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">
                <a href="index.php">ShopPink</a>
            </div>
            <nav>
                <ul>
                    <li><a href="index.php">Trang chủ</a></li>
                    <li><a href="products.php">Sản phẩm</a></li>
                    <?php if ($is_logged_in): ?>
                        <?php if ($user_role === 'seller'): ?>
                            <li><a href="seller.php">Quản lý</a></li>
                            <li><a href="report.php">Báo cáo</a></li>
                        <?php endif; ?>
                        <li><a href="cart.php" class="active"><i class="fas fa-shopping-cart"></i> Giỏ hàng</a></li>
                        <li><a href="logout.php">Đăng xuất</a></li>
                    <?php else: ?>
                        <li><a href="login.php">Đăng nhập</a></li>
                        <li><a href="register.php">Đăng ký</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
    <main>
        <section class="cart-page">
            <div class="container">
                <h1>Giỏ hàng của bạn</h1>
                
                <?php if (!empty($message)): ?>
                    <div class="alert alert-<?php echo $message_type; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])): ?>
                    <div class="empty-cart">
                        <i class="fas fa-shopping-cart"></i>
                        <h2>Giỏ hàng của bạn đang trống</h2>
                        <p>Hãy thêm sản phẩm vào giỏ hàng để tiếp tục mua sắm</p>
                        <a href="products.php" class="btn">Mua sắm ngay</a>
                    </div>
                <?php else: ?>
                    <form action="cart.php" method="post">
                        <div class="cart-table">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Sản phẩm</th>
                                        <th>Giá</th>
                                        <th>Số lượng</th>
                                        <th>Thành tiền</th>
                                        <th>Xóa</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($_SESSION['cart'] as $item): ?>
                                        <tr>
                                            <td class="product-info">
                                                <div class="cart-product-image">
                                                     <img src="assets/images/products/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                                </div>
                                                <div class="product-name">
                                                    <a href="product_detail.php?id=<?php echo $item['id']; ?>"><?php echo htmlspecialchars($item['name']); ?></a>
                                                </div>
                                            </td>
                                            <td class="product-price"><?php echo number_format($item['price'], 0, ',', '.'); ?>₫</td>
                                            <td class="product-quantity">
                                                <input type="number" name="quantity[<?php echo $item['id']; ?>]" min="1" value="<?php echo $item['quantity']; ?>">
                                            </td>
                                            <td class="product-total"><?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?>₫</td>
                                            <td class="product-remove">
                                                <a href="cart.php?action=remove&id=<?php echo $item['id']; ?>" class="btn-remove">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="cart-total-label">Tổng cộng:</td>
                                        <td class="cart-total-value"><?php echo number_format($cart_total, 0, ',', '.'); ?>₫</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        
                        <div class="cart-actions">
                            <div class="cart-buttons">
                                <button type="submit" name="update_cart" class="btn">Cập nhật giỏ hàng</button>
                                <a href="products.php" class="btn">Tiếp tục mua sắm</a>
                            </div>
                            <div class="checkout-button">
                                <a href="checkout.php" class="btn btn-primary">Thanh toán</a>
                            </div>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </section>
    </main>
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>ShopPink</h3>
                    <p>Nơi mua sắm trực tuyến đáng tin cậy với nhiều sản phẩm chất lượng.</p>
                </div>
                <div class="footer-section">
                    <h3>Liên kết nhanh</h3>
                    <ul>
                        <li><a href="index.php">Trang chủ</a></li>
                        <li><a href="products.php">Sản phẩm</a></li>
                        <li><a href="login.php">Đăng nhập</a></li>
                        <li><a href="register.php">Đăng ký</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Liên hệ</h3>
                    <p>Email: contact@shoppink.com</p>
                    <p>Điện thoại: 0123 456 789</p>
                    <p>Địa chỉ: Hà Nội</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> ShopPink.</p>
            </div>
        </div>
    </footer>
    <script src="assets/js/main.js"></script>
</body>
</html>