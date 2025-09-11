<?php
// register.php
// Trang đăng ký
require_once 'config.php';
// Nếu người dùng đã đăng nhập, chuyển hướng về trang chủ
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
$error = '';
$success = '';
// Xử lý khi người dùng gửi form đăng ký
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'customer';
    
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Vui lòng điền đầy đủ thông tin';
    } elseif ($password !== $confirm_password) {
        $error = 'Mật khẩu và xác nhận mật khẩu không khớp';
    } elseif (strlen($password) < 6) {
        $error = 'Mật khẩu phải có ít nhất 6 ký tự';
    } else {
        try {
            // Kiểm tra email đã tồn tại chưa
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                $error = 'Email đã được sử dụng';
            } else {
                // Hash mật khẩu
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Thêm người dùng mới
                $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
                $stmt->execute([$name, $email, $hashed_password, $role]);
                
                $success = 'Đăng ký thành công! Vui lòng <a href="login.php">đăng nhập</a>';
            }
        } catch (PDOException $e) {
            $error = 'Lỗi: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký - ShopPink</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">
                <a href="index.php">ShopPink</a>
            </div>
            <nav>
                <ul>
                    <li><a href="index.php">Trang chủ</a></li>
                    <li><a href="products.php">Sản phẩm</a></li>
                    <li><a href="login.php">Đăng nhập</a></li>
                </ul>
            </nav>
        </div>
    </header>
    <main>
        <section class="auth-form">
            <div class="container">
                <div class="form-container">
                    <h2>Đăng ký</h2>
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-error"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    <form action="register.php" method="post">
                        <div class="form-group">
                            <label for="name">Họ và tên</label>
                            <input type="text" id="name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Mật khẩu</label>
                            <input type="password" id="password" name="password" required>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Xác nhận mật khẩu</label>
                            <input type="password" id="confirm_password" name="confirm_password" required>
                        </div>
                        <div class="form-group">
                            <label for="role">Vai trò</label>
                            <select id="role" name="role">
                                <option value="customer">Khách hàng</option>
                                <option value="seller">Người bán</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn">Đăng ký</button>
                        </div>
                        <div class="form-links">
                            <p>Đã có tài khoản? <a href="login.php">Đăng nhập</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </main>
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>ShopPink</h3>
                    <p>Nơi mua sắm trực tuyến đáng tin cậy với nhiều sản phẩm chất lượng.</p>
                </div>
                <div class="footer-section">
                    <h3>Liên kết nhanh</h3>
                    <ul>
                        <li><a href="index.php">Trang chủ</a></li>
                        <li><a href="products.php">Sản phẩm</a></li>
                        <li><a href="login.php">Đăng nhập</a></li>
                        <li><a href="register.php">Đăng ký</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Liên hệ</h3>
                    <p>Email: contact@shoppink.com</p>
                    <p>Điện thoại: 0123 456 789</p>
                    <p>Địa chỉ: Hà Nội</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> ShopPink.</p>
            </div>
        </div>
    </footer>
    <script src="assets/js/main.js"></script>
</body>
</html>