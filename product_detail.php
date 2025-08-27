<?php
session_start();
include('config.php');

$product_id = $_GET['id'] ?? 0;

// Lấy thông tin sản phẩm
$product_query = "SELECT p.*, c.name as category_name 
                  FROM products p 
                  JOIN categories c ON p.category_id = c.id 
                  WHERE p.id = $product_id";
$product_result = mysqli_query($conn, $product_query);
$product = mysqli_fetch_assoc($product_result);

// Lấy sản phẩm liên quan
$related_query = "SELECT * FROM products WHERE category_id = {$product['category_id']} AND id != $product_id LIMIT 4";
$related_result = mysqli_query($conn, $related_query);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product['name']; ?> - ShopPink</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include('includes/header.php'); ?>
    
    <div class="container">
        <div class="product-detail">
            <div class="product-images">
                <img src="assets/images/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
            </div>
            
            <div class="product-info">
                <h1><?php echo $product['name']; ?></h1>
                <div class="product-meta">
                    <span class="category">Danh mục: <?php echo $product['category_name']; ?></span>
                    <span class="brand">Thương hiệu: <?php echo $product['brand']; ?></span>
                    <span class="gender">Giới tính: <?php echo $product['gender']; ?></span>
                </div>
                
                <div class="product-price">
                    <span class="price"><?php echo number_format($product['price'], 0, ',', '.'); ?> VNĐ</span>
                </div>
                
                <div class="product-description">
                    <h3>Mô tả sản phẩm</h3>
                    <p><?php echo nl2br($product['description']); ?></p>
                </div>
                
                <div class="product-actions">
                    <form action="add_to_cart.php" method="post">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        <div class="quantity-selector">
                            <label for="quantity">Số lượng:</label>
                            <input type="number" id="quantity" name="quantity" value="1" min="1">
                        </div>
                        <button type="submit" class="btn btn-primary">Thêm vào giỏ hàng</button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Sản phẩm liên quan -->
        <?php if (mysqli_num_rows($related_result) > 0) { ?>
        <div class="section">
            <h2>Sản phẩm liên quan</h2>
            <div class="product-grid">
                <?php while ($related_product = mysqli_fetch_assoc($related_result)) { ?>
                <div class="product-card">
                    <img src="assets/images/<?php echo $related_product['image']; ?>" alt="<?php echo $related_product['name']; ?>">
                    <h3><?php echo $related_product['name']; ?></h3>
                    <p class="price"><?php echo number_format($related_product['price'], 0, ',', '.'); ?> VNĐ</p>
                    <a href="product_detail.php?id=<?php echo $related_product['id']; ?>" class="btn">Xem chi tiết</a>
                </div>
                <?php } ?>
            </div>
        </div>
        <?php } ?>
    </div>
    
    <?php include('includes/footer.php'); ?>
</body>
</html>