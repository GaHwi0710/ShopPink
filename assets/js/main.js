// Xử lý modal đăng nhập/đăng ký/giỏ hàng
document.addEventListener('DOMContentLoaded', function() {
    // Xử lý modal
    const modalTriggers = document.querySelectorAll('.modal-trigger');
    const modals = document.querySelectorAll('.modal');
    const closeButtons = document.querySelectorAll('.modal-close');
    
    // Mở modal khi nhấp vào trigger
    modalTriggers.forEach(trigger => {
        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            const modalId = this.getAttribute('data-modal');
            const modal = document.getElementById(modalId);
            
            if (modal) {
                modal.classList.add('active');
                document.body.style.overflow = 'hidden'; // Ngăn cuộn trang khi modal mở
                
                // Load nội dung modal nếu cần
                if (modalId === 'cart-modal') {
                    loadCartContent();
                } else if (modalId === 'wishlist-modal') {
                    loadWishlistContent();
                } else if (modalId === 'notification-modal') {
                    loadNotificationContent();
                } else if (modalId === 'voucher-modal') {
                    loadVoucherContent();
                }
            }
        });
    });
    
    // Đóng modal khi nhấp vào nút close
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const modal = this.closest('.modal');
            if (modal) {
                modal.classList.remove('active');
                document.body.style.overflow = ''; // Khôi phục cuộn trang
            }
        });
    });
    
    // Đóng modal khi nhấp vào vùng ngoài modal
    modals.forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.classList.remove('active');
                document.body.style.overflow = ''; // Khôi phục cuộn trang
            }
        });
    });
    
    // Xử lý form đăng nhập
    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const errorContainer = document.getElementById('login-error-container');
            
            // Xóa thông báo lỗi cũ
            errorContainer.innerHTML = '';
            
            fetch('/login', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Đăng nhập thành công, chuyển hướng đến trang dashboard
                    window.location.href = data.redirect;
                } else {
                    // Hiển thị thông báo lỗi
                    errorContainer.innerHTML = `<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> ${data.message}</div>`;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                errorContainer.innerHTML = `<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> Đã xảy ra lỗi, vui lòng thử lại!</div>`;
            });
        });
    }
    
    // Xử lý form đăng ký
    const registerForm = document.getElementById('register-form');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const errorContainer = document.getElementById('register-error-container');
            
            // Xóa thông báo lỗi cũ
            errorContainer.innerHTML = '';
            
            fetch('/register', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Đăng ký thành công, chuyển hướng đến trang dashboard
                    window.location.href = data.redirect;
                } else {
                    // Hiển thị thông báo lỗi
                    errorContainer.innerHTML = `<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> ${data.message}</div>`;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                errorContainer.innerHTML = `<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> Đã xảy ra lỗi, vui lòng thử lại!</div>`;
            });
        });
    }
    
    // Xử lý nút thêm vào giỏ hàng
    const addToCartBtns = document.querySelectorAll('.add-to-cart, .add-to-cart-btn');
    addToCartBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.getAttribute('data-product-id');
            const quantityInput = this.closest('.product-actions, .quick-view-details')?.querySelector('.quantity-input');
            const quantity = quantityInput ? parseInt(quantityInput.value) : 1;
            
            if (!productId) {
                showToast('error', 'Không tìm thấy sản phẩm!');
                return;
            }
            
            // Add loading state
            this.classList.add('loading');
            this.disabled = true;
            
            // Tạo form data
            const formData = new FormData();
            formData.append('product_id', productId);
            formData.append('quantity', quantity);
            
            // Gửi request
            fetch('/add-to-cart', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // Remove loading state
                this.classList.remove('loading');
                this.disabled = false;
                
                if (data.success) {
                    // Show success message
                    showToast('success', data.message);
                    
                    // Update cart count
                    const cartCount = document.querySelector('.cart-count');
                    if (cartCount) {
                        cartCount.textContent = data.cart_count;
                    }
                    
                    // Update cart modal if open
                    const cartModal = document.getElementById('cart-modal');
                    if (cartModal.classList.contains('active')) {
                        loadCartContent();
                    }
                } else {
                    showToast('error', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('error', 'Đã xảy ra lỗi, vui lòng thử lại!');
                
                // Remove loading state
                this.classList.remove('loading');
                this.disabled = false;
            });
        });
    });
    
    // Xử lý nút yêu thích
    const wishlistBtns = document.querySelectorAll('.wishlist-btn');
    wishlistBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.getAttribute('data-product-id');
            
            if (!productId) {
                showToast('error', 'Không tìm thấy sản phẩm!');
                return;
            }
            
            // Toggle active state
            this.classList.toggle('active');
            const isActive = this.classList.contains('active');
            
            // Tạo form data
            const formData = new FormData();
            formData.append('product_id', productId);
            
            // Gửi request
            fetch('/add-to-wishlist', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('success', data.message);
                    
                    // Update wishlist modal if open
                    const wishlistModal = document.getElementById('wishlist-modal');
                    if (wishlistModal.classList.contains('active')) {
                        loadWishlistContent();
                    }
                } else {
                    showToast('error', data.message);
                    // Revert active state on error
                    this.classList.toggle('active');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('error', 'Đã xảy ra lỗi, vui lòng thử lại!');
                // Revert active state on error
                this.classList.toggle('active');
            });
        });
    });
    
    // Xử lý nút xem nhanh
    const quickViewBtns = document.querySelectorAll('.quick-view-btn');
    const quickViewModal = document.getElementById('quick-view-modal');
    const quickViewClose = document.getElementById('quick-view-close');
    
    if (quickViewBtns && quickViewModal) {
        quickViewBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const productId = this.getAttribute('data-product-id');
                
                if (productId) {
                    // Show loading state
                    quickViewModal.classList.add('active');
                    document.body.style.overflow = 'hidden';
                    
                    const quickViewContent = document.getElementById('quick-view-content');
                    quickViewContent.innerHTML = '<div class="loading-spinner"></div>';
                    
                    // Load product details via AJAX
                    const formData = new FormData();
                    formData.append('product_id', productId);
                    
                    fetch('/quick-view', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            quickViewContent.innerHTML = data.content;
                            
                            // Re-attach event listeners for quantity buttons
                            attachQuantityListeners();
                            
                            // Re-attach event listeners for action buttons
                            const addToCartBtn = quickViewContent.querySelector('.add-to-cart-btn');
                            const wishlistBtn = quickViewContent.querySelector('.wishlist-btn');
                            
                            if (addToCartBtn) {
                                addToCartBtn.addEventListener('click', function() {
                                    const productId = this.getAttribute('data-product-id');
                                    const quantityInput = quickViewContent.querySelector('.quantity-input');
                                    const quantity = quantityInput ? parseInt(quantityInput.value) : 1;
                                    
                                    if (!productId) {
                                        showToast('error', 'Không tìm thấy sản phẩm!');
                                        return;
                                    }
                                    
                                    // Add loading state
                                    this.classList.add('loading');
                                    this.disabled = true;
                                    
                                    // Tạo form data
                                    const formData = new FormData();
                                    formData.append('product_id', productId);
                                    formData.append('quantity', quantity);
                                    
                                    // Gửi request
                                    fetch('/add-to-cart', {
                                        method: 'POST',
                                        body: formData
                                    })
                                    .then(response => response.json())
                                    .then(data => {
                                        // Remove loading state
                                        this.classList.remove('loading');
                                        this.disabled = false;
                                        
                                        if (data.success) {
                                            // Show success message
                                            showToast('success', data.message);
                                            
                                            // Update cart count
                                            const cartCount = document.querySelector('.cart-count');
                                            if (cartCount) {
                                                cartCount.textContent = data.cart_count;
                                            }
                                        } else {
                                            showToast('error', data.message);
                                        }
                                    })
                                    .catch(error => {
                                        console.error('Error:', error);
                                        showToast('error', 'Đã xảy ra lỗi, vui lòng thử lại!');
                                        
                                        // Remove loading state
                                        this.classList.remove('loading');
                                        this.disabled = false;
                                    });
                                });
                            }
                            
                            if (wishlistBtn) {
                                wishlistBtn.addEventListener('click', function() {
                                    const productId = this.getAttribute('data-product-id');
                                    
                                    if (!productId) {
                                        showToast('error', 'Không tìm thấy sản phẩm!');
                                        return;
                                    }
                                    
                                    // Toggle active state
                                    this.classList.toggle('active');
                                    const isActive = this.classList.contains('active');
                                    
                                    // Tạo form data
                                    const formData = new FormData();
                                    formData.append('product_id', productId);
                                    
                                    // Gửi request
                                    fetch('/add-to-wishlist', {
                                        method: 'POST',
                                        body: formData
                                    })
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.success) {
                                            showToast('success', data.message);
                                        } else {
                                            showToast('error', data.message);
                                            // Revert active state on error
                                            this.classList.toggle('active');
                                        }
                                    })
                                    .catch(error => {
                                        console.error('Error:', error);
                                        showToast('error', 'Đã xảy ra lỗi, vui lòng thử lại!');
                                        // Revert active state on error
                                        this.classList.toggle('active');
                                    });
                                });
                            }
                        } else {
                            quickViewContent.innerHTML = `<div class="error-message">${data.message}</div>`;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        quickViewContent.innerHTML = '<div class="error-message">Không thể tải thông tin sản phẩm!</div>';
                    });
                }
            });
        });
        
        quickViewClose.addEventListener('click', () => {
            quickViewModal.classList.remove('active');
            document.body.style.overflow = '';
        });
        
        // Close modal when clicking outside
        quickViewModal.addEventListener('click', (e) => {
            if (e.target === quickViewModal) {
                quickViewModal.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    }
    
    // Xử lý nút tăng/giảm số lượng
    function attachQuantityListeners() {
        const minusBtns = document.querySelectorAll('.quantity-btn.minus');
        const plusBtns = document.querySelectorAll('.quantity-btn.plus');
        
        minusBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const input = this.nextElementSibling;
                const min = parseInt(input.getAttribute('min')) || 1;
                let value = parseInt(input.value);
                
                if (value > min) {
                    input.value = value - 1;
                }
            });
        });
        
        plusBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const input = this.previousElementSibling;
                const max = parseInt(input.getAttribute('max')) || 99;
                let value = parseInt(input.value);
                
                if (value < max) {
                    input.value = value + 1;
                }
            });
        });
    }
    
    // Gọi hàm lần đầu
    attachQuantityListeners();
    
    // Load cart content
    function loadCartContent() {
        const cartContent = document.getElementById('cart-content');
        
        if (cartContent) {
            cartContent.innerHTML = '<div class="loading-spinner"></div>';
            
            fetch('/cart')
                .then(response => response.text())
                .then(html => {
                    cartContent.innerHTML = html;
                    
                    // Re-attach event listeners for cart items
                    attachCartListeners();
                    
                    // Calculate and update cart totals
                    updateCartTotals();
                })
                .catch(error => {
                    console.error('Error:', error);
                    cartContent.innerHTML = '<div class="error-message">Không thể tải giỏ hàng!</div>';
                });
        }
    }
    
    // Load wishlist content
    function loadWishlistContent() {
        const wishlistContent = document.getElementById('wishlist-content');
        
        if (wishlistContent) {
            wishlistContent.innerHTML = '<div class="loading-spinner"></div>';
            
            fetch('/wishlist')
                .then(response => response.text())
                .then(html => {
                    wishlistContent.innerHTML = html;
                })
                .catch(error => {
                    console.error('Error:', error);
                    wishlistContent.innerHTML = '<div class="error-message">Không thể tải danh sách yêu thích!</div>';
                });
        }
    }
    
    // Load notification content
    function loadNotificationContent() {
        const notificationContent = document.getElementById('notification-content');
        
        if (notificationContent) {
            notificationContent.innerHTML = '<div class="loading-spinner"></div>';
            
            fetch('/notifications')
                .then(response => response.text())
                .then(html => {
                    notificationContent.innerHTML = html;
                })
                .catch(error => {
                    console.error('Error:', error);
                    notificationContent.innerHTML = '<div class="error-message">Không thể tải thông báo!</div>';
                });
        }
    }
    
    // Load voucher content
    function loadVoucherContent() {
        const voucherContent = document.getElementById('voucher-content');
        
        if (voucherContent) {
            voucherContent.innerHTML = '<div class="loading-spinner"></div>';
            
            fetch('/vouchers')
                .then(response => response.text())
                .then(html => {
                    voucherContent.innerHTML = html;
                })
                .catch(error => {
                    console.error('Error:', error);
                    voucherContent.innerHTML = '<div class="error-message">Không thể tải voucher!</div>';
                });
        }
    }
    
    // Attach event listeners for cart items
    function attachCartListeners() {
        // Remove item buttons
        const removeBtns = document.querySelectorAll('.remove-item');
        removeBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const productId = this.getAttribute('data-product-id');
                
                if (productId) {
                    if (confirm('Bạn có chắc muốn xóa sản phẩm này khỏi giỏ hàng?')) {
                        window.location.href = `/remove-from-cart?id=${productId}`;
                    }
                }
            });
        });
        
        // Quantity buttons
        const decreaseBtns = document.querySelectorAll('.decrease-qty');
        const increaseBtns = document.querySelectorAll('.increase-qty');
        
        decreaseBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const productId = this.getAttribute('data-product-id');
                const input = document.querySelector(`.qty-input[data-id="${productId}"]`);
                
                if (input && parseInt(input.value) > 1) {
                    input.value = parseInt(input.value) - 1;
                    updateCartItem(productId, input.value);
                }
            });
        });
        
        increaseBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const productId = this.getAttribute('data-product-id');
                const input = document.querySelector(`.qty-input[data-id="${productId}"]`);
                
                if (input) {
                    input.value = parseInt(input.value) + 1;
                    updateCartItem(productId, input.value);
                }
            });
        });
        
        // Apply voucher button
        const applyVoucherBtn = document.getElementById('apply-voucher');
        if (applyVoucherBtn) {
            applyVoucherBtn.addEventListener('click', function() {
                const voucherCode = document.getElementById('voucher-code').value;
                
                if (voucherCode) {
                    const formData = new FormData();
                    formData.append('voucher_code', voucherCode);
                    
                    fetch('/apply-voucher', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('voucher-info').innerHTML = `
                                <div class="success-message">
                                    <i class="fas fa-check-circle"></i> ${data.message}
                                </div>
                            `;
                            document.getElementById('voucher-info').style.display = 'block';
                            
                            // Update cart totals
                            updateCartTotals();
                        } else {
                            document.getElementById('voucher-info').innerHTML = `
                                <div class="error-message">
                                    <i class="fas fa-exclamation-circle"></i> ${data.message}
                                </div>
                            `;
                            document.getElementById('voucher-info').style.display = 'block';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showToast('error', 'Đã xảy ra lỗi, vui lòng thử lại!');
                    });
                }
            });
        }
    }
    
    // Update cart item
    function updateCartItem(productId, quantity) {
        const formData = new FormData();
        formData.append('product_id', productId);
        formData.append('quantity', quantity);
        
        fetch('/update-cart', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('success', data.message);
                
                // Update cart count
                const cartCount = document.querySelector('.cart-count');
                if (cartCount) {
                    cartCount.textContent = data.cart_count;
                }
                
                // Update cart totals
                updateCartTotals();
            } else {
                showToast('error', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('error', 'Đã xảy ra lỗi, vui lòng thử lại!');
        });
    }
    
    // Update cart totals
    function updateCartTotals() {
        let subtotal = 0;
        const cartItems = document.querySelectorAll('.cart-item');
        
        cartItems.forEach(item => {
            const price = parseFloat(item.getAttribute('data-price')) || 0;
            const quantity = parseInt(item.querySelector('.qty-input').value) || 0;
            subtotal += price * quantity;
        });
        
        const shipping = subtotal >= 500000 ? 0 : 30000;
        const voucherDiscount = parseFloat(document.getElementById('voucher-discount')?.value || 0);
        const total = subtotal + shipping - voucherDiscount;
        
        document.getElementById('cart-subtotal').textContent = formatCurrency(subtotal);
        document.getElementById('cart-shipping').textContent = shipping === 0 ? 'Miễn phí' : formatCurrency(shipping);
        document.getElementById('cart-total').textContent = formatCurrency(total);
    }
    
    // Format currency
    function formatCurrency(amount) {
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(amount);
    }
    
    // Toast notification function
    function showToast(type, message) {
        let toastContainer = document.getElementById('toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.className = 'toast-container';
            toastContainer.id = 'toast-container';
            document.body.appendChild(toastContainer);
        }
        
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        
        let icon = '';
        switch(type) {
            case 'success':
                icon = 'fa-check-circle';
                break;
            case 'error':
                icon = 'fa-exclamation-circle';
                break;
            case 'info':
                icon = 'fa-info-circle';
                break;
            default:
                icon = 'fa-bell';
        }
        
        toast.innerHTML = `
            <i class="fas ${icon} toast-icon"></i>
            <span>${message}</span>
            <button class="toast-close">&times;</button>
        `;
        
        toastContainer.appendChild(toast);
        
        // Show toast
        setTimeout(() => {
            toast.classList.add('show');
        }, 10);
        
        // Hide toast after 5 seconds
        const hideTimeout = setTimeout(() => {
            hideToast(toast);
        }, 5000);
        
        // Close button
        toast.querySelector('.toast-close').addEventListener('click', () => {
            clearTimeout(hideTimeout);
            hideToast(toast);
        });
    }
    
    function hideToast(toast) {
        toast.classList.remove('show');
        setTimeout(() => {
            toast.remove();
        }, 300);
    }
});