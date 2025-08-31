<?php
require_once 'includes/autoload.php';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category_id = isset($_GET['category']) ? intval($_GET['category']) : 0;
$min_price = isset($_GET['min_price']) ? floatval($_GET['min_price']) : 0;
$max_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : 0;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'relevance';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;

$results = [];
$total_results = 0;

if (!empty($search)) {
    // Xây dựng câu query tìm kiếm
    $where_conditions = ["p.status = 'active'"];
    $params = [];
    $param_types = "";
    
    // Tìm kiếm theo từ khóa
    $search_terms = explode(' ', $search);
    $search_conditions = [];
    foreach ($search_terms as $term) {
        if (strlen($term) > 1) {
            $search_conditions[] = "(p.name LIKE ? OR p.description LIKE ? OR p.tags LIKE ?)";
            $term_param = "%$term%";
            $params[] = $term_param;
            $params[] = $term_param;
            $params[] = $term_param;
            $param_types .= "sss";
        }
    }
    
    if (!empty($search_conditions)) {
        $where_conditions[] = "(" . implode(" OR ", $search_conditions) . ")";
    }
    
    // Lọc theo danh mục
    if ($category_id > 0) {
        $where_conditions[] = "p.category_id = ?";
        $params[] = $category_id;
        $param_types .= "i";
    }
    
    // Lọc theo giá
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
    
    // Query tìm kiếm
    $search_query = "
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
            $search_query .= "p.price ASC";
            break;
        case 'price_high':
            $search_query .= "p.price DESC";
            break;
        case 'rating':
            $search_query .= "avg_rating DESC";
            break;
        case 'newest':
            $search_query .= "p.created_at DESC";
            break;
        case 'popular':
            $search_query .= "p.sold_count DESC";
            break;
        default:
            $search_query .= "p.name ASC";
    }
    
    $search_query .= " LIMIT ? OFFSET ?";
    $params[] = $per_page;
    $params[] = $offset;
    $param_types .= "ii";
    
    $search_stmt = $conn->prepare($search_query);
    if (!empty($params)) {
        $search_stmt->bind_param($param_types, ...$params);
    }
    $search_stmt->execute();
    $results = $search_stmt->get_result();
    
    // Đếm tổng số kết quả
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
    $total_results = $count_stmt->get_result()->fetch_assoc()['total'];
}

$total_pages = ceil($total_results / $per_page);

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

$page_title = 'Tìm kiếm: ' . htmlspecialchars($search);
include('includes/header.php');
?>

<div class="container">
    <div class="search-page">
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-search"></i> Kết quả tìm kiếm
            </h1>
            
            <!-- Form tìm kiếm -->
            <form method="GET" action="" class="search-form-advanced">
                <div class="search-input-group">
                    <input type="text" name="search" placeholder="Nhập từ khóa tìm kiếm..." 
                           value="<?php echo htmlspecialchars($search); ?>" required>
                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i> Tìm kiếm
                    </button>
                </div>
            </form>
        </div>
        
        <?php if (!empty($search)): ?>
            <div class="search-results-header">
                <p>
                    Tìm thấy <strong><?php echo $total_results; ?></strong> kết quả 
                    cho từ khóa "<strong><?php echo htmlspecialchars($search); ?></strong>"
                </p>
            </div>
            
            <?php if ($total_results > 0): ?>
                <div class="search-container">
                    <!-- Sidebar filter -->
                    <div class="search-sidebar">
                        <div class="filter-section">
                            <h3>Bộ lọc tìm kiếm</h3>
                            
                            <!-- Danh mục -->
                            <div class="filter-group">
                                <h4>Danh mục</h4>
                                <form method="GET" action="">
                                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                                    <?php if ($min_price > 0): ?>
                                        <input type="hidden" name="min_price" value="<?php echo $min_price; ?>">
                                    <?php endif; ?>
                                    <?php if ($max_price > 0): ?>
                                        <input type="hidden" name="max_price" value="<?php echo $max_price; ?>">
                                    <?php endif; ?>
                                    <?php if (!empty($_GET['sort'])): ?>
                                        <input type="hidden" name="sort" value="<?php echo htmlspecialchars($_GET['sort']); ?>">
                                    <?php endif; ?>
                                    
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
                                </form>
                            </div>
                            
                            <!-- Khoảng giá -->
                            <div class="filter-group">
                                <h4>Khoảng giá</h4>
                                <form method="GET" action="" class="price-filter">
                                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
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
                                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
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
                                        <option value="relevance" <?php echo $sort == 'relevance' ? 'selected' : ''; ?>>Liên quan nhất</option>
                                        <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Mới nhất</option>
                                        <option value="price_low" <?php echo $sort == 'price_low' ? 'selected' : ''; ?>>Giá tăng dần</option>
                                        <option value="price_high" <?php echo $sort == 'price_high' ? 'selected' : ''; ?>>Giá giảm dần</option>
                                        <option value="rating" <?php echo $sort == 'rating' ? 'selected' : ''; ?>>Đánh giá cao nhất</option>
                                        <option value="popular" <?php echo $sort == 'popular' ? 'selected' : ''; ?>>Bán chạy nhất</option>
                                    </select>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Kết quả tìm kiếm -->
                    <div class="search-results">
                        <div class="products-grid">
                            <?php while ($product = $results->fetch_assoc()): ?>
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
                                            <a href="category.php?id=<?php echo $product['category_id']; ?>">
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
                    </div>
                </div>
            <?php else: ?>
                <div class="no-results">
                    <div class="no-results-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3>Không tìm thấy kết quả</h3>
                    <p>Không có sản phẩm nào phù hợp với từ khóa tìm kiếm của bạn.</p>
                    <div class="search-suggestions">
                        <h4>Gợi ý tìm kiếm:</h4>
                        <ul>
                            <li>Kiểm tra lại chính tả từ khóa</li>
                            <li>Thử sử dụng từ khóa khác</li>
                            <li>Sử dụng từ khóa tổng quát hơn</li>
                            <li>Bỏ bớt một số từ khóa</li>
                        </ul>
                    </div>
                    <a href="products.php" class="btn btn-primary">
                        <i class="fas fa-shopping-bag"></i> Xem tất cả sản phẩm
                    </a>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="search-empty">
                <div class="search-empty-icon">
                    <i class="fas fa-search"></i>
                </div>
                <h3>Nhập từ khóa để tìm kiếm</h3>
                <p>Hãy nhập từ khóa vào ô tìm kiếm phía trên để tìm sản phẩm bạn muốn.</p>
            </div>
        <?php endif; ?>
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