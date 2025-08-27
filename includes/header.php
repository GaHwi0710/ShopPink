<?php
session_start();
?>
<header>
    <div class="header-top">
        <div class="container">
            <div class="logo">
                <a href="index.php">ShopPink</a>
            </div>
            
            <div class="search-bar">
                <form action="search.php" method="get">
                    <input type="text" name="query" placeholder="Tìm kiếm sản phẩm...">
                    <button type="submit">Tìm</button>
                </form>
            </div>
            
            <div class="user-actions">
                <?php if (isset($_SESSION['user_id'])) { ?>
                    <a href="user_home.php" class="user-home">
                        <img src="assets/images/user-icon.png" alt="User">
                        <span><?php echo $_SESSION['username']; ?></span>
                    </a>
                    <a href="cart.php" class="cart-link">
                        <img src="assets/images/cart-icon.png" alt="Cart">
                        <span>Giỏ hàng</span>
                    </a>
                    <a href="logout.php" class="logout">Đăng xuất</a>
                <?php } else { ?>
                    <a href="login.php" class="login">Đăng nhập</a>
                    <a href="register.php" class="register">Đăng ký</a>
                <?php } ?>
            </div>
        </div>
    </div>
    
    <div class="header-bottom">
        <div class="container">
            <nav class="main-nav">
                <ul>
                    <li><a href="index.php">Trang chủ</a></li>
                    <?php
                    // Lấy danh mục chính
                    include('config.php');
                    $main_categories_query = "SELECT * FROM categories WHERE parent_id IS NULL";
                    $main_categories_result = mysqli_query($conn, $main_categories_query);
                    
                    while ($category = mysqli_fetch_assoc($main_categories_result)) {
                        echo '<li><a href="category.php?id=' . $category['id'] . '">' . $category['name'] . '</a></li>';
                    }
                    ?>
                </ul>
            </nav>
        </div>
    </div>
</header>