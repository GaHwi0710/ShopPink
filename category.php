<?php
session_start();
include('config.php');

// Lấy id danh mục từ URL
$category_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($category_id <= 0) { header("Location: index.php"); exit(); }

// ---- Lấy thông tin danh mục
$category_stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
$category_stmt->bind_param("i", $category_id);
$category_stmt->execute();
$category = $category_stmt->get_result()->fetch_assoc();
$category_stmt->close();

// Nếu không có danh mục -> thông báo & thoát
if (!$category) {
    include('includes/header.php');
    echo "<div class='container'><h2>❌ Danh mục không tồn tại hoặc đã bị xóa.</h2></div>";
    include('includes/footer.php');
    exit();
}

// ---- Lấy danh mục con
$sub_stmt = $conn->prepare("SELECT * FROM categories WHERE parent_id = ?");
$sub_stmt->bind_param("i", $category_id);
$sub_stmt->execute();
$subcategories_result = $sub_stmt->get_result();
$sub_stmt->close();

// ---- Lấy sản phẩm trong danh mục (kể cả danh mục con)
$prod_stmt = $conn->prepare("
    SELECT p.*
    FROM products p
    JOIN categories c ON p.category_id = c.id
    WHERE c.id = ? OR c.parent_id = ?
");
$prod_stmt->bind_param("ii", $category_id, $category_id);
$prod_stmt->execute();
$products_result = $prod_stmt->get_result();
$products = $products_result ? $products_result->fetch_all(MYSQLI_ASSOC) : [];
$prod_stmt->close();

// Giá trị an toàn
$category_name = $category['name'] ?? 'Danh mục';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($category_name); ?> - ShopPink</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<?php include('includes/header.php'); ?>

<div class="container">
    <div class="page-header">
        <h1><?php echo htmlspecialchars($category_name); ?></h1>
    </div>

    <!-- Danh mục con -->
    <?php if ($subcategories_result && $subcategories_result->num_rows > 0) { ?>
        <div class="section">
            <h2>Danh mục con</h2>
            <div class="subcategory-grid">
                <?php while ($subcategory = $subcategories_result->fetch_assoc()) { ?>
                    <div class="subcategory-card">
                        <a href="category.php?id=<?php echo intval($subcategory['id'] ?? 0); ?>">
                            <h3><?php echo htmlspecialchars($subcategory['name'] ?? 'Danh mục'); ?></h3>
                        </a>
                    </div>
                <?php } ?>
            </div>
        </div>
    <?php } ?>

    <!-- Sản phẩm -->
    <div class="section">
        <h2>Sản phẩm</h2>
        <?php if (!empty($products)) { ?>
            <div class="product-grid">
                <?php foreach ($products as $product) {
                    $id    = (int)($product['id'] ?? 0);
                    $name  = $product['name'] ?? 'Sản phẩm';
                    $image = !empty($product['image']) ? $product['image'] : 'no-image.png';
                    $price = (float)($product['price'] ?? 0);
                    if ($id === 0) continue;
                ?>
                    <div class="product-card">
                        <img src="assets/images/<?php echo htmlspecialchars($image); ?>"
                             alt="<?php echo htmlspecialchars($name); ?>">
                        <h3><?php echo htmlspecialchars($name); ?></h3>
                        <p class="price"><?php echo number_format($price, 0, ',', '.'); ?> VNĐ</p>
                        <a href="product_detail.php?id=<?php echo $id; ?>" class="btn">Xem chi tiết</a>
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
