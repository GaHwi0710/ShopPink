# ShopPink - Sàn thương mại điện tử

## Giới thiệu

ShopPink là một dự án sàn thương mại điện tử giống như Shopee/Lazada, cho phép người dùng mua sắm và người bán đăng bán sản phẩm.

## Tính năng

### Người dùng (Customer)
- Xem, tìm kiếm sản phẩm (không cần đăng nhập)
- Thêm sản phẩm vào giỏ hàng
- Đăng ký/đăng nhập tài khoản
- Thanh toán đơn hàng
- Xem lịch sử đơn hàng
- Đánh giá sản phẩm

### Người bán (Seller)
- Đăng ký tài khoản người bán
- Thêm/sửa/xóa sản phẩm
- Quản lý đơn hàng
- Xem thống kê doanh thu

## Công nghệ sử dụng

- **Backend**: PHP 7.4+
- **Frontend**: HTML, CSS, JavaScript
- **Database**: MySQL
- **Server**: Apache (XAMPP)

## Cài đặt

### Yêu cầu
- PHP 7.4+
- MySQL 5.7+
- Apache Server
- XAMPP (để chạy trên localhost)

### Hướng dẫn cài đặt

1. Clone hoặc tải project về máy
2. Đặt project trong thư mục `htdocs` của XAMPP
3. Tạo database tên `shoppink`
4. Import file `database.sql` vào database
5. Cấu hình file `config/database.php` nếu cần
6. Khởi động Apache và MySQL từ XAMPP Control Panel
7. Truy cập `http://localhost/ShopPink` trên trình duyệt

### Cấu hình Virtual Host (tùy chọn)

Nếu muốn sử dụng virtual host, hãy làm theo các bước sau:

1. Mở file `httpd-vhosts.conf` trong thư mục cài đặt XAMPP
2. Thêm đoạn cấu hình sau:
```apache
<VirtualHost *:80>
    DocumentRoot "C:/xampp/htdocs/ShopPink"
    ServerName shoppink.local
    <Directory "C:/xampp/htdocs/ShopPink">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>