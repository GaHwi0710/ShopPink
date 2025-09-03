<div class="container">
    <h1 class="page-title">Thông báo của tôi</h1>
    
    <?php if (empty($notifications)): ?>
        <div class="empty-state">
            <i class="fas fa-bell"></i>
            <h4>Bạn không có thông báo nào</h4>
            <p>Chúng tôi sẽ thông báo cho bạn khi có cập nhật mới về đơn hàng hoặc khuyến mãi.</p>
        </div>
    <?php else: ?>
        <div class="notifications-list">
            <?php foreach ($notifications as $notification): ?>
                <div class="notification-item <?php echo !$notification['is_read'] ? 'unread' : ''; ?>">
                    <div class="notification-icon">
                        <?php if ($notification['type'] == 'order'): ?>
                            <i class="fas fa-receipt"></i>
                        <?php elseif ($notification['type'] == 'promotion'): ?>
                            <i class="fas fa-tag"></i>
                        <?php else: ?>
                            <i class="fas fa-info-circle"></i>
                        <?php endif; ?>
                    </div>
                    <div class="notification-content">
                        <h3><?php echo htmlspecialchars($notification['title']); ?></h3>
                        <p><?php echo htmlspecialchars($notification['message']); ?></p>
                        <div class="notification-time">
                            <?php echo date('d/m/Y H:i', strtotime($notification['created_at'])); ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>