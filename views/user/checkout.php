<div class="container">
    <h1 class="page-title">Thanh toán</h1>
    
    <div class="checkout-container">
        <div class="checkout-form">
            <h3>Thông tin giao hàng</h3>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form action="/checkout" method="POST">
                <div class="form-group">
                    <label for="full_name">Họ và tên</label>
                    <input type="text" id="full_name" name="full_name" value="<?php echo Auth::user()['full_name']; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="phone">Số điện thoại</label>
                    <input type="tel" id="phone" name="phone" value="<?php echo Auth::user()['phone']; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="address">Địa chỉ giao hàng</label>
                    <textarea id="address" name="shipping_address" required><?php echo Auth::user()['address']; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>Phương thức thanh toán</label>
                    <div class="payment-methods">
                        <label class="payment-method">
                            <input type="radio" name="payment_method" value="cod" checked>
                            <div class="payment-method-info">
                                <i class="fas fa-money-bill-wave"></i>
                                <span>Thanh toán khi nhận hàng (COD)</span>
                            </div>
                        </label>
                        
                        <label class="payment-method">
                            <input type="radio" name="payment_method" value="bank_transfer">
                            <div class="payment-method-info">
                                <i class="fas fa-university"></i>
                                <span>Chuyển khoản ngân hàng</span>
                            </div>
                        </label>
                    </div>
                </div>
                
                <button type="submit" class="btn-primary">Đặt hàng</button>
            </form>
        </div>
        
        <div class="checkout-summary">
            <h3>Đơn hàng của bạn</h3>
            
            <div class="checkout-items">
                <?php
                $total = 0;
                foreach ($_SESSION['cart'] as $productId => $quantity):
                    $productModel = new Product();
                    $product = $productModel->getById($productId);
                    
                    if ($product):
                        $subtotal = $product['price'] * $quantity;
                        $total += $subtotal;
                ?>
                    <div class="checkout-item">
                        <div class="checkout-item-img">
                            <img src="<?php echo $product['image'] ?: 'https://via.placeholder.com/80x80/f8bbd0/ffffff?text=Product'; ?>" alt="<?php echo $product['name']; ?>">
                        </div>
                        <div class="checkout-item-info">
                            <h4><?php echo $product['name']; ?></h4>
                            <div class="checkout-item-price">
                                <span><?php echo number_format($product['price'], 0, ',', '.'); ?>đ</span>
                                <span>x <?php echo $quantity; ?></span>
                            </div>
                        </div>
                    </div>
                <?php 
                    endif;
                endforeach; 
                ?>
            </div>
            
            <div class="checkout-totals">
                <div class="summary-item">
                    <span>Tạm tính:</span>
                    <span><?php echo number_format($total, 0, ',', '.'); ?>đ</span>
                </div>
                <div class="summary-item">
                    <span>Phí vận chuyển:</span>
                    <span><?php echo $total >= 500000 ? 'Miễn phí' : '30.000đ'; ?></span>
                </div>
                <div class="summary-item summary-total">
                    <span>Tổng cộng:</span>
                    <span><?php echo number_format($total >= 500000 ? $total : $total + 30000, 0, ',', '.'); ?>đ</span>
                </div>
            </div>
        </div>
    </div>
</div>