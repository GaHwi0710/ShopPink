   </main>
    
    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Về ShopPink</h3>
                    <p>ShopPink là cửa hàng mỹ phẩm uy tín, cung cấp các sản phẩm chất lượng cao với giá cả hợp lý.</p>
                    <div class="social-links">
                        <a href="#" class="social-link"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-youtube"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-tiktok"></i></a>
                    </div>
                </div>
                
                <div class="footer-section">
                    <h3>Danh mục sản phẩm</h3>
                    <ul class="footer-links">
                        <li><a href="products.php?category=1">Chăm sóc da</a></li>
                        <li><a href="products.php?category=2">Trang điểm</a></li>
                        <li><a href="products.php?category=3">Chăm sóc tóc</a></li>
                        <li><a href="products.php?category=4">Nước hoa</a></li>
                        <li><a href="products.php?category=5">Dụng cụ làm đẹp</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Hỗ trợ khách hàng</h3>
                    <ul class="footer-links">
                        <li><a href="about.php">Giới thiệu</a></li>
                        <li><a href="contact.php">Liên hệ</a></li>
                        <li><a href="shipping.php">Chính sách vận chuyển</a></li>
                        <li><a href="return.php">Chính sách đổi trả</a></li>
                        <li><a href="privacy.php">Chính sách bảo mật</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Thông tin liên hệ</h3>
                    <div class="contact-info">
                        <p><i class="fas fa-map-marker-alt"></i> 123 Đường ABC, Quận 1, TP.HCM</p>
                        <p><i class="fas fa-phone"></i> 1900-xxxx</p>
                        <p><i class="fas fa-envelope"></i> info@shoppink.com</p>
                        <p><i class="fas fa-clock"></i> 8:00 - 22:00 (Thứ 2 - Chủ nhật)</p>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <div class="footer-bottom-content">
                    <p>&copy; <?php echo date('Y'); ?> ShopPink. Tất cả quyền được bảo lưu.</p>
                    <div class="payment-methods">
                        <span>Thanh toán:</span>
                        <i class="fab fa-cc-visa"></i>
                        <i class="fab fa-cc-mastercard"></i>
                        <i class="fab fa-cc-paypal"></i>
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Back to top button -->
    <button id="backToTop" class="back-to-top" onclick="scrollToTop()">
        <i class="fas fa-arrow-up"></i>
    </button>
    
    <!-- JavaScript -->
    <script src="assets/js/main.js"></script>
    
    <script>
    // Back to top functionality
    window.onscroll = function() {
        if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
            document.getElementById("backToTop").style.display = "block";
        } else {
            document.getElementById("backToTop").style.display = "none";
        }
    };
    
    function scrollToTop() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }
    
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.remove();
            }, 300);
        });
    }, 5000);
    </script>
</body>
</html>