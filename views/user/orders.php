<div class="container">
    <h1 class="page-title">Đơn hàng của tôi</h1>
    
    <?php if (empty($orders)): ?>
        <div class="empty-state">
            <i class="fas fa-receipt"></i>
            <h4>Bạn chưa có đơn hàng nào</h4>
            <p>Hãy mua sắm để tạo đơn hàng đầu tiên của bạn.</p>
            <a href="/" class="btn">Tiếp tục mua sắm</a>
        </div>
    <?php else: ?>
        <div class="orders-table">
            <table>
                <thead>
                    <tr>
                        <th>Mã đơn hàng</th>
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
                                <a href="/orders/<?php echo $order['id']; ?>" class="btn-view">
                                    <i class="fas fa-eye"></i>
                                </a>
                                
                                <?php if ($order['status'] === 'delivered'): ?>
                                    <button class="btn-review" data-id="<?php echo $order['id']; ?>" title="Đánh giá">
                                        <i class="fas fa-star"></i>
                                    </button>
                                <?php endif; ?>
                                
                                <?php if ($order['status'] === 'pending'): ?>
                                    <form action="/cancel-order" method="POST" style="display: inline;">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <button type="submit" class="btn-delete" title="Hủy đơn">
                                            <i class="fas fa-times"></i>
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

<!-- Review Modal -->
<div class="modal" id="review-modal">
    <div class="modal-content">
        <span class="modal-close">&times;</span>
        <h2>Đánh giá sản phẩm</h2>
        
        <div class="review-form">
            <form id="review-form">
                <input type="hidden" name="order_id" id="review-order-id">
                
                <div class="form-group">
                    <label>Chọn sản phẩm:</label>
                    <select name="product_id" id="review-product-id" required>
                        <option value="">-- Chọn sản phẩm --</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Đánh giá:</label>
                    <div class="rating-input">
                        <i class="far fa-star" data-rating="1"></i>
                        <i class="far fa-star" data-rating="2"></i>
                        <i class="far fa-star" data-rating="3"></i>
                        <i class="far fa-star" data-rating="4"></i>
                        <i class="far fa-star" data-rating="5"></i>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="review-comment">Bình luận:</label>
                    <textarea id="review-comment" name="comment" rows="4" placeholder="Nhập bình luận của bạn..."></textarea>
                </div>
                
                <button type="submit" class="btn-primary">Gửi đánh giá</button>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const reviewBtns = document.querySelectorAll('.btn-review');
    const reviewModal = document.getElementById('review-modal');
    const reviewClose = reviewModal.querySelector('.modal-close');
    const reviewForm = document.getElementById('review-form');
    const reviewOrderId = document.getElementById('review-order-id');
    const reviewProductId = document.getElementById('review-product-id');
    
    reviewBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const orderId = this.getAttribute('data-id');
            reviewOrderId.value = orderId;
            
            // Load products for this order
            fetch(`/api/order-products/${orderId}`)
                .then(response => response.json())
                .then(data => {
                    reviewProductId.innerHTML = '<option value="">-- Chọn sản phẩm --</option>';
                    data.forEach(product => {
                        const option = document.createElement('option');
                        option.value = product.id;
                        option.textContent = product.name;
                        reviewProductId.appendChild(option);
                    });
                    
                    reviewModal.classList.add('active');
                })
                .catch(error => console.error('Error:', error));
        });
    });
    
    reviewClose.addEventListener('click', () => {
        reviewModal.classList.remove('active');
    });
    
    // Rating input
    const ratingStars = document.querySelectorAll('.rating-input i');
    let selectedRating = 0;
    
    ratingStars.forEach(star => {
        star.addEventListener('click', () => {
            selectedRating = parseInt(star.dataset.rating);
            
            ratingStars.forEach((s, index) => {
                if (index < selectedRating) {
                    s.classList.remove('far');
                    s.classList.add('fas');
                } else {
                    s.classList.remove('fas');
                    s.classList.add('far');
                }
            });
        });
        
        star.addEventListener('mouseover', () => {
            const rating = parseInt(star.dataset.rating);
            
            ratingStars.forEach((s, index) => {
                if (index < rating) {
                    s.classList.remove('far');
                    s.classList.add('fas');
                } else {
                    s.classList.remove('fas');
                    s.classList.add('far');
                }
            });
        });
    });
    
    reviewForm.addEventListener('submit', (e) => {
        e.preventDefault();
        
        const formData = new FormData(reviewForm);
        formData.append('rating', selectedRating);
        
        fetch('/review', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('success', 'Đánh giá thành công!');
                reviewModal.classList.remove('active');
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast('error', data.message || 'Đánh giá thất bại!');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('error', 'Đã xảy ra lỗi, vui lòng thử lại!');
        });
    });
});
</script>