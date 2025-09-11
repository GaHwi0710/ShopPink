<?php
// report.php
// Trang báo cáo thống kê
require_once 'config.php';
// Kiểm tra người dùng đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
// Kiểm tra vai trò người dùng
if ($_SESSION['user_role'] !== 'seller') {
    header('Location: index.php');
    exit;
}
// Lấy thống kê đơn hàng
$stmt = $conn->query("
    SELECT 
        COUNT(*) as total_orders,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_orders,
        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_orders,
        SUM(CASE WHEN status = 'completed' THEN total ELSE 0 END) as total_revenue
    FROM orders
");
$order_stats = $stmt->fetch(PDO::FETCH_ASSOC);
// Lấy thống kê sản phẩm
$stmt = $conn->query("
    SELECT 
        COUNT(*) as total_products,
        SUM(stock) as total_stock
    FROM products
");
$product_stats = $stmt->fetch(PDO::FETCH_ASSOC);
// Lấy thống kê người dùng
$stmt = $conn->query("
    SELECT 
        COUNT(*) as total_users,
        SUM(CASE WHEN role = 'customer' THEN 1 ELSE 0 END) as total_customers,
        SUM(CASE WHEN role = 'seller' THEN 1 ELSE 0 END) as total_sellers
    FROM users
");
$user_stats = $stmt->fetch(PDO::FETCH_ASSOC);
// Lấy danh sách sản phẩm bán chạy
$stmt = $conn->query("
    SELECT 
        p.id,
        p.name,
        p.image,
        SUM(od.quantity) as total_sold
    FROM products p
    JOIN order_details od ON p.id = od.product_id
    JOIN orders o ON od.order_id = o.id
    WHERE o.status = 'completed'
    GROUP BY p.id, p.name, p.image
    ORDER BY total_sold DESC
    LIMIT 5
");
$top_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Lấy doanh thu theo tháng
$stmt = $conn->query("
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        SUM(total) as revenue
    FROM orders
    WHERE status = 'completed'
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month DESC
    LIMIT 12
");
$monthly_revenue = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Kiểm tra người dùng đã đăng nhập chưa
$is_logged_in = isset($_SESSION['user_id']);
$user_role = $is_logged_in ? $_SESSION['user_role'] : '';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Báo cáo - ShopPink</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                            <li><a href="report.php" class="active">Báo cáo</a></li>
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
        <section class="report-page">
            <div class="container">
                <h1>Báo cáo thống kê</h1>
                
                <div class="stats-cards">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $order_stats['total_orders']; ?></h3>
                            <p>Tổng đơn hàng</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo number_format($order_stats['total_revenue'], 0, ',', '.'); ?>₫</h3>
                            <p>Doanh thu</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-box"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $product_stats['total_products']; ?></h3>
                            <p>Tổng sản phẩm</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $user_stats['total_customers']; ?></h3>
                            <p>Khách hàng</p>
                        </div>
                    </div>
                </div>
                
                <div class="report-charts">
                    <div class="chart-container">
                        <h2>Trạng thái đơn hàng</h2>
                        <div class="chart-wrapper">
                            <canvas id="orderStatusChart"></canvas>
                        </div>
                    </div>
                    
                    <div class="chart-container">
                        <h2>Doanh thu theo tháng</h2>
                        <div class="chart-wrapper">
                            <canvas id="monthlyRevenueChart"></canvas>
                        </div>
                    </div>
                </div>
                
                <div class="top-products">
                    <h2>Sản phẩm bán chạy</h2>
                    <div class="top-products-list">
                        <?php if (empty($top_products)): ?>
                            <p>Chưa có sản phẩm nào được bán</p>
                        <?php else: ?>
                            <?php foreach ($top_products as $product): ?>
                                <div class="top-product-item">
                                    <div class="report-product-image">
                                        <img src="assets/images/products/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>"> 
                                    </div>
                                    <div class="product-info">
                                        <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                                        <p>Đã bán: <?php echo $product['total_sold']; ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Biểu đồ trạng thái đơn hàng
            const orderStatusCtx = document.getElementById('orderStatusChart').getContext('2d');
            const orderStatusChart = new Chart(orderStatusCtx, {
                type: 'pie',
                data: {
                    labels: ['Chờ xử lý', 'Hoàn thành', 'Đã hủy'],
                    datasets: [{
                        data: [
                            <?php echo $order_stats['pending_orders']; ?>,
                            <?php echo $order_stats['completed_orders']; ?>,
                            <?php echo $order_stats['cancelled_orders']; ?>
                        ],
                        backgroundColor: [
                            '#FFC107',
                            '#28A745',
                            '#DC3545'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    label += context.raw + ' đơn';
                                    return label;
                                }
                            }
                        }
                    }
                }
            });
            
            // Biểu đồ doanh thu theo tháng
            const monthlyRevenueCtx = document.getElementById('monthlyRevenueChart').getContext('2d');
            const monthlyRevenueChart = new Chart(monthlyRevenueCtx, {
                type: 'bar',
                data: {
                    labels: [
                        <?php 
                        $labels = [];
                        foreach ($monthly_revenue as $item) {
                            $labels[] = "'" . date('m/Y', strtotime($item['month'] . '-01')) . "'";
                        }
                        echo implode(',', array_reverse($labels));
                        ?>
                    ],
                    datasets: [{
                        label: 'Doanh thu (VNĐ)',
                        data: [
                            <?php 
                            $data = [];
                            foreach ($monthly_revenue as $item) {
                                $data[] = $item['revenue'];
                            }
                            echo implode(',', array_reverse($data));
                            ?>
                        ],
                        backgroundColor: '#007BFF',
                        borderColor: '#007BFF',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return value.toLocaleString('vi-VN') + '₫';
                                }
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    label += context.raw.toLocaleString('vi-VN') + '₫';
                                    return label;
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>