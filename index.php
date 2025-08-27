<?php
session_start();
include('config.php');

// Lấy danh mục chính
$main_categories_query = "SELECT * FROM categories WHERE parent_id IS NULL LIMIT 6";
$main_categories_result = mysqli_query($conn, $main_categories_query);

// Lấy sản phẩm nổi bật
$featured_query = "SELECT p.*, c.name as category_name 
                   FROM products p 
                   JOIN categories c ON p.category_id = c.id 
                   ORDER BY created_at DESC LIMIT 8";
$featured_result = mysqli_query($conn, $featured_query);

// Lấy sản phẩm bán chạy
$bestseller_query = "SELECT p.*, c.name as category_name, SUM(od.quantity) as total_sold 
                     FROM products p 
                     JOIN categories c ON p.category_id = c.id 
                     JOIN order_details od ON p.id = od.product_id 
                     GROUP BY p.id 
                     ORDER BY total_sold DESC 
                     LIMIT 8";
$bestseller_result = mysqli_query($conn, $bestseller_query);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShopPink - Mua sắm trực tuyến đa dạng</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include('includes/header.php'); ?>
    
    <div class="container">
        <!-- Banner chính -->
        <div class="main-banner">
            <div class="banner-slide">
                <img src="assets/images/banner1.jpg" alt="Banner 1">
                <div class="banner-content">
                    <h2>Khuyến mãi lớn</h2>
                    <p>Giảm giá đến 50% cho tất cả sản phẩm</p>
                    <a href="products.php" class="btn">Mua ngay</a>
                </div>
            </div>
        </div>
        
        <!-- Danh mục chính -->
        <div class="section">
            <h2>Danh mục sản phẩm</h2>
            <div class="category-grid">
                <?php while ($category = mysqli_fetch_assoc($main_categories_result)) { ?>
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
                    <p class="category"><?php echo $product['category_name']; ?></p>
                    <p class="price"><?php echo number_format($product['price'], 0, ',', '.'); ?> VNĐ</p>
                    <a href="product_detail.php?id=<?php echo $product['id']; ?>" class="btn">Xem chi tiết</a>
                </div>
                <?php } ?>
            </div>
        </div>
        
        <!-- Sản phẩm bán chạy -->
        <div class="section">
            <h2>Sản phẩm bán chạy</h2>
            <div class="product-grid">
                <?php while ($product = mysqli_fetch_assoc($bestseller_result)) { ?>
                <div class="product-card">
                    <img src="assets/images/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
                    <h3><?php echo $product['name']; ?></h3>
                    <p class="category"><?php echo $product['category_name']; ?></p>
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