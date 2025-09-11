<?php
// index.php
// Trang chủ của ShopPink
require_once 'config.php';
// Lấy danh sách danh mục
$stmt = $conn->query("SELECT * FROM categories");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Lấy sản phẩm nổi bật (giới hạn 16 sản phẩm)
$stmt = $conn->query("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC LIMIT 16");
$featured_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Lấy sản phẩm theo từng danh mục
$products_by_category = [];
foreach ($categories as $category) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE category_id = ? ORDER BY id DESC LIMIT 4");
    $stmt->execute([$category['id']]);
    $products_by_category[$category['id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
// Xử lý tìm kiếm
$search_query = '';
$search_results = [];
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_query = trim($_GET['search']);
    $stmt = $conn->prepare("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.name LIKE ? OR p.description LIKE ? ORDER BY p.id DESC LIMIT 16");
    $search_term = "%$search_query%";
    $stmt->execute([$search_term, $search_term]);
    $search_results = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
// Xử lý thêm vào giỏ hàng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    
    if ($product_id > 0 && $quantity > 0) {
        // Lấy thông tin sản phẩm
        $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($product) {
            // Khởi tạo giỏ hàng nếu chưa có
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
                    'quantity' => $quantity,
                    'image' => $product['image']
                ];
            }
            
            // Chuyển hướng về trang chủ với thông báo
            header('Location: index.php?added=1');
            exit;
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
    <title>ShopPink - Trang chủ</title>
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
        <!-- Thông báo thêm vào giỏ hàng thành công -->
        <?php if (isset($_GET['added']) && $_GET['added'] == 1): ?>
            <div class="notification success">
                <div class="container">
                    <i class="fas fa-check-circle"></i>
                    <span>Đã thêm sản phẩm vào giỏ hàng!</span>
                    <a href="cart.php" class="btn">Xem giỏ hàng</a>
                </div>
            </div>
        <?php endif; ?>
        <!-- Phần tìm kiếm -->
        <section class="search-section">
            <div class="container">
                <div class="search-container">
                    <h1>Tìm kiếm sản phẩm</h1>
                    <form action="index.php" method="get" class="search-form">
                        <div class="search-box">
                            <input type="text" name="search" placeholder="Nhập tên sản phẩm cần tìm..." value="<?php echo htmlspecialchars($search_query); ?>">
                            <button type="submit"><i class="fas fa-search"></i></button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
        <!-- Kết quả tìm kiếm -->
        <?php if (!empty($search_query)): ?>
            <section class="search-results">
                <div class="container">
                    <h2>Kết quả tìm kiếm cho "<?php echo htmlspecialchars($search_query); ?>"</h2>
                    <?php if (empty($search_results)): ?>
                        <div class="no-results">
                            <p>Không tìm thấy sản phẩm nào phù hợp với từ khóa tìm kiếm.</p>
                            <a href="index.php" class="btn">Xem tất cả sản phẩm</a>
                        </div>
                    <?php else: ?>
                        <div class="product-grid">
                            <?php foreach ($search_results as $product): ?>
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
                                            <form action="index.php" method="post" class="add-to-cart-form">
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
            </section>
        <?php endif; ?>
        <!-- Hero Section -->
        <section class="hero">
            <div class="container">
                <h1>Chào mừng đến với ShopPink</h1>
                <p>Nơi mua sắm trực tuyến đáng tin cậy với nhiều sản phẩm chất lượng.</p>
                <a href="products.php" class="btn">Mua sắm ngay</a>
            </div>
        </section>
        <!-- Danh mục sản phẩm - 3x3 Grid -->
        <section class="categories">
            <div class="container">
                <h2>Danh mục sản phẩm</h2>
                <div class="category-grid-3x3">
                    <?php
                    // Tạo mảng ánh xạ từ tên danh mục sang icon
                    $category_icons = [
                        'Thời trang nữ' => 'fa-female',
                        'Thời trang nam' => 'fa-male',
                        'Phụ kiện' => 'fa-glasses',
                        'Giày dép' => 'fa-shoe-prints',
                        'Túi xách' => 'fa-shopping-bag',
                        'Trang sức' => 'fa-gem',
                        'Thể thao' => 'fa-football-ball',
                        'Đồ chơi' => 'fa-gamepad',
                        'Thực phẩm' => 'fa-utensils'
                    ];
                    $additional_categories = [
                    ];
                    
                    // Kết hợp danh mục từ database và danh mục bổ sung
                    $all_categories = array_merge($categories, $additional_categories);
                    
                    foreach ($all_categories as $category): 
                        // Lấy icon dựa trên tên danh mục
                        $icon_class = isset($category_icons[$category['name']]) ? $category_icons[$category['name']] : 'fa-box';
                    ?>
                        <div class="category-item">
                            <a href="products.php?category_id=<?php echo $category['id']; ?>">
                                <div class="category-icon">
                                    <i class="fas <?php echo $icon_class; ?>"></i>
                                </div>
                                <h3><?php echo htmlspecialchars($category['name']); ?></h3>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <!-- Sản phẩm theo danh mục -->
        <section class="products-by-category">
            <div class="container">
                <?php foreach ($categories as $category): ?>
                    <?php if (!empty($products_by_category[$category['id']])): ?>
                        <div class="category-section">
                            <div class="section-header">
                                <h2><?php echo htmlspecialchars($category['name']); ?></h2>
                                <a href="products.php?category_id=<?php echo $category['id']; ?>" class="view-all">Xem tất cả <i class="fas fa-arrow-right"></i></a>
                            </div>
                            <div class="product-grid">
                                <?php foreach ($products_by_category[$category['id']] as $product): ?>
                                    <div class="product-card">
                                        <div class="product-image-large">
                                            <img src="assets/images/products/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>"> 
                                        </div>
                                        <div class="product-info">
                                            <h3><a href="product_detail.php?id=<?php echo $product['id']; ?>"><?php echo htmlspecialchars($product['name']); ?></a></h3>
                                            <p class="price"><?php echo number_format($product['price'], 0, ',', '.'); ?>₫</p>
                                            <div class="product-actions">
                                                <a href="product_detail.php?id=<?php echo $product['id']; ?>" class="btn">Xem chi tiết</a>
                                                <form action="index.php" method="post" class="add-to-cart-form">
                                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                                    <input type="hidden" name="quantity" value="1">
                                                    <button type="submit" name="add_to_cart" class="btn-cart"><i class="fas fa-shopping-cart"></i></button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </section>
        <!-- Sản phẩm nổi bật -->
        <section class="featured-products">
            <div class="container">
                <h2>Sản phẩm nổi bật</h2>
                <div class="product-grid">
                    <?php foreach ($featured_products as $product): ?>
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
                                    <form action="index.php" method="post" class="add-to-cart-form">
                                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" name="add_to_cart" class="btn-cart"><i class="fas fa-shopping-cart"></i></button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="text-center">
                    <a href="products.php" class="btn">Xem tất cả sản phẩm</a>
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