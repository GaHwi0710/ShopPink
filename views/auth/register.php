<?php include 'views/layouts/header.php'; ?>

<div class="container">
    <div class="auth-container">
        <div class="auth-form register-form">
            <h2>Đăng ký tài khoản</h2>
            
            <?php if (isset($errors) && !empty($errors)): ?>
                <div class="alert alert-error">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form action="/register" method="POST">
                <div class="form-group">
                    <label for="full_name">Họ và tên</label>
                    <input type="text" id="full_name" name="full_name" placeholder="Nhập họ và tên" required>
                </div>
                
                <div class="form-group">
                    <label for="username">Tên đăng nhập</label>
                    <input type="text" id="username" name="username" placeholder="Nhập tên đăng nhập" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="Nhập email" required>
                </div>
                
                <div class="form-group">
                    <label for="phone">Số điện thoại</label>
                    <input type="tel" id="phone" name="phone" placeholder="Nhập số điện thoại">
                </div>
                
                <div class="form-group">
                    <label for="address">Địa chỉ</label>
                    <input type="text" id="address" name="address" placeholder="Nhập địa chỉ">
                </div>
                
                <div class="form-group">
                    <label for="password">Mật khẩu</label>
                    <input type="password" id="password" name="password" placeholder="Nhập mật khẩu" required>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Xác nhận mật khẩu</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Nhập lại mật khẩu" required>
                </div>
                
                <div class="form-group">
                    <label>Đăng ký với tư cách:</label>
                    <div class="radio-group">
                        <label>
                            <input type="radio" name="role" value="customer" checked> Người mua hàng
                        </label>
                        <label>
                            <input type="radio" name="role" value="seller"> Người bán hàng
                        </label>
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="terms">
                        <input type="checkbox" id="agree_terms" name="agree_terms" required>
                        <label for="agree_terms">Tôi đồng ý với <a href="#">điều khoản sử dụng</a> và <a href="#">chính sách bảo mật</a></label>
                    </div>
                </div>
                
                <button type="submit" class="btn-primary">Đăng ký</button>
            </form>
            
            <div class="auth-switch">
                Đã có tài khoản? <a href="/login">Đăng nhập</a>
            </div>
        </div>
    </div>
</div>

<style>
.register-form {
    max-width: 550px;
}

.radio-group {
    display: flex;
    gap: 20px;
    margin-top: 10px;
}

.radio-group label {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
}

.terms {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    margin-top: 10px;
}

.terms label {
    font-size: 14px;
    line-height: 1.4;
}

.terms a {
    color: var(--primary-color);
    text-decoration: none;
}

.terms a:hover {
    text-decoration: underline;
}
</style>

<?php include 'views/layouts/footer.php'; ?>