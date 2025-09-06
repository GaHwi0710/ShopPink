<?php
require_once BASE_PATH . '/models/Product.php';
require_once BASE_PATH . '/models/User.php';
require_once BASE_PATH . '/models/Review.php';
    class ProductController {
        private $productModel;
        private $userModel;
        private $reviewModel;
        
        public function __construct() {
            $this->productModel = new Product();
            $this->userModel = new User();
            $this->reviewModel = new Review();
        }
    
    // Trang chủ - hiển thị sản phẩm
      public function index() {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 12;
        $offset = ($page - 1) * $limit;
        $products = $this->productModel->getAll($limit, $offset);
        
        $page_title = 'Trang chủ - ShopPink';
        
        include BASE_PATH . '/views/layouts/header.php';
        include BASE_PATH . '/views/products/index.php';
        include BASE_PATH . '/views/layouts/footer.php';
    }
    
    // Tìm kiếm sản phẩm
    public function search() {
        $keyword = $_GET['keyword'] ?? '';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 12;
        $offset = ($page - 1) * $limit;
        $products = $this->productModel->search($keyword, $limit, $offset);
        
        // Thêm biến page_title
        $page_title = 'Kết quả tìm kiếm - ShopPink';
        
        include BASE_PATH . '/views/layouts/header.php';
        include BASE_PATH . '/views/products/search.php';
        include BASE_PATH . '/views/layouts/footer.php';
    }
    
    // Chi tiết sản phẩm
    public function detail($id) {
        $product = $this->productModel->getById($id);
        
        if (!$product) {
            include BASE_PATH . '/views/404.php';
            return;
        }
        
        // Lấy đánh giá của sản phẩm
        $reviews = $this->reviewModel->getByProduct($id);
        $averageRating = $this->reviewModel->getAverageRating($id);
        
        // Nếu người dùng đã đăng nhập, thêm vào lịch sử xem
        if (Auth::check()) {
            $this->userModel->addToViewHistory(Auth::id(), $id);
        }
        
        // Thêm biến page_title
        $page_title = $product['name'] . ' - ShopPink';
        
        include BASE_PATH . '/views/layouts/header.php';
        include BASE_PATH . '/views/products/detail.php';
        include BASE_PATH . '/views/layouts/footer.php';
    }
    
    // Xem nhanh sản phẩm (AJAX)
    public function quickView($id) {
        $product = $this->productModel->getById($id);
        
        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại!']);
            exit;
        }
        
        // Lấy đánh giá của sản phẩm
        $averageRating = $this->reviewModel->getAverageRating($id);
        
        // Trả về HTML cho modal quick view
        ob_start();
        include BASE_PATH . '/views/products/quick_view.php';
        $html = ob_get_clean();
        
        echo json_encode(['success' => true, 'html' => $html]);
        exit;
    }
}
?>