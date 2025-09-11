-- database.sql
CREATE DATABASE IF NOT EXISTS shoppink;
USE shoppink;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    address TEXT,
    role ENUM('user', 'seller', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Products table
CREATE TABLE IF NOT EXISTS products (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    stock INT(11) NOT NULL DEFAULT 0,
    category_id INT(11),
    image VARCHAR(255) NOT NULL,
    brand VARCHAR(100),
    featured TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    total DECIMAL(10, 2) NOT NULL,
    payment_method ENUM('cod', 'bank', 'momo') NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'completed', 'cancelled') DEFAULT 'pending',
    shipping_address TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Order details table
CREATE TABLE IF NOT EXISTS order_details (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    order_id INT(11) NOT NULL,
    product_id INT(11) NOT NULL,
    quantity INT(11) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Reviews table
CREATE TABLE IF NOT EXISTS reviews (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    product_id INT(11) NOT NULL,
    user_id INT(11) NOT NULL,
    rating TINYINT(1) NOT NULL,
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY (product_id, user_id)
);

-- Tạo bảng complaints
CREATE TABLE IF NOT EXISTS complaints (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    user_id INT NOT NULL,
    description TEXT NOT NULL,
    status ENUM('pending', 'resolved') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert sample data
-- Categories
INSERT INTO categories (name, description) VALUES
('Thời trang nữ', 'Các sản phẩm thời trang dành cho nữ'),
('Thời trang nam', 'Các sản phẩm thời trang dành cho nam'),
('Phụ kiện', 'Các sản phẩm phụ kiện thời trang'),
('Giày dép', 'Các sản phẩm giày dép thời trang'),
('Túi xách', 'Các sản phẩm túi xách thời trang'),
('Trang sức', 'Các sản phẩm trang sức thời trang');

-- Users
INSERT INTO users (name, email, password, phone, address, role) VALUES
('Admin User', 'admin@shoppink.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0901234567', '123 Nguyễn Huệ, Q.1, TP.HCM', 'admin'),
('Seller User', 'seller@shoppink.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0901234568', '456 Lê Lợi, Q.1, TP.HCM', 'seller'),
('Customer User', 'customer@shoppink.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0901234569', '789 Đồng Khởi, Q.1, TP.HCM', 'user');

-- Products
INSERT INTO products (name, description, price, stock, category_id, image, brand, featured) VALUES
('Áo thun nữ cổ tròn', 'Áo thun nữ cổ tròn chất liệu cotton mềm mại, thoáng mát', 150000, 50, 1, 'assets/assets/images/products/women-tshirt.jpg', 'Nike', 1),
('Áo sơ mi nữ tay dài', 'Áo sơ mi nữ tay dài thiết kế thanh lịch, phù hợp với môi trường công sở', 250000, 30, 1, 'assets/assets/images/products/women-shirt.jpg', 'Zara', 1),
('Váy nữ hoa nhí', 'Váy nữ hoa nhí dáng suông, chất liệu lụa mềm mại', 350000, 20, 1, 'assets/assets/images/products/women-dress.jpg', 'Mango', 0),
('Quần jeans nam', 'Quần jeans nam dáng straight, chất liệu denim bền đẹp', 450000, 40, 2, 'assets/assets/images/products/men-jeans.jpg', 'Levi\'s', 1),
('Áo thun nam tay ngắn', 'Áo thun nam tay ngắn chất liệu cotton co giãn 4 chiều', 180000, 60, 2, 'assets/assets/images/products/men-tshirt.jpg', 'Adidas', 1),
('Áo khoác nam', 'Áo khoác nam chống nước, phù hợp cho những ngày trời lạnh', 650000, 25, 2, 'assets/assets/images/products/men-jacket.jpg', 'The North Face', 0),
('Túi xách da thật', 'Túi xách da thật thiết kế sang trọng, đựng được laptop 15 inch', 1200000, 15, 5, 'assets/assets/images/products/leather-bag.jpg', 'Coach', 1),
('Túi đeo chéo nữ', 'Túi đeo chéo nữ thiết kế trẻ trung, năng động', 350000, 40, 5, 'assets/assets/images/products/crossbody-bag.jpg', 'Michael Kors', 0),
('Giày sneaker nam', 'Giày sneaker nam thiết kế thể thao, đế cao su chống trơn', 850000, 30, 4, 'assets/assets/images/products/men-sneakers.jpg', 'Nike', 1),
('Giày cao gót nữ', 'Giày cao gót nữ 7cm, thiết kế thanh lịch', 550000, 20, 4, 'assets/assets/images/products/women-heels.jpg', 'Charles & Keith', 1),
('Bông tai bạc', 'Bông tai bạc 925 thiết kế tinh tế, phù hợp với mọi outfit', 450000, 50, 6, 'assets/assets/images/products/silver-earrings.jpg', 'Pandora', 0),
('Dây chuyền vàng', 'Dây chuyền vàng 18K thiết kế đơn giản nhưng sang trọng', 2500000, 10, 6, 'assets/assets/images/products/gold-necklace.jpg', 'Cartier', 1),
('Kính mát nữ', 'Kính mát nữ thiết kế thời trang, chống tia UV', 750000, 35, 3, 'assets/assets/images/products/women-sunglasses.jpg', 'Ray-Ban', 1),
('Đồng hồ nam', 'Đồng hồ nam dây da, thiết kế cổ điển', 1500000, 20, 3, 'assets/assets/images/products/men-watch.jpg', 'Casio', 0),
('Ví da nam', 'Ví da nam thiết kế gọn nhẹ, nhiều ngăn', 650000, 45, 3, 'assets/assets/images/products/men-wallet.jpg', 'Hermes', 1),
('Mũ lưỡi trai', 'Mũ lưỡi trai chất liệu cotton, chống nắng hiệu quả', 150000, 60, 3, 'assets/assets/images/products/cap.jpg', 'New Era', 0);

UPDATE products SET image = 'aokhoacnam.jpg' WHERE name = 'Áo khoác nam';
UPDATE products SET image = 'aosominu.jpg' WHERE name = 'Áo sơ mi nữ tay dài';
UPDATE products SET image = 'aothunu.jpg' WHERE name = 'Áo thun nữ cổ tròn';
UPDATE products SET image = 'vaynuhoanhi.jpg' WHERE name = 'Váy nữ hoa nhí';
UPDATE products SET image = 'quanjeansnam.jpg' WHERE name = 'Quần jeans nam';
UPDATE products SET image = 'tuisachdathat.jpg' WHERE name = 'Túi xách da thật';
UPDATE products SET image = 'tuideocheonu.jpg' WHERE name = 'Túi đeo chéo nữ';
UPDATE products SET image = 'donghonam.jpg' WHERE name = 'Đồng hồ nam';
UPDATE products SET image = 'kinhmatnu.jpg' WHERE name = 'Kính mát nữ';
UPDATE products SET image = 'daychuyenvang.jpg' WHERE name = 'Dây chuyền vàng';
UPDATE products SET image = 'bongtaibac.jpg' WHERE name = 'Bông tai bạc';
UPDATE products SET image = 'giaycaogotnu.jpg' WHERE name = 'Giày cao gót nữ';
UPDATE products SET image = 'giaynam.jpg' WHERE name = 'Giày sneaker nam';
UPDATE products SET image = 'vidanam.jpg' WHERE name = 'Ví da nam';
UPDATE products SET image = 'muluoitrai.jpg' WHERE name = 'Mũ lưỡi trai';

