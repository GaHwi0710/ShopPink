<?php
// Hàm đệ quy render menu con
function renderSubmenu($conn, $parent_id) {
    $sub_query = "SELECT * FROM categories WHERE parent_id = " . intval($parent_id);
    $sub_result = mysqli_query($conn, $sub_query);

    if ($sub_result && mysqli_num_rows($sub_result) > 0) {
        echo '<ul class="dropdown">';
        while ($sub = mysqli_fetch_assoc($sub_result)) {
            echo '<li class="has-sub">';
            echo '<a href="category.php?id=' . intval($sub['id']) . '">' . htmlspecialchars($sub['name']) . '</a>';
            
            // Gọi lại chính nó để render cháu
            renderSubmenu($conn, $sub['id']);

            echo '</li>';
        }
        echo '</ul>';
    }
}
?>

<header>
    <div class="header-top">
        <div class="container">
            <!-- Logo -->
            <div class="logo">
                <a href="index.php">ShopPink</a>
            </div>
            
            <!-- Thanh tìm kiếm -->
            <div class="search-bar">
                <form action="search.php" method="get">
                    <input type="text" name="query" placeholder="Tìm kiếm sản phẩm...">
                    <button type="submit">Tìm</button>
                </form>
            </div>
            
            <!-- User actions -->
            <div class="user-actions">
                <?php if (isset($_SESSION['user_id'])) { ?>
                    <a href="user_home.php" class="user-home">
                        <img src="assets/images/user-icon.png" alt="User">
                        <span>
                            <?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Người dùng'; ?>
                        </span>
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
                    if (isset($conn)) {
                        $main_categories_query = "SELECT * FROM categories WHERE parent_id = 0 OR parent_id IS NULL";
                        $main_categories_result = mysqli_query($conn, $main_categories_query);

                        while ($category = mysqli_fetch_assoc($main_categories_result)) {
                            echo '<li class="has-sub">';
                            echo '<a href="category.php?id=' . intval($category['id']) . '">' 
                                . htmlspecialchars($category['name']) . '</a>';

                            // Render submenu con/cháu đệ quy
                            renderSubmenu($conn, $category['id']);

                            echo '</li>';
                        }
                    }
                    ?>
                </ul>
            </nav>
        </div>
    </div>
</header>
