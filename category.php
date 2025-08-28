<?php
session_start();
include('includes/config.php');
// Lấy id danh mục
$category_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($category_id <= 0) {
    header("Location: index.php");
    exit();
}
// Lấy thông tin danh mục
$category_query = "SELECT * FROM categories WHERE id = $category_id";
$category_result = mysqli_query($conn, $category_query);
$category = mysqli_fetch_assoc($category_result);
if (!$category) {
    include('includes/header.php');
    echo "<div class='container'><h2>❌ Danh mục không tồn tại</h2></div>";
    include('includes/footer.php');
    exit();
}
// Breadcrumb (danh mục cha nếu có)
$parent_category = null;
if ($category['parent_id'] > 0) {
    $parent_query = "SELECT * FROM categories WHERE id = " . intval($category['parent_id']);
    $parent_result = mysqli_query($conn, $parent_query);
    $parent_category = mysqli_fetch_assoc($parent_result);
}
// Lấy danh mục con
$sub_query = "SELECT * FROM categories WHERE parent_id = $category_id";
$sub_result = mysqli_query($conn, $sub_query);

// Xử lý lọc và sắp xếp
$where_conditions = "(p.category_id = $category_id OR c.parent_id = $category_id)";
$price_filter = "";
$sort_order = "ORDER BY p.created_at DESC";

// Xử lý lọc giá
if (isset($_GET['price'])) {
    switch ($_GET['price']) {
        case "0-500000": $price_filter = " AND p.price < 500000"; break;
        case "500000-1000000": $price_filter = " AND p.price BETWEEN 500000 AND 1000000"; break;
        case "1000000-2000000": $price_filter = " AND p.price BETWEEN 1000000 AND 2000000"; break;
        case "2000000": $price_filter = " AND p.price > 2000000"; break;
    }
}

// Xử lý sắp xếp
if (isset($_GET['sort'])) {
    switch ($_GET['sort']) {
        case "price-asc": $sort_order = "ORDER BY p.price ASC"; break;
        case "price-desc": $sort_order = "ORDER BY p.price DESC"; break;
        case "name": $sort_order = "ORDER BY p.name ASC"; break;
        case "newest": $sort_order = "ORDER BY p.created_at DESC"; break;
    }
}

// Phân trang
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$items_per_page = 12;
$offset = ($page - 1) * $items_per_page;

// Lấy tổng số sản phẩm
$count_query = "
    SELECT COUNT(*) as total 
    FROM products p
    JOIN categories c ON p.category_id = c.id
    WHERE $where_conditions
    $price_filter
";
$count_result = mysqli_query($conn, $count_query);
$total_items = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_items / $items_per_page);

// Lấy sản phẩm trong danh mục
$product_query = "
    SELECT p.*, c.name as category_name
    FROM products p
    JOIN categories c ON p.category_id = c.id
    WHERE $where_conditions
    $price_filter
    $sort_order
    LIMIT $offset, $items_per_page
";
$product_result = mysqli_query($conn, $product_query);
?>
<?php include('includes/header.php'); ?>

<div class="container">
    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="index.php">Trang chủ</a>
        <?php if ($parent_category) { ?>
            &raquo; <a href="category.php?id=<?php echo $parent_category['id']; ?>">
                <?php echo htmlspecialchars($parent_category['name']); ?>
            </a>
        <?php } ?>
        &raquo; <span><?php echo htmlspecialchars($category['name']); ?></span>
    </div>
    
    <!-- Category Header -->
    <div class="category-header">
        <h1 class="section-title"><?php echo htmlspecialchars($category['name']); ?></h1>
        <p class="category-description"><?php echo nl2br(htmlspecialchars($category['description'] ?? '')); ?></p>
    </div>
    
    <!-- Advanced Filters -->
    <div class="advanced-filters animate-on-scroll">
        <div class="filter-section">
            <div class="filter-title">
                <span>Khoảng giá</span>
                <button class="filter-toggle"><i class="fas fa-minus"></i></button>
            </div>
            <div class="price-range">
                <input type="number" placeholder="Từ" id="price-from">
                <span>-</span>
                <input type="number" placeholder="Đến" id="price-to">
                <button id="apply-price-filter">Áp dụng</button>
            </div>
        </div>
        
        <div class="filter-section">
            <div class="filter-title">
                <span>Thương hiệu</span>
                <button class="filter-toggle"><i class="fas fa-minus"></i></button>
            </div>
            <div class="filter-options">
                <div class="filter-option">
                    <input type="checkbox" id="brand1">
                    <label for="brand1">Nike <span class="filter-count">(25)</span></label>
                </div>
                <div class="filter-option">
                    <input type="checkbox" id="brand2">
                    <label for="brand2">Adidas <span class="filter-count">(18)</span></label>
                </div>
                <div class="filter-option">
                    <input type="checkbox" id="brand3">
                    <label for="brand3">Zara <span class="filter-count">(32)</span></label>
                </div>
                <div class="filter-option">
                    <input type="checkbox" id="brand4">
                    <label for="brand4">H&M <span class="filter-count">(27)</span></label>
                </div>
            </div>
        </div>
        
        <div class="filter-section">
            <div class="filter-title">
                <span>Màu sắc</span>
                <button class="filter-toggle"><i class="fas fa-minus"></i></button>
            </div>
            <div class="color-options">
                <div class="color-option" style="background: #000000" data-color="black"></div>
                <div class="color-option" style="background: #ffffff; border: 1px solid #ddd" data-color="white"></div>
                <div class="color-option" style="background: #ff0000" data-color="red"></div>
                <div class="color-option" style="background: #0000ff" data-color="blue"></div>
                <div class="color-option" style="background: #00ff00" data-color="green"></div>
                <div class="color-option" style="background: #ffff00" data-color="yellow"></div>
                <div class="color-option" style="background: #ff00ff" data-color="pink"></div>
                <div class="color-option" style="background: #00ffff" data-color="cyan"></div>
            </div>
        </div>
        
        <div class="filter-section">
            <div class="filter-title">
                <span>Kích thước</span>
                <button class="filter-toggle"><i class="fas fa-minus"></i></button>
            </div>
            <div class="size-options">
                <div class="size-option" data-size="XS">XS</div>
                <div class="size-option" data-size="S">S</div>
                <div class="size-option" data-size="M">M</div>
                <div class="size-option" data-size="L">L</div>
                <div class="size-option" data-size="XL">XL</div>
                <div class="size-option" data-size="XXL">XXL</div>
            </div>
        </div>
        
        <div class="filter-section">
            <div class="filter-title">
                <span>Đánh giá</span>
                <button class="filter-toggle"><i class="fas fa-minus"></i></button>
            </div>
            <div class="filter-options">
                <div class="filter-option">
                    <input type="checkbox" id="rating5">
                    <label for="rating5">
                        <i class="fas fa-star" style="color: #ffc107"></i>
                        <i class="fas fa-star" style="color: #ffc107"></i>
                        <i class="fas fa-star" style="color: #ffc107"></i>
                        <i class="fas fa-star" style="color: #ffc107"></i>
                        <i class="fas fa-star" style="color: #ffc107"></i>
                        <span class="filter-count">(15)</span>
                    </label>
                </div>
                <div class="filter-option">
                    <input type="checkbox" id="rating4">
                    <label for="rating4">
                        <i class="fas fa-star" style="color: #ffc107"></i>
                        <i class="fas fa-star" style="color: #ffc107"></i>
                        <i class="fas fa-star" style="color: #ffc107"></i>
                        <i class="fas fa-star" style="color: #ffc107"></i>
                        <i class="far fa-star" style="color: #ffc107"></i>
                        <span class="filter-count">(23)</span>
                    </label>
                </div>
                <div class="filter-option">
                    <input type="checkbox" id="rating3">
                    <label for="rating3">
                        <i class="fas fa-star" style="color: #ffc107"></i>
                        <i class="fas fa-star" style="color: #ffc107"></i>
                        <i class="fas fa-star" style="color: #ffc107"></i>
                        <i class="far fa-star" style="color: #ffc107"></i>
                        <i class="far fa-star" style="color: #ffc107"></i>
                        <span class="filter-count">(18)</span>
                    </label>
                </div>
            </div>
        </div>
        
        <?php if (mysqli_num_rows($sub_result) > 0) { ?>
        <div class="filter-section">
            <div class="filter-title">
                <span>Danh mục con</span>
                <button class="filter-toggle"><i class="fas fa-minus"></i></button>
            </div>
            <div class="filter-options">
                <?php mysqli_data_seek($sub_result, 0); ?>
                <?php while ($sub = mysqli_fetch_assoc($sub_result)) { ?>
                    <div class="filter-option">
                        <input type="checkbox" id="sub<?php echo $sub['id']; ?>">
                        <label for="sub<?php echo $sub['id']; ?>">
                            <?php echo htmlspecialchars($sub['name']); ?>
                            <span class="filter-count">(<?php echo rand(5, 30); ?>)</span>
                        </label>
                    </div>
                <?php } ?>
            </div>
        </div>
        <?php } ?>
    </div>
    
    <!-- Filter and Sort Controls -->
    <div class="filter-sort-container animate-on-scroll">
        <div class="filter-controls">
            <label for="price-filter">Khoảng giá:</label>
            <select id="price-filter" onchange="window.location.href='?id=<?php echo $category_id; ?>&price='+this.value+'&sort=<?php echo isset($_GET['sort']) ? $_GET['sort'] : ''; ?>'">
                <option value="">Tất cả</option>
                <option value="0-500000" <?php echo isset($_GET['price']) && $_GET['price'] == '0-500000' ? 'selected' : ''; ?>>Dưới 500.000đ</option>
                <option value="500000-1000000" <?php echo isset($_GET['price']) && $_GET['price'] == '500000-1000000' ? 'selected' : ''; ?>>500.000đ - 1.000.000đ</option>
                <option value="1000000-2000000" <?php echo isset($_GET['price']) && $_GET['price'] == '1000000-2000000' ? 'selected' : ''; ?>>1.000.000đ - 2.000.000đ</option>
                <option value="2000000" <?php echo isset($_GET['price']) && $_GET['price'] == '2000000' ? 'selected' : ''; ?>>Trên 2.000.000đ</option>
            </select>
        </div>
        
        <div class="sort-controls">
            <label for="sort-products">Sắp xếp:</label>
            <select id="sort-products" onchange="window.location.href='?id=<?php echo $category_id; ?>&price=<?php echo isset($_GET['price']) ? $_GET['price'] : ''; ?>&sort='+this.value">
                <option value="newest" <?php echo isset($_GET['sort']) && $_GET['sort'] == 'newest' ? 'selected' : ''; ?>>Mới nhất</option>
                <option value="price-asc" <?php echo isset($_GET['sort']) && $_GET['sort'] == 'price-asc' ? 'selected' : ''; ?>>Giá: Thấp đến cao</option>
                <option value="price-desc" <?php echo isset($_GET['sort']) && $_GET['sort'] == 'price-desc' ? 'selected' : ''; ?>>Giá: Cao đến thấp</option>
                <option value="name" <?php echo isset($_GET['sort']) && $_GET['sort'] == 'name' ? 'selected' : ''; ?>>Tên: A-Z</option>
            </select>
            
            <div class="view-toggle">
                <button class="view-btn active" id="grid-view"><i class="fas fa-th"></i></button>
                <button class="view-btn" id="list-view"><i class="fas fa-list"></i></button>
            </div>
        </div>
    </div>
    
    <!-- Products Grid -->
    <?php if (mysqli_num_rows($product_result) > 0) { ?>
    <div class="products-grid animate-on-scroll" id="products-grid">
        <?php while ($product = mysqli_fetch_assoc($product_result)) { ?>
            <div class="product" data-category="<?php echo $product['category_id']; ?>" data-price="<?php echo $product['price']; ?>">
                <?php if ($product['old_price'] > 0): ?>
                    <div class="product-badge sale">-<?php echo round(($product['old_price'] - $product['price']) / $product['old_price'] * 100); ?>%</div>
                <?php endif; ?>
                
                <div class="product-img" style="background-image: url('assets/images/products/<?php echo htmlspecialchars($product['image'] ?? 'default.jpg'); ?>');"></div>
                <div class="product-actions">
                    <button class="quick-view-btn" title="Xem nhanh"><i class="fas fa-search"></i></button>
                    <button class="compare-btn" title="So sánh"><i class="fas fa-exchange-alt"></i></button>
                    <button class="wishlist-btn" title="Yêu thích"><i class="far fa-heart"></i></button>
                </div>
                <div class="product-info">
                    <div class="product-vendor"><?php echo htmlspecialchars($product['category_name']); ?></div>
                    <div class="product-title"><?php echo htmlspecialchars($product['name']); ?></div>
                    <div class="product-price">
                        <span class="current-price"><?php echo number_format($product['price'], 0, ',', '.'); ?>đ</span>
                        <?php if ($product['old_price'] > 0): ?>
                            <span class="old-price"><?php echo number_format($product['old_price'], 0, ',', '.'); ?>đ</span>
                        <?php endif; ?>
                    </div>
                    <div class="product-rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="far fa-star"></i>
                        <span>(<?php echo rand(5, 30); ?> đánh giá)</span>
                    </div>
                    <div class="product-footer">
                        <button class="add-to-cart">Thêm vào giỏ</button>
                        <button class="wishlist-btn"><i class="far fa-heart"></i></button>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
    
    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <div class="pagination animate-on-scroll">
        <?php if ($page > 1): ?>
            <a href="?id=<?php echo $category_id; ?>&price=<?php echo isset($_GET['price']) ? $_GET['price'] : ''; ?>&sort=<?php echo isset($_GET['sort']) ? $_GET['sort'] : ''; ?>&page=<?php echo $page - 1; ?>" class="page-btn"><i class="fas fa-chevron-left"></i></a>
        <?php else: ?>
            <button class="page-btn disabled"><i class="fas fa-chevron-left"></i></button>
        <?php endif; ?>
        
        <?php 
        // Hiển thị số trang
        $start_page = max(1, $page - 2);
        $end_page = min($total_pages, $page + 2);
        
        if ($start_page > 1) {
            echo '<a href="?id=' . $category_id . '&price=' . (isset($_GET['price']) ? $_GET['price'] : '') . '&sort=' . (isset($_GET['sort']) ? $_GET['sort'] : '') . '&page=1" class="page-btn">1</a>';
            if ($start_page > 2) echo '<span class="page-btn">...</span>';
        }
        
        for ($i = $start_page; $i <= $end_page; $i++) {
            if ($i == $page) {
                echo '<button class="page-btn active">' . $i . '</button>';
            } else {
                echo '<a href="?id=' . $category_id . '&price=' . (isset($_GET['price']) ? $_GET['price'] : '') . '&sort=' . (isset($_GET['sort']) ? $_GET['sort'] : '') . '&page=' . $i . '" class="page-btn">' . $i . '</a>';
            }
        }
        
        if ($end_page < $total_pages) {
            if ($end_page < $total_pages - 1) echo '<span class="page-btn">...</span>';
            echo '<a href="?id=' . $category_id . '&price=' . (isset($_GET['price']) ? $_GET['price'] : '') . '&sort=' . (isset($_GET['sort']) ? $_GET['sort'] : '') . '&page=' . $total_pages . '" class="page-btn">' . $total_pages . '</a>';
        }
        ?>
        
        <?php if ($page < $total_pages): ?>
            <a href="?id=<?php echo $category_id; ?>&price=<?php echo isset($_GET['price']) ? $_GET['price'] : ''; ?>&sort=<?php echo isset($_GET['sort']) ? $_GET['sort'] : ''; ?>&page=<?php echo $page + 1; ?>" class="page-btn"><i class="fas fa-chevron-right"></i></a>
        <?php else: ?>
            <button class="page-btn disabled"><i class="fas fa-chevron-right"></i></button>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <?php } else { ?>
        <div class="empty-state animate-on-scroll">
            <img src="assets/images/no-results.png" alt="No results">
            <p>Không có sản phẩm nào trong danh mục này.</p>
            <a href="index.php" class="btn">Quay lại trang chủ</a>
        </div>
    <?php } ?>
</div>

<?php include('includes/footer.php'); ?>

<script>
// Apply custom price filter
document.getElementById('apply-price-filter').addEventListener('click', function() {
    const priceFrom = document.getElementById('price-from').value;
    const priceTo = document.getElementById('price-to').value;
    let url = '?id=<?php echo $category_id; ?>';
    
    if (priceFrom || priceTo) {
        url += '&custom_price=1';
        if (priceFrom) url += '&from=' + priceFrom;
        if (priceTo) url += '&to=' + priceTo;
    }
    
    window.location.href = url;
});

// Color filter
const colorOptions = document.querySelectorAll('.color-option');
colorOptions.forEach(option => {
    option.addEventListener('click', () => {
        option.classList.toggle('active');
        // Apply color filter logic here
    });
});

// Size filter
const sizeOptions = document.querySelectorAll('.size-option');
sizeOptions.forEach(option => {
    option.addEventListener('click', () => {
        sizeOptions.forEach(opt => opt.classList.remove('active'));
        option.classList.add('active');
        // Apply size filter logic here
    });
});

// View toggle
const gridView = document.getElementById('grid-view');
const listView = document.getElementById('list-view');
const productsGrid = document.getElementById('products-grid');

gridView.addEventListener('click', () => {
    gridView.classList.add('active');
    listView.classList.remove('active');
    productsGrid.classList.remove('products-list');
    productsGrid.classList.add('products-grid');
});

listView.addEventListener('click', () => {
    listView.classList.add('active');
    gridView.classList.remove('active');
    productsGrid.classList.remove('products-grid');
    productsGrid.classList.add('products-list');
    
    // Convert to list view
    const products = productsGrid.querySelectorAll('.product');
    productsGrid.innerHTML = '';
    productsGrid.classList.add('products-list');
    
    products.forEach(product => {
        const productImg = product.querySelector('.product-img').style.backgroundImage;
        const productTitle = product.querySelector('.product-title').textContent;
        const productPrice = product.querySelector('.current-price').textContent;
        const productRating = product.querySelector('.product-rating').innerHTML;
        
        const listItem = document.createElement('div');
        listItem.className = 'product-list-item';
        
        listItem.innerHTML = `
            <div class="product-list-img" style="background-image: ${productImg}"></div>
            <div class="product-list-content">
                <div class="product-list-header">
                    <div class="product-list-title">${productTitle}</div>
                    <div class="product-list-rating">${productRating}</div>
                </div>
                <div class="product-list-description">
                    Mô tả ngắn về sản phẩm. Chất liệu cao cấp, thiết kế hiện đại, phù hợp với nhiều phong cách khác nhau.
                </div>
                <div class="product-list-features">
                    <div class="product-list-feature">
                        <i class="fas fa-truck"></i>
                        <span>Miễn phí vận chuyển</span>
                    </div>
                    <div class="product-list-feature">
                        <i class="fas fa-shield-alt"></i>
                        <span>Bảo hành 12 tháng</span>
                    </div>
                </div>
                <div class="product-list-footer">
                    <div class="product-list-price">${productPrice}</div>
                    <div class="product-list-actions">
                        <button class="add-to-cart">Thêm vào giỏ</button>
                        <button class="wishlist-btn"><i class="far fa-heart"></i></button>
                    </div>
                </div>
            </div>
        `;
        
        productsGrid.appendChild(listItem);
    });
    
    // Re-attach event listeners
    attachEventListeners();
});

// Re-attach event listeners after dynamic content changes
function attachEventListeners() {
    // Re-attach add to cart buttons
    const newAddToCartBtns = document.querySelectorAll('.add-to-cart');
    newAddToCartBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            // Add loading state
            this.classList.add('loading');
            this.disabled = true;
            
            // Simulate API call
            setTimeout(() => {
                // Remove loading state
                this.classList.remove('loading');
                this.disabled = false;
                
                // Show success message
                showToast('success', 'Đã thêm sản phẩm vào giỏ hàng!');
                
                // Reset button text
                this.textContent = 'Đã thêm';
                this.style.background = 'var(--success-color)';
                
                setTimeout(() => {
                    this.textContent = 'Thêm vào giỏ';
                    this.style.background = '';
                }, 2000);
            }, 800);
        });
    });
    
    // Re-attach wishlist buttons
    const newWishlistBtns = document.querySelectorAll('.wishlist-btn');
    newWishlistBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            btn.classList.toggle('active');
            const icon = btn.querySelector('i');
            
            if (btn.classList.contains('active')) {
                icon.classList.remove('far');
                icon.classList.add('fas');
                showToast('success', 'Đã thêm vào danh sách yêu thích!');
            } else {
                icon.classList.remove('fas');
                icon.classList.add('far');
                showToast('success', 'Đã xóa khỏi danh sách yêu thích!');
            }
        });
    });
}
</script>