<?php include 'views/layouts/header.php'; ?>

<div class="container">
    <div class="auth-container">
        <div class="auth-form">
            <h2>Đăng nhập</h2>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form action="/login" method="POST">
                <div class="form-group">
                    <label for="username">Tên đăng nhập</label>
                    <input type="text" id="username" name="username" placeholder="Nhập tên đăng nhập" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Mật khẩu</label>
                    <input type="password" id="password" name="password" placeholder="Nhập mật khẩu" required>
                </div>
                
                <div class="form-group">
                    <div class="remember-me">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">Ghi nhớ đăng nhập</label>
                    </div>
                    <a href="#" class="forgot-password">Quên mật khẩu?</a>
                </div>
                
                <button type="submit" class="btn-primary">Đăng nhập</button>
            </form>
            
            <div class="auth-switch">
                Chưa có tài khoản? <a href="/register">Đăng ký ngay</a>
            </div>
            
            <div class="social-login">
                <p>Hoặc đăng nhập với</p>
                <div class="social-buttons">
                    <button class="btn-facebook"><i class="fab fa-facebook-f"></i> Facebook</button>
                    <button class="btn-google"><i class="fab fa-google"></i> Google</button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.auth-container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 70vh;
    padding: 20px 0;
}

.auth-form {
    background: white;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    padding: 40px;
    width: 100%;
    max-width: 450px;
}

.auth-form h2 {
    text-align: center;
    margin-bottom: 30px;
    color: var(--dark-color);
}

.auth-switch {
    text-align: center;
    margin-top: 20px;
    font-size: 14px;
}

.auth-switch a {
    color: var(--primary-color);
    font-weight: 600;
}

.social-login {
    margin-top: 30px;
    text-align: center;
}

.social-login p {
    color: var(--gray);
    margin-bottom: 15px;
    position: relative;
}

.social-login p:before,
.social-login p:after {
    content: '';
    position: absolute;
    top: 50%;
    width: 40%;
    height: 1px;
    background: #eee;
}

.social-login p:before {
    left: 0;
}

.social-login p:after {
    right: 0;
}

.social-buttons {
    display: flex;
    gap: 15px;
}

.btn-facebook,
.btn-google {
    flex: 1;
    padding: 12px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-facebook {
    background: #1877f2;
    color: white;
}

.btn-google {
    background: #fff;
    color: #757575;
    border: 1px solid #ddd;
}

.btn-facebook:hover {
    background: #166fe5;
}

.btn-google:hover {
    background: #f8f8f8;
}

.forgot-password {
    color: var(--gray);
    font-size: 14px;
    text-decoration: none;
}

.forgot-password:hover {
    color: var(--primary-color);
}
</style>

<?php include 'views/layouts/footer.php'; ?>