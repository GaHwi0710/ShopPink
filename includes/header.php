p
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Meta tags -->
    <meta name="description" content="<?php echo isset($page_description) ? $page_description : 'ShopPink - Cửa hàng mỹ phẩm chất lượng cao'; ?>">
    <meta name="keywords" content="<?php echo isset($page_keywords) ? $page_keywords : 'mỹ phẩm, làm đẹp, skincare, makeup'; ?>">
    
    <!-- Open Graph -->
    <meta property="og:title" content="<?php echo isset($page_title) ? $page_title . ' - ' . SITE_NAME : SITE_NAME; ?>">
    <meta property="og:description" content="<?php echo isset($page_description) ? $page_description : 'ShopPink - Cửa hàng mỹ phẩm chất lượng cao'; ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo SITE_URL . $_SERVER['REQUEST_URI']; ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-top">
            <div class="container">
                <div class="header-top-content">
                    <div class="header-top-left">
                        <span class="phone">
                            <i class="fas fa-phone"></i> 1900-xxxx
                        </span>
                        <span class="email">
                            <i class="fas fa-envelope"></i> info@shoppink.com
                        </span>
                    </div>
                    
                    <div class="header-top-right">
                        <?php if (is_logged_in()): ?>
                            <span class="welcome">
                                Xin chào, <?php echo htmlspecialchars($_SESSION['user_name'] ?? $_SESSION['username']); ?>!
                            </span>
                            <a href="customer/profile.php" class="header-link">
                                <i class="fas fa-user"></i> Tài khoản
                            </a>
                            <a href="auth/logout.php" class="header-link">
                                <i class="fas fa-sign-out-alt"></i> Đăng xuất
                            </a>
                        <?php else: ?>
                            <a href="auth/login.php" class="header-link">
                                <i class="fas fa-sign-in-alt"></i> Đăng nhập
                            </a>
                            <a href="auth/register.php" class="header-link">
                                <i class="fas fa-user-plus"></i> Đăng ký
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="header-main">
            <div class="container">
                <div class="header-main-content">
                    <div class="logo">
                        <a href="index.php">
                            <img src="assets/images/logo.png" alt="<?php echo SITE_NAME; ?>">
                        </a>
                    </div>
                    
                    <div class="search-box">
                        <form method="GET" action="products.php" class="search-form">
                            <input type="text" name="search" placeholder="Tìm kiếm sản phẩm..." 
                                   value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                            <button type="submit" class="search-btn">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                    </div>
                    
                    <div class="header-actions">
                        <a href="customer/cart.php" class="cart-icon">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="cart-count">
                                <?php
                                if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
                                    echo array_sum($_SESSION['cart']);
                                } else {
                                    echo '0';
                                }
                                ?>
                            </span>
                        </a>
                        
                        <?php if (is_logged_in()): ?>
                            <a href="customer/orders.php" class="order-icon">
                                <i class="fas fa-shopping-bag"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <nav class="main-nav">
            <div class="container">
                <ul class="nav-menu">
                    <li class="nav-item">
                        <a href="index.php" class="nav-link">
                            <i class="fas fa-home"></i> Trang chủ
                        </a>
                    </li>
                    
                    <li class="nav-item dropdown">
                        <a href="products.php" class="nav-link">
                            <i class="fas fa-shopping-bag"></i> Sản phẩm
                        </a>
                        <ul class="dropdown-menu">
                            <?php
                            $categories = getCategories();
                            while ($category = $categories->fetch_assoc()):
                            ?>
                                <li>
                                    <a href="products.php?category=<?php echo $category['id']; ?>">
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </a>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    </li>
                    
                    <li class="nav-item">
                        <a href="products.php?featured=1" class="nav-link">
                            <i class="fas fa-star"></i> Sản phẩm nổi bật
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a href="products.php?bestseller=1" class="nav-link">
                            <i class="fas fa-fire"></i> Bán chạy nhất
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a href="about.php" class="nav-link">
                            <i class="fas fa-info-circle"></i> Giới thiệu
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a href="contact.php" class="nav-link">
                            <i class="fas fa-envelope"></i> Liên hệ
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
    </header>
    
    <!-- Main content -->
    <main class="main-content">
        <?php display_flash_message(); ?>