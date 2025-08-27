<?php
session_start();
include('config.php');

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Kiểm tra giỏ hàng
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit();
}

// Lấy thông tin user
$user_id = $_SESSION['user_id'];
$user_query = "SELECT * FROM users WHERE id = $user_id";
$user_result = mysqli_query($conn, $user_query);
$user = mysqli_fetch_assoc($user_result);

// Xử lý đặt hàng
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    $payment_method = $_POST['payment_method'];
    
    // Tính tổng tiền
    $product_ids = array_keys($_SESSION['cart']);
    $ids = implode(',', $product_ids);
    
    $sql = "SELECT * FROM products WHERE id IN ($ids)";
    $result = mysqli_query($conn, $sql);
    
    $total = 0;
    $order_items = array();
    
    while ($row = mysqli_fetch_assoc($result)) {
        $quantity = $_SESSION['cart'][$row['id']];
        $subtotal = $row['price'] * $quantity;
        $total += $subtotal;
        
        $order_items[] = array(
            'product_id' => $row['id'],
            'quantity' => $quantity,
            'price' => $row['price']
        );
    }
    
    // Thêm phí vận chuyển
    $shipping_fee = 30000;
    $total += $shipping_fee;
    
    // Thêm đơn hàng vào database
    $order_query = "INSERT INTO orders (user_id, total, address, phone, payment_method, status) 
                   VALUES ($user_id, $total, '$address', '$phone', '$payment_method', 'pending')";
    
    if (mysqli_query($conn, $order_query)) {
        $order_id = mysqli_insert_id($conn);
        
        // Thêm chi tiết đơn hàng
        foreach ($order_items as $item) {
            $product_id = $item['product_id'];
            $quantity = $item['quantity'];
            $price = $item['price'];
            
            $detail_query = "INSERT INTO order_details (order_id, product_id, quantity, price) 
                            VALUES ($order_id, $product_id, $quantity, $price)";
            mysqli_query($conn, $detail_query);
        }
        
        // Xóa giỏ hàng
        unset($_SESSION['cart']);
        
        // Chuyển hướng đến trang xác nhận đơn hàng
        header("Location: order_confirmation.php?id=$order_id");
        exit();
    } else {
        $error = "Lỗi: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán - ShopPink</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/form.css">
</head>
<body>
    <?php include('includes/header.php'); ?>
    
    <div class="container">
        <h1>Thanh toán</h1>
        
        <?php if (isset($error)) { ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php } ?>
        
        <div class="checkout-container">
            <div class="checkout-form">
                <h2>Thông tin giao hàng</h2>
                <form method="post" action="checkout.php">
                    <div class="form-group">
                        <label for="fullname">Họ và tên</label>
                        <input type="text" id="fullname" value="<?php echo $user['username']; ?>" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" value="<?php echo $user['email']; ?>" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Địa chỉ giao hàng *</label>
                        <input type="text" id="address" name="address" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Số điện thoại *</label>
                        <input type="text" id="phone" name="phone" required>
                    </div>
                    
                    <h2>Phương thức thanh toán</h2>
                    <div class="payment-methods">
                        <div class="payment-method">
                            <input type="radio" id="cod" name="payment_method" value="cod" checked>
                            <label for="cod">Thanh toán khi nhận hàng (COD)</label>
                        </div>
                        
                        <div class="payment-method">
                            <input type="radio" id="bank" name="payment_method" value="bank">
                            <label for="bank">Chuyển khoản ngân hàng</label>
                        </div>
                        
                        <div class="payment-method">
                            <input type="radio" id="momo" name="payment_method" value="momo">
                            <label for="momo">Ví MoMo</label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Đặt hàng</button>
                    </div>
                </form>
            </div>
            
            <div class="order-summary">
                <h2>Đơn hàng của bạn</h2>
                
                <div class="order-items">
                    <?php
                    $product_ids = array_keys($_SESSION['cart']);
                    $ids = implode(',', $product_ids);
                    
                    $sql = "SELECT * FROM products WHERE id IN ($ids)";
                    $result = mysqli_query($conn, $sql);
                    
                    $subtotal = 0;
                    
                    while ($row = mysqli_fetch_assoc($result)) {
                        $quantity = $_SESSION['cart'][$row['id']];
                        $item_total = $row['price'] * $quantity;
                        $subtotal += $item_total;
                    ?>
                        <div class="order-item">
                            <div class="item-info">
                                <img src="assets/images/<?php echo $row['image']; ?>" alt="<?php echo $row['name']; ?>">
                                <div>
                                    <h3><?php echo $row['name']; ?></h3>
                                    <p>Số lượng: <?php echo $quantity; ?></p>
                                </div>
                            </div>
                            <div class="item-price">
                                <?php echo number_format($item_total, 0, ',', '.'); ?> VNĐ
                            </div>
                        </div>
                    <?php } ?>
                </div>
                
                <div class="order-totals">
                    <div class="total-row">
                        <span>Tạm tính:</span>
                        <span><?php echo number_format($subtotal, 0, ',', '.'); ?> VNĐ</span>
                    </div>
                    <div class="total-row">
                        <span>Phí vận chuyển:</span>
                        <span>30,000 VNĐ</span>
                    </div>
                    <div class="total-row grand-total">
                        <span>Tổng cộng:</span>
                        <span><?php echo number_format($subtotal + 30000, 0, ',', '.'); ?> VNĐ</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include('includes/footer.php'); ?>
</body>
</html>