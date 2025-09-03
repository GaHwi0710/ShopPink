<?php
class Auth {
    public static function check() {
        return isset($_SESSION['user']);
    }
    
    public static function user() {
        return $_SESSION['user'] ?? null;
    }
    
    public static function id() {
        return self::user()['id'] ?? null;
    }
    
    public static function isCustomer() {
        return self::check() && self::user()['role'] === 'customer';
    }
    
    public static function isSeller() {
        return self::check() && self::user()['role'] === 'seller';
    }
    
    public static function requireLogin() {
        if (!self::check()) {
            $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
            header('Location: /login');
            exit;
        }
    }
    
    public static function requireCustomer() {
        self::requireLogin();
        if (!self::isCustomer()) {
            header('Location: /login');
            exit;
        }
    }
    
    public static function requireSeller() {
        self::requireLogin();
        if (!self::isSeller()) {
            header('Location: /login');
            exit;
        }
    }
    
    public static function login($user) {
        $_SESSION['user'] = $user;
        
        // Redirect to intended URL or dashboard
        $redirect = $_SESSION['redirect_url'] ?? '/';
        unset($_SESSION['redirect_url']);
        
        if ($user['role'] === 'seller') {
            $redirect = '/seller/dashboard';
        }
        
        header('Location: ' . $redirect);
        exit;
    }
    
    public static function logout() {
        unset($_SESSION['user']);
        header('Location: /');
        exit;
    }
}
?>