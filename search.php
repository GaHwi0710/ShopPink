<?php
session_start();
include('includes/config.php');
$query = trim($_GET['query'] ?? '');
// Nếu từ khóa trống
if ($query === '') {
    header("Location: index.php");
    exit();
}
// Tìm kiếm sản phẩm bằng prepared statement
$search = "%" . $query . "%";
$stmt = $conn->prepare("
    SELECT p.*, c.name as category_name 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    WHERE p.name LIKE ? OR p.description LIKE ? 
    ORDER BY p.created_at DESC
");
$stmt->bind_param("ss", $search, $search);
$stmt->execute();
$result = $stmt->get_result();
$products = $result->fetch_all(MYSQLI_ASSOC);
// Lấy các sản phẩm nổi bật để gợi ý (nếu không có kết quả tìm kiếm)
$featured_products = [];
if (empty($products)) {
    $featured_stmt = $conn->prepare("
        SELECT p.*, c.name as category_name 
        FROM products p 
        JOIN categories c ON p.category_id = c.id 
        ORDER BY p.created_at DESC 
        LIMIT 6
    ");
    $featured_stmt->execute();
    $featured_result = $featured_stmt->get_result();
    $featured_products = $featured_result->fetch_all(MYSQLI_ASSOC);
}
?>
<?php include('includes/header.php'); ?>

<div class="container">
    <!-- Search Header -->
    <div class="search-header animate-on-scroll">
        <h1>Kết quả tìm kiếm</h1>
        <div class="search-query">
            <span>Từ khóa: </span>
            <strong>"<?php echo htmlspecialchars($query); ?>"</strong>
        </div>
    </div>
    
    <?php if (!empty($products)) { ?>
        <!-- Search Results -->
        <div class="search-results animate-on-scroll">
            <div class="results-info">
                <p>Tìm thấy <strong><?php echo count($products); ?></strong> sản phẩm phù hợp với từ khóa</p>
            </div>
            
            <!-- Filter and Sort Controls -->
            <div class="filter-sort-container">
                <div class="filter-controls">
                    <label for="category-filter">Danh mục:</label>
                    <select id="category-filter">
                        <option value="">Tất cả</option>
                        <option value="women">Thời trang nữ</option>
                        <option value="men">Thời trang nam</option>
                        <option value="accessories">Phụ kiện</option>
                    </select>
                    
                    <label for="price-filter">Khoảng giá:</label>
                    <select id="price-filter">
                        <option value="">Tất cả</option>
                        <option value="0-500000">Dưới 500.000đ</option>
                        <option value="500000-1000000">500.000đ - 1.000.000đ</option>
                        <option value="1000000-2000000">1.000.000đ - 2.000.000đ</option>
                        <option value="2000000">Trên 2.000.000đ</option>
                    </select>
                </div>
                
                <div class="sort-controls">
                    <label for="sort-products">Sắp xếp:</label>
                    <select id="sort-products">
                        <option value="default">Mặc định</option>
                        <option value="price-asc">Giá: Thấp đến cao</option>
                        <option value="price-desc">Giá: Cao đến thấp</option>
                        <option value="name">Tên: A-Z</option>
                        <option value="newest">Mới nhất</option>
                    </select>
                    
                    <div class="view-toggle">
                        <button class="view-btn active" id="grid-view"><i class="fas fa-th"></i></button>
                        <button class="view-btn" id="list-view"><i class="fas fa-list"></i></button>
                    </div>
                </div>
            </div>
            
            <!-- Products Grid -->
            <div class="products-grid" id="products-grid">
                <?php foreach ($products as $product) { ?>
                    <div class="product" data-category="<?php echo $product['category_id']; ?>" data-price="<?php echo $product['price']; ?>">
                        <?php if (!empty($product['old_price']) && $product['old_price'] > $product['price']): ?>
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
                                <?php if (!empty($product['old_price']) && $product['old_price'] > $product['price']): ?>
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
                                <a href="product_detail.php?id=<?php echo $product['id']; ?>" class="btn">Xem chi tiết</a>
                                <button class="wishlist-btn"><i class="far fa-heart"></i></button>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
            
            <!-- Pagination -->
            <div class="pagination">
                <button class="page-btn disabled"><i class="fas fa-chevron-left"></i></button>
                <button class="page-btn active">1</button>
                <button class="page-btn">2</button>
                <button class="page-btn">3</button>
                <span class="page-btn">...</span>
                <button class="page-btn">10</button>
                <button class="page-btn"><i class="fas fa-chevron-right"></i></button>
            </div>
        </div>
    <?php } else { ?>
        <!-- No Results -->
        <div class="no-results animate-on-scroll">
            <img src="assets/images/no-results.png" alt="Không tìm thấy">
            <h3>Không tìm thấy sản phẩm nào</h3>
            <p>Không tìm thấy sản phẩm nào phù hợp với từ khóa "<strong><?php echo htmlspecialchars($query); ?></strong>"</p>
            <p>Vui lòng thử lại với từ khóa khác</p>
            
            <!-- Search Suggestions -->
            <div class="search-suggestions">
                <h4>Gợi ý tìm kiếm:</h4>
                <div class="suggestion-tags">
                    <a href="search.php?query=áo thun" class="tag">Áo thun</a>
                    <a href="search.php?query=đầm" class="tag">Đầm</a>
                    <a href="search.php?query=giày" class="tag">Giày</a>
                    <a href="search.php?query=túi xách" class="tag">Túi xách</a>
                    <a href="search.php?query=son môi" class="tag">Son môi</a>
                </div>
            </div>
            
            <!-- Featured Products -->
            <div class="featured-products">
                <h4>Sản phẩm nổi bật</h4>
                <div class="products-grid">
                    <?php foreach ($featured_products as $product) { ?>
                        <div class="product" data-category="<?php echo $product['category_id']; ?>" data-price="<?php echo $product['price']; ?>">
                            <div class="product-img" style="background-image: url('assets/images/products/<?php echo htmlspecialchars($product['image'] ?? 'default.jpg'); ?>');"></div>
                            <div class="product-info">
                                <div class="product-vendor"><?php echo htmlspecialchars($product['category_name']); ?></div>
                                <div class="product-title"><?php echo htmlspecialchars($product['name']); ?></div>
                                <div class="product-price">
                                    <span class="current-price"><?php echo number_format($product['price'], 0, ',', '.'); ?>đ</span>
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
                                    <a href="product_detail.php?id=<?php echo $product['id']; ?>" class="btn">Xem chi tiết</a>
                                    <button class="wishlist-btn"><i class="far fa-heart"></i></button>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
            
            <div class="no-results-actions">
                <a href="index.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Về trang chủ
                </a>
                <a href="category.php" class="btn">
                    <i class="fas fa-th-large"></i> Xem tất cả sản phẩm
                </a>
            </div>
        </div>
    <?php } ?>
</div>

<?php include('includes/footer.php'); ?>

<script>
// Filter and sort functionality
document.getElementById('category-filter')?.addEventListener('change', function() {
    filterAndSortProducts();
});

document.getElementById('price-filter')?.addEventListener('change', function() {
    filterAndSortProducts();
});

document.getElementById('sort-products')?.addEventListener('change', function() {
    filterAndSortProducts();
});

function filterAndSortProducts() {
    const categoryValue = document.getElementById('category-filter').value;
    const priceValue = document.getElementById('price-filter').value;
    const sortValue = document.getElementById('sort-products').value;
    const productsGrid = document.getElementById('products-grid');
    const products = productsGrid.querySelectorAll('.product');
    
    // Convert NodeList to Array for easier manipulation
    let productsArray = Array.from(products);
    
    // Filter by category
    if (categoryValue !== '') {
        productsArray = productsArray.filter(product => {
            return product.dataset.category === categoryValue;
        });
    }
    
    // Filter by price
    if (priceValue !== '') {
        productsArray = productsArray.filter(product => {
            const price = parseInt(product.dataset.price);
            
            switch(priceValue) {
                case '0-500000':
                    return price < 500000;
                case '500000-1000000':
                    return price >= 500000 && price < 1000000;
                case '1000000-2000000':
                    return price >= 1000000 && price < 2000000;
                case '2000000':
                    return price >= 2000000;
                default:
                    return true;
            }
        });
    }
    
    // Sort products
    productsArray.sort((a, b) => {
        switch(sortValue) {
            case 'price-asc':
                return parseInt(a.dataset.price) - parseInt(b.dataset.price);
            case 'price-desc':
                return parseInt(b.dataset.price) - parseInt(a.dataset.price);
            case 'name':
                return a.querySelector('.product-title').textContent.localeCompare(b.querySelector('.product-title').textContent);
            case 'newest':
                return productsArray.indexOf(b) - productsArray.indexOf(a);
            default:
                return 0;
        }
    });
    
    // Clear and rebuild products grid
    productsGrid.innerHTML = '';
    productsArray.forEach(product => {
        productsGrid.appendChild(product);
    });
    
    // Add animation class to filtered products
    productsArray.forEach((product, index) => {
        setTimeout(() => {
            product.style.opacity = '0';
            product.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                product.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                product.style.opacity = '1';
                product.style.transform = 'translateY(0)';
            }, 50);
        }, index * 100);
    });
}

// View toggle
const gridView = document.getElementById('grid-view');
const listView = document.getElementById('list-view');
const productsGrid = document.getElementById('products-grid');

gridView?.addEventListener('click', () => {
    gridView.classList.add('active');
    listView.classList.remove('active');
    productsGrid.classList.remove('products-list');
    productsGrid.classList.add('products-grid');
});

listView?.addEventListener('click', () => {
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