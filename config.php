<?php
$servername = "localhost";  // Hoặc 127.0.0.1
$username   = "root";       // Tài khoản mặc định của XAMPP
$password   = "";           // Mặc định XAMPP để trống
$dbname     = "shoppink";  // Tên database bạn đã tạo trong phpMyAdmin

// Tạo kết nối
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if (!$conn) {
    die("Kết nối thất bại: " . mysqli_connect_error());
}
?>
