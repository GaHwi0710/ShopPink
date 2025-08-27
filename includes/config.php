<?php
// Cấu hình kết nối database
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root'); // Thay bằng username của bạn
define('DB_PASSWORD', ''); // Thay bằng password của bạn
define('DB_DATABASE', 'shop_db'); // Tên database mới

// Kết nối
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE);

// Kiểm tra kết nối
if ($conn === false) {
    die("Lỗi: Không thể kết nối. " . mysqli_connect_error());
}

// Thiết lập charset
mysqli_set_charset($conn, "utf8");
?>