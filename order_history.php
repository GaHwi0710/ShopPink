<?php
session_start();
include('config.php');

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Lấy danh sách đơn hàng của user
$orders_query = "SELECT * FROM orders WHERE user_id = $user_id ORDER BY created_at DESC";
$orders_result = mysqli_query($conn, $orders_query);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch sử đơn hàng - ShopPink</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/form.css">
</head>
<body>
    <?php include('includes/header.php'); ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Lịch sử đơn hàng</h1>
        </div>
        
        <?php if (mysqli_num_rows($orders_result) > 0) { ?>
            <div class="order-history">
                <table>
                    <thead>
                        <tr>
                            <th>Mã đơn hàng</th>
                            <th>Ngày đặt</th>
                            <th>Tổng tiền</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($order = mysqli_fetch_assoc($orders_result)) { ?>
                            <tr>
                                <td>#<?php echo $order['id']; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($order['created_at'])); ?></td>
                                <td><?php echo number_format($order['total'], 0, ',', '.'); ?> VNĐ</td>
                                <td>
                                    <span class="status <?php echo $order['status']; ?>">
                                        <?php 
                                        switch ($order['status']) {
                                            case 'pending': echo 'Đang xử lý'; break;
                                            case 'confirmed': echo 'Đã xác nhận'; break;
                                            case 'shipping': echo 'Đang giao hàng'; break;
                                            case 'completed': echo 'Hoàn thành'; break;
                                            case 'cancelled': echo 'Đã hủy'; break;
                                        }
                                        ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="order_detail.php?id=<?php echo $order['id']; ?>" class="btn btn-sm">Xem chi tiết</a>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } else { ?>
            <div class="empty-state">
                <img src="assets/images/no-orders.png" alt="Không có đơn hàng">
                <p>Bạn chưa có đơn hàng nào</p>
                <a href="index.php" class="btn">Mua sắm ngay</a>
            </div>
        <?php } ?>
    </div>
    
    <?php include('includes/footer.php'); ?>
</body>
</html>