<?php
session_start();
include('config.php');

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_query = "SELECT * FROM users WHERE id = $user_id";
$user_result = mysqli_query($conn, $user_query);
$user = mysqli_fetch_assoc($user_result);

// Lấy danh mục sản phẩm
$category_query = "SELECT * FROM categories";
$category_result = mysqli_query($conn, $category_query);

// Lấy sản phẩm nổi bật
$featured_query = "SELECT * FROM products ORDER BY created_at DESC LIMIT 8";
$featured_result = mysqli_query($conn, $featured_query);

// Lấy sản phẩm theo sở thích
$preferences = json_decode($user['preferences'], true);
$preferred_products = [];
if (!empty($preferences)) {
    $pref_categories = implode(',', $preferences);
    $pref_query = "SELECT * FROM products WHERE category_id IN ($pref_categories) LIMIT 4";
    $preferred_result = mysqli_query($conn, $pref_query);
    $preferred_products = mysqli_fetch_all($preferred_result, MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang chủ của <?php echo $user['username']; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/<?php echo $user['theme']; ?>.css">
</head>
<body>
    <?php include('includes/header.php'); ?>
    
    <div class="container">
        <div class="welcome-banner">
            <h1>Chào mừng bạn, <?php echo $user['username']; ?>!</h1>
            <p>Khám phá các sản phẩm được đề xuất riêng cho bạn</p>
        </div>
        
        <!-- Sản phẩm theo sở thích -->
        <?php if (!empty($preferred_products)) { ?>
        <div class="section">
            <h2>Gợi ý cho bạn</h2>
            <div class="product-grid">
                <?php foreach ($preferred_products as $product) { ?>
                <div class="product-card">
                    <img src="assets/images/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
                    <h3><?php echo $product['name']; ?></h3>
                    <p class="price"><?php echo number_format($product['price'], 0, ',', '.'); ?> VNĐ</p>
                    <a href="product_detail.php?id=<?php echo $product['id']; ?>" class="btn">Xem chi tiết</a>
                </div>
                <?php } ?>
            </div>
        </div>
        <?php } ?>
        
        <!-- Danh mục sản phẩm -->
        <div class="section">
            <h2>Danh mục sản phẩm</h2>
            <div class="category-grid">
                <?php while ($category = mysqli_fetch_assoc($category_result)) { ?>
                <div class="category-card">
                    <a href="category.php?id=<?php echo $category['id']; ?>">
                        <img src="assets/images/categories/<?php echo $category['id']; ?>.jpg" alt="<?php echo $category['name']; ?>">
                        <h3><?php echo $category['name']; ?></h3>
                    </a>
                </div>
                <?php } ?>
            </div>
        </div>
        
        <!-- Sản phẩm nổi bật -->
        <div class="section">
            <h2>Sản phẩm nổi bật</h2>
            <div class="product-grid">
                <?php while ($product = mysqli_fetch_assoc($featured_result)) { ?>
                <div class="product-card">
                    <img src="assets/images/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
                    <h3><?php echo $product['name']; ?></h3>
                    <p class="price"><?php echo number_format($product['price'], 0, ',', '.'); ?> VNĐ</p>
                    <a href="product_detail.php?id=<?php echo $product['id']; ?>" class="btn">Xem chi tiết</a>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>
    
    <?php include('includes/footer.php'); ?>
</body>
</html>