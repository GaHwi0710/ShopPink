<?php
// Include autoload để tự động nạp các file cần thiết
require_once 'includes/autoload.php';
// ...

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Lấy order_id
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($order_id <= 0) {
    header("Location: user_home.php");
    exit();
}

// Lấy thông tin đơn hàng (chỉ cho phép review đơn đã hoàn thành)
$order_stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ? AND status = 'completed'");
$order_stmt->bind_param("ii", $order_id, $_SESSION['user_id']);
$order_stmt->execute();
$order = $order_stmt->get_result()->fetch_assoc();

if (!$order) {
    header("Location: user_home.php");
    exit();
}

// Lấy sản phẩm trong đơn
$details_stmt = $conn->prepare("
    SELECT od.product_id, od.quantity, p.name, p.image 
    FROM order_details od 
    JOIN products p ON od.product_id = p.id 
    WHERE od.order_id = ?
");
$details_stmt->bind_param("i", $order_id);
$details_stmt->execute();
$details_result = $details_stmt->get_result();

// Xử lý lưu review
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['reviews'])) {
    $reviews = $_POST['reviews'];

    $review_stmt = $conn->prepare("
        INSERT INTO reviews (user_id, product_id, rating, comment, created_at) 
        VALUES (?, ?, ?, ?, NOW())
    ");

    foreach ($reviews as $product_id => $review) {
        $rating = intval($review['rating']);
        $comment = trim($review['comment']);

        if ($rating >= 1 && $rating <= 5) {
            $review_stmt->bind_param("iiis", $_SESSION['user_id'], $product_id, $rating, $comment);
            $review_stmt->execute();
        }
    }

    header("Location: order_detail.php?id=" . $order_id);
    exit();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đánh giá sản phẩm - Đơn hàng #<?php echo htmlspecialchars($order_id); ?> | ShopPink</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/form.css">
    <style>
        .review-form { margin-bottom: 20px; padding: 15px; border: 1px solid #eee; border-radius: 8px; background: #fff; }
        .review-item { display: flex; gap: 15px; }
        .review-item img { width: 80px; height: 80px; object-fit: cover; border-radius: 6px; }
        .review-item h3 { margin: 0 0 8px 0; }
        .review-item select, .review-item textarea { width: 100%; margin-top: 5px; }
        .review-item textarea { min-height: 80px; resize: vertical; padding: 8px; }
    </style>
</head>
<body>
<?php include('includes/header.php'); ?>

<div class="container">
    <h1>Đánh giá sản phẩm - Đơn hàng #<?php echo htmlspecialchars($order_id); ?></h1>

    <form method="post" action="review.php?id=<?php echo htmlspecialchars($order_id); ?>">
        <?php while ($item = $details_result->fetch_assoc()) { ?>
            <div class="review-form">
                <div class="review-item">
                    <img src="assets/images/products/<?php echo htmlspecialchars($item['image']); ?>" 
                         alt="<?php echo htmlspecialchars($item['name']); ?>">
                    <div>
                        <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                        <label for="rating_<?php echo $item['product_id']; ?>">Đánh giá:</label>
                        <select name="reviews[<?php echo $item['product_id']; ?>][rating]" id="rating_<?php echo $item['product_id']; ?>">
                            <option value="5">⭐️⭐️⭐️⭐️⭐️ - Rất tốt</option>
                            <option value="4">⭐️⭐️⭐️⭐️ - Tốt</option>
                            <option value="3">⭐️⭐️⭐️ - Bình thường</option>
                            <option value="2">⭐️⭐️ - Tệ</option>
                            <option value="1">⭐️ - Rất tệ</option>
                        </select>
                        <textarea name="reviews[<?php echo $item['product_id']; ?>][comment]" placeholder="Viết nhận xét của bạn..."></textarea>
                    </div>
                </div>
            </div>
        <?php } ?>

        <button type="submit" class="btn btn-primary">Gửi đánh giá</button>
        <a href="order_detail.php?id=<?php echo htmlspecialchars($order_id); ?>" class="btn btn-outline">Hủy</a>
    </form>
</div>

<?php include('includes/footer.php'); ?>
</body>
</html>
