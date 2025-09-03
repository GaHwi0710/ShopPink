<div class="container">
    <h1 class="page-title">Quản lý đơn hàng</h1>
    
    <?php if (empty($orders)): ?>
        <div class="empty-state">
            <i class="fas fa-receipt"></i>
            <h4>Không có đơn hàng nào</h4>
            <p>Bạn chưa có đơn hàng nào cần xử lý.</p>
        </div>
    <?php else: ?>
        <div class="orders-table">
            <table>
                <thead>
                    <tr>
                        <th>Mã đơn hàng</th>
                        <th>Khách hàng</th>
                        <th>Ngày đặt</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>#<?php echo $order['id']; ?></td>
                            <td><?php echo $order['customer_name']; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($order['created_at'])); ?></td>
                            <td><?php echo number_format($order['total_amount'], 0, ',', '.'); ?>đ</td>
                            <td>
                                <span class="status-badge <?php echo $order['status']; ?>">
                                    <?php
                                    switch ($order['status']) {
                                        case 'pending': echo 'Chờ xử lý'; break;
                                        case 'processing': echo 'Đang xử lý'; break;
                                        case 'shipped': echo 'Đang giao'; break;
                                        case 'delivered': echo 'Đã giao'; break;
                                        case 'cancelled': echo 'Đã hủy'; break;
                                    }
                                    ?>
                                </span>
                            </td>
                            <td>
                                <a href="/seller/order-detail/<?php echo $order['id']; ?>" class="btn-view">
                                    <i class="fas fa-eye"></i>
                                </a>
                                
                                <?php if ($order['status'] === 'pending'): ?>
                                    <form action="/seller/update-order-status" method="POST" style="display: inline;">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <input type="hidden" name="status" value="processing">
                                        <button type="submit" class="btn-edit" title="Xác nhận">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                                
                                <?php if ($order['status'] === 'processing'): ?>
                                    <form action="/seller/update-order-status" method="POST" style="display: inline;">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <input type="hidden" name="status" value="shipped">
                                        <button type="submit" class="btn-edit" title="Giao hàng">
                                            <i class="fas fa-shipping-fast"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                                
                                <?php if ($order['status'] === 'shipped'): ?>
                                    <form action="/seller/update-order-status" method="POST" style="display: inline;">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <input type="hidden" name="status" value="delivered">
                                        <button type="submit" class="btn-edit" title="Đã giao">
                                            <i class="fas fa-check-double"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>