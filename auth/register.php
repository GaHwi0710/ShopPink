<?php
require_once '../includes/autoload.php';

// Nếu đã đăng nhập thì chuyển về trang chủ
if (is_logged_in()) {
    header("Location: ../index.php");
    exit();
}

$error = '';
$success = '';
$form_data = [
    'username' => '',
    'full_name' => '',
    'phone' => '',
    'email' => '',
    'address' => ''
];

// Xử lý đăng ký
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    
    // Validation
    if (empty($username) || empty($password) || empty($confirm_password) || empty($full_name) || empty($phone)) {
        $error = "Vui lòng nhập đầy đủ thông tin bắt buộc!";
    } elseif (strlen($username) < 3) {
        $error = "Tên đăng nhập phải có ít nhất 3 ký tự!";
    } elseif (strlen($password) < 6) {
        $error = "Mật khẩu phải có ít nhất 6 ký tự!";
    } elseif ($password !== $confirm_password) {
        $error = "Mật khẩu xác nhận không khớp!";
    } elseif (!preg_match('/^[0-9]{10,11}$/', $phone)) {
        $error = "Số điện thoại không hợp lệ!";
    } elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Email không hợp lệ!";
    } else {
        // Kiểm tra username đã tồn tại chưa
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $check_stmt->bind_param("s", $username);
        $check_stmt->execute();
        
        if ($check_stmt->get_result()->num_rows > 0) {
            $error = "Tên đăng nhập đã tồn tại!";
        } else {
            // Kiểm tra email đã tồn tại chưa (nếu có)
            if (!empty($email)) {
                $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
                $check_stmt->bind_param("s", $email);
                $check_stmt->execute();
                
                if ($check_stmt->get_result()->num_rows > 0) {
                    $error = "Email đã được sử dụng!";
                }
            }
            
            if (empty($error)) {
                // Tạo user mới
                if (createUser($username, $password, $full_name, $phone, $email, $address)) {
                    $success = "Đăng ký thành công! Bạn có thể đăng nhập ngay bây giờ.";
                    
                    // Reset form
                    $form_data = [
                        'username' => '',
                        'full_name' => '',
                        'phone' => '',
                        'email' => '',
                        'address' => ''
                    ];
                } else {
                    $error = "Có lỗi xảy ra khi tạo tài khoản!";
                }
            }
        }
    }
    
    // Lưu form data để hiển thị lại
    if ($error) {
        $form_data = [
            'username' => $username,
            'full_name' => $full_name,
            'phone' => $phone,
            'email' => $email,
            'address' => $address
        ];
    }
}

include('../includes/header.php');
?>

<div class="container">
    <div class="auth-page">
        <div class="auth-container">
            <div class="auth-header">
                <h1 class="auth-title">
                    <i class="fas fa-user-plus"></i> Đăng ký tài khoản
                </h1>
                <p class="auth-subtitle">Tạo tài khoản mới để mua sắm và quản lý đơn hàng</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" class="auth-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="username">Tên đăng nhập *</label>
                        <input type="text" id="username" name="username" required 
                               value="<?php echo htmlspecialchars($form_data['username']); ?>"
                               placeholder="Nhập tên đăng nhập"
                               minlength="3">
                        <small>Tên đăng nhập phải có ít nhất 3 ký tự</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="full_name">Họ và tên *</label>
                        <input type="text" id="full_name" name="full_name" required 
                               value="<?php echo htmlspecialchars($form_data['full_name']); ?>"
                               placeholder="Nhập họ và tên đầy đủ">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="phone">Số điện thoại *</label>
                        <input type="tel" id="phone" name="phone" required 
                               value="<?php echo htmlspecialchars($form_data['phone']); ?>"
                               placeholder="Nhập số điện thoại"
                               pattern="[0-9]{10,11}"
                               title="Số điện thoại phải có 10-11 chữ số">
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" 
                               value="<?php echo htmlspecialchars($form_data['email']); ?>"
                               placeholder="Nhập email (không bắt buộc)">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="address">Địa chỉ</label>
                    <textarea id="address" name="address" rows="3" 
                              placeholder="Nhập địa chỉ (không bắt buộc)"><?php echo htmlspecialchars($form_data['address']); ?></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Mật khẩu *</label>
                        <input type="password" id="password" name="password" required 
                               placeholder="Nhập mật khẩu"
                               minlength="6">
                        <small>Mật khẩu phải có ít nhất 6 ký tự</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Xác nhận mật khẩu *</label>
                        <input type="password" id="confirm_password" name="confirm_password" required 
                               placeholder="Nhập lại mật khẩu">
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-user-plus"></i> Đăng ký
                </button>
            </form>
            
            <div class="auth-footer">
                <p>Đã có tài khoản? 
                    <a href="login.php" class="auth-link">Đăng nhập ngay</a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>