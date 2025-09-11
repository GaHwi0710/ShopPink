<?php
// product_detail.php
// Trang chi tiết sản phẩm
require_once 'config.php';
// Kiểm tra ID sản phẩm
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($product_id <= 0) {
    header('Location: products.php');
    exit;
}
// Lấy thông tin sản phẩm
$stmt = $conn->prepare("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$product) {
    header('Location: products.php');
    exit;
}
// Lấy đánh giá của sản phẩm
$stmt = $conn->prepare("SELECT r.*, u.name as user_name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.product_id = ? ORDER BY r.created_at DESC");
$stmt->execute([$product_id]);
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Tính trung bình đánh giá
$avg_rating = 0;
if (count($reviews) > 0) {
    $total_rating = 0;
    foreach ($reviews as $review) {
        $total_rating += $review['rating'];
    }
    $avg_rating = $total_rating / count($reviews);
}
// Xử lý thêm vào giỏ hàng
$cart_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    
    if ($quantity <= 0) {
        $cart_message = 'Số lượng phải lớn hơn 0';
    } elseif ($quantity > $product['stock']) {
        $cart_message = 'Số lượng vượt quá tồn kho';
    } else {
        // Thêm vào giỏ hàng (session)
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        // Kiểm tra sản phẩm đã có trong giỏ hàng chưa
        $found = false;
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['id'] == $product_id) {
                $item['quantity'] += $quantity;
                $found = true;
                break;
            }
        }
        
        if (!$found) {
            $_SESSION['cart'][] = [
                'id' => $product_id,
                'name' => $product['name'],
                'price' => $product['price'],
                'image' => $product['image'],
                'quantity' => $quantity
            ];
        }
        
        $cart_message = 'Đã thêm sản phẩm vào giỏ hàng';
    }
}
// Xử lý đánh giá sản phẩm
$review_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    // Kiểm tra người dùng đã đăng nhập chưa
    if (!isset($_SESSION['user_id'])) {
        $review_message = 'Vui lòng đăng nhập để đánh giá sản phẩm';
    } else {
        $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
        $comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';
        
        if ($rating < 1 || $rating > 5) {
            $review_message = 'Vui lòng chọn đánh giá từ 1 đến 5 sao';
        } else {
            try {
                $stmt = $conn->prepare("INSERT INTO reviews (product_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
                $stmt->execute([$product_id, $_SESSION['user_id'], $rating, $comment]);
                
                // Tải lại trang để hiển thị đánh giá mới
                header("Location: product_detail.php?id=$product_id");
                exit;
            } catch (PDOException $e) {
                $review_message = 'Lỗi: ' . $e->getMessage();
            }
        }
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
    <title><?php echo htmlspecialchars($product['name']); ?> - ShopPink</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* CSS cho thiết kế hiện đại */
        :root {
            --primary-color: #e91e63;
            --secondary-color: #f8bbd0;
            --text-color: #333;
            --light-gray: #f5f5f5;
            --border-radius: 8px;
            --box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            --transition: all 0.3s ease;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--text-color);
            background-color: #fafafa;
        }
        
        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }
        
        /* Header Styles */
        header {
            background-color: white;
            box-shadow: var(--box-shadow);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        header .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
        }
        
        .logo a {
            font-size: 24px;
            font-weight: bold;
            color: var(--primary-color);
            text-decoration: none;
        }
        
        nav ul {
            display: flex;
            list-style: none;
        }
        
        nav ul li {
            margin-left: 20px;
        }
        
        nav ul li a {
            text-decoration: none;
            color: var(--text-color);
            font-weight: 500;
            transition: var(--transition);
            padding: 5px 10px;
            border-radius: 4px;
        }
        
        nav ul li a:hover {
            color: var(--primary-color);
        }
        
        /* Product Detail Styles */
        .product-detail {
            padding: 40px 0;
        }
        
        .breadcrumb {
            margin-bottom: 30px;
            font-size: 14px;
        }
        
        .breadcrumb a {
            color: var(--primary-color);
            text-decoration: none;
        }
        
        .breadcrumb a:hover {
            text-decoration: underline;
        }
        
        .breadcrumb span {
            color: #777;
        }
        
        .product-container {
            display: flex;
            gap: 40px;
            margin-bottom: 50px;
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
        }
        
        .product-image {
            flex: 1;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--light-gray);
        }
        
        .product-image img {
            max-width: 100%;
            max-height: 400px;
            object-fit: contain;
            border-radius: var(--border-radius);
            transition: var(--transition);
        }
        
        .product-image img:hover {
            transform: scale(1.03);
        }
        
        .product-info {
            flex: 1;
            padding: 30px;
            display: flex;
            flex-direction: column;
        }
        
        .product-info h1 {
            font-size: 28px;
            margin-bottom: 15px;
            color: var(--text-color);
        }
        
        .product-meta {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
            font-size: 14px;
            color: #777;
        }
        
        .product-rating {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .product-rating i {
            color: #ddd;
            font-size: 16px;
            margin-right: 2px;
        }
        
        .product-rating i.active {
            color: #ffc107;
        }
        
        .product-rating span {
            margin-left: 10px;
            font-size: 14px;
            color: #777;
        }
        
        .product-price {
            margin-bottom: 20px;
        }
        
        .product-price .price {
            font-size: 28px;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .product-description {
            margin-bottom: 25px;
        }
        
        .product-description h3 {
            font-size: 18px;
            margin-bottom: 10px;
            color: var(--text-color);
        }
        
        .product-description p {
            color: #555;
            line-height: 1.6;
        }
        
        .add-to-cart-form {
            margin-top: auto;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .form-group input[type="number"] {
            width: 80px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        
        .btn {
            display: inline-block;
            background-color: var(--primary-color);
            color: white;
            padding: 12px 25px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        
        .btn:hover {
            background-color: #c2185b;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #c2185b;
        }
        
        .alert {
            padding: 12px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        
        .alert-error {
            background-color: #ffebee;
            color: #c62828;
        }
        
        .alert-info {
            background-color: #e3f2fd;
            color: #1565c0;
        }
        
        /* Reviews Section */
        .product-reviews {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 30px;
        }
        
        .product-reviews h2 {
            font-size: 24px;
            margin-bottom: 25px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .review-form {
            margin-bottom: 40px;
            padding: 20px;
            background-color: var(--light-gray);
            border-radius: var(--border-radius);
        }
        
        .review-form h3 {
            font-size: 18px;
            margin-bottom: 15px;
        }
        
        .rating-input {
            display: flex;
            flex-direction: row-reverse;
            margin-bottom: 15px;
        }
        
        .rating-input input {
            display: none;
        }
        
        .rating-input label {
            cursor: pointer;
            font-size: 24px;
            color: #ddd;
            margin-right: 5px;
            transition: var(--transition);
        }
        
        .rating-input input:checked ~ label,
        .rating-input label:hover,
        .rating-input label:hover ~ label {
            color: #ffc107;
        }
        
        .review-form textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            resize: vertical;
            min-height: 100px;
            font-family: inherit;
            font-size: 14px;
        }
        
        .reviews-list {
            margin-top: 30px;
        }
        
        .review-item {
            padding: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .review-item:last-child {
            border-bottom: none;
        }
        
        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .review-header h4 {
            font-size: 16px;
            font-weight: 600;
        }
        
        .review-rating {
            display: flex;
        }
        
        .review-rating i {
            color: #ddd;
            font-size: 14px;
            margin-right: 2px;
        }
        
        .review-rating i.active {
            color: #ffc107;
        }
        
        .review-date {
            font-size: 12px;
            color: #999;
        }
        
        .review-content p {
            color: #555;
            line-height: 1.6;
        }
        
        /* Footer Styles */
        footer {
            background-color: #333;
            color: white;
            padding: 50px 0 20px;
            margin-top: 50px;
        }
        
        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .footer-section h3 {
            font-size: 1.3rem;
            margin-bottom: 20px;
            position: relative;
            padding-bottom: 10px;
        }
        
        .footer-section h3:after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 50px;
            height: 2px;
            background-color: var(--primary-color);
        }
        
        .footer-section ul {
            list-style: none;
        }
        
        .footer-section ul li {
            margin-bottom: 10px;
        }
        
        .footer-section ul li a {
            color: #ddd;
            text-decoration: none;
            transition: var(--transition);
        }
        
        .footer-section ul li a:hover {
            color: var(--primary-color);
            padding-left: 5px;
        }
        
        .footer-bottom {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #444;
            color: #aaa;
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .product-container {
                flex-direction: column;
            }
        }
        
        @media (max-width: 768px) {
            header .container {
                flex-direction: column;
            }
            
            nav ul {
                margin-top: 15px;
                flex-wrap: wrap;
                justify-content: center;
            }
            
            nav ul li {
                margin: 5px 10px;
            }
        }
    </style>
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
                        <li><a href="cart.php"><i class="fas fa-shopping-cart"></i> Giỏ hàng</a></li>
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
        <section class="product-detail">
            <div class="container">
                <div class="breadcrumb">
                    <a href="index.php">Trang chủ</a> / 
                    <a href="products.php?category_id=<?php echo $product['category_id']; ?>"><?php echo htmlspecialchars($product['category_name']); ?></a> / 
                    <span><?php echo htmlspecialchars($product['name']); ?></span>
                </div>
                <div class="product-container">
                    <div class="product-image">
                        <!-- Sửa đường dẫn ảnh từ assets/assets/images/products/ thành assets/images/products/ -->
                        <img src="assets/images/products/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>"> 
                    </div>
                    <div class="product-info">
                        <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                        <div class="product-meta">
                            <span class="category">Danh mục: <?php echo htmlspecialchars($product['category_name']); ?></span>
                            <span class="stock">Tồn kho: <?php echo $product['stock']; ?></span>
                        </div>
                        
                        <div class="product-rating">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star <?php echo $i <= round($avg_rating) ? 'active' : ''; ?>"></i>
                            <?php endfor; ?>
                            <span>(<?php echo count($reviews); ?> đánh giá)</span>
                        </div>
                        <div class="product-price">
                            <span class="price"><?php echo number_format($product['price'], 0, ',', '.'); ?>₫</span>
                        </div>
                        <div class="product-description">
                            <h3>Mô tả sản phẩm</h3>
                            <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                        </div>
                        <?php if (!empty($cart_message)): ?>
                            <div class="alert <?php echo strpos($cart_message, 'Lỗi') !== false || strpos($cart_message, 'vượt quá') !== false ? 'alert-error' : 'alert-success'; ?>">
                                <?php echo $cart_message; ?>
                            </div>
                        <?php endif; ?>
                        <form action="product_detail.php?id=<?php echo $product_id; ?>" method="post" class="add-to-cart-form">
                            <div class="form-group">
                                <label for="quantity">Số lượng</label>
                                <input type="number" id="quantity" name="quantity" min="1" max="<?php echo $product['stock']; ?>" value="1">
                            </div>
                            <div class="form-group">
                                <button type="submit" name="add_to_cart" class="btn btn-primary">Thêm vào giỏ hàng</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="product-reviews">
                    <h2>Đánh giá sản phẩm</h2>
                    
                    <?php if ($is_logged_in): ?>
                        <div class="review-form">
                            <h3>Viết đánh giá của bạn</h3>
                            <?php if (!empty($review_message)): ?>
                                <div class="alert <?php echo strpos($review_message, 'Lỗi') !== false ? 'alert-error' : 'alert-success'; ?>">
                                    <?php echo $review_message; ?>
                                </div>
                            <?php endif; ?>
                            <form action="product_detail.php?id=<?php echo $product_id; ?>" method="post">
                                <div class="form-group">
                                    <label>Đánh giá</label>
                                    <div class="rating-input">
                                        <?php for ($i = 5; $i >= 1; $i--): ?>
                                            <input type="radio" id="star<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>" <?php echo $i == 5 ? 'checked' : ''; ?>>
                                            <label for="star<?php echo $i; ?>"><i class="fas fa-star"></i></label>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="comment">Bình luận</label>
                                    <textarea id="comment" name="comment" rows="4"></textarea>
                                </div>
                                <div class="form-group">
                                    <button type="submit" name="submit_review" class="btn">Gửi đánh giá</button>
                                </div>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <p>Vui lòng <a href="login.php">đăng nhập</a> để đánh giá sản phẩm</p>
                        </div>
                    <?php endif; ?>
                    <div class="reviews-list">
                        <?php if (empty($reviews)): ?>
                            <p>Chưa có đánh giá nào cho sản phẩm này.</p>
                        <?php else: ?>
                            <?php foreach ($reviews as $review): ?>
                                <div class="review-item">
                                    <div class="review-header">
                                        <h4><?php echo htmlspecialchars($review['user_name']); ?></h4>
                                        <div class="review-rating">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star <?php echo $i <= $review['rating'] ? 'active' : ''; ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                        <span class="review-date"><?php echo date('d/m/Y', strtotime($review['created_at'])); ?></span>
                                    </div>
                                    <div class="review-content">
                                        <p><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
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