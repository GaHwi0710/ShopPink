<?php include 'views/layouts/header.php'; ?>

<div class="container">
    <h1 class="page-title">Giỏ hàng của bạn</h1>
    
    <?php if (empty($_SESSION['cart'])): ?>
        <div class="empty-cart">
            <i class="fas fa-shopping-cart"></i>
            <h4>Giỏ hàng của bạn đang trống</h4>
            <p>Hãy thêm sản phẩm vào giỏ hàng để tiếp tục mua sắm.</p>
            <a href="/" class="btn">Tiếp tục mua sắm</a>
        </div>
    <?php else: ?>
        <div class="cart-container">
            <div class="cart-items">
                <div class="cart-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Sản phẩm</th>
                                <th>Giá</th>
                                <th>Số lượng</th>
                                <th>Thành tiền</th>
                                <th>Xóa</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $total = 0;
                            foreach ($_SESSION['cart'] as $productId => $quantity):
                                $productModel = new Product();
                                $product = $productModel->getById($productId);
                                
                                if ($product):
                                    $subtotal = $product['price'] * $quantity;
                                    $total += $subtotal;
                            ?>
                                <tr>
                                    <td class="cart-product">
                                        <div class="cart-product-img">
                                            <img src="<?php echo $product['image'] ?: 'https://via.placeholder.com/80x80/f8bbd0/ffffff?text=Product'; ?>" alt="<?php echo $product['name']; ?>">
                                        </div>
                                        <div class="cart-product-info">
                                            <h4><?php echo $product['name']; ?></h4>
                                            <p>Bán bởi: <?php echo $product['seller_name']; ?></p>
                                        </div>
                                    </td>
                                    <td><?php echo number_format($product['price'], 0, ',', '.'); ?>đ</td>
                                    <td>
                                        <div class="quantity-control">
                                            <button class="decrease-qty" data-id="<?php echo $productId; ?>">-</button>
                                            <input type="text" class="qty-input" data-id="<?php echo $productId; ?>" value="<?php echo $quantity; ?>" readonly>
                                            <button class="increase-qty" data-id="<?php echo $productId; ?>">+</button>
                                        </div>
                                    </td>
                                    <td><?php echo number_format($subtotal, 0, ',', '.'); ?>đ</td>
                                    <td>
                                        <a href="/remove-from-cart?id=<?php echo $productId; ?>" class="remove-item">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php 
                                endif;
                            endforeach; 
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="cart-summary">
                <h3>Tóm tắt đơn hàng</h3>
                <div class="summary-item">
                    <span>Tạm tính:</span>
                    <span><?php echo number_format($total, 0, ',', '.'); ?>đ</span>
                </div>
                <div class="summary-item">
                    <span>Phí vận chuyển:</span>
                    <span><?php echo $total >= 500000 ? 'Miễn phí' : '30.000đ'; ?></span>
                </div>
                
                <!-- Voucher -->
                <div class="voucher-section">
                    <h4>Mã giảm giá</h4>
                    <div class="voucher-form">
                        <input type="text" id="voucher-code" placeholder="Nhập mã giảm giá">
                        <button id="apply-voucher">Áp dụng</button>
                    </div>
                    <div id="voucher-info" class="voucher-info" style="display: none;"></div>
                </div>
                
                <div class="summary-item summary-total">
                    <span>Tổng cộng:</span>
                    <span id="total-amount"><?php echo number_format($total >= 500000 ? $total : $total + 30000, 0, ',', '.'); ?>đ</span>
                </div>
                
                <?php if (Auth::isCustomer()): ?>
                    <a href="/checkout" class="btn-primary checkout-btn">Tiến hành thanh toán</a>
                <?php else: ?>
                    <a href="/login" class="btn-primary checkout-btn">Đăng nhập để thanh toán</a>
                <?php endif; ?>
                
                <a href="/" class="continue-shopping">Tiếp tục mua sắm</a>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Quantity controls
    const decreaseBtns = document.querySelectorAll('.decrease-qty');
    const increaseBtns = document.querySelectorAll('.increase-qty');
    
    decreaseBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.getAttribute('data-id');
            const input = document.querySelector(`.qty-input[data-id="${productId}"]`);
            let qty = parseInt(input.value);
            
            if (qty > 1) {
                qty--;
                input.value = qty;
                updateCart(productId, qty);
            }
        });
    });
    
    increaseBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.getAttribute('data-id');
            const input = document.querySelector(`.qty-input[data-id="${productId}"]`);
            let qty = parseInt(input.value);
            
            qty++;
            input.value = qty;
            updateCart(productId, qty);
        });
    });
    
    // Update cart function
    function updateCart(productId, quantity) {
        const formData = new FormData();
        formData.append('product_id', productId);
        formData.append('quantity', quantity);
        
        fetch('/update-cart', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update cart total
                const cartItems = document.querySelectorAll('.cart-product');
                let newTotal = 0;
                
                cartItems.forEach(item => {
                    const priceText = item.parentElement.querySelector('td:nth-child(2)').textContent;
                    const price = parseInt(priceText.replace(/[^\d]/g, ''));
                    const qtyInput = item.parentElement.querySelector('.qty-input');
                    const qty = parseInt(qtyInput.value);
                    newTotal += price * qty;
                });
                
                const shipping = newTotal >= 500000 ? 0 : 30000;
                const totalWithShipping = newTotal + shipping;
                
                document.getElementById('total-amount').textContent = new Intl.NumberFormat('vi-VN').format(totalWithShipping) + 'đ';
                
                // Update cart count in header
                const cartCount = document.querySelector('.cart-count');
                if (cartCount) {
                    cartCount.textContent = data.cart_count;
                }
            } else {
                showToast('error', data.message || 'Cập nhật giỏ hàng thất bại!');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('error', 'Đã xảy ra lỗi, vui lòng thử lại!');
        });
    }
    
    // Apply voucher
    const applyVoucherBtn = document.getElementById('apply-voucher');
    const voucherCodeInput = document.getElementById('voucher-code');
    const voucherInfo = document.getElementById('voucher-info');
    
    if (applyVoucherBtn) {
        applyVoucherBtn.addEventListener('click', function() {
            const code = voucherCodeInput.value.trim();
            
            if (!code) {
                showToast('error', 'Vui lòng nhập mã voucher!');
                return;
            }
            
            const formData = new FormData();
            formData.append('voucher_code', code);
            
            fetch('/apply-voucher', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    voucherInfo.style.display = 'block';
                    voucherInfo.innerHTML = `
                        <div class="voucher-applied">
                            <i class="fas fa-check-circle"></i>
                            <span>Đã áp dụng voucher: ${data.voucher.code}</span>
                            <button class="remove-voucher" data-voucher-id="${data.voucher.id}">×</button>
                        </div>
                    `;
                    
                    // Update total amount
                    const totalText = document.getElementById('total-amount').textContent;
                    const total = parseInt(totalText.replace(/[^\d]/g, ''));
                    
                    let discount = 0;
                    if (data.voucher.discount_type === 'fixed') {
                        discount = data.voucher.discount_value;
                    } else {
                        discount = total * data.voucher.discount_value / 100;
                        if (data.voucher.max_discount_amount && discount > data.voucher.max_discount_amount) {
                            discount = data.voucher.max_discount_amount;
                        }
                    }
                    
                    const newTotal = Math.max(0, total - discount);
                    document.getElementById('total-amount').textContent = new Intl.NumberFormat('vi-VN').format(newTotal) + 'đ';
                    
                    showToast('success', 'Áp dụng voucher thành công!');
                } else {
                    showToast('error', data.message || 'Mã voucher không hợp lệ!');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('error', 'Đã xảy ra lỗi, vui lòng thử lại!');
            });
        });
    }
    
    // Remove voucher
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-voucher')) {
            voucherInfo.style.display = 'none';
            voucherCodeInput.value = '';
            
            // Recalculate total without voucher
            const cartItems = document.querySelectorAll('.cart-product');
            let total = 0;
            
            cartItems.forEach(item => {
                const priceText = item.parentElement.querySelector('td:nth-child(2)').textContent;
                const price = parseInt(priceText.replace(/[^\d]/g, ''));
                const qtyInput = item.parentElement.querySelector('.qty-input');
                const qty = parseInt(qtyInput.value);
                total += price * qty;
            });
            
            const shipping = total >= 500000 ? 0 : 30000;
            const totalWithShipping = total + shipping;
            
            document.getElementById('total-amount').textContent = new Intl.NumberFormat('vi-VN').format(totalWithShipping) + 'đ';
            
            showToast('info', 'Đã xóa voucher');
        }
    });
});
</script>

<style>
.empty-cart {
    text-align: center;
    padding: 60px 20px;
}

.empty-cart i {
    font-size: 80px;
    color: var(--gray-light);
    margin-bottom: 20px;
}

.empty-cart h4 {
    font-size: 24px;
    margin-bottom: 10px;
    color: var(--dark-color);
}

.empty-cart p {
    color: var(--gray);
    margin-bottom: 30px;
}

.cart-container {
    display: flex;
    gap: 30px;
}

.cart-items {
    flex: 2;
}

.cart-summary {
    flex: 1;
    background: white;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    height: fit-content;
}

.cart-table {
    width: 100%;
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.cart-table th,
.cart-table td {
    padding: 20px;
    text-align: left;
    border-bottom: 1px solid #f0f0f0;
}

.cart-table th {
    background: var(--light-color);
    font-weight: 600;
    color: var(--dark-color);
}

.cart-product {
    display: flex;
    align-items: center;
    gap: 15px;
}

.cart-product-img {
    width: 80px;
    height: 80px;
    border-radius: 10px;
    overflow: hidden;
}

.cart-product-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.cart-product-info h4 {
    font-size: 16px;
    font-weight: 600;
    color: var(--dark-color);
    margin-bottom: 5px;
}

.cart-product-info p {
    font-size: 14px;
    color: var(--gray);
}

.quantity-control {
    display: flex;
    align-items: center;
    gap: 10px;
    justify-content: center;
}

.quantity-control button {
    width: 35px;
    height: 35px;
    border: 1px solid #ddd;
    background: white;
    border-radius: 5px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.quantity-control button:hover {
    background: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
}

.quantity-control input {
    width: 50px;
    text-align: center;
    border: 1px solid #ddd;
    border-radius: 5px;
    padding: 8px;
    font-weight: 600;
}

.remove-item {
    color: var(--error-color);
    background: none;
    border: none;
    cursor: pointer;
    font-size: 18px;
    transition: all 0.3s ease;
}

.remove-item:hover {
    transform: scale(1.2);
}

.cart-summary h3 {
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f0f0f0;
    color: var(--dark-color);
}

.summary-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 15px;
    font-size: 16px;
}

.summary-total {
    font-size: 20px;
    font-weight: bold;
    margin: 25px 0;
    padding-top: 15px;
    border-top: 2px solid #f0f0f0;
    color: var(--dark-color);
}

.checkout-btn {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    padding: 15px 30px;
    border: none;
    border-radius: 25px;
    cursor: pointer;
    font-size: 16px;
    font-weight: 600;
    width: 100%;
    transition: all 0.3s ease;
    margin-top: 10px;
    text-align: center;
    display: block;
    text-decoration: none;
}

.checkout-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(233, 30, 99, 0.4);
}

.continue-shopping {
    display: inline-block;
    margin-top: 20px;
    color: var(--primary-color);
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
}

.continue-shopping:hover {
    color: var(--secondary-color);
}

.voucher-section {
    margin: 20px 0;
    padding: 15px 0;
    border-top: 1px dashed #eee;
    border-bottom: 1px dashed #eee;
}

.voucher-section h4 {
    margin-bottom: 10px;
    font-size: 16px;
    color: var(--dark-color);
}

.voucher-form {
    display: flex;
    gap: 10px;
}

.voucher-form input {
    flex: 1;
    padding: 10px 15px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
}

.voucher-form button {
    padding: 10px 15px;
    background: var(--primary-color);
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.voucher-form button:hover {
    background: var(--secondary-color);
}

.voucher-info {
    margin-top: 10px;
}

.voucher-applied {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #f0f9ff;
    padding: 10px 15px;
    border-radius: 5px;
    font-size: 14px;
    color: var(--primary-color);
}

.remove-voucher {
    background: none;
    border: none;
    color: var(--primary-color);
    cursor: pointer;
    font-size: 16px;
    padding: 0;
    margin-left: 10px;
}

@media (max-width: 992px) {
    .cart-container {
        flex-direction: column;
    }
    
    .cart-summary {
        width: 100%;
    }
}

@media (max-width: 768px) {
    .cart-table,
    .cart-table tbody,
    .cart-table tr,
    .cart-table td {
        display: block;
    }
    
    .cart-table thead {
        display: none;
    }
    
    .cart-table tr {
        border: 1px solid #ddd;
        margin-bottom: 15px;
        border-radius: 10px;
        overflow: hidden;
    }
    
    .cart-table td {
        border: none;
        padding: 15px;
        text-align: right;
        padding-left: 50%;
        position: relative;
    }
    
    .cart-table td::before {
        content: attr(data-label);
        position: absolute;
        left: 15px;
        width: 45%;
        padding-right: 10px;
        white-space: nowrap;
        text-align: left;
        font-weight: bold;
    }
    
    .cart-product {
        justify-content: flex-end;
    }
}
</style>

<?php include 'views/layouts/footer.php'; ?>