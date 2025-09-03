<?php
session_start();
// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define base path
define('BASE_PATH', __DIR__);

// Define SITE_URL
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$scriptName = $_SERVER['SCRIPT_NAME'];
$path = str_replace(basename($scriptName), '', $scriptName);
define('SITE_URL', $protocol . '://' . $host . $path);

// Sửa lại để xử lý hash routing
$url = '';
if (isset($_GET['url'])) {
    $url = $_GET['url'];
} elseif (isset($_SERVER['REQUEST_URI'])) {
    // Xử lý hash routing
    $hash = parse_url($_SERVER['REQUEST_URI'], PHP_URL_FRAGMENT);
    if ($hash) {
        $url = $hash;
    }
}

// Mặc định là home nếu không có URL
if (empty($url)) {
    $url = 'home';
}
// Load required files
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/core/Auth.php';

// Autoload classes
spl_autoload_register(function ($class_name) {
    $paths = [
        BASE_PATH . '/controllers/',
        BASE_PATH . '/models/',
        BASE_PATH . '/core/'
    ];
    
    foreach ($paths as $path) {
        $file = $path . $class_name . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Simple routing
switch ($url) {
// Dòng ~40-45
    case 'home':
        $controller = new ProductController();
        $controller->index();
        break;
        
    case 'products':
        $controller = new ProductController();
        if (isset($_GET['id']) && is_numeric($_GET['id'])) {
            $controller->detail($_GET['id']);
        } else {
            $controller->index();
        }
        break;
        
    case 'search':
        $controller = new ProductController();
        $controller->search();
        break;
        
    case 'login':
        $controller = new AuthController();
        $controller->login();
        break;
        
    case 'register':
        $controller = new AuthController();
        $controller->register();
        break;
        
    case 'logout':
        $controller = new AuthController();
        $controller->logout();
        break;
        
    case 'cart':
        $controller = new UserController();
        $controller->cart();
        break;
        
    case 'add-to-cart':
        $controller = new UserController();
        $controller->addToCart();
        break;
        
    case 'remove-from-cart':
        $controller = new UserController();
        $controller->removeFromCart();
        break;
        
    case 'update-cart':
        $controller = new UserController();
        $controller->updateCart();
        break;
        
    case 'checkout':
        $controller = new UserController();
        $controller->checkout();
        break;
        
    case 'place-order':
        $controller = new UserController();
        $controller->placeOrder();
        break;
        
    case 'orders':
        $controller = new UserController();
        if (isset($_GET['id']) && is_numeric($_GET['id'])) {
            $controller->orderDetail($_GET['id']);
        } else {
            $controller->orders();
        }
        break;
        
    case 'cancel-order':
        $controller = new UserController();
        if (isset($_GET['id']) && is_numeric($_GET['id'])) {
            $controller->cancelOrder($_GET['id']);
        }
        break;
        
    case 'review':
        $controller = new UserController();
        $controller->review();
        break;
        
    case 'profile':
        $controller = new UserController();
        $controller->profile();
        break;
        
    case 'update-profile':
        $controller = new UserController();
        $controller->updateProfile();
        break;
        
    case 'change-password':
        $controller = new UserController();
        $controller->changePassword();
        break;
        
    case 'addresses':
        $controller = new UserController();
        $controller->addresses();
        break;
        
    case 'add-address':
        $controller = new UserController();
        $controller->addAddress();
        break;
        
    case 'edit-address':
        $controller = new UserController();
        if (isset($_GET['id']) && is_numeric($_GET['id'])) {
            $controller->editAddress($_GET['id']);
        }
        break;
        
    case 'delete-address':
        $controller = new UserController();
        if (isset($_GET['id']) && is_numeric($_GET['id'])) {
            $controller->deleteAddress($_GET['id']);
        }
        break;
        
    case 'set-default-address':
        $controller = new UserController();
        if (isset($_GET['id']) && is_numeric($_GET['id'])) {
            $controller->setDefaultAddress($_GET['id']);
        }
        break;
        
    case 'wishlist':
        $controller = new UserController();
        $controller->wishlist();
        break;
        
    case 'add-to-wishlist':
        $controller = new UserController();
        $controller->addToWishlist();
        break;
        
    case 'remove-from-wishlist':
        $controller = new UserController();
        $controller->removeFromWishlist();
        break;
        
    case 'notifications':
        $controller = new UserController();
        $controller->notifications();
        break;
        
    case 'view-history':
        $controller = new UserController();
        $controller->viewHistory();
        break;
        
    case 'vouchers':
        $controller = new UserController();
        $controller->vouchers();
        break;
        
    case 'apply-voucher':
        $controller = new UserController();
        $controller->applyVoucher();
        break;
        
    // User dashboard
    case 'user':
        $controller = new UserController();
        if (!isset($_GET['url']) || $_GET['url'] === 'dashboard') {
            $controller->dashboard();
        }
        break;
        
    // Seller dashboard
    case 'seller':
        $controller = new SellerController();
        if (!isset($_GET['url']) || $_GET['url'] === 'dashboard') {
            $controller->dashboard();
        } elseif ($_GET['url'] === 'products') {
            $controller->products();
        } elseif ($_GET['url'] === 'add-product') {
            $controller->addProduct();
        } elseif (strpos($_GET['url'], 'edit-product') === 0) {
            $parts = explode('-', $_GET['url']);
            if (isset($parts[2]) && is_numeric($parts[2])) {
                $controller->editProduct($parts[2]);
            }
        } elseif (strpos($_GET['url'], 'delete-product') === 0) {
            $parts = explode('-', $_GET['url']);
            if (isset($parts[2]) && is_numeric($parts[2])) {
                $controller->deleteProduct($parts[2]);
            }
        } elseif ($_GET['url'] === 'orders') {
            $controller->orders();
        } elseif (strpos($_GET['url'], 'order-detail') === 0) {
            $parts = explode('-', $_GET['url']);
            if (isset($parts[2]) && is_numeric($parts[2])) {
                $controller->orderDetail($parts[2]);
            }
        } elseif ($_GET['url'] === 'update-order-status') {
            $controller->updateOrderStatus();
        } elseif ($_GET['url'] === 'revenue') {
            $controller->revenue();
        } elseif ($_GET['url'] === 'settings') {
            $controller->settings();
        }
        break;
        
    default:
        include BASE_PATH . '/views/404.php';
        break;
}
?>