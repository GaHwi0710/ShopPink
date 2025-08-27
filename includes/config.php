<?php
// Bắt đầu session (chỉ gọi ở đây 1 lần duy nhất)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cấu hình kết nối database
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');      // Username MySQL (XAMPP mặc định là root)
define('DB_PASSWORD', '');          // Password MySQL (XAMPP mặc định rỗng)
define('DB_DATABASE', 'shoppink');  // Đặt đúng tên database bạn đã tạo trong phpMyAdmin

// Kết nối
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE);

// Kiểm tra kết nối
if (!$conn) {
    die("❌ Lỗi kết nối Database: " . mysqli_connect_error());
}

// Thiết lập charset để hỗ trợ tiếng Việt
mysqli_set_charset($conn, "utf8mb4");
?>
