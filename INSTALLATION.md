# Hướng dẫn cài đặt ShopPink trên Localhost

## Yêu cầu hệ thống:
- **XAMPP** (Apache + MySQL + PHP) hoặc **WAMP** hoặc **LAMP**
- **PHP 7.4+** 
- **MySQL 5.7+**
- **Apache 2.4+**

## Bước 1: Cài đặt XAMPP
1. Tải XAMPP từ: https://www.apachefriends.org/
2. Cài đặt XAMPP (chọn Apache và MySQL)
3. Khởi động XAMPP Control Panel
4. Start Apache và MySQL

## Bước 2: Cài đặt dự án
1. Copy thư mục `ShopPink` vào `C:\xampp\htdocs\` (Windows) hoặc `/opt/lampp/htdocs/` (Linux)
2. Đảm bảo đường dẫn: `C:\xampp\htdocs\ShopPink\`

## Bước 3: Tạo Database
1. Mở trình duyệt, truy cập: `http://localhost/phpmyadmin`
2. Tạo database mới tên: `shoppink`
3. Import file SQL:
   - `create_orders_tables.sql`
   - `database_update.sql`

## Bước 4: Cấu hình Database
1. Mở file `config.php` trong thư mục gốc
2. Kiểm tra thông tin kết nối:
```php
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "shoppink";
```

## Bước 5: Cấu hình quyền thư mục
### Windows:
- Không cần thay đổi quyền

### Linux/Mac:
```bash
chmod 755 -R /opt/lampp/htdocs/ShopPink/
chmod 777 -R /opt/lampp/htdocs/ShopPink/assets/uploads/
chmod 777 -R /opt/lampp/htdocs/ShopPink/logs/
```

## Bước 6: Truy cập website
1. Mở trình duyệt
2. Truy cập: `http://localhost/ShopPink/`
3. Website sẽ hiển thị trang chủ

## Xử lý lỗi thường gặp:

### Lỗi 1: "Connection failed"
- Kiểm tra MySQL đã start chưa
- Kiểm tra thông tin database trong `config.php`
- Đảm bảo database `shoppink` đã được tạo

### Lỗi 2: "File not found"
- Kiểm tra đường dẫn thư mục
- Đảm bảo file `index.php` tồn tại
- Kiểm tra Apache đã start

### Lỗi 3: "Permission denied"
- Kiểm tra quyền thư mục (Linux/Mac)
- Đảm bảo Apache có quyền đọc thư mục

### Lỗi 4: "500 Internal Server Error"
- Kiểm tra file `.htaccess`
- Kiểm tra PHP error log
- Đảm bảo mod_rewrite đã enable

### Lỗi 5: "Undefined function"
- Kiểm tra file `includes/autoload.php` tồn tại
- Kiểm tra file `includes/functions.php` tồn tại

## Cấu trúc thư mục sau khi cài đặt:
```
C:\xampp\htdocs\ShopPink\
├── assets/
│   ├── css/
│   ├── js/
│   └── images/
├── includes/
│   ├── autoload.php
│   ├── config.php
│   ├── functions.php
│   ├── header.php
│   └── footer.php
├── pages/
├── index.php
├── login.php
├── register.php
├── cart.php
├── config.php
└── .htaccess
```

## Test các chức năng:
1. **Đăng ký tài khoản mới**
2. **Đăng nhập**
3. **Xem sản phẩm**
4. **Thêm vào giỏ hàng**
5. **Thanh toán**

## Lưu ý quan trọng:
- Đảm bảo XAMPP chạy trước khi truy cập website
- Nếu thay đổi port Apache, cập nhật URL tương ứng
- Backup database trước khi cập nhật
- Kiểm tra PHP error log nếu có lỗi

## Hỗ trợ:
Nếu gặp vấn đề, kiểm tra:
1. XAMPP error log
2. PHP error log
3. Apache error log
4. Database connection

---
*Hướng dẫn này dành cho môi trường development local*