<?php
session_start();
include('config.php');

$query = $_GET['query'] ?? '';

// Tìm kiếm sản phẩm
$search_query = "SELECT p.*, c.name as category_name 
                FROM products p 
                JOIN categories c ON p.category_id = c.id 
                WHERE p.name LIKE '%$query%' OR p.description LIKE '%$query%' 
                ORDER BY p.created_at DESC";
$result = mysqli_query($conn, $search_query);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kết quả tìm kiếm: "<?php echo $query; ?>" - ShopPink</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/form.css">
</head>
<body>
    <?php include('includes/header.php'); ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Kết quả tìm kiếm cho "<?php echo $query; ?>"</h1>
        </div>
        
        <?php if (mysqli_num_rows($result) > 0) { ?>
            <div class="search-results">
                <p>Tìm thấy <?php echo mysqli_num_rows($result); ?> sản phẩm</p>
                <div class="product-grid">
                    <?php while ($product = mysqli_fetch_assoc($result)) { ?>
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
        <?php } else { ?>
            <div class="no-results">
                <img src="assets/images/no-results.png" alt="Không tìm thấy">
                <p>Không tìm thấy sản phẩm nào phù hợp với từ khóa "<?php echo $query; ?>"</p>
                <p>Vui lòng thử lại với từ khóa khác</p>
                <a href="index.php" class="btn">Về trang chủ</a>
            </div>
        <?php } ?>
    </div>
    
    <?php include('includes/footer.php'); ?>
</body>
</html>