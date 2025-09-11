# ShopPink - Hệ thống thương mại điện tử

ShopPink là một hệ thống thương mại điện tử cơ bản được xây dựng bằng **PHP thuần**, **MySQL**, **HTML**, **CSS** và **JavaScript**.  
Hệ thống cung cấp đầy đủ các chức năng thiết yếu cho một trang web mua sắm trực tuyến hiện đại.

---

## ✨ Tính năng

### 👩‍💻 Khách hàng
- Đăng ký và đăng nhập tài khoản
- Xem danh mục sản phẩm
- Tìm kiếm sản phẩm
- Xem chi tiết sản phẩm
- Thêm sản phẩm vào giỏ hàng
- Thanh toán đơn hàng
- Đánh giá sản phẩm
- Gửi khiếu nại

### 🛒 Người bán
- Quản lý sản phẩm (Thêm, Sửa, Xóa)
- Quản lý đơn hàng
- Xem báo cáo thống kê
- Xử lý khiếu nại

---

## ⚙️ Yêu cầu hệ thống
- PHP 7.0 hoặc cao hơn  
- MySQL 5.6 hoặc cao hơn  
- Web server (Apache, Nginx, v.v.)  
- Trình duyệt web hiện đại  

---

## 🚀 Hướng dẫn cài đặt

### 1. Tải mã nguồn
- Clone hoặc tải mã nguồn về và giải nén vào thư mục web server của bạn  
  (ví dụ: `htdocs/ShopPink` đối với XAMPP).

### 2. Tạo cơ sở dữ liệu và import dữ liệu
- Tạo database tên `shoppink` trong phpMyAdmin.  
- Import file `database.sql` có sẵn vào database vừa tạo.

### 3. Cấu hình kết nối database
Sửa file `config.php` nếu cần:
```php
<?php
$host = 'localhost';
$dbname = 'shoppink';
$username = 'root';
$password = '';

Truy cập website: http://localhost/ShopPink/
