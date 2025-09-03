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
        // Khởi tạo giỏ hàng nếu chưa có
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        include BASE_PATH . '/views/layouts/header.php';
        include BASE_PATH . '/views/user/cart.php';
        include BASE_PATH . '/views/layouts/footer.php';
    }
    
    // Thêm sản phẩm vào giỏ hàng
    public function addToCart() {
        $productId = $_POST['product_id'] ?? 0;
        $quantity = $_POST['quantity'] ?? 1;
        
        if ($productId > 0 && $quantity > 0) {
            // Kiểm tra sản phẩm có tồn tại không
            $product = $this->productModel->getById($productId);
            
            if ($product && $product['stock'] >= $quantity) {
                // Thêm vào giỏ hàng
                if (isset($_SESSION['cart'][$productId])) {
                    $_SESSION['cart'][$productId] += $quantity;
                } else {
                    $_SESSION['cart'][$productId] = $quantity;
                }
                
                $_SESSION['success'] = 'Đã thêm sản phẩm vào giỏ hàng!';
            } else {
                $_SESSION['error'] = 'Sản phẩm không tồn tại hoặc không đủ số lượng!';
            }
        } else {
            $_SESSION['error'] = 'Dữ liệu không hợp lệ!';
        }
        
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
    
    // Xóa sản phẩm khỏi giỏ hàng
    public function removeFromCart() {
        $productId = $_GET['id'] ?? 0;
        
        if ($productId > 0 && isset($_SESSION['cart'][$productId])) {
            unset($_SESSION['cart'][$productId]);
            $_SESSION['success'] = 'Đã xóa sản phẩm khỏi giỏ hàng!';
        } else {
            $_SESSION['error'] = 'Không thể xóa sản phẩm!';
        }
        
        header('Location: /cart');
        exit;
    }
    
    // Cập nhật giỏ hàng
    public function updateCart() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $productId = $_POST['product_id'] ?? 0;
            $quantity = $_POST['quantity'] ?? 1;
            
            if ($productId > 0 && $quantity > 0) {
                // Kiểm tra sản phẩm có tồn tại không
                $product = $this->productModel->getById($productId);
                
                if ($product && $product['stock'] >= $quantity) {
                    $_SESSION['cart'][$productId] = $quantity;
                    $_SESSION['success'] = 'Đã cập nhật giỏ hàng!';
                } else {
                    $_SESSION['error'] = 'Sản phẩm không tồn tại hoặc không đủ số lượng!';
                }
            } else {
                $_SESSION['error'] = 'Dữ liệu không hợp lệ!';
            }
        }
        
        header('Location: /cart');
        exit;
    }
    
    // Thanh toán
    public function checkout() {
        Auth::requireCustomer();
        
        // Kiểm tra giỏ hàng có trống không
        if (empty($_SESSION['cart'])) {
            $_SESSION['error'] = 'Giỏ hàng của bạn đang trống!';
            header('Location: /cart');
            exit;
        }
        
        // Lấy thông tin người dùng
        $user = Auth::user();
        
        // Lấy địa chỉ mặc định
        $defaultAddress = $this->userModel->getDefaultAddress($user['id']);
        
        // Lấy voucher của người dùng
        $vouchers = $this->userModel->getVouchers($user['id']);
        
        include BASE_PATH . '/views/layouts/header.php';
        include BASE_PATH . '/views/user/checkout.php';
        include BASE_PATH . '/views/layouts/footer.php';
    }
    
    // Đặt hàng
    public function placeOrder() {
        Auth::requireCustomer();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Kiểm tra giỏ hàng có trống không
            if (empty($_SESSION['cart'])) {
                $_SESSION['error'] = 'Giỏ hàng của bạn đang trống!';
                header('Location: /cart');
                exit;
            }
            
            $userId = Auth::id();
            $shippingAddress = $_POST['shipping_address'] ?? '';
            $paymentMethod = $_POST['payment_method'] ?? 'cod';
            $voucherId = $_POST['voucher_id'] ?? null;
            
            // Tính tổng tiền
            $totalAmount = 0;
            $items = [];
            
            foreach ($_SESSION['cart'] as $productId => $quantity) {
                $product = $this->productModel->getById($productId);
                
                if ($product && $product['stock'] >= $quantity) {
                    $subtotal = $product['price'] * $quantity;
                    $totalAmount += $subtotal;
                    
                    $items[] = [
                        'product_id' => $productId,
                        'quantity' => $quantity,
                        'price' => $product['price']
                    ];
                } else {
                    $_SESSION['error'] = 'Một số sản phẩm không còn đủ số lượng!';
                    header('Location: /checkout');
                    exit;
                }
            }
            
            // Áp dụng voucher nếu có
            if ($voucherId) {
                $voucher = $this->userModel->getVoucherById($voucherId, $userId);
                if ($voucher && !$voucher['is_used']) {
                    if ($voucher['discount_type'] === 'fixed') {
                        $totalAmount -= $voucher['discount_value'];
                    } else {
                        $totalAmount -= $totalAmount * ($voucher['discount_value'] / 100);
                    }
                    
                    // Đảm bảo tổng tiền không âm
                    if ($totalAmount < 0) {
                        $totalAmount = 0;
                    }
                }
            }
            
            // Tạo đơn hàng
            $orderData = [
                'customer_id' => $userId,
                'total_amount' => $totalAmount,
                'shipping_address' => $shippingAddress,
                                'payment_method' => $paymentMethod,
                'items' => $items
            ];
            
            $orderId = $this->orderModel->create($orderData);
            
            if ($orderId) {
                // Cập nhật voucher đã sử dụng nếu có
                if ($voucherId) {
                    $this->userModel->useVoucher($voucherId, $orderId);
                }
                
                // Xóa giỏ hàng
                unset($_SESSION['cart']);
                
                // Thêm thông báo cho người dùng
                $this->userModel->addNotification($userId, 'Đơn hàng mới', 'Đơn hàng #' . $orderId . ' của bạn đã được tạo thành công.', 'order');
                
                $_SESSION['success'] = 'Đặt hàng thành công!';
                header('Location: /orders/' . $orderId);
                exit;
            } else {
                $_SESSION['error'] = 'Đặt hàng thất bại!';
            }
        }
        
        header('Location: /checkout');
        exit;
    }
    
    // Lịch sử đơn hàng
    public function orders() {
        Auth::requireCustomer();
        
        $orders = $this->orderModel->getByCustomer(Auth::id());
        
        include BASE_PATH . '/views/layouts/header.php';
        include BASE_PATH . '/views/user/orders.php';
        include BASE_PATH . '/views/layouts/footer.php';
    }
    
    // Chi tiết đơn hàng
    public function orderDetail($id) {
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
            if ($this->orderModel->updateStatus($id, 'cancelled')) {
                // Hoàn lại số lượng tồn kho
                $orderItems = $this->orderModel->getOrderItems($id);
                foreach ($orderItems as $item) {
                    $this->productModel->increaseStock($item['product_id'], $item['quantity']);
                }
                
                // Thêm thông báo cho người dùng
                $this->userModel->addNotification(Auth::id(), 'Hủy đơn hàng', 'Đơn hàng #' . $id . ' đã được hủy thành công.', 'order');
                
                $_SESSION['success'] = 'Hủy đơn hàng thành công!';
            } else {
                $_SESSION['error'] = 'Hủy đơn hàng thất bại!';
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
            $rating = $_POST['rating'] ?? 5;
            $comment = $_POST['comment'] ?? '';
            
            // Kiểm tra xem người dùng đã mua sản phẩm chưa
            if ($this->orderModel->hasPurchasedProduct(Auth::id(), $productId)) {
                // Kiểm tra xem người dùng đã đánh giá sản phẩm chưa
                if (!$this->reviewModel->hasReviewed(Auth::id(), $productId)) {
                    $data = [
                        'product_id' => $productId,
                        'customer_id' => Auth::id(),
                        'rating' => $rating,
                        'comment' => $comment
                    ];
                    
                    if ($this->reviewModel->create($data)) {
                        // Thêm thông báo cho người bán
                        $product = $this->productModel->getById($productId);
                        $this->userModel->addNotification($product['seller_id'], 'Đánh giá mới', 'Sản phẩm của bạn vừa nhận được một đánh giá mới.', 'system');
                        
                        $_SESSION['success'] = 'Đánh giá thành công!';
                    } else {
                        $_SESSION['error'] = 'Đánh giá thất bại!';
                    }
                } else {
                    $_SESSION['error'] = 'Bạn đã đánh giá sản phẩm này rồi!';
                }
            } else {
                $_SESSION['error'] = 'Bạn phải mua sản phẩm này mới được đánh giá!';
            }
        }
        
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
    
    // Hồ sơ người dùng
    public function profile() {
        Auth::requireCustomer();
        
        $user = Auth::user();
        
        include BASE_PATH . '/views/layouts/header.php';
        include BASE_PATH . '/views/user/profile.php';
        include BASE_PATH . '/views/layouts/footer.php';
    }
    
    // Cập nhật hồ sơ
    public function updateProfile() {
        Auth::requireCustomer();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = Auth::id();
            $data = [
                'full_name' => $_POST['full_name'] ?? '',
                'phone' => $_POST['phone'] ?? '',
                'address' => $_POST['address'] ?? ''
            ];
            
            // Xử lý upload avatar
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
                $avatar = $this->uploadImage();
                if ($avatar) {
                    $data['avatar'] = $avatar;
                }
            }
            
            if ($this->userModel->update($userId, $data)) {
                // Cập nhật session
                $_SESSION['user'] = $this->userModel->getById($userId);
                
                $_SESSION['success'] = 'Cập nhật hồ sơ thành công!';
            } else {
                $_SESSION['error'] = 'Cập nhật hồ sơ thất bại!';
            }
        }
        
        header('Location: /profile');
        exit;
    }
    
    // Đổi mật khẩu
    public function changePassword() {
        Auth::requireCustomer();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = Auth::id();
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            // Kiểm tra mật khẩu hiện tại
            $user = $this->userModel->getById($userId);
            if ($user && password_verify($currentPassword, $user['password'])) {
                // Kiểm tra mật khẩu mới và xác nhận mật khẩu
                if ($newPassword === $confirmPassword && strlen($newPassword) >= 6) {
                    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                    
                    if ($this->userModel->changePassword($userId, $hashedPassword)) {
                        $_SESSION['success'] = 'Đổi mật khẩu thành công!';
                    } else {
                        $_SESSION['error'] = 'Đổi mật khẩu thất bại!';
                    }
                } else {
                    $_SESSION['error'] = 'Mật khẩu mới không hợp lệ hoặc không khớp!';
                }
            } else {
                $_SESSION['error'] = 'Mật khẩu hiện tại không đúng!';
            }
        }
        
        header('Location: /profile');
        exit;
    }
    
    // Địa chỉ
    public function addresses() {
        Auth::requireCustomer();
        
        $addresses = $this->userModel->getAddresses(Auth::id());
        
        include BASE_PATH . '/views/layouts/header.php';
        include BASE_PATH . '/views/user/addresses.php';
        include BASE_PATH . '/views/layouts/footer.php';
    }
    
    // Thêm địa chỉ
    public function addAddress() {
        Auth::requireCustomer();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = Auth::id();
            $data = [
                'recipient_name' => $_POST['recipient_name'] ?? '',
                'phone' => $_POST['phone'] ?? '',
                'address' => $_POST['address'] ?? '',
                'province' => $_POST['province'] ?? '',
                'district' => $_POST['district'] ?? '',
                'ward' => $_POST['ward'] ?? '',
                'is_default' => isset($_POST['is_default'])
            ];
            
            if ($data['is_default']) {
                // Bỏ mặc định tất cả các địa chỉ khác
                $this->userModel->unsetDefaultAddresses($userId);
            }
            
            if ($this->userModel->addAddress($userId, $data)) {
                $_SESSION['success'] = 'Thêm địa chỉ thành công!';
            } else {
                $_SESSION['error'] = 'Thêm địa chỉ thất bại!';
            }
        }
        
        header('Location: /addresses');
        exit;
    }
    
    // Sửa địa chỉ
    public function editAddress($id) {
        Auth::requireCustomer();
        
        $address = $this->userModel->getAddressById($id);
        
        if (!$address || $address['user_id'] != Auth::id()) {
            include BASE_PATH . '/views/404.php';
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = Auth::id();
            $data = [
                'recipient_name' => $_POST['recipient_name'] ?? '',
                'phone' => $_POST['phone'] ?? '',
                'address' => $_POST['address'] ?? '',
                'province' => $_POST['province'] ?? '',
                'district' => $_POST['district'] ?? '',
                'ward' => $_POST['ward'] ?? '',
                'is_default' => isset($_POST['is_default'])
            ];
            
            if ($data['is_default']) {
                // Bỏ mặc định tất cả các địa chỉ khác
                $this->userModel->unsetDefaultAddresses($userId);
            }
            
            if ($this->userModel->updateAddress($id, $data)) {
                $_SESSION['success'] = 'Cập nhật địa chỉ thành công!';
            } else {
                $_SESSION['error'] = 'Cập nhật địa chỉ thất bại!';
            }
            
            header('Location: /addresses');
            exit;
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
            $userId = Auth::id();
            
            // Bỏ mặc định tất cả các địa chỉ khác
            $this->userModel->unsetDefaultAddresses($userId);
            
            // Đặt địa chỉ này làm mặc định
            if ($this->userModel->setDefaultAddress($id)) {
                $_SESSION['success'] = 'Cập nhật địa chỉ mặc định thành công!';
            } else {
                $_SESSION['error'] = 'Cập nhật địa chỉ mặc định thất bại!';
            }
        } else {
            $_SESSION['error'] = 'Không thể cập nhật địa chỉ mặc định!';
        }
        
        header('Location: /addresses');
        exit;
    }
    
    // Danh sách yêu thích
    public function wishlist() {
        Auth::requireCustomer();
        
        $wishlist = $this->userModel->getWishlist(Auth::id());
        
        include BASE_PATH . '/views/layouts/header.php';
        include BASE_PATH . '/views/user/wishlist.php';
        include BASE_PATH . '/views/layouts/footer.php';
    }
    
    // Thêm vào danh sách yêu thích
    public function addToWishlist() {
        Auth::requireCustomer();
        
        $productId = $_GET['id'] ?? 0;
        
        if ($productId > 0) {
            $product = $this->productModel->getById($productId);
            
            if ($product) {
                if ($this->userModel->addToWishlist(Auth::id(), $productId)) {
                    $_SESSION['success'] = 'Đã thêm vào danh sách yêu thích!';
                } else {
                    $_SESSION['error'] = 'Sản phẩm đã có trong danh sách yêu thích!';
                }
            } else {
                $_SESSION['error'] = 'Sản phẩm không tồn tại!';
            }
        } else {
            $_SESSION['error'] = 'Dữ liệu không hợp lệ!';
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
            $_SESSION['error'] = 'Dữ liệu không hợp lệ!';
        }
        
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
    
    // Thông báo
    public function notifications() {
        Auth::requireCustomer();
        
        $notifications = $this->userModel->getNotifications(Auth::id());
        
        // Đánh dấu tất cả thông báo là đã đọc
        $this->userModel->markAllNotificationsAsRead(Auth::id());
        
        include BASE_PATH . '/views/layouts/header.php';
        include BASE_PATH . '/views/user/notifications.php';
        include BASE_PATH . '/views/layouts/footer.php';
    }
    
    // Lịch sử xem sản phẩm
    public function viewHistory() {
        Auth::requireCustomer();
        
        $viewHistory = $this->userModel->getViewHistory(Auth::id());
        
        include BASE_PATH . '/views/layouts/header.php';
        include BASE_PATH . '/views/user/view_history.php';
        include BASE_PATH . '/views/layouts/footer.php';
    }
    
    // Voucher
    public function vouchers() {
        Auth::requireCustomer();
        
        $vouchers = $this->userModel->getVouchers(Auth::id());
        
        include BASE_PATH . '/views/layouts/header.php';
        include BASE_PATH . '/views/user/vouchers.php';
        include BASE_PATH . '/views/layouts/footer.php';
    }
    
    // Áp dụng voucher
    public function applyVoucher() {
        Auth::requireCustomer();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $code = $_POST['voucher_code'] ?? '';
            
            if (!empty($code)) {
                $voucher = $this->userModel->getVoucherByCode($code, Auth::id());
                
                if ($voucher) {
                    echo json_encode([
                        'success' => true,
                        'voucher' => [
                            'id' => $voucher['id'],
                            'code' => $voucher['code'],
                            'discount_type' => $voucher['discount_type'],
                            'discount_value' => $voucher['discount_value'],
                            'min_order_value' => $voucher['min_order_value'],
                            'max_discount_amount' => $voucher['max_discount_amount']
                        ]
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Mã voucher không hợp lệ hoặc đã hết hạn!'
                    ]);
                }
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Vui lòng nhập mã voucher!'
                ]);
            }
        }
        
        exit;
    }
    
    // Upload hình ảnh
    private function uploadImage() {
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
            $targetDir = 'assets/images/avatars/';
            $fileName = time() . '_' . basename($_FILES['avatar']['name']);
            $targetFile = $targetDir . $fileName;
            
            // Kiểm tra và tạo thư mục nếu chưa tồn tại
            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0777, true);
            }
            
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $targetFile)) {
                return $targetFile;
            }
        }
        
        return null;
    }
}
?>