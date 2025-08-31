<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'shoppink');

// Site configuration
define('SITE_NAME', 'ShopPink');
define('SITE_URL', 'http://localhost/ShopPink');
define('SITE_EMAIL', 'info@shoppink.com');

// Cart configuration
define('CART_EXPIRY', 30 * 24 * 60 * 60); // 30 days

// Upload configuration
define('UPLOAD_PATH', 'assets/images/products/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif']);

// Database connection
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8");

// Helper functions
function format_price($price) {
    return number_format($price, 0, ',', '.') . ' ₫';
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function get_user_info($user_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function set_flash_message($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

function display_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        
        $alert_class = $message['type'] == 'error' ? 'alert-error' : 'alert-success';
        $icon_class = $message['type'] == 'error' ? 'fa-exclamation-circle' : 'fa-check-circle';
        
        echo "<div class='alert $alert_class'>";
        echo "<i class='fas $icon_class'></i>";
        echo htmlspecialchars($message['message']);
        echo "</div>";
    }
}

function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function create_slug($string) {
    $string = strtolower($string);
    $string = preg_replace('/[^a-z0-9\s-]/', '', $string);
    $string = preg_replace('/[\s-]+/', '-', $string);
    return trim($string, '-');
}

function is_valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function generate_random_password($length = 8) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[rand(0, strlen($chars) - 1)];
    }
    return $password;
}

function send_email($to, $subject, $message) {
    $headers = "From: " . SITE_EMAIL . "\r\n";
    $headers .= "Reply-To: " . SITE_EMAIL . "\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    return mail($to, $subject, $message, $headers);
}

function log_error($message, $file = '', $line = '') {
    $log_entry = date('Y-m-d H:i:s') . " - ERROR: $message";
    if ($file) $log_entry .= " in $file";
    if ($line) $log_entry .= " on line $line";
    $log_entry .= "\n";
    
    error_log($log_entry, 3, 'error.log');
}

function get_client_ip() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>