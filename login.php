<?php
session_start();
include('config.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $sql = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            
            // Chuyển hướng đến trang chủ riêng của user
            header("Location: user_home.php");
        } else {
            $error = "Mật khẩu không đúng!";
        }
    } else {
        $error = "Username không tồn tại!";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - ShopPink</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/form.css">
</head>
<body>
    <?php include('includes/header.php'); ?>
    
    <div class="container">
        <div class="form-container">
            <h1>Đăng nhập</h1>
            
            <?php if (isset($error)) { ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php } ?>
            
            <form method="post" action="login.php">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Mật khẩu</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <div class="form-group">
                    <div class="remember-me">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">Ghi nhớ đăng nhập</label>
                    </div>
                    <a href="forgot_password.php" class="forgot-password">Quên mật khẩu?</a>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Đăng nhập</button>
                </div>
                
                <div class="form-footer">
                    <p>Chưa có tài khoản? <a href="register.php">Đăng ký</a></p>
                </div>
            </form>
        </div>
    </div>
    
    <?php include('includes/footer.php'); ?>
</body>
</html>