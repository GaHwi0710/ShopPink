<?php
// Include autoload để tự động nạp các file cần thiết
// KHÔNG gọi session_start() ở đây vì đã được gọi trong autoload.php
require_once 'autoload.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShopPink - Mua sắm trực tuyến hàng đầu Việt Nam</title>
    <!-- CSS Libs -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/form.css">
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
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <a href="login.php"><i class="fas fa-user"></i> Tài khoản</a>
                        <a href="register.php"><i class="fas fa-user-plus"></i> Đăng ký</a>
                    <?php else: ?>
                        <a href="user_home.php"><i class="fas fa-user"></i> <?php echo $_SESSION['user_name']; ?></a>
                        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
                    <?php endif; ?>
                    <a href="#"><i class="fas fa-heart"></i> Yêu thích</a>
                    <a href="cart.php"><i class="fas fa-shopping-cart"></i> Giỏ hàng</a>
                </div>
            </div>
        </div>
        
        <div class="container">
            <div class="main-header">
                <div class="logo">
                    <i class="fas fa-shopping-bag"></i> ShopPink
                </div>
                
                <div class="search-bar">
                    <form action="search.php" method="get">
                        <input type="text" placeholder="Tìm kiếm sản phẩm..." id="search-input" name="query">
                        <button type="submit"><i class="fas fa-search"></i> Tìm kiếm</button>
                    </form>
                    <div class="search-suggestions" id="search-suggestions">
                        <a href="#"><i class="fas fa-search"></i> Áo thun nữ</a>
                        <a href="#"><i class="fas fa-search"></i> Đầm dự tiệc</a>
                        <a href="#"><i class="fas fa-search"></i> Giày cao gót</a>
                        <a href="#"><i class="fas fa-search"></i> Túi xách hàng hiệu</a>
                        <a href="#"><i class="fas fa-search"></i> Son môi</a>
                    </div>
                </div>
                
                <div class="user-actions">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <a href="user_home.php"><i class="fas fa-user"></i> <span><?php echo $_SESSION['user_name']; ?></span></a>
                    <?php else: ?>
                        <a href="#" id="login-btn"><i class="fas fa-user"></i> <span>Đăng nhập</span></a>
                    <?php endif; ?>
                    <a href="#"><i class="fas fa-heart"></i> <span>Yêu thích</span></a>
                    <a href="cart.php"><i class="fas fa-shopping-cart"></i> <span>Giỏ hàng</span> 
                        <?php 
                        $cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
                        if($cart_count > 0): 
                        ?>
                            <span class="cart-count"><?php echo $cart_count; ?></span>
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
                    <a href="index.php"><i class="fas fa-home"></i> Trang chủ</a>
                    
                    <div class="category-menu">
                        <a href="#"><i class="fas fa-bars"></i> Danh mục sản phẩm</a>
                        <div class="mega-menu">
                            <div class="mega-menu-content">
                                <div class="mega-menu-col">
                                    <h4>Thời trang nữ</h4>
                                    <?php
                                    // Lấy danh mục thời trang nữ
                                    $women_categories = getCategories(1);
                                    foreach ($women_categories as $cat) {
                                        echo '<a href="category.php?id='.$cat['id'].'">'.htmlspecialchars($cat['name']).'</a>';
                                    }
                                    ?>
                                </div>
                                <div class="mega-menu-col">
                                    <h4>Thời trang nam</h4>
                                    <?php
                                    // Lấy danh mục thời trang nam
                                    $men_categories = getCategories(2);
                                    foreach ($men_categories as $cat) {
                                        echo '<a href="category.php?id='.$cat['id'].'">'.htmlspecialchars($cat['name']).'</a>';
                                    }
                                    ?>
                                </div>
                                <div class="mega-menu-col">
                                    <h4>Phụ kiện</h4>
                                    <?php
                                    // Lấy danh mục phụ kiện
                                    $accessory_categories = getCategories(3);
                                    foreach ($accessory_categories as $cat) {
                                        echo '<a href="category.php?id='.$cat['id'].'">'.htmlspecialchars($cat['name']).'</a>';
                                    }
                                    ?>
                                </div>
                                <div class="mega-menu-col">
                                    <h4>Làm đẹp</h4>
                                    <?php
                                    // Lấy danh mục làm đẹp
                                    $beauty_categories = getCategories(4);
                                    foreach ($beauty_categories as $cat) {
                                        echo '<a href="category.php?id='.$cat['id'].'">'.htmlspecialchars($cat['name']).'</a>';
                                    }
                                    ?>
                                </div>
                                <div class="mega-menu-col">
                                    <h4>Giày dép</h4>
                                    <?php
                                    // Lấy danh mục giày dép
                                    $shoe_categories = getCategories(5);
                                    foreach ($shoe_categories as $cat) {
                                        echo '<a href="category.php?id='.$cat['id'].'">'.htmlspecialchars($cat['name']).'</a>';
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <a href="#"><i class="fas fa-fire"></i> Sản phẩm hot</a>
                    <a href="#"><i class="fas fa-percent"></i> Khuyến mãi</a>
                    <a href="#"><i class="fas fa-gift"></i> Ưu đãi mới</a>
                    <a href="#"><i class="fas fa-star"></i> Thương hiệu</a>
                    <a href="contact.php"><i class="fas fa-phone"></i> Liên hệ</a>
                </div>
            </div>
        </nav>
    </header>
    
    <!-- Login Modal -->
    <div class="modal" id="login-modal">
        <div class="modal-content">
            <span class="modal-close" id="login-close">&times;</span>
            <h2>Đăng nhập</h2>
            <div class="alert alert-success" id="login-success">
                <i class="fas fa-check-circle"></i> Đăng nhập thành công!
            </div>
            <div class="alert alert-error" id="login-error">
                <i class="fas fa-exclamation-circle"></i> Tên đăng nhập hoặc mật khẩu không đúng!
            </div>
            <form id="login-form" action="login.php" method="post">
                <div class="form-group">
                    <label for="username">Tên đăng nhập</label>
                    <input type="text" id="username" name="username" placeholder="Nhập tên đăng nhập" required>
                </div>
                <div class="form-group">
                    <label for="password">Mật khẩu</label>
                    <input type="password" id="password" name="password" placeholder="Nhập mật khẩu" required>
                </div>
                <button type="submit" class="btn-primary">Đăng nhập</button>
            </form>
            <p style="text-align: center; margin-top: 20px;">
                Chưa có tài khoản? <a href="register.php" style="color: var(--primary-color);">Đăng ký ngay</a>
            </p>
        </div>
    </div>