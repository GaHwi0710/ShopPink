<?php
require_once BASE_PATH . '/models/Product.php';
require_once BASE_PATH . '/models/Order.php';
require_once BASE_PATH . '/models/User.php';

class SellerController {
    private $productModel;
    private $orderModel;
    private $userModel;
    
    public function __construct() {
        $this->productModel = new Product();
        $this->orderModel = new Order();
        $this->userModel = new User();
    }
    
    // Trang tổng quan của người bán
    public function dashboard() {
        Auth::requireSeller();
        
        $seller = Auth::user();
        
        // Lấy thống kê
        $stats = $this->getSellerStats();
        
        // Lấy đơn hàng gần đây
        $recentOrders = array_slice($this->orderModel->getBySeller(Auth::id()), 0, 5);
        
        // Lấy sản phẩm bán chạy
        $topProducts = $this->productModel->getTopSellingBySeller(Auth::id(), 5);
        
        include BASE_PATH . '/views/layouts/header.php';
        include BASE_PATH . '/views/seller/dashboard.php';
        include BASE_PATH . '/views/layouts/footer.php';
    }
    
    // Quản lý sản phẩm
    public function products() {
        Auth::requireSeller();
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        $products = $this->productModel->getBySeller(Auth::id(), $limit, $offset);
        
        include BASE_PATH . '/views/layouts/header.php';
        include BASE_PATH . '/views/seller/products.php';
        include BASE_PATH . '/views/layouts/footer.php';
    }
    
    // Thêm sản phẩm mới
    public function addProduct() {
        Auth::requireSeller();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'seller_id' => Auth::id(),
                'name' => $_POST['name'] ?? '',
                'description' => $_POST['description'] ?? '',
                'price' => $_POST['price'] ?? 0,
                'stock' => $_POST['stock'] ?? 0,
                'image' => $this->uploadImage()
            ];
            
            if ($this->productModel->create($data)) {
                $_SESSION['success'] = 'Thêm sản phẩm thành công!';
                header('Location: /seller/products');
                exit;
            } else {
                $_SESSION['error'] = 'Thêm sản phẩm thất bại!';
            }
        }
        
        include BASE_PATH . '/views/layouts/header.php';
        include BASE_PATH . '/views/seller/add_product.php';
        include BASE_PATH . '/views/layouts/footer.php';
    }
    
    // Sửa sản phẩm
    public function editProduct($id) {
        Auth::requireSeller();
        
        $product = $this->productModel->getById($id);
        
        if (!$product || $product['seller_id'] != Auth::id()) {
            include BASE_PATH . '/views/404.php';
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'seller_id' => Auth::id(),
                'name' => $_POST['name'] ?? '',
                'description' => $_POST['description'] ?? '',
                'price' => $_POST['price'] ?? 0,
                'stock' => $_POST['stock'] ?? 0,
                'image' => $this->uploadImage($product['image'])
            ];
            
            if ($this->productModel->update($id, $data)) {
                $_SESSION['success'] = 'Cập nhật sản phẩm thành công!';
                header('Location: /seller/products');
                exit;
            } else {
                $_SESSION['error'] = 'Cập nhật sản phẩm thất bại!';
            }
        }
        
        include BASE_PATH . '/views/layouts/header.php';
        include BASE_PATH . '/views/seller/edit_product.php';
        include BASE_PATH . '/views/layouts/footer.php';
    }
    
    // Xóa sản phẩm
    public function deleteProduct($id) {
        Auth::requireSeller();
        
        $product = $this->productModel->getById($id);
        
        if ($product && $product['seller_id'] == Auth::id()) {
            if ($this->productModel->delete($id, Auth::id())) {
                $_SESSION['success'] = 'Xóa sản phẩm thành công!';
            } else {
                $_SESSION['error'] = 'Xóa sản phẩm thất bại!';
            }
        } else {
            $_SESSION['error'] = 'Không thể xóa sản phẩm!';
        }
        
        header('Location: /seller/products');
        exit;
    }
    
    // Quản lý đơn hàng
    public function orders() {
        Auth::requireSeller();
        
        $orders = $this->orderModel->getBySeller(Auth::id());
        
        include BASE_PATH . '/views/layouts/header.php';
        include BASE_PATH . '/views/seller/orders.php';
        include BASE_PATH . '/views/layouts/footer.php';
    }
    
    // Chi tiết đơn hàng
    public function orderDetail($id) {
        Auth::requireSeller();
        
        $order = $this->orderModel->getById($id);
        
        if (!$order) {
            include BASE_PATH . '/views/404.php';
            return;
        }
        
        // Kiểm tra xem đơn hàng có thuộc về người bán này không
        $orderItems = $this->orderModel->getOrderItems($id);
        $belongsToSeller = false;
        
        foreach ($orderItems as $item) {
            $product = $this->productModel->getById($item['product_id']);
            if ($product && $product['seller_id'] == Auth::id()) {
                $belongsToSeller = true;
                break;
            }
        }
        
        if (!$belongsToSeller) {
            include BASE_PATH . '/views/404.php';
            return;
        }
        
        include BASE_PATH . '/views/layouts/header.php';
        include BASE_PATH . '/views/seller/order_detail.php';
        include BASE_PATH . '/views/layouts/footer.php';
    }
    
    // Cập nhật trạng thái đơn hàng
    public function updateOrderStatus() {
        Auth::requireSeller();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $orderId = $_POST['order_id'] ?? 0;
            $status = $_POST['status'] ?? '';
            
            if ($orderId > 0 && in_array($status, ['processing', 'shipped', 'delivered'])) {
                if ($this->orderModel->updateStatus($orderId, $status, Auth::id())) {
                    $_SESSION['success'] = 'Cập nhật trạng thái đơn hàng thành công!';
                } else {
                    $_SESSION['error'] = 'Cập nhật trạng thái đơn hàng thất bại!';
                }
            } else {
                $_SESSION['error'] = 'Không thể cập nhật trạng thái đơn hàng!';
            }
        }
        
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
    
    // Doanh thu 
    public function revenue() {
        Auth::requireSeller();
        
        $sellerId = Auth::id();
        
        // Lấy khoảng thời gian lọc
        $period = $_GET['period'] ?? 'month'; // day, week, month, year
        
        // Lấy dữ liệu doanh thu theo khoảng thời gian
        $revenueData = $this->orderModel->getRevenueBySeller($sellerId, $period);
        
        // Lấy top sản phẩm bán chạy
        $topProducts = $this->productModel->getTopSellingBySeller($sellerId, 10);
        
        include BASE_PATH . '/views/layouts/header.php';
        include BASE_PATH . '/views/seller/revenue.php';
        include BASE_PATH . '/views/layouts/footer.php';
    }
    
    // Cài đặt
    public function settings() {
        Auth::requireSeller();
        
        $seller = Auth::user();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'full_name' => $_POST['full_name'] ?? '',
                'phone' => $_POST['phone'] ?? '',
                'address' => $_POST['address'] ?? ''
            ];
            
            // Xử lý upload avatar
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
                $data['avatar'] = $this->uploadAvatar($seller['avatar']);
            }
            
            if ($this->userModel->update($seller['id'], $data)) {
                // Cập nhật lại session
                $_SESSION['user'] = array_merge($_SESSION['user'], $data);
                $_SESSION['success'] = 'Cập nhật thông tin thành công!';
            } else {
                $_SESSION['error'] = 'Cập nhật thông tin thất bại!';
            }
        }
        
        include BASE_PATH . '/views/layouts/header.php';
        include BASE_PATH . '/views/seller/settings.php';
        include BASE_PATH . '/views/layouts/footer.php';
    }
    
    // Upload hình ảnh sản phẩm
    private function uploadImage($currentImage = null) {
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $targetDir = 'assets/images/products/';
            $fileName = time() . '_' . basename($_FILES['image']['name']);
            $targetFile = $targetDir . $fileName;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                return $targetFile;
            }
        }
        
        return $currentImage ?? 'assets/images/products/default.jpg';
    }
    
    // Upload avatar
    private function uploadAvatar($currentAvatar = null) {
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
            $targetDir = 'assets/images/avatars/';
            $fileName = time() . '_' . basename($_FILES['avatar']['name']);
            $targetFile = $targetDir . $fileName;
            
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $targetFile)) {
                return $targetFile;
            }
        }
        
        return $currentAvatar ?? 'assets/images/default-avatar.png';
    }
    
    // Lấy thống kê người bán
    private function getSellerStats() {
        $stats = [
            'total_products' => 0,
            'total_orders' => 0,
            'total_revenue' => 0,
            'pending_orders' => 0
        ];
        
        // Lấy số lượng sản phẩm
        $products = $this->productModel->getBySeller(Auth::id());
        $stats['total_products'] = count($products);
        
        // Lấy thống kê đơn hàng
        $orders = $this->orderModel->getBySeller(Auth::id());
        $stats['total_orders'] = count($orders);
        
        foreach ($orders as $order) {
            $stats['total_revenue'] += $order['total_amount'];
            if ($order['status'] === 'pending') {
                $stats['pending_orders']++;
            }
        }
        
        return $stats;
    }
}
?>