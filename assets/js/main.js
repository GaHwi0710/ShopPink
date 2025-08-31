// Main JavaScript file for ShopPink

// DOM Content Loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

// Initialize application
function initializeApp() {
    setupEventListeners();
    setupAnimations();
    setupLazyLoading();
    setupSearchSuggestions();
    setupCartAnimations();
}

// Setup event listeners
function setupEventListeners() {
    // Mobile menu toggle
    const navToggle = document.querySelector('.nav-toggle');
    const navMenu = document.querySelector('.nav-menu');
    
    if (navToggle && navMenu) {
        navToggle.addEventListener('click', function() {
            navMenu.classList.toggle('active');
            navToggle.classList.toggle('active');
        });
    }
    
    // Close mobile menu when clicking outside
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.main-nav')) {
            if (navMenu) navMenu.classList.remove('active');
            if (navToggle) navToggle.classList.remove('active');
        }
    });
    
    // Search form submission
    const searchForm = document.querySelector('.search-form');
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            const searchInput = this.querySelector('input[name="search"]');
            if (searchInput && searchInput.value.trim() === '') {
                e.preventDefault();
                searchInput.focus();
                showNotification('Vui lòng nhập từ khóa tìm kiếm', 'warning');
            }
        });
    }
    
    // Quantity input validation
    const quantityInputs = document.querySelectorAll('input[type="number"]');
    quantityInputs.forEach(input => {
        input.addEventListener('change', function() {
            const value = parseInt(this.value);
            const min = parseInt(this.min);
            const max = parseInt(this.max);
            
            if (value < min) this.value = min;
            if (value > max) this.value = max;
        });
    });
    
    // Form validation
    setupFormValidation();
    
    // Image gallery
    setupImageGallery();
    
    // Product tabs
    setupProductTabs();
    
    // Modal functionality
    setupModals();
}

// Setup animations
function setupAnimations() {
    // Fade in elements on scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in');
            }
        });
    }, observerOptions);
    
    // Observe elements for animation
    const animateElements = document.querySelectorAll('.product-card, .category-card, .stat-card');
    animateElements.forEach(el => observer.observe(el));
    
    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

// Setup lazy loading for images
function setupLazyLoading() {
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    imageObserver.unobserve(img);
                }
            });
        });
        
        document.querySelectorAll('img[data-src]').forEach(img => {
            imageObserver.observe(img);
        });
    }
}

// Setup search suggestions
function setupSearchSuggestions() {
    const searchInput = document.querySelector('input[name="search"]');
    if (!searchInput) return;
    
    let searchTimeout;
    const suggestionsContainer = document.createElement('div');
    suggestionsContainer.className = 'search-suggestions';
    searchInput.parentNode.appendChild(suggestionsContainer);
    
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();
        
        if (query.length < 2) {
            suggestionsContainer.style.display = 'none';
            return;
        }
        
        searchTimeout = setTimeout(() => {
            fetchSearchSuggestions(query, suggestionsContainer);
        }, 300);
    });
    
    // Hide suggestions when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.search-input-group')) {
            suggestionsContainer.style.display = 'none';
        }
    });
}

// Fetch search suggestions
async function fetchSearchSuggestions(query, container) {
    try {
        const response = await fetch(`api/search_suggestions.php?q=${encodeURIComponent(query)}`);
        const suggestions = await response.json();
        
        if (suggestions.length > 0) {
            displaySearchSuggestions(suggestions, container);
        } else {
            container.style.display = 'none';
        }
    } catch (error) {
        console.error('Error fetching suggestions:', error);
        container.style.display = 'none';
    }
}

// Display search suggestions
function displaySearchSuggestions(suggestions, container) {
    container.innerHTML = '';
    
    suggestions.forEach(suggestion => {
        const item = document.createElement('div');
        item.className = 'suggestion-item';
        item.innerHTML = `
            <a href="products.php?search=${encodeURIComponent(suggestion.name)}">
                <i class="fas fa-search"></i>
                ${suggestion.name}
            </a>
        `;
        container.appendChild(item);
    });
    
    container.style.display = 'block';
}

// Setup cart animations
function setupCartAnimations() {
    // Animate cart count changes
    const cartCount = document.querySelector('.cart-count');
    if (cartCount) {
        cartCount.addEventListener('DOMSubtreeModified', function() {
            this.classList.add('pulse');
            setTimeout(() => {
                this.classList.remove('pulse');
            }, 300);
        });
    }
}

// Setup form validation
function setupFormValidation() {
    const forms = document.querySelectorAll('form[data-validate]');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
            }
        });
    });
}

// Validate form
function validateForm(form) {
    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            showFieldError(field, 'Trường này là bắt buộc');
            isValid = false;
        } else {
            clearFieldError(field);
        }
        
        // Email validation
        if (field.type === 'email' && field.value.trim()) {
            if (!isValidEmail(field.value)) {
                showFieldError(field, 'Email không hợp lệ');
                isValid = false;
            }
        }
        
        // Phone validation
        if (field.name === 'phone' && field.value.trim()) {
            if (!isValidPhone(field.value)) {
                showFieldError(field, 'Số điện thoại không hợp lệ');
                isValid = false;
            }
        }
    });
    
    return isValid;
}

// Show field error
function showFieldError(field, message) {
    clearFieldError(field);
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'field-error';
    errorDiv.textContent = message;
    
    field.parentNode.appendChild(errorDiv);
    field.classList.add('error');
}

// Clear field error
function clearFieldError(field) {
    const errorDiv = field.parentNode.querySelector('.field-error');
    if (errorDiv) {
        errorDiv.remove();
    }
    field.classList.remove('error');
}

// Email validation
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Phone validation
function isValidPhone(phone) {
    const phoneRegex = /^[0-9]{10,11}$/;
    return phoneRegex.test(phone);
}

// Setup image gallery
function setupImageGallery() {
    const mainImage = document.getElementById('mainImage');
    const thumbnails = document.querySelectorAll('.thumbnail-images img');
    
    if (mainImage && thumbnails.length > 0) {
        thumbnails.forEach(thumb => {
            thumb.addEventListener('click', function() {
                changeMainImage(this.src);
                updateActiveThumbnail(this);
            });
        });
    }
}

// Change main image
function changeMainImage(src) {
    const mainImage = document.getElementById('mainImage');
    if (mainImage) {
        mainImage.style.opacity = '0';
        setTimeout(() => {
            mainImage.src = src;
            mainImage.style.opacity = '1';
        }, 150);
    }
}

// Update active thumbnail
function updateActiveThumbnail(activeThumb) {
    document.querySelectorAll('.thumbnail-images img').forEach(thumb => {
        thumb.classList.remove('active');
    });
    activeThumb.classList.add('active');
}

// Setup product tabs
function setupProductTabs() {
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetTab = this.getAttribute('onclick').match(/'([^']+)'/)[1];
            openTab(event, targetTab);
        });
    });
}

// Open tab
function openTab(evt, tabName) {
    const tabcontent = document.querySelectorAll('.tab-content');
    const tablinks = document.querySelectorAll('.tab-btn');
    
    // Hide all tab content
    tabcontent.forEach(content => {
        content.classList.remove('active');
    });
    
    // Remove active class from all tab buttons
    tablinks.forEach(link => {
        link.classList.remove('active');
    });
    
    // Show the selected tab content
    document.getElementById(tabName).classList.add('active');
    
    // Add active class to the button that opened the tab
    evt.currentTarget.classList.add('active');
}

// Setup modals
function setupModals() {
    const modals = document.querySelectorAll('.modal');
    
    modals.forEach(modal => {
        const closeBtn = modal.querySelector('.close');
        const modalContent = modal.querySelector('.modal-content');
        
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                closeModal(modal);
            });
        }
        
        // Close modal when clicking outside
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeModal(modal);
            }
        });
        
        // Close modal with Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modal.style.display === 'block') {
                closeModal(modal);
            }
        });
    });
}

// Close modal
function closeModal(modal) {
    modal.style.display = 'none';
}

// Show notification
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${getNotificationIcon(type)}"></i>
            <span>${message}</span>
            <button class="notification-close">&times;</button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Show notification
    setTimeout(() => {
        notification.classList.add('show');
    }, 100);
    
    // Auto hide after 5 seconds
    setTimeout(() => {
        hideNotification(notification);
    }, 5000);
    
    // Close button
    const closeBtn = notification.querySelector('.notification-close');
    closeBtn.addEventListener('click', () => {
        hideNotification(notification);
    });
}

// Hide notification
function hideNotification(notification) {
    notification.classList.remove('show');
    setTimeout(() => {
        notification.remove();
    }, 300);
}

// Get notification icon
function getNotificationIcon(type) {
    const icons = {
        success: 'check-circle',
        error: 'exclamation-circle',
        warning: 'exclamation-triangle',
        info: 'info-circle'
    };
    return icons[type] || 'info-circle';
}

// Utility functions
function formatPrice(price) {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(price);
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function throttle(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

// Export functions for global use
window.ShopPink = {
    showNotification,
    formatPrice,
    debounce,
    throttle,
    openTab,
    changeMainImage,
    closeModal
};