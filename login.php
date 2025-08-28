<?php
session_start();
include('includes/config.php');

// Nếu người dùng đã đăng nhập, chuyển hướng đến trang chủ
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';
$success = '';

// Xử lý đăng nhập
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']) ? true : false;
    
    if (empty($username) || empty($password)) {
        $error = "Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu.";
    } else {
        // Sử dụng prepared statement để tránh SQL injection
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            // Kiểm tra mật khẩu
            if (password_verify($password, $user['password'])) {
                // Đăng nhập thành công
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['full_name'] ?? $user['username'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                
                // Cập nhật thời gian đăng nhập cuối
                $update_stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $update_stmt->bind_param("i", $user['id']);
                $update_stmt->execute();
                
                // Nếu người dùng chọn ghi nhớ đăng nhập
                if ($remember) {
                    // Tạo token ghi nhớ đăng nhập
                    $token = bin2hex(random_bytes(32));
                    $expiry = date('Y-m-d H:i:s', strtotime('+30 days'));
                    
                    // Lưu token vào database
                    $token_stmt = $conn->prepare("INSERT INTO user_tokens (user_id, token, expiry) VALUES (?, ?, ?)");
                    $token_stmt->bind_param("iss", $user['id'], $token, $expiry);
                    $token_stmt->execute();
                    
                    // Lưu token vào cookie
                    setcookie('remember_token', $token, time() + (86400 * 30), '/', '', true, true);
                }
                
                // Chuyển hướng đến trang trước đó hoặc trang chủ
                $redirect = isset($_SESSION['redirect_after_login']) ? $_SESSION['redirect_after_login'] : 'index.php';
                unset($_SESSION['redirect_after_login']);
                header("Location: $redirect");
                exit();
            } else {
                $error = "Mật khẩu không đúng!";
            }
        } else {
            $error = "Tên đăng nhập hoặc email không tồn tại!";
        }
    }
}

// Kiểm tra cookie ghi nhớ đăng nhập
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];
    
    // Kiểm tra token trong database
    $token_stmt = $conn->prepare("SELECT u.* FROM users u JOIN user_tokens t ON u.id = t.user_id WHERE t.token = ? AND t.expiry > NOW()");
    $token_stmt->bind_param("s", $token);
    $token_stmt->execute();
    $result = $token_stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        // Đăng nhập tự động
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['full_name'] ?? $user['username'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        
        // Cập nhật thời gian đăng nhập cuối
        $update_stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $update_stmt->bind_param("i", $user['id']);
        $update_stmt->execute();
        
        // Tạo token mới
        $new_token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+30 days'));
        
        // Cập nhật token trong database
        $update_token_stmt = $conn->prepare("UPDATE user_tokens SET token = ?, expiry = ? WHERE token = ?");
        $update_token_stmt->bind_param("sss", $new_token, $expiry, $token);
        $update_token_stmt->execute();
        
        // Cập nhật cookie
        setcookie('remember_token', $new_token, time() + (86400 * 30), '/', '', true, true);
        
        // Chuyển hướng đến trang chủ
        header("Location: index.php");
        exit();
    } else {
        // Xóa cookie không hợp lệ
        setcookie('remember_token', '', time() - 3600, '/', '', true, true);
    }
}
?>
<?php include('includes/header.php'); ?>

<div class="container">
    <div class="login-container animate-on-scroll">
        <div class="login-form">
            <h1>Đăng nhập</h1>
            <p>Đăng nhập để trải nghiệm mua sắm tuyệt vời tại ShopPink</p>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <form method="post" action="login.php" id="login-form">
                <div class="form-group">
                    <label for="username">Tên đăng nhập hoặc Email</label>
                    <div class="input-group">
                        <i class="fas fa-user"></i>
                        <input type="text" id="username" name="username" placeholder="Nhập tên đăng nhập hoặc email" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Mật khẩu</label>
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="Nhập mật khẩu" required>
                        <button type="button" id="toggle-password" class="toggle-password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div class="form-options">
                    <div class="remember-me">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">Ghi nhớ đăng nhập</label>
                    </div>
                    <a href="forgot_password.php" class="forgot-password">Quên mật khẩu?</a>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn-primary">Đăng nhập</button>
                </div>
                
                <div class="social-login">
                    <p>Hoặc đăng nhập với</p>
                    <div class="social-buttons">
                        <button type="button" class="btn-facebook">
                            <i class="fab fa-facebook-f"></i> Facebook
                        </button>
                        <button type="button" class="btn-google">
                            <i class="fab fa-google"></i> Google
                        </button>
                    </div>
                </div>
                
                <div class="form-footer">
                    <p>Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a></p>
                </div>
            </form>
        </div>
        
        <div class="login-info animate-on-scroll">
            <div class="login-info-content">
                <h2>Chào mừng bạn trở lại!</h2>
                <p>Đăng nhập để trải nghiệm mua sắm tuyệt vời tại ShopPink và nhận những ưu đãi độc quyền chỉ dành cho thành viên.</p>
                
                <div class="benefits">
                    <div class="benefit-item">
                        <i class="fas fa-shipping-fast"></i>
                        <h3>Giao hàng nhanh chóng</h3>
                        <p>Giao hàng trong 2-5 ngày làm việc</p>
                    </div>
                    
                    <div class="benefit-item">
                        <i class="fas fa-shield-alt"></i>
                        <h3>Thanh toán an toàn</h3>
                        <p>Bảo mật thông tin và thanh toán</p>
                    </div>
                    
                    <div class="benefit-item">
                        <i class="fas fa-undo-alt"></i>
                        <h3>Đổi trả dễ dàng</h3>
                        <p>Đổi trả trong vòng 30 ngày</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>

<script>
// Toggle password visibility
document.getElementById('toggle-password')?.addEventListener('click', function() {
    const passwordInput = document.getElementById('password');
    const icon = this.querySelector('i');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
});

// Form validation
document.getElementById('login-form')?.addEventListener('submit', function(e) {
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value;
    
    if (!username || !password) {
        e.preventDefault();
        showToast('error', 'Vui lòng nhập đầy đủ thông tin.');
    }
});

// Social login buttons
document.querySelector('.btn-facebook')?.addEventListener('click', function() {
    showToast('info', 'Đăng nhập bằng Facebook đang được phát triển.');
});

document.querySelector('.btn-google')?.addEventListener('click', function() {
    showToast('info', 'Đăng nhập bằng Google đang được phát triển.');
});
</script>