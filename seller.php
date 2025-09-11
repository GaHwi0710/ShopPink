<?php
// seller.php
// Trang quản lý cho người bán
require_once 'config.php';
// Kiểm tra người dùng đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
// Kiểm tra vai trò người dùng
if ($_SESSION['user_role'] !== 'seller') {
    header('Location: index.php');
    exit;
}
// Xử lý các hành động
$message = '';
$message_type = '';
// Thêm sản phẩm mới
if (isset($_POST['add_product'])) {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $price = isset($_POST['price']) ? (float)$_POST['price'] : 0;
    $stock = isset($_POST['stock']) ? (int)$_POST['stock'] : 0;
    $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
    
    // Xử lý upload ảnh
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file_name = $_FILES['image']['name'];
        $file_tmp = $_FILES['image']['tmp_name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        // Kiểm tra định dạng file
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($file_ext, $allowed_extensions)) {
            // Tạo tên file mới
            $new_file_name = uniqid() . '.' . $file_ext;
            $upload_path = 'assets/assets/images/products/' . $new_file_name;
            
            // Di chuyển file đến thư mục đích
            if (move_uploaded_file($file_tmp, $upload_path)) {
                $image = $new_file_name;
            } else {
                $message = 'Không thể upload ảnh sản phẩm';
                $message_type = 'error';
            }
        } else {
            $message = 'Định dạng ảnh không hợp lệ. Chỉ chấp nhận JPG, JPEG, PNG, GIF';
            $message_type = 'error';
        }
    }
    
    if (empty($message)) {
        if (empty($name) || $price <= 0 || $stock < 0 || $category_id <= 0) {
            $message = 'Vui lòng điền đầy đủ thông tin sản phẩm';
            $message_type = 'error';
        } else {
            try {
                $stmt = $conn->prepare("INSERT INTO products (name, description, price, stock, category_id, image) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $description, $price, $stock, $category_id, $image]);
                
                $message = 'Thêm sản phẩm thành công';
                $message_type = 'success';
            } catch (PDOException $e) {
                $message = 'Lỗi: ' . $e->getMessage();
                $message_type = 'error';
            }
        }
    }
}
// Cập nhật sản phẩm
if (isset($_POST['update_product'])) {
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $price = isset($_POST['price']) ? (float)$_POST['price'] : 0;
    $stock = isset($_POST['stock']) ? (int)$_POST['stock'] : 0;
    $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
    
    if ($product_id <= 0 || empty($name) || $price <= 0 || $stock < 0 || $category_id <= 0) {
        $message = 'Vui lòng điền đầy đủ thông tin sản phẩm';
        $message_type = 'error';
    } else {
        try {
            // Xử lý upload ảnh mới nếu có
            $update_image = false;
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $file_name = $_FILES['image']['name'];
                $file_tmp = $_FILES['image']['tmp_name'];
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                
                // Kiểm tra định dạng file
                $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
                if (in_array($file_ext, $allowed_extensions)) {
                    // Tạo tên file mới
                    $new_file_name = uniqid() . '.' . $file_ext;
                    $upload_path = 'assets/assets/images/products/' . $new_file_name;
                    
                    // Di chuyển file đến thư mục đích
                    if (move_uploaded_file($file_tmp, $upload_path)) {
                        // Lấy tên ảnh cũ để xóa
                        $stmt = $conn->prepare("SELECT image FROM products WHERE id = ?");
                        $stmt->execute([$product_id]);
                        $old_image = $stmt->fetchColumn();
                        
                        // Xóa ảnh cũ nếu tồn tại
                        if ($old_image && file_exists('assets/assets/images/products/' . $old_image)) {
                            unlink('assets/assets/images/products/' . $old_image);
                        }
                        
                        $update_image = true;
                        $image = $new_file_name;
                    } else {
                        $message = 'Không thể upload ảnh sản phẩm';
                        $message_type = 'error';
                    }
                } else {
                    $message = 'Định dạng ảnh không hợp lệ. Chỉ chấp nhận JPG, JPEG, PNG, GIF';
                    $message_type = 'error';
                }
            }
            
            if (empty($message)) {
                if ($update_image) {
                    $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ?, stock = ?, category_id = ?, image = ? WHERE id = ?");
                    $stmt->execute([$name, $description, $price, $stock, $category_id, $image, $product_id]);
                } else {
                    $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ?, stock = ?, category_id = ? WHERE id = ?");
                    $stmt->execute([$name, $description, $price, $stock, $category_id, $product_id]);
                }
                
                $message = 'Cập nhật sản phẩm thành công';
                $message_type = 'success';
            }
        } catch (PDOException $e) {
            $message = 'Lỗi: ' . $e->getMessage();
            $message_type = 'error';
        }
    }
}
// Xóa sản phẩm
if (isset($_GET['action']) && $_GET['action'] === 'delete_product' && isset($_GET['id'])) {
    $product_id = (int)$_GET['id'];
    
    if ($product_id > 0) {
        try {
            // Lấy tên ảnh để xóa
            $stmt = $conn->prepare("SELECT image FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            $image = $stmt->fetchColumn();
            
            // Xóa sản phẩm
            $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            
            // Xóa ảnh nếu tồn tại
            if ($image && file_exists('assets/assets/images/products/' . $image)) {
                unlink('assets/assets/images/products/' . $image);
            }
            
            $message = 'Xóa sản phẩm thành công';
            $message_type = 'success';
        } catch (PDOException $e) {
            $message = 'Lỗi: ' . $e->getMessage();
            $message_type = 'error';
        }
    }
    
    header("Location: seller.php?message=" . urlencode($message) . "&type=" . $message_type);
    exit;
}
// Cập nhật trạng thái đơn hàng
if (isset($_POST['update_order_status'])) {
    $order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
    $status = isset($_POST['status']) ? $_POST['status'] : '';
    
    if ($order_id > 0 && in_array($status, ['pending', 'completed', 'cancelled'])) {
        try {
            $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $stmt->execute([$status, $order_id]);
            
            $message = 'Cập nhật trạng thái đơn hàng thành công';
            $message_type = 'success';
        } catch (PDOException $e) {
            $message = 'Lỗi: ' . $e->getMessage();
            $message_type = 'error';
        }
    } else {
        $message = 'Dữ liệu không hợp lệ';
        $message_type = 'error';
    }
}
// Lấy thông báo từ URL
$message = isset($_GET['message']) ? $_GET['message'] : $message;
$message_type = isset($_GET['type']) ? $_GET['type'] : $message_type;
// Lấy danh sách danh mục
$stmt = $conn->query("SELECT * FROM categories");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Lấy danh sách sản phẩm
$stmt = $conn->query("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Lấy danh sách đơn hàng
$stmt = $conn->query("
    SELECT o.*, u.name as user_name 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC
");
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Lấy thông tin sản phẩm để chỉnh sửa
$edit_product = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit_product' && isset($_GET['id'])) {
    $product_id = (int)$_GET['id'];
    
    if ($product_id > 0) {
        $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $edit_product = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
// Kiểm tra người dùng đã đăng nhập chưa
$is_logged_in = isset($_SESSION['user_id']);
$user_role = $is_logged_in ? $_SESSION['user_role'] : '';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý - ShopPink</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">
                <a href="index.php">ShopPink</a>
            </div>
            <nav>
                <ul>
                    <li><a href="index.php">Trang chủ</a></li>
                    <li><a href="products.php">Sản phẩm</a></li>
                    <?php if ($is_logged_in): ?>
                        <?php if ($user_role === 'seller'): ?>
                            <li><a href="seller.php" class="active">Quản lý</a></li>
                            <li><a href="report.php">Báo cáo</a></li>
                        <?php endif; ?>
                        <li><a href="cart.php"><i class="fas fa-shopping-cart"></i> Giỏ hàng</a></li>
                        <li><a href="logout.php">Đăng xuất</a></li>
                    <?php else: ?>
                        <li><a href="login.php">Đăng nhập</a></li>
                        <li><a href="register.php">Đăng ký</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
    <main>
        <section class="seller-dashboard">
            <div class="container">
                <h1>Trang quản lý</h1>
                
                <?php if (!empty($message)): ?>
                    <div class="alert alert-<?php echo $message_type; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>
                
                <div class="dashboard-tabs">
                    <ul class="tabs">
                        <li class="tab active" data-tab="products">Sản phẩm</li>
                        <li class="tab" data-tab="orders">Đơn hàng</li>
                    </ul>
                    
                    <div class="tab-content active" id="products-tab">
                        <div class="tab-header">
                            <h2>Quản lý sản phẩm</h2>
                            <button class="btn btn-primary" id="add-product-btn">Thêm sản phẩm mới</button>
                        </div>
                        
                        <div class="product-form-container" id="add-product-form" style="display: none;">
                            <h3>Thêm sản phẩm mới</h3>
                            <form action="seller.php" method="post" enctype="multipart/form-data">
                                <div class="form-group">
                                    <label for="name">Tên sản phẩm</label>
                                    <input type="text" id="name" name="name" required>
                                </div>
                                <div class="form-group">
                                    <label for="description">Mô tả</label>
                                    <textarea id="description" name="description" rows="4"></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="price">Giá (VNĐ)</label>
                                    <input type="number" id="price" name="price" min="0" step="1000" required>
                                </div>
                                <div class="form-group">
                                    <label for="stock">Tồn kho</label>
                                    <input type="number" id="stock" name="stock" min="0" required>
                                </div>
                                <div class="form-group">
                                    <label for="category_id">Danh mục</label>
                                    <select id="category_id" name="category_id" required>
                                        <option value="">-- Chọn danh mục --</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="image">Hình ảnh</label>
                                    <input type="file" id="image" name="image" accept="image/*">
                                </div>
                                <div class="form-group">
                                    <button type="submit" name="add_product" class="btn btn-primary">Thêm sản phẩm</button>
                                    <button type="button" class="btn" id="cancel-add-product">Hủy</button>
                                </div>
                            </form>
                        </div>
                        
                        <?php if ($edit_product): ?>
                            <div class="product-form-container">
                                <h3>Chỉnh sửa sản phẩm</h3>
                                <form action="seller.php" method="post" enctype="multipart/form-data">
                                    <input type="hidden" name="product_id" value="<?php echo $edit_product['id']; ?>">
                                    <div class="form-group">
                                        <label for="edit_name">Tên sản phẩm</label>
                                        <input type="text" id="edit_name" name="name" value="<?php echo htmlspecialchars($edit_product['name']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="edit_description">Mô tả</label>
                                        <textarea id="edit_description" name="description" rows="4"><?php echo htmlspecialchars($edit_product['description']); ?></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for="edit_price">Giá (VNĐ)</label>
                                        <input type="number" id="edit_price" name="price" min="0" step="1000" value="<?php echo $edit_product['price']; ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="edit_stock">Tồn kho</label>
                                        <input type="number" id="edit_stock" name="stock" min="0" value="<?php echo $edit_product['stock']; ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="edit_category_id">Danh mục</label>
                                        <select id="edit_category_id" name="category_id" required>
                                            <?php foreach ($categories as $category): ?>
                                                <option value="<?php echo $category['id']; ?>" <?php echo $category['id'] == $edit_product['category_id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($category['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="edit_image">Hình ảnh</label>
                                        <div class="current-image">
                                            <img src="assets/images/products/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>"> 
                                            <p>Ảnh hiện tại</p>
                                        </div>
                                        <input type="file" id="edit_image" name="image" accept="image/*">
                                        <p class="help-text">Để trống nếu không muốn thay đổi ảnh</p>
                                    </div>
                                    <div class="form-group">
                                        <button type="submit" name="update_product" class="btn btn-primary">Cập nhật sản phẩm</button>
                                        <a href="seller.php" class="btn">Hủy</a>
                                    </div>
                                </form>
                            </div>
                        <?php endif; ?>
                        
                        <div class="products-table">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Hình ảnh</th>
                                        <th>Tên sản phẩm</th>
                                        <th>Danh mục</th>
                                        <th>Giá</th>
                                        <th>Tồn kho</th>
                                        <th>Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($products as $product): ?>
                                        <tr>
                                            <td><?php echo $product['id']; ?></td>
                                            <td>
                                                <div class="seller-product-image">
                                                    <img src="assets/images/products/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>"> 
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                                            <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                            <td><?php echo number_format($product['price'], 0, ',', '.'); ?>₫</td>
                                            <td><?php echo $product['stock']; ?></td>
                                            <td class="actions">
                                                <a href="seller.php?action=edit_product&id=<?php echo $product['id']; ?>" class="btn-edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="seller.php?action=delete_product&id=<?php echo $product['id']; ?>" class="btn-delete" onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này?');">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="tab-content" id="orders-tab">
                        <div class="tab-header">
                            <h2>Quản lý đơn hàng</h2>
                        </div>
                        
                        <div class="orders-table">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Khách hàng</th>
                                        <th>Ngày đặt</th>
                                        <th>Tổng tiền</th>
                                        <th>Trạng thái</th>
                                        <th>Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td>#<?php echo $order['id']; ?></td>
                                            <td><?php echo htmlspecialchars($order['user_name']); ?></td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                                            <td><?php echo number_format($order['total'], 0, ',', '.'); ?>₫</td>
                                            <td>
                                                <form action="seller.php" method="post">
                                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                    <select name="status" onchange="this.form.submit()">
                                                        <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Chờ xử lý</option>
                                                        <option value="completed" <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>>Hoàn thành</option>
                                                        <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Đã hủy</option>
                                                    </select>
                                                    <input type="hidden" name="update_order_status" value="1">
                                                </form>
                                            </td>
                                            <td>
                                                <a href="javascript:void(0);" class="btn-view-order" data-order-id="<?php echo $order['id']; ?>">
                                                    <i class="fas fa-eye"></i> Xem chi tiết
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
    <!-- Modal xem chi tiết đơn hàng -->
    <div id="order-detail-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Chi tiết đơn hàng</h3>
                <span class="close-modal">&times;</span>
            </div>
            <div class="modal-body" id="order-detail-content">
                <!-- Nội dung sẽ được tải bằng AJAX -->
            </div>
        </div>
    </div>
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>ShopPink</h3>
                    <p>Nơi mua sắm trực tuyến đáng tin cậy với nhiều sản phẩm chất lượng.</p>
                </div>
                <div class="footer-section">
                    <h3>Liên kết nhanh</h3>
                    <ul>
                        <li><a href="index.php">Trang chủ</a></li>
                        <li><a href="products.php">Sản phẩm</a></li>
                        <li><a href="login.php">Đăng nhập</a></li>
                        <li><a href="register.php">Đăng ký</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Liên hệ</h3>
                    <p>Email: contact@shoppink.com</p>
                    <p>Điện thoại: 0123 456 789</p>
                    <p>Địa chỉ: Hà Nội</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> ShopPink.</p>
            </div>
        </div>
    </footer>
    <script src="assets/js/main.js"></script>
    <script>
        // Xử lý tabs
        document.addEventListener('DOMContentLoaded', function() {
            const tabs = document.querySelectorAll('.tab');
            const tabContents = document.querySelectorAll('.tab-content');
            
            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    const tabId = this.getAttribute('data-tab');
                    
                    // Remove active class from all tabs and contents
                    tabs.forEach(t => t.classList.remove('active'));
                    tabContents.forEach(tc => tc.classList.remove('active'));
                    
                    // Add active class to clicked tab and corresponding content
                    this.classList.add('active');
                    document.getElementById(tabId + '-tab').classList.add('active');
                });
            });
            
            // Xử lý form thêm sản phẩm
            const addProductBtn = document.getElementById('add-product-btn');
            const addProductForm = document.getElementById('add-product-form');
            const cancelAddProduct = document.getElementById('cancel-add-product');
            
            if (addProductBtn) {
                addProductBtn.addEventListener('click', function() {
                    addProductForm.style.display = 'block';
                });
            }
            
            if (cancelAddProduct) {
                cancelAddProduct.addEventListener('click', function() {
                    addProductForm.style.display = 'none';
                });
            }
            
            // Xử lý modal xem chi tiết đơn hàng
            const modal = document.getElementById('order-detail-modal');
            const closeModal = document.querySelector('.close-modal');
            const viewOrderButtons = document.querySelectorAll('.btn-view-order');
            
            viewOrderButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const orderId = this.getAttribute('data-order-id');
                    
                    // Tải chi tiết đơn hàng bằng AJAX
                    fetch(`get_order_details.php?order_id=${orderId}`)
                        .then(response => response.text())
                        .then(html => {
                            document.getElementById('order-detail-content').innerHTML = html;
                            modal.style.display = 'block';
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Không thể tải chi tiết đơn hàng');
                        });
                });
            });
            
            if (closeModal) {
                closeModal.addEventListener('click', function() {
                    modal.style.display = 'none';
                });
            }
            
            window.addEventListener('click', function(event) {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>