<?php
session_start();
include('config.php');

$category_id = $_GET['id'] ?? 0;

// Lấy thông tin danh mục
$category_query = "SELECT * FROM categories WHERE id = $category_id";
$category_result = mysqli_query($conn, $category_query);
$category = mysqli_fetch_assoc($category_result);

// Lấy sản phẩm trong danh mục
$products_query = "SELECT * FROM products WHERE category_id = $category_id";
$products_result = mysqli_query($conn, $products_query);

// Lấy danh mục con
$subcategories_query = "SELECT * FROM categories WHERE parent_id = $category_id";
$subcategories_result = mysqli_query($conn, $subcategories_query);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $category['name']; ?> - ShopPink</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include('includes/header.php'); ?>
    
    <div class="container">
        <div class="page-header">
            <h1><?php echo $category['name']; ?></h1>
        </div>
        
        <!-- Danh mục con -->
        <?php if (mysqli_num_rows($subcategories_result) > 0) { ?>
        <div class="section">
            <h2>Danh mục con</h2>
            <div class="subcategory-grid">
                <?php while ($subcategory = mysqli_fetch_assoc($subcategories_result)) { ?>
                <div class="subcategory-card">
                    <a href="category.php?id=<?php echo $subcategory['id']; ?>">
                        <h3><?php echo $subcategory['name']; ?></h3>
                    </a>
                </div>
                <?php } ?>
            </div>
        </div>
        <?php } ?>
        
        <!-- Sản phẩm trong danh mục -->
        <div class="section">
            <h2>Sản phẩm</h2>
            <?php if (mysqli_num_rows($products_result) > 0) { ?>
            <div class="product-grid">
                <?php while ($product = mysqli_fetch_assoc($products_result)) { ?>
                <div class="product-card">
                    <img src="assets/images/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
                    <h3><?php echo $product['name']; ?></h3>
                    <p class="price"><?php echo number_format($product['price'], 0, ',', '.'); ?> VNĐ</p>
                    <a href="product_detail.php?id=<?php echo $product['id']; ?>" class="btn">Xem chi tiết</a>
                </div>
                <?php } ?>
            </div>
            <?php } else { ?>
            <p>Không có sản phẩm nào trong danh mục này.</p>
            <?php } ?>
        </div>
    </div>
    
    <?php include('includes/footer.php'); ?>
</body>
</html>