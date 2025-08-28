<?php
// Include autoload để đảm bảo các hàm cần thiết đã được nạp
require_once 'autoload.php';
?>
    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <h3>Về ShopPink</h3>
                    <p>ShopPink là cửa hàng thời trang uy tín, chuyên cung cấp các sản phẩm chất lượng với giá thành hợp lý.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                <div class="footer-column">
                    <h3>Thông tin</h3>
                    <ul>
                        <li><a href="about.php">Về chúng tôi</a></li>
                        <li><a href="#">Dịch vụ</a></li>
                        <li><a href="privacy.php">Chính sách bảo mật</a></li>
                        <li><a href="terms.php">Điều khoản sử dụng</a></li>
                        <li><a href="#">Hệ thống cửa hàng</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Hỗ trợ khách hàng</h3>
                    <ul>
                        <li><a href="#">Hướng dẫn mua hàng</a></li>
                        <li><a href="#">Chính sách đổi trả</a></li>
                        <li><a href="shipping-policy.php">Phương thức vận chuyển</a></li>
                        <li><a href="#">Phương thức thanh toán</a></li>
                        <li><a href="faq.php">Câu hỏi thường gặp</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Liên hệ</h3>
                    <ul class="contact-info">
                        <li>
                            <i class="fas fa-map-marker-alt"></i>
                            <span>123 Đường Nguyễn Văn Linh, Quận 7, TP. Hồ Chí Minh</span>
                        </li>
                        <li>
                            <i class="fas fa-phone"></i>
                            <span>0123 456 789</span>
                        </li>
                        <li>
                            <i class="fas fa-envelope"></i>
                            <span>info@shoppink.com</span>
                        </li>
                        <li>
                            <i class="fas fa-clock"></i>
                            <span>Thứ 2 - Chủ nhật: 8:00 - 22:00</span>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="copyright">
                <p>&copy; <?php echo date('Y'); ?> ShopPink. Tất cả quyền được bảo lưu.</p>
            </div>
        </div>
    </footer>
    
    <!-- Back to Top -->
    <div class="back-to-top" id="back-to-top">
        <i class="fas fa-arrow-up"></i>
    </div>
    
    <!-- Dark Mode Toggle -->
    <div class="dark-mode-toggle" id="dark-mode-toggle">
        <i class="fas fa-moon"></i>
    </div>
    
    <!-- Quick View Modal -->
    <div class="quick-view-modal" id="quick-view-modal">
        <div class="quick-view-content">
            <span class="quick-view-close" id="quick-view-close">&times;</span>
            <div class="quick-view-body">
                <div class="quick-view-image">
                    <img src="https://via.placeholder.com/400x400/f8bbd0/ffffff?text=Product+Image" alt="Product Image">
                </div>
                <div class="quick-view-details">
                    <h3 class="quick-view-title">Tên sản phẩm</h3>
                    <div class="quick-view-price">299.000đ</div>
                    <div class="quick-view-description">
                        Mô tả chi tiết về sản phẩm. Chất liệu cao cấp, thiết kế hiện đại, phù hợp với nhiều phong cách khác nhau.
                    </div>
                    
                    <!-- Product Variants -->
                    <div class="product-variants">
                        <div class="variant-title">Màu sắc:</div>
                        <div class="variant-options">
                            <div class="variant-option active">Đen</div>
                            <div class="variant-option">Trắng</div>
                            <div class="variant-option">Xanh</div>
                        </div>
                    </div>
                    
                    <div class="product-variants">
                        <div class="variant-title">Kích thước:</div>
                        <div class="variant-options">
                            <div class="variant-option">S</div>
                            <div class="variant-option active">M</div>
                            <div class="variant-option">L</div>
                        </div>
                    </div>
                    
                    <!-- Stock Status -->
                    <div class="stock-status">
                        <div class="stock-indicator in-stock"></div>
                        <div class="stock-text">Còn hàng</div>
                    </div>
                    
                    <!-- Countdown Timer -->
                    <div class="countdown-timer">
                        <div class="countdown-item">
                            <div class="countdown-value">12</div>
                            <div class="countdown-label">Giờ</div>
                        </div>
                        <div class="countdown-item">
                            <div class="countdown-value">34</div>
                            <div class="countdown-label">Phút</div>
                        </div>
                        <div class="countdown-item">
                            <div class="countdown-value">56</div>
                            <div class="countdown-label">Giây</div>
                        </div>
                    </div>
                    
                    <div class="quick-view-actions">
                        <div class="quick-view-quantity">
                            <button id="decrease-quantity">-</button>
                            <input type="number" id="quantity" value="1" min="1" max="10">
                            <button id="increase-quantity">+</button>
                        </div>
                        <button class="btn-primary" id="add-to-cart-quick-view">Thêm vào giỏ hàng</button>
                    </div>
                </div>
            </div>
            
            <!-- Product Tabs -->
            <div class="product-tabs">
                <div class="tab-nav">
                    <button class="tab-btn active" data-tab="description">Mô tả</button>
                    <button class="tab-btn" data-tab="specifications">Thông số</button>
                    <button class="tab-btn" data-tab="reviews">Đánh giá</button>
                    <button class="tab-btn" data-tab="shipping">Vận chuyển</button>
                </div>
                
                <div class="tab-content active" id="description">
                    <p>Chi tiết mô tả sản phẩm. Chất liệu cao cấp, thiết kế hiện đại, phù hợp với nhiều phong cách khác nhau.</p>
                </div>
                
                <div class="tab-content" id="specifications">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: 600;">Chất liệu</td>
                            <td style="padding: 10px; border-bottom: 1px solid #eee;">Cotton 100%</td>
                        </tr>
                        <tr>
                            <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: 600;">Xuất xứ</td>
                            <td style="padding: 10px; border-bottom: 1px solid #eee;">Việt Nam</td>
                        </tr>
                        <tr>
                            <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: 600;">Bảo hành</td>
                            <td style="padding: 10px; border-bottom: 1px solid #eee;">12 tháng</td>
                        </tr>
                    </table>
                </div>
                
                <div class="tab-content" id="reviews">
                    <!-- Product Reviews -->
                    <div class="product-reviews">
                        <div class="review-header">
                            <div class="review-summary">
                                <div class="review-average">4.5</div>
                                <div>
                                    <div class="review-stars">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star-half-alt"></i>
                                    </div>
                                    <div class="review-count">(15 đánh giá)</div>
                                </div>
                            </div>
                            <div class="review-filters">
                                <button class="review-filter active">Tất cả</button>
                                <button class="review-filter">5 sao</button>
                                <button class="review-filter">4 sao</button>
                                <button class="review-filter">3 sao</button>
                            </div>
                        </div>
                        
                        <div class="review-list">
                            <div class="review-item">
                                <div class="review-meta">
                                    <div class="review-author">Nguyễn Văn A</div>
                                    <div class="review-date">15/05/2023</div>
                                </div>
                                <div class="review-rating">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                </div>
                                <div class="review-content">
                                    Sản phẩm chất lượng tốt, thiết kế đẹp, màu sắc như hình. Rất hài lòng với mua sắm này.
                                </div>
                                <div class="review-images">
                                    <img src="https://via.placeholder.com/80x80/f8bbd0/ffffff?text=Image+1" class="review-image">
                                    <img src="https://via.placeholder.com/80x80/e91e63/ffffff?text=Image+2" class="review-image">
                                </div>
                                <div class="review-actions">
                                    <div class="review-action">
                                        <i class="fas fa-thumbs-up"></i>
                                        <span>Hữu ích (5)</span>
                                    </div>
                                    <div class="review-action">
                                        <i class="fas fa-reply"></i>
                                        <span>Trả lời</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="review-item">
                                <div class="review-meta">
                                    <div class="review-author">Trần Thị B</div>
                                    <div class="review-date">10/05/2023</div>
                                </div>
                                <div class="review-rating">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="far fa-star"></i>
                                </div>
                                <div class="review-content">
                                    Sản phẩm tốt nhưng giao hàng hơi chậm. Chất liệu vải mát, mặc thoải mái.
                                </div>
                                <div class="review-actions">
                                    <div class="review-action">
                                        <i class="fas fa-thumbs-up"></i>
                                        <span>Hữu ích (3)</span>
                                    </div>
                                    <div class="review-action">
                                        <i class="fas fa-reply"></i>
                                        <span>Trả lời</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="review-form">
                            <h3>Viết đánh giá của bạn</h3>
                            <div class="rating-input">
                                <i class="far fa-star" data-rating="1"></i>
                                <i class="far fa-star" data-rating="2"></i>
                                <i class="far fa-star" data-rating="3"></i>
                                <i class="far fa-star" data-rating="4"></i>
                                <i class="far fa-star" data-rating="5"></i>
                            </div>
                            <div class="form-group">
                                <textarea placeholder="Viết đánh giá của bạn..." rows="4"></textarea>
                            </div>
                            <button class="btn-primary">Gửi đánh giá</button>
                        </div>
                    </div>
                </div>
                
                <div class="tab-content" id="shipping">
                    <p>Thông tin vận chuyển sản phẩm. Thời gian giao hàng từ 2-5 ngày làm việc. Phí vận chuyển miễn phí cho đơn hàng từ 500.000đ.</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Compare Modal -->
    <div class="compare-modal" id="compare-modal">
        <div class="compare-content">
            <div class="compare-header">
                <h2>So sánh sản phẩm</h2>
                <span class="compare-close" id="compare-close">&times;</span>
            </div>
            <div class="compare-body" id="compare-body">
                <p style="text-align: center; padding: 50px; color: var(--gray);">Chưa có sản phẩm nào để so sánh</p>
            </div>
        </div>
    </div>
    
    <!-- Toast Container -->
    <div class="toast-container" id="toast-container"></div>
    
    <!-- JS Libs -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
    <!-- Main JS -->
    <script src="assets/js/main.js"></script>
</body>
</html>