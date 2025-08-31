<?php
require_once '../includes/autoload.php';

// Nếu đã đăng nhập thì chuyển về trang chủ
if (is_logged_in()) {
    header("Location: ../index.php");
    exit();
}

$error = '';
$username = '';

// Xử lý đăng nhập
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $remember_me = isset($_POST['remember_me']);
    
    if (empty($username) || empty($password)) {
        $error = "Vui lòng nhập đầy đủ thông tin!";
    } else {
        // Xác thực user
        $user = authenticateUser($username, $password);
        
        if ($user) {
            // Đăng nhập thành công
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_role'] = $user['role'] ?? 'customer';
            
            // Xử lý remember me
            if ($remember_me) {
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
                
                $token_stmt = $conn->prepare("
                    INSERT INTO user_tokens (user_id, token, expires_at) 
                    VALUES (?, ?, ?)
                ");
                $token_stmt->bind_param("iss", $user['id'], $token, $expires);
                $token_stmt->execute();
                
                setcookie('remember_token', $token, strtotime('+30 days'), '/', '', true, true);
            }
            
            // Chuyển hướng sau đăng nhập
            if (isset($_SESSION['redirect_after_login'])) {
                $redirect_url = $_SESSION['redirect_after_login'];
                unset($_SESSION['redirect_after_login']);
                header("Location: $redirect_url");
            } else {
                header("Location: ../index.php");
            }
            exit();
            
        } else {
            $error = "Tên đăng nhập hoặc mật khẩu không đúng!";
        }
    }
}

// Kiểm tra remember me cookie
if (!is_logged_in() && isset($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];
    
    $token_stmt = $conn->prepare("
        SELECT u.* FROM users u 
        JOIN user_tokens ut ON u.id = ut.user_id 
        WHERE ut.token = ? AND ut.expires_at > NOW() AND u.status = 'active'
    ");
    $token_stmt->bind_param("s", $token);
    $token_stmt->execute();
    $user = $token_stmt->get_result()->fetch_assoc();
    
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['user_role'] = $user['role'] ?? 'customer';
        
        header("Location: ../index.php");
        exit();
    } else {
        // Token không hợp lệ, xóa cookie
        setcookie('remember_token', '', time() - 3600, '/');
    }
}

include('../includes/header.php');
?>

<div class="container">
    <div class="auth-page">
        <div class="auth-container">
            <div class="auth-header">
                <h1 class="auth-title">
                    <i class="fas fa-sign-in-alt"></i> Đăng nhập
                </h1>
                <p class="auth-subtitle">Đăng nhập để mua sắm và quản lý tài khoản</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" class="auth-form">
                <div class="form-group">
                    <label for="username">Tên đăng nhập *</label>
                    <input type="text" id="username" name="username" required 
                           value="<?php echo htmlspecialchars($username); ?>"
                           placeholder="Nhập tên đăng nhập">
                </div>
                
                <div class="form-group">
                    <label for="password">Mật khẩu *</label>
                    <input type="password" id="password" name="password" required 
                           placeholder="Nhập mật khẩu">
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember_me" id="remember_me">
                        <span class="checkmark"></span>
                        Ghi nhớ đăng nhập
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-sign-in-alt"></i> Đăng nhập
                </button>
            </form>
            
            <div class="auth-footer">
                <p>Chưa có tài khoản? 
                    <a href="register.php" class="auth-link">Đăng ký ngay</a>
                </p>
                
                <p>
                    <a href="forgot_password.php" class="auth-link">
                        <i class="fas fa-key"></i> Quên mật khẩu?
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>