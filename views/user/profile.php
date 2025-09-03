<div class="container">
    <div class="user-profile">
        <h1 class="page-title">Tài khoản của tôi</h1>
        
        <div class="profile-container">
            <div class="profile-sidebar">
                <div class="profile-avatar">
                    <img src="<?php echo Auth::user()['avatar'] ?: '/assets/images/default-avatar.png'; ?>" alt="Avatar" class="avatar-img">
                    <h3><?php echo Auth::user()['full_name']; ?></h3>
                    <p><?php echo Auth::user()['email']; ?></p>
                </div>
                
                <div class="profile-menu">
                    <a href="/profile" class="active"><i class="fas fa-user"></i> Thông tin tài khoản</a>
                    <a href="/change-password"><i class="fas fa-lock"></i> Đổi mật khẩu</a>
                    <a href="/addresses"><i class="fas fa-map-marker-alt"></i> Sổ địa chỉ</a>
                    <a href="/orders"><i class="fas fa-receipt"></i> Đơn hàng của tôi</a>
                    <a href="/wishlist"><i class="fas fa-heart"></i> Danh sách yêu thích</a>
                    <a href="/view-history"><i class="fas fa-history"></i> Lịch sử xem</a>
                    <a href="/vouchers"><i class="fas fa-ticket-alt"></i> Voucher của tôi</a>
                    <a href="/notifications"><i class="fas fa-bell"></i> Thông báo
                        <?php 
                        $notificationCount = $this->userModel->countUnreadNotifications(Auth::id());
                        if ($notificationCount > 0): 
                            echo '<span class="notification-count">' . $notificationCount . '</span>';
                        endif;
                        ?>
                    </a>
                </div>
            </div>
            
            <div class="profile-content">
                <div class="profile-section">
                    <h2>Thông tin tài khoản</h2>
                    
                    <?php if (isset($error)): ?>
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form action="/profile" method="POST" class="profile-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="full_name">Họ và tên</label>
                                <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="phone">Số điện thoại</label>
                                <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="address">Địa chỉ</label>
                                <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Ảnh đại diện</label>
                            <div class="avatar-upload">
                                <div class="avatar-preview" id="avatar-preview">
                                    <img src="<?php echo Auth::user()['avatar'] ?: '/assets/images/default-avatar.png'; ?>" alt="Avatar">
                                </div>
                                <input type="file" id="avatar" name="avatar" accept="image/*">
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn-primary">Lưu thay đổi</button>
                        </div>
                    </form>
                </div>
                
                <div class="profile-section">
                    <h2>Thống kê tài khoản</h2>
                    
                    <div class="stats-grid">
                        <div class="stat-box">
                            <i class="fas fa-shopping-cart"></i>
                            <h3><?php echo count($this->orderModel->getByCustomer(Auth::id())); ?></h3>
                            <p>Đơn hàng</p>
                        </div>
                        
                        <div class="stat-box">
                            <i class="fas fa-heart"></i>
                            <h3><?php echo count($this->userModel->getWishlist(Auth::id())); ?></h3>
                            <p>Sản phẩm yêu thích</p>
                        </div>
                        
                        <div class="stat-box">
                            <i class="fas fa-map-marker-alt"></i>
                            <h3><?php echo count($this->userModel->getAddresses(Auth::id())); ?></h3>
                            <p>Địa chỉ</p>
                        </div>
                        
                        <div class="stat-box">
                            <i class="fas fa-ticket-alt"></i>
                            <h3>0</h3>
                            <p>Voucher</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // User dropdown menu
    const userMenuToggle = document.querySelector('.user-menu-toggle');
    const userDropdownMenu = document.querySelector('.user-dropdown-menu');
    
    if (userMenuToggle && userDropdownMenu) {
        userMenuToggle.addEventListener('click', function(e) {
            e.preventDefault();
            userDropdownMenu.classList.toggle('active');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!userMenuToggle.contains(e.target) && !userDropdownMenu.contains(e.target)) {
                userDropdownMenu.classList.remove('active');
            }
        });
    }
    
    // Avatar upload
    const avatarInput = document.getElementById('avatar');
    const avatarPreview = document.getElementById('avatar-preview');
    
    if (avatarInput && avatarPreview) {
        avatarInput.addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    avatarPreview.innerHTML = `<img src="${e.target.result}" alt="Avatar">`;
                }
                
                reader.readAsDataURL(e.target.files[0]);
            }
        });
    }
});
</script>