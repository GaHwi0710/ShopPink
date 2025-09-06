<?php
// Cấu hình kết nối
$host = 'localhost';
$db_name = 'shoppink'; // Quan trọng: Đảm bảo tên database này chính xác
$username = 'root';
$password = ''; // Để trống nếu không đặt mật khẩu

echo "Đang thử kết nối tới Database '{$db_name}'...<br>";

try {
    // Tạo đối tượng PDO
    $conn = new PDO("mysql:host=" . $host . ";dbname=" . $db_name, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Nếu không có lỗi, in ra thông báo thành công
    echo "<b>KẾT NỐI THÀNH CÔNG!</b><br>";
    echo "Phiên bản MySQL Server: " . $conn->getAttribute(PDO::ATTR_SERVER_VERSION);

} catch(PDOException $e) {
    // Nếu có lỗi, in ra thông báo lỗi
    echo "<b>KẾT NỐI THẤT BẠI:</b> <br>" . $e->getMessage();
}

// Đóng kết nối
$conn = null;
?>