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

// Xử lý đăng ký
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = trim($_POST['full_name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    
    // Validation
    if (empty($full_name) || empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "Vui lòng điền đầy đủ thông tin bắt buộc.";
    } elseif (strlen($password) < 6) {
        $error = "Mật khẩu phải có ít nhất 6 ký tự.";
    } elseif ($password !== $confirm_password) {
        $error = "Mật khẩu xác nhận không khớp.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Email không hợp lệ.";
    } elseif (!empty($phone) && !preg_match('/^[0-9]{9,11}$/', $phone)) {
        $error = "Số điện thoại không hợp lệ.";
    } else {
        // Kiểm tra username hoặc email đã tồn tại chưa
        $check_stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $check_stmt->bind_param("ss", $username, $email);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "Tên đăng nhập hoặc email đã tồn tại!";
        } else {
            // Mã hóa mật khẩu
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Thêm user mới
            $insert_stmt = $conn->prepare("
                INSERT INTO users (full_name, username, email, password, phone, address, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            $insert_stmt->bind_param("ssssss", $full_name, $username, $email, $hashed_password, $phone, $address);
            
            if ($insert_stmt->execute()) {
                $user_id = $insert_stmt->insert_id;
                
                // Đăng nhập tự động sau khi đăng ký
                $_SESSION['user_id'] = $user_id;
                $_SESSION['user_name'] = $full_name;
                $_SESSION['username'] = $username;
                $_SESSION['email'] = $email;
                
                // Gửi email chào mừng
                $to = $email;
                $subject = "Chào mừng bạn đến với ShopPink!";
                $message = "
                    <html>
                    <head>
                        <title>Chào mừng bạn đến với ShopPink!</title>
                    </head>
                    <body>
                        <h2>Chào mừng $full_name,</h2>
                        <p>Cảm ơn bạn đã đăng ký tài khoản tại ShopPink.</p>
                        <p>Tên đăng nhập: <strong>$username</strong></p>
                        <p>Email: <strong>$email</strong></p>
                        <p>Bây giờ bạn có thể trải nghiệm mua sắm tuyệt vời tại ShopPink với nhiều ưu đãi độc quyền.</p>
                        <p>Trân trọng,<br>Đội ngũ ShopPink</p>
                    </body>
                    </html>
                ";
                
                $headers = "MIME-Version: 1.0" . "\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                $headers .= 'From: <no-reply@shoppink.com>' . "\r\n";
                
                // mail($to, $subject, $message, $headers);
                
                $success = "Đăng ký thành công! Bạn đã được tự động đăng nhập.";
                
                // Chuyển hướng sau 2 giây
                header("refresh:2;url=index.php");
            } else {
                $error = "Lỗi: " . $conn->error;
            }
        }
    }
}
?>
<?php include('includes/header.php'); ?>

<div class="container">
    <div class="register-container animate-on-scroll">
        <div class="register-form">
            <h1>Đăng ký tài khoản</h1>
            <p>Tạo tài khoản để trải nghiệm mua sắm tuyệt vời tại ShopPink</p>
            
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
            
            <form method="post" action="register.php" id="register-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="full_name">Họ và tên <span class="required">*</span></label>
                        <div class="input-group">
                            <i class="fas fa-user"></i>
                            <input type="text" id="full_name" name="full_name" placeholder="Nhập họ và tên" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="username">Tên đăng nhập <span class="required">*</span></label>
                        <div class="input-group">
                            <i class="fas fa-user-tag"></i>
                            <input type="text" id="username" name="username" placeholder="Nhập tên đăng nhập" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="email">Email <span class="required">*</span></label>
                    <div class="input-group">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" placeholder="Nhập email" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Mật khẩu <span class="required">*</span></label>
                        <div class="input-group">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="password" name="password" placeholder="Nhập mật khẩu" required>
                            <button type="button" id="toggle-password" class="toggle-password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="password-strength">
                            <div class="strength-bar">
                                <div class="strength-indicator"></div>
                            </div>
                            <span class="strength-text">Mật khẩu yếu</span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Xác nhận mật khẩu <span class="required">*</span></label>
                        <div class="input-group">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="confirm_password" name="confirm_password" placeholder="Xác nhận lại mật khẩu" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="phone">Số điện thoại</label>
                        <div class="input-group">
                            <i class="fas fa-phone"></i>
                            <input type="tel" id="phone" name="phone" placeholder="Nhập số điện thoại">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Địa chỉ</label>
                        <div class="input-group">
                            <i class="fas fa-map-marker-alt"></i>
                            <input type="text" id="address" name="address" placeholder="Nhập địa chỉ">
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="terms-checkbox">
                        <input type="checkbox" id="terms" name="terms" required>
                        <label for="terms">Tôi đồng ý với <a href="terms.php" target="_blank">Điều khoản sử dụng</a> và <a href="privacy.php" target="_blank">Chính sách bảo mật</a></label>
                    </div>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn-primary">Đăng ký</button>
                </div>
                
                <div class="social-login">
                    <p>Hoặc đăng ký với</p>
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
                    <p>Đã có tài khoản? <a href="login.php">Đăng nhập</a></p>
                </div>
            </form>
        </div>
        
        <div class="register-info animate-on-scroll">
            <div class="register-info-content">
                <h2>Tham gia cùng chúng tôi!</h2>
                <p>Đăng ký tài khoản để nhận những ưu đãi độc quyền và trải nghiệm mua sắm tuyệt vời tại ShopPink.</p>
                
                <div class="benefits">
                    <div class="benefit-item">
                        <i class="fas fa-gift"></i>
                        <h3>Ưu đãi độc quyền</h3>
                        <p>Nhận mã giảm giá và khuyến mãi đặc biệt</p>
                    </div>
                    
                    <div class="benefit-item">
                        <i class="fas fa-bell"></i>
                        <h3>Thông báo mới nhất</h3>
                        <p>Cập nhật sản phẩm và chương trình khuyến mãi</p>
                    </div>
                    
                    <div class="benefit-item">
                        <i class="fas fa-history"></i>
                        <h3>Lịch sử mua hàng</h3>
                        <p>Theo dõi lịch sử mua hàng của bạn</p>
                    </div>
                    
                    <div class="benefit-item">
                        <i class="fas fa-heart"></i>
                        <h3>Danh sách yêu thích</h3>
                        <p>Lưu trữ sản phẩm yêu thích của bạn</p>
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

// Password strength indicator
const passwordInput = document.getElementById('password');
const strengthIndicator = document.querySelector('.strength-indicator');
const strengthText = document.querySelector('.strength-text');

passwordInput?.addEventListener('input', function() {
    const password = this.value;
    let strength = 0;
    
    // Check password strength
    if (password.length >= 6) strength++;
    if (password.match(/[a-z]+/)) strength++;
    if (password.match(/[A-Z]+/)) strength++;
    if (password.match(/[0-9]+/)) strength++;
    if (password.match(/[$@#&!]+/)) strength++;
    
    // Update strength indicator
    const strengthPercent = (strength / 5) * 100;
    strengthIndicator.style.width = strengthPercent + '%';
    
    // Update strength text and color
    if (strength <= 1) {
        strengthText.textContent = 'Mật khẩu yếu';
        strengthIndicator.style.backgroundColor = '#ff4d4d';
    } else if (strength <= 3) {
        strengthText.textContent = 'Mật khẩu trung bình';
        strengthIndicator.style.backgroundColor = '#ffa64d';
    } else if (strength <= 4) {
        strengthText.textContent = 'Mật khẩu tốt';
        strengthIndicator.style.backgroundColor = '#ffff4d';
    } else {
        strengthText.textContent = 'Mật khẩu mạnh';
        strengthIndicator.style.backgroundColor = '#4dff4d';
    }
});

// Confirm password validation
const confirmPassword = document.getElementById('confirm_password');

confirmPassword?.addEventListener('input', function() {
    const password = document.getElementById('password').value;
    
    if (this.value !== password) {
        this.setCustomValidity('Mật khẩu xác nhận không khớp');
    } else {
        this.setCustomValidity('');
    }
});

// Form validation
document.getElementById('register-form')?.addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const terms = document.getElementById('terms').checked;
    
    if (password.length < 6) {
        e.preventDefault();
        showToast('error', 'Mật khẩu phải có ít nhất 6 ký tự.');
    } else if (password !== confirmPassword) {
        e.preventDefault();
        showToast('error', 'Mật khẩu xác nhận không khớp.');
    } else if (!terms) {
        e.preventDefault();
        showToast('error', 'Vui lòng đồng ý với điều khoản sử dụng và chính sách bảo mật.');
    }
});

// Social login buttons
document.querySelector('.btn-facebook')?.addEventListener('click', function() {
    showToast('info', 'Đăng ký bằng Facebook đang được phát triển.');
});

document.querySelector('.btn-google')?.addEventListener('click', function() {
    showToast('info', 'Đăng ký bằng Google đang được phát triển.');
});
</script>