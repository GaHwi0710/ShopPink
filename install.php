<?php
// Cấu hình kết nối MySQL
$host = 'localhost';
$username = 'root';
$password = '';
$db_name = 'shoppink';

// Tạo kết nối
$conn = new mysqli($host, $username, $password);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Tạo cơ sở dữ liệu nếu chưa tồn tại
$sql = "CREATE DATABASE IF NOT EXISTS $db_name";
if ($conn->query($sql) === TRUE) {
    echo "Cơ sở dữ liệu $db_name đã được tạo hoặc đã tồn tại.<br>";
} else {
    die("Lỗi khi tạo cơ sở dữ liệu: " . $conn->error);
}

// Chuyển sang sử dụng cơ sở dữ liệu vừa tạo
$conn->select_db($db_name);

// SQL để tạo bảng users
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    role ENUM('customer', 'seller') NOT NULL DEFAULT 'customer',
    avatar VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Bảng users đã được tạo hoặc đã tồn tại.<br>";
} else {
    die("Lỗi khi tạo bảng users: " . $conn->error);
}

// SQL để tạo bảng products
$sql = "CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    seller_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255),
    stock INT NOT NULL DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (seller_id) REFERENCES users(id)
)";

if ($conn->query($sql) === TRUE) {
    echo "Bảng products đã được tạo hoặc đã tồn tại.<br>";
} else {
    die("Lỗi khi tạo bảng products: " . $conn->error);
}

// SQL để tạo bảng orders
$sql = "CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    shipping_address TEXT NOT NULL,
    payment_method ENUM('cod', 'bank_transfer') DEFAULT 'cod',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(id)
)";

if ($conn->query($sql) === TRUE) {
    echo "Bảng orders đã được tạo hoặc đã tồn tại.<br>";
} else {
    die("Lỗi khi tạo bảng orders: " . $conn->error);
}

// SQL để tạo bảng order_items
$sql = "CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
)";

if ($conn->query($sql) === TRUE) {
    echo "Bảng order_items đã được tạo hoặc đã tồn tại.<br>";
} else {
    die("Lỗi khi tạo bảng order_items: " . $conn->error);
}

// SQL để tạo bảng reviews
$sql = "CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    customer_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE
)";

if ($conn->query($sql) === TRUE) {
    echo "Bảng reviews đã được tạo hoặc đã tồn tại.<br>";
} else {
    die("Lỗi khi tạo bảng reviews: " . $conn->error);
}

// SQL để tạo bảng user_addresses
$sql = "CREATE TABLE IF NOT EXISTS user_addresses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    recipient_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address TEXT NOT NULL,
    province VARCHAR(50) NOT NULL,
    district VARCHAR(50) NOT NULL,
    ward VARCHAR(50) NOT NULL,
    is_default BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

if ($conn->query($sql) === TRUE) {
    echo "Bảng user_addresses đã được tạo hoặc đã tồn tại.<br>";
} else {
    die("Lỗi khi tạo bảng user_addresses: " . $conn->error);
}

// SQL để tạo bảng user_wishlist
$sql = "CREATE TABLE IF NOT EXISTS user_wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY (user_id, product_id)
)";

if ($conn->query($sql) === TRUE) {
    echo "Bảng user_wishlist đã được tạo hoặc đã tồn tại.<br>";
} else {
    die("Lỗi khi tạo bảng user_wishlist: " . $conn->error);
}

// SQL để tạo bảng user_notifications
$sql = "CREATE TABLE IF NOT EXISTS user_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('order', 'promotion', 'system') DEFAULT 'system',
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

if ($conn->query($sql) === TRUE) {
    echo "Bảng user_notifications đã được tạo hoặc đã tồn tại.<br>";
} else {
    die("Lỗi khi tạo bảng user_notifications: " . $conn->error);
}

// SQL để tạo bảng user_view_history
$sql = "CREATE TABLE IF NOT EXISTS user_view_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
)";

if ($conn->query($sql) === TRUE) {
    echo "Bảng user_view_history đã được tạo hoặc đã tồn tại.<br>";
} else {
    die("Lỗi khi tạo bảng user_view_history: " . $conn->error);
}

// SQL để tạo bảng vouchers
$sql = "CREATE TABLE IF NOT EXISTS vouchers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    discount_type ENUM('fixed', 'percentage') NOT NULL,
    discount_value DECIMAL(10,2) NOT NULL,
    min_order_value DECIMAL(10,2) DEFAULT 0,
    max_discount_amount DECIMAL(10,2) DEFAULT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    usage_limit INT DEFAULT NULL,
    used_count INT DEFAULT 0,
    status ENUM('active', 'inactive', 'expired') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Bảng vouchers đã được tạo hoặc đã tồn tại.<br>";
} else {
    die("Lỗi khi tạo bảng vouchers: " . $conn->error);
}

// SQL để tạo bảng user_vouchers
$sql = "CREATE TABLE IF NOT EXISTS user_vouchers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    voucher_id INT NOT NULL,
    is_used BOOLEAN DEFAULT FALSE,
    used_at TIMESTAMP NULL,
    order_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (voucher_id) REFERENCES vouchers(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL
)";

if ($conn->query($sql) === TRUE) {
    echo "Bảng user_vouchers đã được tạo hoặc đã tồn tại.<br>";
} else {
    die("Lỗi khi tạo bảng user_vouchers: " . $conn->error);
}

// Thêm dữ liệu mẫu
// Kiểm tra xem đã có dữ liệu mẫu chưa
$check_sql = "SELECT COUNT(*) as count FROM users";
$result = $conn->query($check_sql);
$row = $result->fetch_assoc();

if ($row['count'] == 0) {
    // Mật khẩu mặc định: 'password123'
    $hashed_password = password_hash('password123', PASSWORD_DEFAULT);
    
    // Thêm người dùng mẫu
    $sql = "INSERT INTO users (username, email, password, full_name, phone, address, role) VALUES
            ('customer1', 'customer1@example.com', '$hashed_password', 'Nguyễn Văn A', '0912345678', '123 Đường Nguyễn Trãi, Hà Nội', 'customer'),
            ('seller1', 'seller1@example.com', '$hashed_password', 'Trần Thị B', '0987654321', '456 Đường Lê Văn Lương, Hà Nội', 'seller')";
    
    if ($conn->query($sql) === TRUE) {
        echo "Đã thêm dữ liệu mẫu người dùng.<br>";
    } else {
        echo "Lỗi khi thêm dữ liệu mẫu người dùng: " . $conn->error . "<br>";
    }
    
    // Lấy ID của người bán vừa tạo
    $seller_id = $conn->insert_id;
    
    // Thêm sản phẩm mẫu
    $sql = "INSERT INTO products (seller_id, name, description, price, image, stock) VALUES
            ($seller_id, 'Áo thun nam cổ tròn', 'Áo thun nam chất liệu cotton co giãn 4 chiều, thoáng mát, thấm hút mồ hôi tốt', 150000, 'assets/images/products/ao_thun_nam.jpg', 100),
            ($seller_id, 'Đầm hoa nữ tính', 'Đầm hoa nhí chất liệu voan mỏng nhẹ, thiết kế dáng suông ôm body tôn dáng', 350000, 'assets/images/products/dam_hoa.jpg', 50),
            ($seller_id, 'Tai nghe không dây', 'Tai nghe Bluetooth 5.0 chất lượng cao, thời lượng pin lên đến 8 giờ', 500000, 'assets/images/products/tai_nghe.jpg', 30),
            ($seller_id, 'Balo laptop', 'Balo laptop chống sốc, chống nước, nhiều ngăn tiện lợi, phù hợp với laptop 15.6 inch', 450000, 'assets/images/products/balo.jpg', 20)";
    
    if ($conn->query($sql) === TRUE) {
        echo "Đã thêm dữ liệu mẫu sản phẩm.<br>";
    } else {
        echo "Lỗi khi thêm dữ liệu mẫu sản phẩm: " . $conn->error . "<br>";
    }
    
    // Thêm voucher mẫu
    $sql = "INSERT INTO vouchers (code, name, description, discount_type, discount_value, min_order_value, start_date, end_date, usage_limit, status) VALUES
            ('WELCOME10', 'Chào mừng 10%', 'Giảm 10% cho đơn hàng đầu tiên', 'percentage', 10, 0, '2023-01-01', '2025-12-31', 100, 'active'),
            ('SUMMER20', 'Hè rực rỡ 20%', 'Giảm 20% cho các sản phẩm mùa hè', 'fixed', 100000, 200000, '2023-01-01', '2025-12-31', 200, 'active')";
    
    if ($conn->query($sql) === TRUE) {
        echo "Đã thêm dữ liệu mẫu voucher.<br>";
    } else {
        echo "Lỗi khi thêm dữ liệu mẫu voucher: " . $conn->error . "<br>";
    }
    
    // Gán voucher cho người dùng mẫu
    $customer_id = $conn->insert_id - 1; // ID của customer1
    $voucher_id = $conn->insert_id; // ID của voucher vừa thêm
    
    $sql = "INSERT INTO user_vouchers (user_id, voucher_id) VALUES ($customer_id, $voucher_id)";
    
    if ($conn->query($sql) === TRUE) {
        echo "Đã thêm dữ liệu mẫu voucher cho người dùng.<br>";
    } else {
        echo "Lỗi khi thêm dữ liệu mẫu voucher cho người dùng: " . $conn->error . "<br>";
    }
} else {
    echo "Đã có dữ liệu trong cơ sở dữ liệu, bỏ qua thêm dữ liệu mẫu.<br>";
}

// Đóng kết nối
$conn->close();

echo "Cơ sở dữ liệu đã được thiết lập thành công!<br>";
echo "Bạn có thể xóa file install.php sau khi hoàn tất.";
?>