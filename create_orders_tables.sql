-- Tạo bảng orders
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_code VARCHAR(20) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    total DECIMAL(10, 2) NOT NULL,
    address TEXT NOT NULL,
    phone VARCHAR(20) NOT NULL,
    city VARCHAR(100) NOT NULL,
    district VARCHAR(100) NOT NULL,
    payment_method ENUM('cod', 'bank', 'momo') NOT NULL,
    payment_status ENUM('unpaid', 'paid', 'refunded') DEFAULT 'unpaid',
    status ENUM('pending', 'confirmed', 'processing', 'shipping', 'completed', 'cancelled') DEFAULT 'pending',
    note TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX (order_code),
    INDEX (user_id),
    INDEX (status)
);

-- Tạo bảng order_details
CREATE TABLE IF NOT EXISTS order_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    product_image VARCHAR(255),
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    total DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX (order_id),
    INDEX (product_id)
);

-- Tạo bảng order_status_history để theo dõi lịch sử thay đổi trạng thái đơn hàng
CREATE TABLE IF NOT EXISTS order_status_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    status ENUM('pending', 'confirmed', 'processing', 'shipping', 'completed', 'cancelled') NOT NULL,
    note TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    INDEX (order_id)
);

-- Tạo bảng reviews để lưu đánh giá sản phẩm
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    order_id INT,
    rating TINYINT(1) NOT NULL CHECK (rating BETWEEN 1 AND 5),
    title VARCHAR(255),
    comment TEXT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
    INDEX (user_id),
    INDEX (product_id),
    INDEX (rating)
);

-- Tạo bảng wishlist để lưu sản phẩm yêu thích
CREATE TABLE IF NOT EXISTS wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY (user_id, product_id),
    INDEX (user_id),
    INDEX (product_id)
);

-- Tạo bảng notifications để lưu thông báo cho người dùng
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'success', 'warning', 'error') DEFAULT 'info',
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX (user_id),
    INDEX (is_read)
);

-- Tạo bảng banners để quản lý quảng cáo
CREATE TABLE IF NOT EXISTS banners (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    image VARCHAR(255) NOT NULL,
    link VARCHAR(255),
    position ENUM('home', 'sidebar', 'footer') DEFAULT 'home',
    sort_order INT DEFAULT 0,
    status BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (position),
    INDEX (status)
);

-- Tạo bảng brands để quản lý thương hiệu
CREATE TABLE IF NOT EXISTS brands (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL,
    logo VARCHAR(255),
    description TEXT,
    status BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY (slug),
    INDEX (status)
);

-- Cập nhật bảng products để thêm các trường cần thiết
ALTER TABLE products 
ADD COLUMN brand_id INT NULL AFTER category_id,
ADD COLUMN slug VARCHAR(255) NULL AFTER name,
ADD COLUMN old_price DECIMAL(10, 2) NULL AFTER price,
ADD COLUMN stock INT NOT NULL DEFAULT 0 AFTER old_price,
ADD COLUMN status BOOLEAN DEFAULT TRUE AFTER stock,
ADD COLUMN featured BOOLEAN DEFAULT FALSE AFTER status,
ADD COLUMN view_count INT DEFAULT 0 AFTER featured,
ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER view_count,
ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;

-- Thêm khóa ngoại cho brand_id
ALTER TABLE products 
ADD FOREIGN KEY (brand_id) REFERENCES brands(id) ON DELETE SET NULL;

-- Thêm unique index cho slug
ALTER TABLE products 
ADD UNIQUE INDEX (slug);

-- Cập nhật bảng categories để thêm slug
ALTER TABLE categories 
ADD COLUMN slug VARCHAR(100) NULL AFTER name,
ADD COLUMN status BOOLEAN DEFAULT TRUE AFTER slug,
ADD COLUMN sort_order INT DEFAULT 0 AFTER status,
ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER sort_order,
ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;

-- Thêm unique index cho slug
ALTER TABLE categories 
ADD UNIQUE INDEX (slug);

-- Cập nhật bảng users để thêm các trường cần thiết
ALTER TABLE users 
ADD COLUMN full_name VARCHAR(100) NULL AFTER name,
ADD COLUMN phone VARCHAR(20) NULL AFTER email,
ADD COLUMN avatar VARCHAR(255) NULL AFTER phone,
ADD COLUMN address TEXT NULL AFTER avatar,
ADD COLUMN city VARCHAR(100) NULL AFTER address,
ADD COLUMN district VARCHAR(100) NULL AFTER city,
ADD COLUMN preferences TEXT NULL AFTER district COMMENT 'JSON array of category IDs',
ADD COLUMN last_login TIMESTAMP NULL AFTER preferences,
ADD COLUMN status BOOLEAN DEFAULT TRUE AFTER last_login,
ADD COLUMN role ENUM('user', 'admin') DEFAULT 'user' AFTER status,
ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER role,
ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;

-- Thêm dữ liệu mẫu cho bảng brands
INSERT INTO brands (name, slug, status) VALUES
('Nike', 'nike', TRUE),
('Adidas', 'adidas', TRUE),
('Zara', 'zara', TRUE),
('H&M', 'hm', TRUE),
('Gucci', 'gucci', TRUE),
('Louis Vuitton', 'louis-vuitton', TRUE),
('Chanel', 'chanel', TRUE),
('Dior', 'dior', TRUE);

-- Thêm dữ liệu mẫu cho bảng banners
INSERT INTO banners (title, image, position, sort_order, status) VALUES
('Banner 1', 'banner1.jpg', 'home', 1, TRUE),
('Banner 2', 'banner2.jpg', 'home', 2, TRUE),
('Banner 3', 'banner3.jpg', 'home', 3, TRUE),
('Banner Sidebar', 'banner-sidebar.jpg', 'sidebar', 1, TRUE);

-- Tạo trigger để tự động tạo mã đơn hàng
DELIMITER //
CREATE TRIGGER before_order_insert 
BEFORE INSERT ON orders
FOR EACH ROW
BEGIN
    DECLARE order_code VARCHAR(20);
    SET order_code = CONCAT('ORD', DATE_FORMAT(NOW(), '%Y%m%d'), LPAD((SELECT IFNULL(MAX(CAST(SUBSTRING(order_code, 10) AS UNSIGNED)), 0) + 1 FROM orders WHERE DATE(created_at) = CURDATE()), 6, '0'));
    SET NEW.order_code = order_code;
END//
DELIMITER ;

-- Tạo trigger để tự động cập nhật tổng tiền trong order_details
DELIMITER //
CREATE TRIGGER before_order_detail_insert 
BEFORE INSERT ON order_details
FOR EACH ROW
BEGIN
    SET NEW.total = NEW.quantity * NEW.price;
END//
DELIMITER ;

-- Tạo trigger để tự động cập nhật tổng tiền đơn hàng khi thêm chi tiết đơn hàng
DELIMITER //
CREATE TRIGGER after_order_detail_insert 
AFTER INSERT ON order_details
FOR EACH ROW
BEGIN
    UPDATE orders o 
    SET o.total = (SELECT SUM(od.total) FROM order_details od WHERE od.order_id = NEW.order_id) 
    WHERE o.id = NEW.order_id;
END//
DELIMITER ;

-- Tạo trigger để tự động cập nhật tổng tiền đơn hàng khi cập nhật chi tiết đơn hàng
DELIMITER //
CREATE TRIGGER after_order_detail_update 
AFTER UPDATE ON order_details
FOR EACH ROW
BEGIN
    UPDATE orders o 
    SET o.total = (SELECT SUM(od.total) FROM order_details od WHERE od.order_id = NEW.order_id) 
    WHERE o.id = NEW.order_id;
END//
DELIMITER ;

-- Tạo trigger để tự động cập nhật tổng tiền đơn hàng khi xóa chi tiết đơn hàng
DELIMITER //
CREATE TRIGGER after_order_detail_delete 
AFTER DELETE ON order_details
FOR EACH ROW
BEGIN
    UPDATE orders o 
    SET o.total = (SELECT IFNULL(SUM(od.total), 0) FROM order_details od WHERE od.order_id = OLD.order_id) 
    WHERE o.id = OLD.order_id;
END//
DELIMITER ;