-- Tạo bảng categories
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    parent_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Cập nhật bảng products
ALTER TABLE products 
ADD COLUMN category_id INT NOT NULL AFTER id,
ADD COLUMN gender ENUM('nam', 'nữ', 'unisex') DEFAULT 'unisex' AFTER name,
ADD COLUMN brand VARCHAR(100) AFTER gender,
ADD FOREIGN KEY (category_id) REFERENCES categories(id);

-- Thêm dữ liệu mẫu
INSERT INTO categories (name) VALUES 
('Điện tử'), 
('Thời trang nam'), 
('Thời trang nữ'), 
('Đồ gia dụng'), 
('Mỹ phẩm'), 
('Thể thao');

-- Cập nhật bảng users
ALTER TABLE users 
ADD COLUMN theme VARCHAR(50) DEFAULT 'default' AFTER email,
ADD COLUMN preferences TEXT AFTER theme;