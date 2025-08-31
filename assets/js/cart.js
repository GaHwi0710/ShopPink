// Cart functionality for ShopPink

class Cart {
    constructor() {
        this.items = this.loadFromStorage();
        this.init();
    }
    
    init() {
        this.updateCartCount();
        this.setupEventListeners();
    }
    
    // Load cart from localStorage
    loadFromStorage() {
        const cartData = localStorage.getItem('shoppink_cart');
        return cartData ? JSON.parse(cartData) : {};
    }
    
    // Save cart to localStorage
    saveToStorage() {
        localStorage.setItem('shoppink_cart', JSON.stringify(this.items));
    }
    
    // Add item to cart
    addItem(productId, quantity = 1) {
        if (this.items[productId]) {
            this.items[productId] += quantity;
        } else {
            this.items[productId] = quantity;
        }
        
        this.saveToStorage();
        this.updateCartCount();
        this.showAddToCartAnimation();
    }
    
    // Update item quantity
    updateItem(productId, quantity) {
        if (quantity > 0) {
            this.items[productId] = quantity;
        } else {
            delete this.items[productId];
        }
        
        this.saveToStorage();
        this.updateCartCount();
        this.updateCartDisplay();
    }
    
    // Remove item from cart
    removeItem(productId) {
        delete this.items[productId];
        this.saveToStorage();
        this.updateCartCount();
        this.updateCartDisplay();
    }
    
    // Clear cart
    clearCart() {
        this.items = {};
        this.saveToStorage();
        this.updateCartCount();
        this.updateCartDisplay();
    }
    
    // Get cart items with product details
    async getCartItems() {
        const productIds = Object.keys(this.items);
        if (productIds.length === 0) return [];
        
        try {
            const response = await fetch('api/cart_items.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ product_ids: productIds })
            });
            
            const products = await response.json();
            
            return products.map(product => ({
                ...product,
                quantity: this.items[product.id],
                subtotal: product.price * this.items[product.id]
            }));
        } catch (error) {
            console.error('Error fetching cart items:', error);
            return [];
        }
    }
    
    // Get cart total
    async getCartTotal() {
        const items = await this.getCartItems();
        return items.reduce((total, item) => total + item.subtotal, 0);
    }
    
    // Update cart count display
    updateCartCount() {
        const cartCount = document.querySelector('.cart-count');
        if (cartCount) {
            const totalItems = Object.values(this.items).reduce((sum, qty) => sum + qty, 0);
            cartCount.textContent = totalItems;
            
            if (totalItems > 0) {
                cartCount.style.display = 'flex';
            } else {
                cartCount.style.display = 'none';
            }
        }
    }
    
    // Update cart display
    updateCartDisplay() {
        const cartContainer = document.querySelector('.cart-items');
        if (cartContainer) {
            this.renderCartItems(cartContainer);
        }
    }
    
    // Render cart items
    async renderCartItems(container) {
        const items = await this.getCartItems();
        
        if (items.length === 0) {
            container.innerHTML = `
                <div class="empty-cart">
                    <div class="empty-cart-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <h3>Giỏ hàng trống</h3>
                    <p>Bạn chưa có sản phẩm nào trong giỏ hàng.</p>
                    <a href="products.php" class="btn btn-primary">
                        <i class="fas fa-shopping-bag"></i> Tiếp tục mua sắm
                    </a>
                </div>
            `;
            return;
        }
        
        let html = '';
        items.forEach(item => {
            html += `
                <div class="cart-item" data-product-id="${item.id}">
                    <div class="item-image">
                        <img src="assets/images/products/${item.image}" alt="${item.name}">
                    </div>
                    
                    <div class="item-info">
                        <h4 class="item-name">
                            <a href="product_detail.php?id=${item.id}">${item.name}</a>
                        </h4>
                        <p class="item-category">${item.category_name}</p>
                        <p class="item-price">${this.formatPrice(item.price)}</p>
                    </div>
                    
                    <div class="item-quantity">
                        <div class="quantity-controls">
                            <button type="button" class="qty-btn" onclick="cart.changeQuantity(${item.id}, -1)">-</button>
                            <input type="number" value="${item.quantity}" min="1" max="${item.stock}" 
                                   onchange="cart.changeQuantity(${item.id}, this.value)">
                            <button type="button" class="qty-btn" onclick="cart.changeQuantity(${item.id}, 1)">+</button>
                        </div>
                    </div>
                    
                    <div class="item-subtotal">
                        <span class="subtotal-price">${this.formatPrice(item.subtotal)}</span>
                    </div>
                    
                    <div class="item-actions">
                        <button class="btn btn-danger btn-small" onclick="cart.removeItem(${item.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `;
        });
        
        container.innerHTML = html;
        this.updateCartSummary();
    }
    
    // Update cart summary
    async updateCartSummary() {
        const summaryContainer = document.querySelector('.cart-summary');
        if (!summaryContainer) return;
        
        const total = await this.getCartTotal();
        
        summaryContainer.innerHTML = `
            <h3>Tổng kết giỏ hàng</h3>
            
            <div class="summary-item">
                <span>Tổng tiền hàng:</span>
                <span>${this.formatPrice(total)}</span>
            </div>
            
            <div class="summary-item">
                <span>Phí vận chuyển:</span>
                <span>Miễn phí</span>
            </div>
            
            <div class="summary-total">
                <span>Tổng cộng:</span>
                <span>${this.formatPrice(total)}</span>
            </div>
            
            <div class="cart-actions">
                <a href="products.php" class="btn btn-outline-primary btn-block">
                    <i class="fas fa-arrow-left"></i> Tiếp tục mua sắm
                </a>
                
                <button class="btn btn-outline-danger btn-block" onclick="cart.clearCart()">
                    <i class="fas fa-trash"></i> Làm trống giỏ hàng
                </button>
                
                <a href="checkout.php" class="btn btn-primary btn-block">
                    <i class="fas fa-credit-card"></i> Tiến hành thanh toán
                </a>
            </div>
        `;
    }
    
    // Change quantity
    changeQuantity(productId, change) {
        let newQuantity;
        
        if (typeof change === 'string') {
            newQuantity = parseInt(change);
        } else {
            newQuantity = (this.items[productId] || 0) + change;
        }
        
        if (newQuantity > 0) {
            this.updateItem(productId, newQuantity);
        } else {
            this.removeItem(productId);
        }
    }
    
    // Show add to cart animation
    showAddToCartAnimation() {
        const cartIcon = document.querySelector('.cart-icon');
        if (cartIcon) {
            cartIcon.classList.add('bounce');
            setTimeout(() => {
                cartIcon.classList.remove('bounce');
            }, 300);
        }
        
        // Show notification
        if (window.ShopPink) {
            window.ShopPink.showNotification('Sản phẩm đã được thêm vào giỏ hàng!', 'success');
        }
    }
    
    // Setup event listeners
    setupEventListeners() {
        // Quantity input changes
        document.addEventListener('change', (e) => {
            if (e.target.matches('.item-quantity input[type="number"]')) {
                const productId = parseInt(e.target.closest('.cart-item').dataset.productId);
                const quantity = parseInt(e.target.value);
                this.changeQuantity(productId, quantity);
            }
        });
        
        // Clear cart confirmation
        document.addEventListener('click', (e) => {
            if (e.target.matches('.btn-outline-danger') && e.target.textContent.includes('Làm trống')) {
                if (!confirm('Bạn có chắc muốn làm trống giỏ hàng?')) {
                    e.preventDefault();
                }
            }
        });
    }
    
    // Format price
    formatPrice(price) {
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(price);
    }
}

// Initialize cart
const cart = new Cart();

// Global cart functions
window.addToCart = function(productId, quantity = 1) {
    cart.addItem(productId, quantity);
};

window.updateCartItem = function(productId, quantity) {
    cart.updateItem(productId, quantity);
};

window.removeFromCart = function(productId) {
    cart.removeItem(productId);
};

window.clearCart = function() {
    cart.clearCart();
};