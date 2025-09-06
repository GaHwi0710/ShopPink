<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Định nghĩa hằng số SITE_URL nếu chưa được định nghĩa
if (!defined('SITE_URL')) {
    // Xác định URL gốc của trang web
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $scriptName = $_SERVER['SCRIPT_NAME'];
    $path = str_replace(basename($scriptName), '', $scriptName);
    define('SITE_URL', $protocol . '://' . $host . $path);
}
// Kiểm tra người dùng đã đăng nhập chưa
if (!isset($_SESSION['user'])) {
    $loginUrl = '#login-modal';
    $registerUrl = '#register-modal';
} else {
    $loginUrl = '/logout';
    $registerUrl = '#';
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'ShopPink - Mua sắm trực tuyến hàng đầu Việt Nam'; ?></title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Owl Carousel -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head>
<body>
<!-- Loading Screen -->
<div class="loading-screen" id="loading-screen">
    <div class="spinner"></div>
</div>
<!-- Header -->
<header>
    <div class="container">
        <div class="top-header">
            <div class="top-contact">
                <a href="tel:0123456789"><i class="fas fa-phone"></i> 0123 456 789</a>
                <a href="mailto:info@shoppink.com"><i class="fas fa-envelope"></i> info@shoppink.com</a>
            </div>
            <div class="top-links">
                <?php if (isset($_SESSION['user'])): ?>
                    <a href="<?php echo SITE_URL; ?>/user/profile"><i class="fas fa-user"></i> Tài khoản</a>
                    <a href="#wishlist-modal" class="modal-trigger" data-modal="wishlist-modal"><i class="fas fa-heart"></i> Yêu thích</a>
                    <a href="#cart-modal" class="modal-trigger" data-modal="cart-modal"><i class="fas fa-shopping-cart"></i> Giỏ hàng</a>
                <?php else: ?>
                    <a href="<?php echo $loginUrl; ?>" class="modal-trigger" data-modal="login-modal"><i class="fas fa-user"></i> Tài khoản</a>
                    <a href="<?php echo $registerUrl; ?>" class="modal-trigger" data-modal="register-modal"><i class="fas fa-user-plus"></i> Đăng ký</a>
                    <a href="#cart-modal" class="modal-trigger" data-modal="cart-modal"><i class="fas fa-shopping-cart"></i> Giỏ hàng</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="main-header">
            <div class="logo">
                <a href="<?php echo SITE_URL; ?>/">
                    <i class="fas fa-shopping-bag"></i> ShopPink
                </a>
            </div>
            <div class="search-bar">
                <form action="<?php echo SITE_URL; ?>/search" method="GET">
                    <input type="text" name="keyword" placeholder="Tìm kiếm sản phẩm..." id="search-input">
                    <button type="submit"><i class="fas fa-search"></i> Tìm kiếm</button>
                </form>
                <div class="search-suggestions" id="search-suggestions">
                    <a href="<?php echo SITE_URL; ?>/search?keyword=Áo thun nữ"><i class="fas fa-search"></i> Áo thun nữ</a>
                    <a href="<?php echo SITE_URL; ?>/search?keyword=Đầm dự tiệc"><i class="fas fa-search"></i> Đầm dự tiệc</a>
                    <a href="<?php echo SITE_URL; ?>/search?keyword=Giày cao gót"><i class="fas fa-search"></i> Giày cao gót</a>
                    <a href="<?php echo SITE_URL; ?>/search?keyword=Túi xách"><i class="fas fa-search"></i> Túi xách</a>
                    <a href="<?php echo SITE_URL; ?>/search?keyword=Son môi"><i class="fas fa-search"></i> Son môi</a>
                </div>
            </div>
            <div class="user-actions">
                <?php if (isset($_SESSION['user'])): ?>
                    <div class="user-dropdown">
                        <a href="javascript:void(0)" class="user-menu-toggle">
                            <img src="<?php echo $_SESSION['user']['avatar'] ?? SITE_URL . '/assets/images/default-avatar.png'; ?>" alt="<?php echo $_SESSION['user']['full_name']; ?>" class="user-avatar">
                            <span><?php echo $_SESSION['user']['full_name']; ?></span>
                            <i class="fas fa-chevron-down"></i>
                        </a>
                        <div class="user-dropdown-menu">
                            <?php if ($_SESSION['user']['role'] === 'customer'): ?>
                                <a href="<?php echo SITE_URL; ?>/user/dashboard"><i class="fas fa-tachometer-alt"></i> Tổng quan</a>
                                <a href="<?php echo SITE_URL; ?>/orders"><i class="fas fa-shopping-bag"></i> Đơn hàng của tôi</a>
                                <a href="#wishlist-modal" class="modal-trigger" data-modal="wishlist-modal"><i class="fas fa-heart"></i> Sản phẩm yêu thích</a>
                                <a href="<?php echo SITE_URL; ?>/user/addresses"><i class="fas fa-map-marker-alt"></i> Sổ địa chỉ</a>
                                <a href="#voucher-modal" class="modal-trigger" data-modal="voucher-modal"><i class="fas fa-ticket-alt"></i> Voucher của tôi</a>
                                <a href="#notification-modal" class="modal-trigger" data-modal="notification-modal"><i class="fas fa-bell"></i> Thông báo</a>
                                <a href="<?php echo SITE_URL; ?>/user/profile"><i class="fas fa-user-cog"></i> Cài đặt tài khoản</a>
                            <?php elseif ($_SESSION['user']['role'] === 'seller'): ?>
                                <a href="<?php echo SITE_URL; ?>/seller/dashboard"><i class="fas fa-tachometer-alt"></i> Tổng quan</a>
                                <a href="<?php echo SITE_URL; ?>/seller/products"><i class="fas fa-box"></i> Quản lý sản phẩm</a>
                                <a href="<?php echo SITE_URL; ?>/seller/add-product"><i class="fas fa-plus-circle"></i> Thêm sản phẩm</a>
                                <a href="<?php echo SITE_URL; ?>/seller/orders"><i class="fas fa-shopping-bag"></i> Quản lý đơn hàng</a>
                                <a href="<?php echo SITE_URL; ?>/seller/revenue"><i class="fas fa-chart-line"></i> Doanh thu</a>
                                <a href="<?php echo SITE_URL; ?>/seller/settings"><i class="fas fa-cog"></i> Cài đặt</a>
                            <?php endif; ?>
                            <a href="<?php echo $loginUrl; ?>"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="<?php echo $loginUrl; ?>" class="modal-trigger" data-modal="login-modal" id="login-btn">
                        <i class="fas fa-user"></i> 
                        <span>Đăng nhập</span>
                    </a>
                    <a href="<?php echo $registerUrl; ?>" class="modal-trigger" data-modal="register-modal">
                        <i class="fas fa-user-plus"></i> 
                        <span>Đăng ký</span>
                    </a>
                <?php endif; ?>
                <a href="#cart-modal" class="modal-trigger" data-modal="cart-modal">
                    <i class="fas fa-shopping-cart"></i> 
                    <span>Giỏ hàng</span>
                    <?php 
                    $cartCount = 0;
                    if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
                        foreach ($_SESSION['cart'] as $quantity) {
                            $cartCount += $quantity;
                        }
                    }
                    if ($cartCount > 0): ?>
                        <span class="cart-count"><?php echo $cartCount; ?></span>
                    <?php endif; ?>
                </a>
            </div>
            <button class="mobile-menu-toggle" id="mobile-menu-toggle">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </div>
    <nav>
        <div class="container">
            <div class="main-nav" id="main-nav">
                <a href="<?php echo SITE_URL; ?>/"><i class="fas fa-home"></i> Trang chủ</a>
                
                <div class="category-menu">
                    <a href="javascript:void(0)"><i class="fas fa-bars"></i> Danh mục sản phẩm</a>
                    <div class="mega-menu">
                        <div class="mega-menu-content">
                            <div class="mega-menu-col">
                                <h4>Thời trang</h4>
                                <a href="<?php echo SITE_URL; ?>/search?keyword=thoi trang nam">Thời trang nam</a>
                                <a href="<?php echo SITE_URL; ?>/search?keyword=thoi trang nu">Thời trang nữ</a>
                                <a href="<?php echo SITE_URL; ?>/search?keyword=tre em">Thời trang trẻ em</a>
                                <a href="<?php echo SITE_URL; ?>/search?keyword=giay">Giày dép</a>
                                <a href="<?php echo SITE_URL; ?>/search?keyword=tui xach">Túi xách</a>
                            </div>
                            <div class="mega-menu-col">
                                <h4>Điện tử</h4>
                                <a href="<?php echo SITE_URL; ?>/search?keyword=dien thoai">Điện thoại</a>
                                <a href="<?php echo SITE_URL; ?>/search?keyword=laptop">Laptop</a>
                                <a href="<?php echo SITE_URL; ?>/search?keyword=may tinh bang">Máy tính bảng</a>
                                <a href="<?php echo SITE_URL; ?>/search?keyword=phu kien">Phụ kiện điện tử</a>
                                <a href="<?php echo SITE_URL; ?>/search?keyword=thiet bi gia dung">Thiết bị gia dụng</a>
                            </div>
                            <div class="mega-menu-col">
                                <h4>Làm đẹp</h4>
                                <a href="<?php echo SITE_URL; ?>/search?keyword=trang diem">Trang điểm</a>
                                <a href="<?php echo SITE_URL; ?>/search?keyword=cham soc da">Chăm sóc da</a>
                                <a href="<?php echo SITE_URL; ?>/search?keyword=nước hoa">Nước hoa</a>
                                <a href="<?php echo SITE_URL; ?>/search?keyword=my pham">Mỹ phẩm</a>
                                <a href="<?php echo SITE_URL; ?>/search?keyword=cham soc toc">Chăm sóc tóc</a>
                            </div>
                            <div class="mega-menu-col">
                                <h4>Đồ chơi</h4>
                                <a href="<?php echo SITE_URL; ?>/search?keyword=do choi tre em">Đồ chơi trẻ em</a>
                                <a href="<?php echo SITE_URL; ?>/search?keyword=do choi giao duc">Đồ chơi giáo dục</a>
                                <a href="<?php echo SITE_URL; ?>/search?keyword=lego">Lego</a>
                                <a href="<?php echo SITE_URL; ?>/search?keyword=do choi the thao">Đồ chơi thể thao</a>
                                <a href="<?php echo SITE_URL; ?>/search?keyword=do choi dien tu">Đồ chơi điện tử</a>
                            </div>
                            <div class="mega-menu-col">
                                <h4>Thể thao</h4>
                                <a href="<?php echo SITE_URL; ?>/search?keyword=quần áo thể thao">Quần áo thể thao</a>
                                <a href="<?php echo SITE_URL; ?>/search?keyword=giày thể thao">Giày thể thao</a>
                                <a href="<?php echo SITE_URL; ?>/search?keyword=dụng cụ thể thao">Dụng cụ thể thao</a>
                                <a href="<?php echo SITE_URL; ?>/search?keyword=xe đạp">Xe đạp</a>
                                <a href="<?php echo SITE_URL; ?>/search?keyword=phụ kiện thể thao">Phụ kiện thể thao</a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <a href="<?php echo SITE_URL; ?>/products?filter=hot"><i class="fas fa-fire"></i> Sản phẩm hot</a>
                <a href="<?php echo SITE_URL; ?>/products?filter=promotion"><i class="fas fa-percent"></i> Khuyến mãi</a>
                <a href="<?php echo SITE_URL; ?>/products?filter=new"><i class="fas fa-gift"></i> Ưu đãi mới</a>
                <a href="<?php echo SITE_URL; ?>/brands"><i class="fas fa-star"></i> Thương hiệu</a>
                <a href="<?php echo SITE_URL; ?>/contact"><i class="fas fa-phone"></i> Liên hệ</a>
            </div>
        </div>
    </nav>
</header>
<!-- Login Modal -->
<div class="modal" id="login-modal">
    <div class="modal-content">
        <span class="modal-close">&times;</span>
        <h2>Đăng nhập</h2>
        
        <div id="login-error-container"></div>
        
        <form id="login-form" action="<?php echo SITE_URL; ?>/login" method="POST">
            <div class="form-group">
                <label for="login-username">Tên đăng nhập</label>
                <input type="text" id="login-username" name="username" placeholder="Nhập tên đăng nhập" required>
            </div>
            
            <div class="form-group">
                <label for="login-password">Mật khẩu</label>
                <input type="password" id="login-password" name="password" placeholder="Nhập mật khẩu" required>
            </div>
            
            <div class="form-group">
                <div class="remember-me">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Ghi nhớ đăng nhập</label>
                </div>
                <a href="#" class="forgot-password">Quên mật khẩu?</a>
            </div>
            
            <button type="submit" class="btn-primary">Đăng nhập</button>
        </form>
        
        <div class="auth-switch">
            Chưa có tài khoản? <a href="#register-modal" class="modal-trigger" data-modal="register-modal">Đăng ký ngay</a>
        </div>
        
        <div class="social-login">
            <p>Hoặc đăng nhập với</p>
            <div class="social-buttons">
                <button type="button" class="btn-facebook"><i class="fab fa-facebook-f"></i> Facebook</button>
                <button type="button" class="btn-google"><i class="fab fa-google"></i> Google</button>
            </div>
        </div>
    </div>
</div>
<!-- Register Modal -->
<div class="modal" id="register-modal">
    <div class="modal-content">
        <span class="modal-close">&times;</span>
        <h2>Đăng ký tài khoản</h2>
        
        <div id="register-error-container"></div>
        
        <form id="register-form" action="<?php echo SITE_URL; ?>/register" method="POST">
            <div class="form-group">
                <label for="full_name">Họ và tên</label>
                <input type="text" id="full_name" name="full_name" placeholder="Nhập họ và tên" required>
            </div>
            
            <div class="form-group">
                <label for="register-username">Tên đăng nhập</label>
                <input type="text" id="register-username" name="username" placeholder="Nhập tên đăng nhập" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Nhập email" required>
            </div>
            
            <div class="form-group">
                <label for="phone">Số điện thoại</label>
                <input type="tel" id="phone" name="phone" placeholder="Nhập số điện thoại">
            </div>
            
            <div class="form-group">
                <label for="address">Địa chỉ</label>
                <input type="text" id="address" name="address" placeholder="Nhập địa chỉ">
            </div>
            
            <div class="form-group">
                <label for="register-password">Mật khẩu</label>
                <input type="password" id="register-password" name="password" placeholder="Nhập mật khẩu" required>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Xác nhận mật khẩu</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Nhập lại mật khẩu" required>
            </div>
            
            <div class="form-group">
                <label>Đăng ký với tư cách:</label>
                <div class="radio-group">
                    <label>
                        <input type="radio" name="role" value="customer" checked> Người mua hàng
                    </label>
                    <label>
                        <input type="radio" name="role" value="seller"> Người bán hàng
                    </label>
                </div>
            </div>
            
            <div class="form-group">
                <div class="terms">
                    <input type="checkbox" id="agree_terms" name="agree_terms" required>
                    <label for="agree_terms">Tôi đồng ý với <a href="#">điều khoản sử dụng</a> và <a href="#">chính sách bảo mật</a></label>
                </div>
            </div>
            
            <button type="submit" class="btn-primary">Đăng ký</button>
        </form>
        
        <div class="auth-switch">
            Đã có tài khoản? <a href="#login-modal" class="modal-trigger" data-modal="login-modal">Đăng nhập</a>
        </div>
    </div>
</div>
<!-- Cart Modal -->
<div class="modal cart-modal" id="cart-modal">
    <div class="modal-content">
        <span class="modal-close">&times;</span>
        <h2>Giỏ hàng của bạn</h2>
        
        <div id="cart-content">
            <!-- Cart items will be loaded here dynamically -->
        </div>
        
        <div class="cart-summary">
            <h3>Tóm tắt đơn hàng</h3>
            <div class="summary-item">
                <span>Tạm tính:</span>
                <span id="cart-subtotal">0đ</span>
            </div>
            <div class="summary-item">
                <span>Phí vận chuyển:</span>
                <span id="cart-shipping">0đ</span>
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
                <span id="cart-total">0đ</span>
            </div>
            
            <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'customer'): ?>
                <a href="<?php echo SITE_URL; ?>/checkout" class="btn-primary checkout-btn">Tiến hành thanh toán</a>
            <?php else: ?>
                <a href="<?php echo $loginUrl; ?>" class="modal-trigger btn-primary checkout-btn" data-modal="login-modal">Đăng nhập để thanh toán</a>
            <?php endif; ?>
            
            <a href="<?php echo SITE_URL; ?>/" class="continue-shopping">Tiếp tục mua sắm</a>
        </div>
    </div>
</div>
<!-- Wishlist Modal -->
<div class="modal" id="wishlist-modal">
    <div class="modal-content">
        <span class="modal-close">&times;</span>
        <h2>Danh sách yêu thích</h2>
        
        <div id="wishlist-content">
            <!-- Wishlist items will be loaded here dynamically -->
        </div>
    </div>
</div>
<!-- Notification Modal -->
<div class="modal" id="notification-modal">
    <div class="modal-content">
        <span class="modal-close">&times;</span>
        <h2>Thông báo của tôi</h2>
        
        <div id="notification-content">
            <!-- Notifications will be loaded here dynamically -->
        </div>
    </div>
</div>
<!-- Voucher Modal -->
<div class="modal" id="voucher-modal">
    <div class="modal-content">
        <span class="modal-close">&times;</span>
        <h2>Voucher của tôi</h2>
        
        <div id="voucher-content">
            <!-- Vouchers will be loaded here dynamically -->
        </div>
    </div>
</div>
<!-- Quick View Modal -->
<div class="modal quick-view-modal" id="quick-view-modal">
    <div class="modal-content">
        <span class="modal-close">&times;</span>
        <div id="quick-view-content">
            <!-- Product details will be loaded here dynamically -->
        </div>
    </div>
</div>
<!-- Display messages -->
<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
    </div>
<?php endif; ?>
<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
    </div>
<?php endif; ?>