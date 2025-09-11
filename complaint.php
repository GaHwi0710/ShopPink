<?php
// complaint.php
// Trang khiếu nại
require_once 'config.php';
// Kiểm tra người dùng đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$message = '';
$message_type = '';
// Xử lý gửi khiếu nại
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_complaint'])) {
    $order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    
    if ($order_id <= 0 || empty($description)) {
        $message = 'Vui lòng điền đầy đủ thông tin';
        $message_type = 'error';
    } else {
        // Kiểm tra đơn hàng có thuộc về người dùng không
        $stmt = $conn->prepare("SELECT id FROM orders WHERE id = ? AND user_id = ?");
        $stmt->execute([$order_id, $_SESSION['user_id']]);
        
        if (!$stmt->fetch()) {
            $message = 'Đơn hàng không hợp lệ';
            $message_type = 'error';
        } else {
            try {
                $stmt = $conn->prepare("INSERT INTO complaints (order_id, user_id, description) VALUES (?, ?, ?)");
                $stmt->execute([$order_id, $_SESSION['user_id'], $description]);
                
                $message = 'Gửi khiếu nại thành công';
                $message_type = 'success';
            } catch (PDOException $e) {
                $message = 'Lỗi: ' . $e->getMessage();
                $message_type = 'error';
            }
        }
    }
}
// Xử lý cập nhật trạng thái khiếu nại (cho người bán)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_complaint_status'])) {
    // Kiểm tra vai trò người dùng
    if ($_SESSION['user_role'] !== 'seller') {
        $message = 'Bạn không có quyền thực hiện hành động này';
        $message_type = 'error';
    } else {
        $complaint_id = isset($_POST['complaint_id']) ? (int)$_POST['complaint_id'] : 0;
        $status = isset($_POST['status']) ? $_POST['status'] : '';
        
        if ($complaint_id <= 0 || !in_array($status, ['pending', 'resolved'])) {
            $message = 'Dữ liệu không hợp lệ';
            $message_type = 'error';
        } else {
            try {
                $stmt = $conn->prepare("UPDATE complaints SET status = ? WHERE id = ?");
                $stmt->execute([$status, $complaint_id]);
                
                $message = 'Cập nhật trạng thái khiếu nại thành công';
                $message_type = 'success';
            } catch (PDOException $e) {
                $message = 'Lỗi: ' . $e->getMessage();
                $message_type = 'error';
            }
        }
    }
}
// Lấy ID đơn hàng từ URL (nếu có)
$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
// Lấy danh sách đơn hàng của người dùng
$stmt = $conn->prepare("SELECT id, total, status, created_at FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$user_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Lấy danh sách khiếu nại của người dùng
$stmt = $conn->prepare("
    SELECT c.*, o.total as order_total, o.created_at as order_date 
    FROM complaints c 
    JOIN orders o ON c.order_id = o.id 
    WHERE c.user_id = ? 
    ORDER BY c.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$user_complaints = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Nếu là người bán, lấy tất cả khiếu nại
$seller_complaints = [];
if ($_SESSION['user_role'] === 'seller') {
    $stmt = $conn->query("
        SELECT c.*, o.id as order_id, o.total as order_total, u.name as user_name 
        FROM complaints c 
        JOIN orders o ON c.order_id = o.id 
        JOIN users u ON o.user_id = u.id 
        ORDER BY c.created_at DESC
    ");
    $seller_complaints = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
// Kiểm tra người dùng đã đăng nhập chưa
$is_logged_in = isset($_SESSION['user_id']);
$user_role = $is_logged_in ? $_SESSION['user_role'] : '';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Khiếu nại - ShopPink</title>
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
                    <?php if ($is_logged_in): ?>
                        <?php if ($user_role === 'seller'): ?>
                            <li><a href="seller.php">Quản lý</a></li>
                            <li><a href="report.php">Báo cáo</a></li>
                        <?php endif; ?>
                        <li><a href="cart.php"><i class="fas fa-shopping-cart"></i> Giỏ hàng</a></li>
                        <li><a href="logout.php">Đăng xuất</a></li>
                    <?php else: ?>
                        <li><a href="login.php">Đăng nhập</a></li>
                        <li><a href="register.php">Đăng ký</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
    <main>
        <section class="complaint-page">
            <div class="container">
                <h1>Khiếu nại</h1>
                
                <?php if (!empty($message)): ?>
                    <div class="alert alert-<?php echo $message_type; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($_SESSION['user_role'] === 'seller'): ?>
                    <!-- Giao diện người bán -->
                    <div class="complaint-tabs">
                        <ul class="tabs">
                            <li class="tab active" data-tab="seller-complaints">Danh sách khiếu nại</li>
                        </ul>
                        
                        <div class="tab-content active" id="seller-complaints-tab">
                            <h2>Khiếu nại từ khách hàng</h2>
                            
                            <?php if (empty($seller_complaints)): ?>
                                <p>Chưa có khiếu nại nào</p>
                            <?php else: ?>
                                <div class="complaints-list">
                                    <?php foreach ($seller_complaints as $complaint): ?>
                                        <div class="complaint-item">
                                            <div class="complaint-header">
                                                <h3>Khiếu nại #<?php echo $complaint['id']; ?></h3>
                                                <span class="complaint-date"><?php echo date('d/m/Y H:i', strtotime($complaint['created_at'])); ?></span>
                                                <span class="status <?php echo $complaint['status']; ?>">
                                                    <?php 
                                                    switch ($complaint['status']) {
                                                        case 'pending':
                                                            echo 'Chờ xử lý';
                                                            break;
                                                        case 'resolved':
                                                            echo 'Đã giải quyết';
                                                            break;
                                                        default:
                                                            echo $complaint['status'];
                                                    }
                                                    ?>
                                                </span>
                                            </div>
                                            <div class="complaint-info">
                                                <div class="info-row">
                                                    <span>Khách hàng:</span>
                                                    <span><?php echo htmlspecialchars($complaint['user_name']); ?></span>
                                                </div>
                                                <div class="info-row">
                                                    <span>Đơn hàng:</span>
                                                    <span>#<?php echo $complaint['order_id']; ?> - <?php echo number_format($complaint['order_total'], 0, ',', '.'); ?>₫</span>
                                                </div>
                                            </div>
                                            <div class="complaint-content">
                                                <h4>Nội dung khiếu nại:</h4>
                                                <p><?php echo nl2br(htmlspecialchars($complaint['description'])); ?></p>
                                            </div>
                                            
                                            <?php if ($complaint['status'] === 'pending'): ?>
                                                <div class="complaint-actions">
                                                    <form action="complaint.php" method="post">
                                                        <input type="hidden" name="complaint_id" value="<?php echo $complaint['id']; ?>">
                                                        <input type="hidden" name="status" value="resolved">
                                                        <button type="submit" name="update_complaint_status" class="btn btn-primary">Đánh dấu đã giải quyết</button>
                                                    </form>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Giao diện khách hàng -->
                    <div class="complaint-tabs">
                        <ul class="tabs">
                            <li class="tab active" data-tab="new-complaint">Gửi khiếu nại</li>
                            <li class="tab" data-tab="my-complaints">Khiếu nại của tôi</li>
                        </ul>
                        
                        <div class="tab-content active" id="new-complaint-tab">
                            <h2>Gửi khiếu nại mới</h2>
                            <form action="complaint.php" method="post">
                                <div class="form-group">
                                    <label for="order_id">Đơn hàng</label>
                                    <select id="order_id" name="order_id" required>
                                        <option value="">-- Chọn đơn hàng --</option>
                                        <?php foreach ($user_orders as $order): ?>
                                            <option value="<?php echo $order['id']; ?>" <?php echo $order_id == $order['id'] ? 'selected' : ''; ?>>
                                                #<?php echo $order['id']; ?> - 
                                                <?php echo number_format($order['total'], 0, ',', '.'); ?>₫ - 
                                                <?php echo date('d/m/Y', strtotime($order['created_at'])); ?> - 
                                                <?php 
                                                switch ($order['status']) {
                                                    case 'pending':
                                                        echo 'Chờ xử lý';
                                                        break;
                                                    case 'completed':
                                                        echo 'Hoàn thành';
                                                        break;
                                                    case 'cancelled':
                                                        echo 'Đã hủy';
                                                        break;
                                                    default:
                                                        echo $order['status'];
                                                }
                                                ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="description">Nội dung khiếu nại</label>
                                    <textarea id="description" name="description" rows="5" required></textarea>
                                </div>
                                <div class="form-group">
                                    <button type="submit" name="submit_complaint" class="btn btn-primary">Gửi khiếu nại</button>
                                </div>
                            </form>
                        </div>
                        
                        <div class="tab-content" id="my-complaints-tab">
                            <h2>Khiếu nại của tôi</h2>
                            
                            <?php if (empty($user_complaints)): ?>
                                <p>Bạn chưa có khiếu nại nào</p>
                            <?php else: ?>
                                <div class="complaints-list">
                                    <?php foreach ($user_complaints as $complaint): ?>
                                        <div class="complaint-item">
                                            <div class="complaint-header">
                                                <h3>Khiếu nại #<?php echo $complaint['id']; ?></h3>
                                                <span class="complaint-date"><?php echo date('d/m/Y H:i', strtotime($complaint['created_at'])); ?></span>
                                                <span class="status <?php echo $complaint['status']; ?>">
                                                    <?php 
                                                    switch ($complaint['status']) {
                                                        case 'pending':
                                                            echo 'Chờ xử lý';
                                                            break;
                                                        case 'resolved':
                                                            echo 'Đã giải quyết';
                                                            break;
                                                        default:
                                                            echo $complaint['status'];
                                                    }
                                                    ?>
                                                </span>
                                            </div>
                                            <div class="complaint-info">
                                                <div class="info-row">
                                                    <span>Đơn hàng:</span>
                                                    <span>#<?php echo $complaint['order_id']; ?> - <?php echo number_format($complaint['order_total'], 0, ',', '.'); ?>₫</span>
                                                </div>
                                                <div class="info-row">
                                                    <span>Ngày đặt hàng:</span>
                                                    <span><?php echo date('d/m/Y', strtotime($complaint['order_date'])); ?></span>
                                                </div>
                                            </div>
                                            <div class="complaint-content">
                                                <h4>Nội dung khiếu nại:</h4>
                                                <p><?php echo nl2br(htmlspecialchars($complaint['description'])); ?></p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Xử lý tabs
            const tabs = document.querySelectorAll('.tab');
            const tabContents = document.querySelectorAll('.tab-content');
            
            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    const tabId = this.getAttribute('data-tab');
                    
                    // Remove active class from all tabs and contents
                    tabs.forEach(t => t.classList.remove('active'));
                    tabContents.forEach(tc => tc.classList.remove('active'));
                    
                    // Add active class to clicked tab and corresponding content
                    this.classList.add('active');
                    document.getElementById(tabId + '-tab').classList.add('active');
                });
            });
        });
    </script>
</body>
</html>