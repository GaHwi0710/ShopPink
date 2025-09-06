<?php
require_once BASE_PATH . '/models/User.php';
require_once BASE_PATH . '/models/Product.php';
require_once BASE_PATH . '/models/Order.php';
require_once BASE_PATH . '/models/Review.php';
class UserController {
    private $userModel;
    private $productModel;
    private $orderModel;
    private $reviewModel;
    
    public function __construct() {
        $this->userModel = new User();
        $this->productModel = new Product();
        $this->orderModel = new Order();
        $this->reviewModel = new Review();
    }
    
    // Trang tổng quan của người dùng
    public function dashboard() {
        Auth::requireCustomer();
        
        $page_title = 'Tổng quan - ShopPink';
        
        $user = Auth::user();
        
        // Lấy đơn hàng gần đây
        $orders = $this->orderModel->getByCustomer(Auth::id());
        $recentOrders = array_slice($orders, 0, 5);
        
        // Lấy danh sách yêu thích
        $wishlist = $this->userModel->getWishlist(Auth::id());
        
        // Lấy địa chỉ
        $addresses = $this->userModel->getAddresses(Auth::id());
        
        // Lấy voucher
        $vouchers = $this->userModel->getVouchers(Auth::id());
        
        // Lấy sản phẩm gợi ý (ngẫu nhiên)
        $recommendedProducts = $this->productModel->getRandom(8);
        
        include BASE_PATH . '/views/layouts/header.php';
        include BASE_PATH . '/views/user/dashboard.php';
        include BASE_PATH . '/views/layouts/footer.php';
    }
    
    // Giỏ hàng
    public function cart() {
        $page_title = 'Giỏ hàng - ShopPink';
        
        // Khởi tạo giỏ hàng nếu chưa có
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        include BASE_PATH . '/views/layouts/header.php';
        include BASE_PATH . '/views/user/cart.php';
        include BASE_PATH . '/views/layouts/footer.php';
    }
    
    // Thêm vào giỏ hàng
    public function addToCart() {
        $productId = $_POST['product_id'] ?? 0;
        $quantity = $_POST['quantity'] ?? 1;
        
        if ($productId > 0 && $quantity > 0) {
            // Khởi tạo giỏ hàng nếu chưa có
            if (!isset($_SESSION['cart'])) {
                $_SESSION['cart'] = [];
            }
            
            // Nếu sản phẩm đã có trong giỏ hàng, tăng số lượng
            if (isset($_SESSION['cart'][$productId])) {
                $_SESSION['cart'][$productId] += $quantity;
            } else {
                $_SESSION['cart'][$productId] = $quantity;
            }
            
            $_SESSION['success'] = 'Đã thêm sản phẩm vào giỏ hàng!';
        } else {
            $_SESSION['error'] = 'Không thể thêm sản phẩm vào giỏ hàng!';
        }
        
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
    
    // Xóa khỏi giỏ hàng
    public function removeFromCart() {
        $productId = $_GET['id'] ?? 0;
        
        if ($productId > 0 && isset($_SESSION['cart'][$productId])) {
            unset($_SESSION['cart'][$productId]);
            $_SESSION['success'] = 'Đã xóa sản phẩm khỏi giỏ hàng!';
        } else {
            $_SESSION['error'] = 'Không thể xóa sản phẩm khỏi giỏ hàng!';
        }
        
        header('Location: /cart');
        exit;
    }
    
    // Cập nhật giỏ hàng
    public function updateCart() {
        if (isset($_POST['quantity']) && is_array($_POST['quantity'])) {
            foreach ($_POST['quantity'] as $productId => $quantity) {
                if ($quantity > 0) {
                    $_SESSION['cart'][$productId] = $quantity;
                } else {
                    unset($_SESSION['cart'][$productId]);
                }
            }
            
            $_SESSION['success'] = 'Đã cập nhật giỏ hàng!';
        } else {
            $_SESSION['error'] = 'Không thể cập nhật giỏ hàng!';
        }
        
        header('Location: /cart');
        exit;
    }
    
    // Thanh toán
    public function checkout() {
        $page_title = 'Thanh toán - ShopPink';
        
        Auth::requireCustomer();
        
        // Kiểm tra giỏ hàng
        if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
            $_SESSION['error'] = 'Giỏ hàng của bạn đang trống!';
            header('Location: /cart');
            exit;
        }
        
        // Lấy địa chỉ mặc định của người dùng
        $defaultAddress = $this->userModel->getDefaultAddress(Auth::id());
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'customer_id' => Auth::id(),
                'items' => [],
                'total_amount' => 0,
                'shipping_address' => $_POST['shipping_address'],
                'payment_method' => $_POST['payment_method']
            ];
            
            // Tính tổng tiền và chuẩn bị dữ liệu các sản phẩm
            foreach ($_SESSION['cart'] as $productId => $quantity) {
                $product = $this->productModel->getById($productId);
                
                if ($product && $product['stock'] >= $quantity) {
                    $item = [
                        'product_id' => $productId,
                        'quantity' => $quantity,
                        'price' => $product['price']
                    ];
                    
                    $data['items'][] = $item;
                    $data['total_amount'] += $product['price'] * $quantity;
                } else {
                    $_SESSION['error'] = 'Sản phẩm ' . $product['name'] . ' không đủ số lượng!';
                    include BASE_PATH . '/views/layouts/header.php';
                    include BASE_PATH . '/views/user/checkout.php';
                    include BASE_PATH . '/views/layouts/footer.php';
                    return;
                }
            }
            
            // Áp dụng voucher nếu có
            $voucherId = $_POST['voucher_id'] ?? 0;
            if ($voucherId > 0) {
                // Xử lý áp dụng voucher
                // (Cần triển khai thêm)
            }
            
            // Tạo đơn hàng
            $orderId = $this->orderModel->create($data);
            
            if ($orderId) {
                // Xóa giỏ hàng
                unset($_SESSION['cart']);
                
                $_SESSION['success'] = 'Đặt hàng thành công!';
                header('Location: /orders/' . $orderId);
                exit;
            } else {
                $error = 'Đặt hàng thất bại. Vui lòng thử lại!';
            }
        }
        
        include BASE_PATH . '/views/layouts/header.php';
        include BASE_PATH . '/views/user/checkout.php';
        include BASE_PATH . '/views/layouts/footer.php';
    }
    
    // Lịch sử đơn hàng
    public function orders() {
        $page_title = 'Lịch sử đơn hàng - ShopPink';
        
        Auth::requireCustomer();
        
        $orders = $this->orderModel->getByCustomer(Auth::id());
        
        include BASE_PATH . '/views/layouts/header.php';
        include BASE_PATH . '/views/user/orders.php';
        include BASE_PATH . '/views/layouts/footer.php';
    }
    
    // Chi tiết đơn hàng
    public function orderDetail($id) {
        $page_title = 'Chi tiết đơn hàng - ShopPink';
        
        Auth::requireCustomer();
        
        $order = $this->orderModel->getById($id);
        
        if (!$order || $order['customer_id'] != Auth::id()) {
            include BASE_PATH . '/views/404.php';
            return;
        }
        
        $orderItems = $this->orderModel->getOrderItems($id);
        
        include BASE_PATH . '/views/layouts/header.php';
        include BASE_PATH . '/views/user/order_detail.php';
        include BASE_PATH . '/views/layouts/footer.php';
    }
    
    // Hủy đơn hàng
    public function cancelOrder($id) {
        Auth::requireCustomer();
        
        $order = $this->orderModel->getById($id);
        
        if ($order && $order['customer_id'] == Auth::id() && $order['status'] === 'pending') {
            if ($this->orderModel->cancel($id, Auth::id())) {
                $_SESSION['success'] = 'Đã hủy đơn hàng thành công!';
            } else {
                $_SESSION['error'] = 'Không thể hủy đơn hàng!';
            }
        } else {
            $_SESSION['error'] = 'Không thể hủy đơn hàng!';
        }
        
        header('Location: /orders');
        exit;
    }
    
    // Đánh giá sản phẩm
    public function review() {
        Auth::requireCustomer();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $productId = $_POST['product_id'] ?? 0;
            $customerId = Auth::id();
            $rating = $_POST['rating'] ?? 5;
            $comment = $_POST['comment'] ?? '';
            
            // Kiểm tra xem khách hàng đã mua sản phẩm chưa
            if (!$this->reviewModel->hasPurchased($customerId, $productId)) {
                $_SESSION['error'] = 'Bạn chỉ có thể đánh giá sản phẩm đã mua!';
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            }
            
            // Kiểm tra xem khách hàng đã đánh giá sản phẩm chưa
            if ($this->reviewModel->hasReviewed($customerId, $productId)) {
                $_SESSION['error'] = 'Bạn đã đánh giá sản phẩm này rồi!';
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            }
            
            $data = [
                'product_id' => $productId,
                'customer_id' => $customerId,
                'rating' => $rating,
                'comment' => $comment
            ];
            
            if ($this->reviewModel->create($data)) {
                $_SESSION['success'] = 'Đánh giá thành công!';
            } else {
                $_SESSION['error'] = 'Đánh giá thất bại!';
            }
        }
        
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
    
    // Hồ sơ người dùng
    public function profile() {
        $page_title = 'Hồ sơ người dùng - ShopPink';
        
        Auth::requireCustomer();
        
        $user = Auth::user();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'full_name' => $_POST['full_name'] ?? '',
                'phone' => $_POST['phone'] ?? '',
                'address' => $_POST['address'] ?? ''
            ];
            
            // Xử lý upload avatar
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
                $targetDir = 'assets/images/avatars/';
                $fileName = time() . '_' . basename($_FILES['avatar']['name']);
                $targetFile = $targetDir . $fileName;
                
                if (move_uploaded_file($_FILES['avatar']['tmp_name'], $targetFile)) {
                    $data['avatar'] = $targetFile;
                }
            }
            
            if ($this->userModel->update(Auth::id(), $data)) {
                $_SESSION['user'] = array_merge($_SESSION['user'], $data);
                $_SESSION['success'] = 'Cập nhật thông tin thành công!';
            } else {
                $_SESSION['error'] = 'Cập nhật thông tin thất bại!';
            }
        }
        
        include BASE_PATH . '/views/layouts/header.php';
        include BASE_PATH . '/views/user/profile.php';
        include BASE_PATH . '/views/layouts/footer.php';
    }
    
    // Đổi mật khẩu
    public function changePassword() {
        $page_title = 'Đổi mật khẩu - ShopPink';
        
        Auth::requireCustomer();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            $user = $this->userModel->getById(Auth::id());
            
            if (!$user || !password_verify($currentPassword, $user['password'])) {
                $_SESSION['error'] = 'Mật khẩu hiện tại không đúng!';
            } elseif ($newPassword !== $confirmPassword) {
                $_SESSION['error'] = 'Mật khẩu mới và xác nhận mật khẩu không khớp!';
            } elseif (strlen($newPassword) < 6) {
                $_SESSION['error'] = 'Mật khẩu mới phải có ít nhất 6 ký tự!';
            } else {
                if ($this->userModel->changePassword(Auth::id(), $newPassword)) {
                    $_SESSION['success'] = 'Đổi mật khẩu thành công!';
                } else {
                    $_SESSION['error'] = 'Đổi mật khẩu thất bại!';
                }
            }
        }
        
        include BASE_PATH . '/views/layouts/header.php';
        include BASE_PATH . '/views/user/change_password.php';
        include BASE_PATH . '/views/layouts/footer.php';
    }
    
    // Địa chỉ
    public function addresses() {
        $page_title = 'Địa chỉ - ShopPink';
        
        Auth::requireCustomer();
        
        $addresses = $this->userModel->getAddresses(Auth::id());
        
        include BASE_PATH . '/views/layouts/header.php';
        include BASE_PATH . '/views/user/addresses.php';
        include BASE_PATH . '/views/layouts/footer.php';
    }
    
    // Thêm địa chỉ mới
    public function addAddress() {
        $page_title = 'Thêm địa chỉ - ShopPink';
        
        Auth::requireCustomer();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'user_id' => Auth::id(),
                'recipient_name' => $_POST['recipient_name'] ?? '',
                'phone' => $_POST['phone'] ?? '',
                'address' => $_POST['address'] ?? '',
                'province' => $_POST['province'] ?? '',
                'district' => $_POST['district'] ?? '',
                'ward' => $_POST['ward'] ?? '',
                'is_default' => isset($_POST['is_default'])
            ];
            
            if ($this->userModel->addAddress($data)) {
                $_SESSION['success'] = 'Thêm địa chỉ thành công!';
                header('Location: /addresses');
                exit;
            } else {
                $_SESSION['error'] = 'Thêm địa chỉ thất bại!';
            }
        }
        
        include BASE_PATH . '/views/layouts/header.php';
        include BASE_PATH . '/views/user/add_address.php';
        include BASE_PATH . '/views/layouts/footer.php';
    }
    
    // Sửa địa chỉ
    public function editAddress($id) {
        $page_title = 'Sửa địa chỉ - ShopPink';
        
        Auth::requireCustomer();
        
        $address = $this->userModel->getAddressById($id);
        
        if (!$address || $address['user_id'] != Auth::id()) {
            include BASE_PATH . '/views/404.php';
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'user_id' => Auth::id(),
                'recipient_name' => $_POST['recipient_name'] ?? '',
                'phone' => $_POST['phone'] ?? '',
                'address' => $_POST['address'] ?? '',
                'province' => $_POST['province'] ?? '',
                'district' => $_POST['district'] ?? '',
                'ward' => $_POST['ward'] ?? '',
                'is_default' => isset($_POST['is_default'])
            ];
            
            if ($this->userModel->updateAddress($id, $data)) {
                $_SESSION['success'] = 'Cập nhật địa chỉ thành công!';
                header('Location: /addresses');
                exit;
            } else {
                $_SESSION['error'] = 'Cập nhật địa chỉ thất bại!';
            }
        }
        
        include BASE_PATH . '/views/layouts/header.php';
        include BASE_PATH . '/views/user/edit_address.php';
        include BASE_PATH . '/views/layouts/footer.php';
    }
    
    // Xóa địa chỉ
    public function deleteAddress($id) {
        Auth::requireCustomer();
        
        $address = $this->userModel->getAddressById($id);
        
        if ($address && $address['user_id'] == Auth::id()) {
            if ($this->userModel->deleteAddress($id)) {
                $_SESSION['success'] = 'Xóa địa chỉ thành công!';
            } else {
                $_SESSION['error'] = 'Xóa địa chỉ thất bại!';
            }
        } else {
            $_SESSION['error'] = 'Không thể xóa địa chỉ!';
        }
        
        header('Location: /addresses');
        exit;
    }
    
    // Đặt địa chỉ mặc định
    public function setDefaultAddress($id) {
        Auth::requireCustomer();
        
        $address = $this->userModel->getAddressById($id);
        
        if ($address && $address['user_id'] == Auth::id()) {
            if ($this->userModel->setDefaultAddress($id, Auth::id())) {
                $_SESSION['success'] = 'Đã đặt địa chỉ mặc định!';
            } else {
                $_SESSION['error'] = 'Không thể đặt địa chỉ mặc định!';
            }
        } else {
            $_SESSION['error'] = 'Không thể đặt địa chỉ mặc định!';
        }
        
        header('Location: /addresses');
        exit;
    }
    
    // Danh sách yêu thích
    public function wishlist() {
        $page_title = 'Danh sách yêu thích - ShopPink';
        
        Auth::requireCustomer();
        
        $wishlist = $this->userModel->getWishlist(Auth::id());
        
        include BASE_PATH . '/views/layouts/header.php';
        include BASE_PATH . '/views/user/wishlist.php';
        include BASE_PATH . '/views/layouts/footer.php';
    }
    
    // Thêm vào danh sách yêu thích
    public function addToWishlist() {
        Auth::requireCustomer();
        
        $productId = $_POST['product_id'] ?? 0;
        
        if ($productId > 0) {
            if ($this->userModel->isInWishlist(Auth::id(), $productId)) {
                $_SESSION['error'] = 'Sản phẩm đã có trong danh sách yêu thích!';
            } else {
                if ($this->userModel->addToWishlist(Auth::id(), $productId)) {
                    $_SESSION['success'] = 'Đã thêm vào danh sách yêu thích!';
                } else {
                    $_SESSION['error'] = 'Không thể thêm vào danh sách yêu thích!';
                }
            }
        } else {
            $_SESSION['error'] = 'Không thể thêm vào danh sách yêu thích!';
        }
        
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
    
    // Xóa khỏi danh sách yêu thích
    public function removeFromWishlist() {
        Auth::requireCustomer();
        
        $productId = $_GET['id'] ?? 0;
        
        if ($productId > 0) {
            if ($this->userModel->removeFromWishlist(Auth::id(), $productId)) {
                $_SESSION['success'] = 'Đã xóa khỏi danh sách yêu thích!';
            } else {
                $_SESSION['error'] = 'Không thể xóa khỏi danh sách yêu thích!';
            }
        } else {
            $_SESSION['error'] = 'Không thể xóa khỏi danh sách yêu thích!';
        }
        
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
    
    // Thông báo
    public function notifications() {
        $page_title = 'Thông báo - ShopPink';
        
        Auth::requireCustomer();
        
        $notifications = $this->userModel->getNotifications(Auth::id());
        
        // Đánh dấu tất cả thông báo đã đọc
        $this->userModel->markAllNotificationsAsRead(Auth::id());
        
        include BASE_PATH . '/views/layouts/header.php';
        include BASE_PATH . '/views/user/notifications.php';
        include BASE_PATH . '/views/layouts/footer.php';
    }
    
    // Lịch sử xem sản phẩm
    public function viewHistory() {
        $page_title = 'Lịch sử xem sản phẩm - ShopPink';
        
        Auth::requireCustomer();
        
        $viewHistory = $this->userModel->getViewHistory(Auth::id());
        
        include BASE_PATH . '/views/layouts/header.php';
        include BASE_PATH . '/views/user/view_history.php';
        include BASE_PATH . '/views/layouts/footer.php';
    }
    
    // Voucher
    public function vouchers() {
        $page_title = 'Voucher - ShopPink';
        
        Auth::requireCustomer();
        
        $vouchers = $this->userModel->getVouchers(Auth::id());
        
        include BASE_PATH . '/views/layouts/header.php';
        include BASE_PATH . '/views/user/vouchers.php';
        include BASE_PATH . '/views/layouts/footer.php';
    }
    
    // Áp dụng voucher
    public function applyVoucher() {
        Auth::requireCustomer();
        
        $code = $_POST['voucher_code'] ?? '';
        
        if (empty($code)) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng nhập mã voucher!']);
            exit;
        }
        
        // Kiểm tra voucher
        // (Cần triển khai thêm)
        
        echo json_encode(['success' => false, 'message' => 'Mã voucher không hợp lệ hoặc đã hết hạn!']);
        exit;
    }
}
?>