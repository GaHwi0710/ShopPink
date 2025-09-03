<?php
require_once BASE_PATH . '/models/User.php';

class AuthController {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    // Trang đăng nhập
    public function login() {
        if (Auth::check()) {
            header('Location: /');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            $remember = isset($_POST['remember']);
            
            $user = $this->userModel->login($username, $password);
            
            if ($user) {
                // Lưu thông tin người dùng vào session
                $_SESSION['user'] = $user;
                
                // Nếu chọn ghi nhớ đăng nhập, lưu cookie
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    $expiry = time() + (86400 * 30); // 30 days
                    
                    setcookie('remember_token', $token, $expiry, '/');
                    setcookie('remember_user_id', $user['id'], $expiry, '/');
                    
                    // Lưu token vào database
                    // (Bạn cần thêm bảng remember_tokens vào database)
                }
                
                Auth::login($user);
            } else {
                $error = 'Tên đăng nhập hoặc mật khẩu không đúng!';
                include BASE_PATH . '/views/layouts/header.php';
                include BASE_PATH . '/views/auth/login.php';
                include BASE_PATH . '/views/layouts/footer.php';
                return;
            }
        }
        
        include BASE_PATH . '/views/layouts/header.php';
        include BASE_PATH . '/views/auth/login.php';
        include BASE_PATH . '/views/layouts/footer.php';
    }
    
    // Trang đăng ký
    public function register() {
        if (Auth::check()) {
            header('Location: /');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'username' => $_POST['username'] ?? '',
                'email' => $_POST['email'] ?? '',
                'password' => $_POST['password'] ?? '',
                'confirm_password' => $_POST['confirm_password'] ?? '',
                'full_name' => $_POST['full_name'] ?? '',
                'phone' => $_POST['phone'] ?? '',
                'address' => $_POST['address'] ?? '',
                'role' => $_POST['role'] ?? 'customer'
            ];
            
            $errors = $this->validateRegister($data);
            
            if (empty($errors)) {
                unset($data['confirm_password']);
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
                
                if ($this->userModel->create($data)) {
                    $_SESSION['success'] = 'Đăng ký thành công! Vui lòng đăng nhập.';
                    header('Location: /login');
                    exit;
                } else {
                    $errors[] = 'Đăng ký thất bại. Vui lòng thử lại!';
                }
            }
        }
        
        include BASE_PATH . '/views/layouts/header.php';
        include BASE_PATH . '/views/auth/register.php';
        include BASE_PATH . '/views/layouts/footer.php';
    }
    
    // Đăng xuất
    public function logout() {
        // Xóa cookie ghi nhớ đăng nhập
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/');
            setcookie('remember_user_id', '', time() - 3600, '/');
        }
        
        Auth::logout();
    }
    
    // Validate đăng ký
    private function validateRegister($data) {
        $errors = [];
        
        if (empty($data['username'])) {
            $errors[] = 'Vui lòng nhập tên đăng nhập!';
        } elseif ($this->userModel->getByUsername($data['username'])) {
            $errors[] = 'Tên đăng nhập đã tồn tại!';
        }
        
        if (empty($data['email'])) {
            $errors[] = 'Vui lòng nhập email!';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email không hợp lệ!';
        } elseif ($this->userModel->getByEmail($data['email'])) {
            $errors[] = 'Email đã tồn tại!';
        }
        
        if (empty($data['password'])) {
            $errors[] = 'Vui lòng nhập mật khẩu!';
        } elseif (strlen($data['password']) < 6) {
            $errors[] = 'Mật khẩu phải có ít nhất 6 ký tự!';
        }
        
        if ($data['password'] !== $data['confirm_password']) {
            $errors[] = 'Mật khẩu xác nhận không khớp!';
        }
        
        if (empty($data['full_name'])) {
            $errors[] = 'Vui lòng nhập họ tên!';
        }
        
        return $errors;
    }
}
?>