<?php
session_start();
include('config.php');

// Lấy id sản phẩm, đảm bảo là số nguyên
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Nếu không có id hợp lệ thì quay về trang chủ
if ($product_id <= 0) {
    header("Location: index.php");
    exit;
}

// Lấy thông tin sản phẩm
$product_stmt = $conn->prepare("SELECT p.*, c.name as category_name 
                                FROM products p 
                                JOIN categories c ON p.category_id = c.id 
                                WHERE p.id = ?");
$product_stmt->bind_param("i", $product_id);
$product_stmt->execute();
$product_result = $product_stmt->get_result();
$product = $product_result->fetch_assoc();

if (!$product) {
    echo "<p>Sản phẩm không tồn tại.</p>";
    exit;
}

// Lấy sản phẩm liên quan (cùng danh mục, trừ chính nó)
$related_stmt = $conn->prepare("SELECT * FROM products 
                                WHERE category_id = ? AND id != ? 
                                LIMIT 4");
$related_stmt->bind_param("ii", $product['category_id'], $product_id);
$related_stmt->execute();
$related_result = $related_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - ShopPink</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include('includes/header.php'); ?>
    
    <div class="container">
        <div class="product-detail">
            <div class="product-images">
                <img src="assets/images/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
            </div>
            
            <div class="product-info">
                <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                <div class="product-meta">
                    <span class="category">Danh mục: <?php echo htmlspecialchars($product['category_name']); ?></span>
                    <?php if (!empty($product['brand'])) { ?>
                        <span class="brand">Thương hiệu: <?php echo htmlspecialchars($product['brand']); ?></span>
                    <?php } ?>
                    <?php if (!empty($product['gender'])) { ?>
                        <span class="gender">Giới tính: <?php echo htmlspecialchars($product['gender']); ?></span>
                    <?php } ?>
                </div>
                
                <div class="product-price">
                    <span class="price"><?php echo number_format($product['price'], 0, ',', '.'); ?> VNĐ</span>
                </div>
                
                <div class="product-description">
                    <h3>Mô tả sản phẩm</h3>
                    <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
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
        <?php if ($related_result->num_rows > 0) { ?>
        <div class="section">
            <h2>Sản phẩm liên quan</h2>
            <div class="product-grid">
                <?php while ($related_product = $related_result->fetch_assoc()) { ?>
                <div class="product-card">
                    <img src="assets/images/<?php echo htmlspecialchars($related_product['image']); ?>" alt="<?php echo htmlspecialchars($related_product['name']); ?>">
                    <h3><?php echo htmlspecialchars($related_product['name']); ?></h3>
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
