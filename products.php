<?php
// products.php
// Trang hiển thị danh sách sản phẩm
require_once 'config.php';
// Lấy danh sách danh mục
$stmt = $conn->query("SELECT * FROM categories");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Xác định danh mục được chọn
$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
// Xử lý tìm kiếm
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
// Xây dựng câu truy vấn SQL
$sql = "SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE 1=1";
$params = [];
if ($category_id > 0) {
    $sql .= " AND p.category_id = ?";
    $params[] = $category_id;
}
if (!empty($search)) {
    $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
$sql .= " ORDER BY p.id DESC";
// Thực thi truy vấn
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Kiểm tra người dùng đã đăng nhập chưa
$is_logged_in = isset($_SESSION['user_id']);
$user_role = $is_logged_in ? $_SESSION['user_role'] : '';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sản phẩm - ShopPink</title>
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
                    <li><a href="products.php" class="active">Sản phẩm</a></li>
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
        <section class="products-page">
            <div class="container">
                <div class="products-header">
                    <h1>Sản phẩm</h1>
                    <div class="search-bar">
                        <form action="products.php" method="get">
                            <div class="search-box">
                                <input type="text" name="search" placeholder="Tìm kiếm sản phẩm..." value="<?php echo htmlspecialchars($search); ?>">
                                <button type="submit"><i class="fas fa-search"></i></button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="products-container">
                    <div class="sidebar">
                        <h3>Danh mục</h3>
                        <ul class="category-list">
                            <li><a href="products.php" <?php echo $category_id == 0 ? 'class="active"' : ''; ?>>Tất cả sản phẩm</a></li>
                            <?php foreach ($categories as $category): ?>
                                <li>
                                    <a href="products.php?category_id=<?php echo $category['id']; ?>" <?php echo $category_id == $category['id'] ? 'class="active"' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <div class="products-content">
                        <?php if (empty($products)): ?>
                            <div class="no-products">
                                <p>Không tìm thấy sản phẩm nào phù hợp.</p>
                            </div>
                        <?php else: ?>
                            <div class="product-grid">
                                <?php foreach ($products as $product): ?>
                                    <div class="product-card">
                                        <div class="product-image-large">
                                            <img src="assets/images/products/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>"> 
                                        </div>
                                        <div class="product-info">
                                            <h3><a href="product_detail.php?id=<?php echo $product['id']; ?>"><?php echo htmlspecialchars($product['name']); ?></a></h3>
                                            <p class="category"><?php echo htmlspecialchars($product['category_name']); ?></p>
                                            <p class="price"><?php echo number_format($product['price'], 0, ',', '.'); ?>₫</p>
                                            <div class="product-actions">
                                                <a href="product_detail.php?id=<?php echo $product['id']; ?>" class="btn">Xem chi tiết</a>
                                                <form action="products.php" method="post" class="add-to-cart-form">
                                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                                    <input type="hidden" name="quantity" value="1">
                                                    <button type="submit" name="add_to_cart" class="btn-cart"><i class="fas fa-shopping-cart"></i></button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
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