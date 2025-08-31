<?php
require_once '../includes/autoload.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Lấy thông tin user hiện tại
$user_stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();

// Xử lý cập nhật thông tin
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    
    if (empty($full_name) || empty($phone)) {
        $error = "Vui lòng nhập đầy đủ thông tin bắt buộc.";
    } elseif (!preg_match('/^[0-9]{10,11}$/', $phone)) {
        $error = "Số điện thoại không hợp lệ!";
    } elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Email không hợp lệ!";
    } else {
        $update_stmt = $conn->prepare("
            UPDATE users 
            SET full_name = ?, phone = ?, email = ?, address = ?, updated_at = NOW() 
            WHERE id = ?
        ");
        $update_stmt->bind_param("ssssi", $full_name, $phone, $email, $address, $user_id);
        
        if ($update_stmt->execute()) {
            $success = "Cập nhật thông tin thành công!";
            // Cập nhật session
            $_SESSION['user_name'] = $full_name;
            // Refresh user data
            $user_stmt->execute();
            $user = $user_stmt->get_result()->fetch_assoc();
        } else {
            $error = "Có lỗi xảy ra khi cập nhật thông tin!";
        }
    }
}

// Xử lý đổi mật khẩu
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = "Vui lòng nhập đầy đủ thông tin!";
    } elseif ($new_password !== $confirm_password) {
        $error = "Mật khẩu xác nhận không khớp!";
    } elseif (strlen($new_password) < 6) {
        $error = "Mật khẩu mới phải có ít nhất 6 ký tự!";
    } else {
        // Kiểm tra mật khẩu hiện tại
        if (password_verify($current_password, $user['password'])) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $password_stmt = $conn->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
            $password_stmt->bind_param("si", $hashed_password, $user_id);
            
            if ($password_stmt->execute()) {
                $success = "Đổi mật khẩu thành công!";
            } else {
                $error = "Có lỗi xảy ra khi đổi mật khẩu!";
            }
        } else {
            $error = "Mật khẩu hiện tại không đúng!";
        }
    }
}

// Lấy thống kê đơn hàng
$stats_stmt = $conn->prepare("
    SELECT 
        COUNT(*) as total_orders,
        SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as completed_orders,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
        SUM(total_amount) as total_spent
    FROM orders 
    WHERE user_id = ?
");
$stats_stmt->bind_param("i", $user_id);
$stats_stmt->execute();
$stats = $stats_stmt->get_result()->fetch_assoc();

// Lấy đơn hàng gần đây
$recent_orders_stmt = $conn->prepare("
    SELECT * FROM orders 
    WHERE user_id = ? 
    ORDER BY created_at DESC 
    LIMIT 5
");
$recent_orders_stmt->bind_param("i", $user_id);
$recent_orders_stmt->execute();
$recent_orders = $recent_orders_stmt->get_result();

include('../includes/header.php');
?>

<div class="container">
    <div class="profile-page">
        <h1 class="page-title">
            <i class="fas fa-user"></i> Tài khoản của tôi
        </h1>
        
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
        
        <div class="profile-container">
            <!-- Thống kê tổng quan -->
            <div class="profile-stats">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $stats['total_orders']; ?></h3>
                        <p>Tổng đơn hàng</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $stats['completed_orders']; ?></h3>
                        <p>Đơn hàng hoàn thành</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $stats['pending_orders']; ?></h3>
                        <p>Đơn hàng đang xử lý</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo format_price($stats['total_spent'] ?? 0); ?></h3>
                        <p>Tổng chi tiêu</p>
                    </div>
                </div>
            </div>
            
            <div class="profile-content">
                <!-- Thông tin cá nhân -->
                <div class="profile-section">
                    <h3>Thông tin cá nhân</h3>
                    <form method="POST" action="" class="profile-form">
                        <input type="hidden" name="update_profile" value="1">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="full_name">Họ và tên *</label>
                                <input type="text" id="full_name" name="full_name" required 
                                       value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="phone">Số điện thoại *</label>
                                <input type="tel" id="phone" name="phone" required 
                                       value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                                       pattern="[0-9]{10,11}" 
                                       title="Số điện thoại phải có 10-11 chữ số">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="username">Tên đăng nhập</label>
                                <input type="text" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                                <small>Tên đăng nhập không thể thay đổi</small>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="address">Địa chỉ</label>
                            <textarea id="address" name="address" rows="3"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Cập nhật thông tin
                        </button>
                    </form>
                </div>
                
                <!-- Đổi mật khẩu -->
                <div class="profile-section">
                    <h3>Đổi mật khẩu</h3>
                    <form method="POST" action="" class="password-form">
                        <input type="hidden" name="change_password" value="1">
                        
                        <div class="form-group">
                            <label for="current_password">Mật khẩu hiện tại *</label>
                            <input type="password" id="current_password" name="current_password" required>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="new_password">Mật khẩu mới *</label>
                                <input type="password" id="new_password" name="new_password" required 
                                       minlength="6" 
                                       title="Mật khẩu phải có ít nhất 6 ký tự">
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password">Xác nhận mật khẩu *</label>
                                <input type="password" id="confirm_password" name="confirm_password" required>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-key"></i> Đổi mật khẩu
                        </button>
                    </form>
                </div>
                
                <!-- Đơn hàng gần đây -->
                <div class="profile-section">
                    <h3>Đơn hàng gần đây</h3>
                    <?php if ($recent_orders->num_rows > 0): ?>
                        <div class="recent-orders">
                            <?php while ($order = $recent_orders->fetch_assoc()): ?>
                                <div class="recent-order">
                                    <div class="order-info">
                                        <h4>Đơn hàng #<?php echo $order['id']; ?></h4>
                                        <p class="order-date"><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></p>
                                        <p class="order-status status-<?php echo $order['status']; ?>">
                                            <?php
                                            $status_labels = [
                                                'pending' => 'Chờ xử lý',
                                                'processing' => 'Đang xử lý',
                                                'shipped' => 'Đang giao hàng',
                                                'delivered' => 'Đã giao hàng',
                                                'cancelled' => 'Đã hủy'
                                            ];
                                            echo $status_labels[$order['status']] ?? $order['status'];
                                            ?>
                                        </p>
                                    </div>
                                    
                                    <div class="order-amount">
                                        <?php echo format_price($order['total_amount']); ?>
                                    </div>
                                    
                                    <div class="order-action">
                                        <a href="order_detail.php?id=<?php echo $order['id']; ?>" 
                                           class="btn btn-primary btn-small">
                                            Xem chi tiết
                                        </a>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                            
                            <div class="view-all-orders">
                                <a href="orders.php" class="btn btn-outline-primary">
                                    <i class="fas fa-list"></i> Xem tất cả đơn hàng
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="no-orders">
                            <p>Bạn chưa có đơn hàng nào.</p>
                            <a href="../products.php" class="btn btn-primary">
                                <i class="fas fa-shopping-bag"></i> Mua sắm ngay
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>