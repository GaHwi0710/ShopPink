<?php
include('includes/config.php');

$query = trim($_GET['q'] ?? '');

if ($query === '') {
    echo '';
    exit;
}

$search = "%" . $query . "%";
$stmt = $conn->prepare("
    SELECT id, name, image, price 
    FROM products 
    WHERE name LIKE ? 
    ORDER BY created_at DESC 
    LIMIT 5
");
$stmt->bind_param("s", $search);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo '<ul>';
    while ($row = $result->fetch_assoc()) {
        echo '<li>
                <a href="product_detail.php?id=' . $row['id'] . '">
                    <img src="assets/images/products/' . htmlspecialchars($row['image'] ?? 'default.jpg') . '" 
                         alt="' . htmlspecialchars($row['name']) . '">
                    <span class="name">' . htmlspecialchars($row['name']) . '</span>
                    <span class="price">' . number_format($row['price'], 0, ',', '.') . ' VNĐ</span>
                </a>
              </li>';
    }
    echo '</ul>';
} else {
    echo '<p class="no-result">Không tìm thấy sản phẩm...</p>';
}
