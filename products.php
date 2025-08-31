<?php
require_once 'includes/autoload.php';

// Lấy tham số tìm kiếm và lọc
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category_id = isset($_GET['category']) ? intval($_GET['category']) : 0;
$min_price = isset($_GET['min_price']) ? floatval($_GET['min_price']) : 0;
$max_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : 0;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;

// Xây dựng câu query
$where_conditions = ["p.status = 'active'"];
$params = [];
$param_types = "";

if (!empty($search)) {
    $where_conditions[] = "(p.name LIKE ? OR p.description LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $param_types .= "ss";
}

if ($category_id > 0) {
    $where_conditions[] = "p.category_id = ?";
    $params[] = $category_id;
    $param_types .= "i";
}

if ($min_price > 0) {
    $where_conditions[] = "p.price >= ?";
    $params[] = $min_price;
    $param_types .= "d";
}

if ($max_price > 0) {
    $where_conditions[] = "p.price <= ?";
    $params[] = $max_price;
    $param_types .= "d";
}

$where_clause = implode(" AND ", $where_conditions);

// Query sản phẩm
$products_query = "
    SELECT p.*, c.name as category_name, 
           COALESCE(AVG(pr.rating), 0) as avg_rating,
           COUNT(pr.id) as review_count
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    LEFT JOIN product_reviews pr ON p.id = pr.product_id 
    WHERE $where_clause 
    GROUP BY p.id 
    ORDER BY ";

switch ($sort) {
    case 'price_low':
        $products_query .= "p.price ASC";
        break;
    case 'price_high':
        $products_query .= "p.price DESC";
        break;
    case 'rating':
        $products_query .= "avg_rating DESC";
        break;
    case 'popular':
        $products_query .= "p.sold_count DESC";
        break;
    default:
        $products_query .= "p.created_at DESC";
}

$products_query .= " LIMIT ? OFFSET ?";
$params[] = $per_page;
$params[] = $offset;
$param_types .= "ii";

$products_stmt = $conn->prepare($products_query);
if (!empty($params)) {
    $products_stmt->bind_param($param_types, ...$params);
}
$products_stmt->execute();
$products = $products_stmt->get_result();

// Đếm tổng số sản phẩm để phân trang
$count_query = "
    SELECT COUNT(DISTINCT p.id) as total 
    FROM products p 
    WHERE $where_clause
";

$count_stmt = $conn->prepare($count_query);
if (!empty(array_slice($params, 0, -2))) {
    $count_stmt->bind_param(substr($param_types, 0, -2), ...array_slice($params, 0, -2));
}
$count_stmt->execute();
$total_products = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_products / $per_page);

// Lấy danh mục cho filter
$categories_stmt = $conn->prepare("
    SELECT c.*, COUNT(p.id) as product_count 
    FROM categories c 
    LEFT JOIN products p ON c.id = p.category_id AND p.status = 'active'
    WHERE c.status = 'active' 
    GROUP BY c.id 
    ORDER BY c.name
");
$categories_stmt->execute();
$categories = $categories_stmt->get_result();

include('includes/header.php');
?>

<div class="container">
    <div class="products-page">
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-shopping-bag"></i> Tất cả sản phẩm
            </h1>
            
            <!-- Tìm kiếm -->
            <form method="GET" action="" class="search-form">
                <div class="search-input-group">
                    <input type="text" name="search" placeholder="Tìm kiếm sản phẩm..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
        </div>
        
        <div class="products-container">
            <!-- Sidebar filter -->
            <div class="products-sidebar">
                <div class="filter-section">
                    <h3>Bộ lọc</h3>
                    
                    <!-- Danh mục -->
                    <div class="filter-group">
                        <h4>Danh mục</h4>
                        <div class="filter-options">
                            <label class="filter-option">
                                <input type="radio" name="category" value="0" 
                                       <?php echo $category_id == 0 ? 'checked' : ''; ?> 
                                       onchange="this.form.submit()">
                                <span>Tất cả danh mục</span>
                            </label>
                            <?php while ($category = $categories->fetch_assoc()): ?>
                                <label class="filter-option">
                                    <input type="radio" name="category" value="<?php echo $category['id']; ?>" 
                                           <?php echo $category_id == $category['id'] ? 'checked' : ''; ?> 
                                           onchange="this.form.submit()">
                                    <span><?php echo htmlspecialchars($category['name']); ?></span>
                                    <small>(<?php echo $category['product_count']; ?>)</small>
                                </label>
                            <?php endwhile; ?>
                        </div>
                    </div>
                    
                    <!-- Khoảng giá -->
                    <div class="filter-group">
                        <h4>Khoảng giá</h4>
                        <form method="GET" action="" class="price-filter">
                            <?php if (!empty($search)): ?>
                                <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                            <?php endif; ?>
                            <?php if ($category_id > 0): ?>
                                <input type="hidden" name="category" value="<?php echo $category_id; ?>">
                            <?php endif; ?>
                            <?php if (!empty($_GET['sort'])): ?>
                                <input type="hidden" name="sort" value="<?php echo htmlspecialchars($_GET['sort']); ?>">
                            <?php endif; ?>
                            
                            <div class="price-inputs">
                                <input type="number" name="min_price" placeholder="Từ" 
                                       value="<?php echo $min_price > 0 ? $min_price : ''; ?>" min="0" step="1000">
                                <span>-</span>
                                <input type="number" name="max_price" placeholder="Đến" 
                                       value="<?php echo $max_price > 0 ? $max_price : ''; ?>" min="0" step="1000">
                            </div>
                            <button type="submit" class="btn btn-outline-primary btn-small">Áp dụng</button>
                        </form>
                    </div>
                    
                    <!-- Sắp xếp -->
                    <div class="filter-group">
                        <h4>Sắp xếp</h4>
                        <form method="GET" action="" class="sort-filter">
                            <?php if (!empty($search)): ?>
                                <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                            <?php endif; ?>
                            <?php if ($category_id > 0): ?>
                                <input type="hidden" name="category" value="<?php echo $category_id; ?>">
                            <?php endif; ?>
                            <?php if ($min_price > 0): ?>
                                <input type="hidden" name="min_price" value="<?php echo $min_price; ?>">
                            <?php endif; ?>
                            <?php if ($max_price > 0): ?>
                                <input type="hidden" name="max_price" value="<?php echo $max_price; ?>">
                            <?php endif; ?>
                            
                            <select name="sort" onchange="this.form.submit()">
                                <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Mới nhất</option>
                                <option value="price_low" <?php echo $sort == 'price_low' ? 'selected' : ''; ?>>Giá tăng dần</option>
                                <option value="price_high" <?php echo $sort == 'price_high' ? 'selected' : ''; ?>>Giá giảm dần</option>
                                <option value="rating" <?php echo $sort == 'rating' ? 'selected' : ''; ?>>Đánh giá cao nhất</option>
                                <option value="popular" <?php echo $sort == 'popular' ? 'selected' : ''; ?>>Bán chạy nhất</option>
                            </select>
                        </form>
                    </div>
                    
                    <!-- Xóa bộ lọc -->
                    <?php if (!empty($search) || $category_id > 0 || $min_price > 0 || $max_price > 0): ?>
                        <div class="filter-group">
                            <a href="products.php" class="btn btn-outline-secondary btn-block">
                                <i class="fas fa-times"></i> Xóa bộ lọc
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Danh sách sản phẩm -->
            <div class="products-main">
                <!-- Kết quả tìm kiếm -->
                <div class="search-results">
                    <p>
                        Tìm thấy <strong><?php echo $total_products; ?></strong> sản phẩm
                        <?php if (!empty($search)): ?>
                            cho "<strong><?php echo htmlspecialchars($search); ?></strong>"
                        <?php endif; ?>
                    </p>
                </div>
                
                <!-- Danh sách sản phẩm -->
                <?php if ($products->num_rows > 0): ?>
                    <div class="products-grid">
                        <?php while ($product = $products->fetch_assoc()): ?>
                            <div class="product-card">
                                <div class="product-image">
                                    <a href="product_detail.php?id=<?php echo $product['id']; ?>">
                                        <img src="assets/images/products/<?php echo htmlspecialchars($product['image']); ?>" 
                                             alt="<?php echo htmlspecialchars($product['name']); ?>">
                                    </a>
                                    
                                    <?php if ($product['discount'] > 0): ?>
                                        <div class="discount-badge">
                                            -<?php echo $product['discount']; ?>%
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="product-info">
                                    <h3 class="product-name">
                                        <a href="product_detail.php?id=<?php echo $product['id']; ?>">
                                            <?php echo htmlspecialchars($product['name']); ?>
                                        </a>
                                    </h3>
                                    
                                    <div class="product-category">
                                        <a href="?category=<?php echo $product['category_id']; ?>">
                                            <?php echo htmlspecialchars($product['category_name']); ?>
                                        </a>
                                    </div>
                                    
                                    <div class="product-rating">
                                        <div class="stars">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star <?php echo $i <= $product['avg_rating'] ? 'active' : ''; ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                        <span class="rating-text">
                                            (<?php echo $product['review_count']; ?> đánh giá)
                                        </span>
                                    </div>
                                    
                                    <div class="product-price">
                                        <?php if ($product['discount'] > 0): ?>
                                            <span class="old-price"><?php echo format_price($product['price']); ?></span>
                                            <span class="new-price">
                                                <?php echo format_price($product['price'] * (1 - $product['discount'] / 100)); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="price"><?php echo format_price($product['price']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="product-actions">
                                        <button class="btn btn-primary btn-block add-to-cart-btn" 
                                                onclick="addToCart(<?php echo $product['id']; ?>)">
                                            <i class="fas fa-shopping-cart"></i> Thêm vào giỏ hàng
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                    
                    <!-- Phân trang -->
                    <?php if ($total_pages > 1): ?>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" 
                                   class="page-link">
                                    <i class="fas fa-chevron-left"></i> Trước
                                </a>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" 
                                   class="page-link <?php echo $i == $page ? 'active' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" 
                                   class="page-link">
                                    Sau <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                <?php else: ?>
                    <div class="no-products">
                        <div class="no-products-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <h3>Không tìm thấy sản phẩm</h3>
                        <p>Không có sản phẩm nào phù hợp với tiêu chí tìm kiếm của bạn.</p>
                        <a href="products.php" class="btn btn-primary">
                            <i class="fas fa-shopping-bag"></i> Xem tất cả sản phẩm
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function addToCart(productId) {
    <?php if (is_logged_in()): ?>
        // Nếu đã đăng nhập, thêm vào giỏ hàng
        fetch('customer/cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=add&product_id=' + productId + '&quantity=1'
        })
        .then(response => response.text())
        .then(data => {
            // Reload trang để cập nhật giỏ hàng
            location.reload();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Có lỗi xảy ra khi thêm vào giỏ hàng!');
        });
    <?php else: ?>
        // Nếu chưa đăng nhập, chuyển đến trang đăng nhập
        if (confirm('Bạn cần đăng nhập để thêm sản phẩm vào giỏ hàng. Đăng nhập ngay?')) {
            window.location.href = 'auth/login.php';
        }
    <?php endif; ?>
}
</script>

<?php include('includes/footer.php'); ?>