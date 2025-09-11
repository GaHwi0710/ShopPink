// assets/js/main.js
// File JavaScript chung cho website

document.addEventListener('DOMContentLoaded', function() {
    // Xử lý menu mobile
    const menuToggle = document.querySelector('.menu-toggle');
    const mainMenu = document.querySelector('nav ul');
    
    if (menuToggle) {
        menuToggle.addEventListener('click', function() {
            mainMenu.classList.toggle('show');
        });
    }
    
    // Xử lý dropdown menu
    const dropdowns = document.querySelectorAll('.dropdown');
    
    dropdowns.forEach(dropdown => {
        const trigger = dropdown.querySelector('.dropdown-trigger');
        
        if (trigger) {
            trigger.addEventListener('click', function(e) {
                e.preventDefault();
                dropdown.classList.toggle('active');
            });
        }
    });
    
    // Đóng dropdown khi click bên ngoài
    document.addEventListener('click', function(e) {
        dropdowns.forEach(dropdown => {
            if (!dropdown.contains(e.target)) {
                dropdown.classList.remove('active');
            }
        });
    });
    
    // Xử lý form validation
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('error');
                    isValid = false;
                } else {
                    field.classList.remove('error');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Vui lòng điền đầy đủ thông tin');
            }
        });
    });
    
    // Xử lý input number
    const numberInputs = document.querySelectorAll('input[type="number"]');
    
    numberInputs.forEach(input => {
        input.addEventListener('input', function() {
            const min = parseFloat(input.min);
            const max = parseFloat(input.max);
            const value = parseFloat(input.value);
            
            if (min && value < min) {
                input.value = min;
            }
            
            if (max && value > max) {
                input.value = max;
            }
        });
    });
    
    // Xử lý smooth scroll
    const scrollLinks = document.querySelectorAll('a[href^="#"]');
    
    scrollLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                window.scrollTo({
                    top: targetElement.offsetTop - 50,
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // Xử lý sticky header
    const header = document.querySelector('header');
    
    if (header) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 100) {
                header.classList.add('sticky');
            } else {
                header.classList.remove('sticky');
            }
        });
    }
    
    // Xử lý back to top button
    const backToTopButton = document.querySelector('.back-to-top');
    
    if (backToTopButton) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 300) {
                backToTopButton.classList.add('show');
            } else {
                backToTopButton.classList.remove('show');
            }
        });
        
        backToTopButton.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
    
    // Xử lý gallery
    const galleryItems = document.querySelectorAll('.gallery-item');
    
    galleryItems.forEach(item => {
        item.addEventListener('click', function() {
            const imageSrc = this.querySelector('img').getAttribute('src');
            const modal = document.createElement('div');
            modal.className = 'image-modal';
            modal.innerHTML = `
                <div class="modal-content">
                    <span class="close-modal">&times;</span>
                    <img src="${imageSrc}" alt="Gallery Image">
                </div>
            `;
            
            document.body.appendChild(modal);
            
            const closeModal = modal.querySelector('.close-modal');
            
            closeModal.addEventListener('click', function() {
                document.body.removeChild(modal);
            });
            
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    document.body.removeChild(modal);
                }
            });
        });
    });
    
    // Xử lý accordion
    const accordions = document.querySelectorAll('.accordion');
    
    accordions.forEach(accordion => {
        const header = accordion.querySelector('.accordion-header');
        const content = accordion.querySelector('.accordion-content');
        
        if (header && content) {
            header.addEventListener('click', function() {
                accordion.classList.toggle('active');
                
                if (accordion.classList.contains('active')) {
                    content.style.maxHeight = content.scrollHeight + 'px';
                } else {
                    content.style.maxHeight = 0;
                }
            });
        }
    });
    
    // Xử lý tabs
    const tabs = document.querySelectorAll('.tab');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            const tabContents = document.querySelectorAll('.tab-content');
            
            // Remove active class from all tabs and contents
            tabs.forEach(t => t.classList.remove('active'));
            tabContents.forEach(tc => tc.classList.remove('active'));
            
            // Add active class to clicked tab and corresponding content
            this.classList.add('active');
            document.getElementById(tabId + '-tab').classList.add('active');
        });
    });
    
    // Xử lý carousel
    const carousels = document.querySelectorAll('.carousel');
    
    carousels.forEach(carousel => {
        const slides = carousel.querySelectorAll('.carousel-slide');
        const prevButton = carousel.querySelector('.carousel-prev');
        const nextButton = carousel.querySelector('.carousel-next');
        const indicators = carousel.querySelectorAll('.carousel-indicator');
        
        let currentSlide = 0;
        
        function showSlide(index) {
            if (index < 0) {
                index = slides.length - 1;
            } else if (index >= slides.length) {
                index = 0;
            }
            
            slides.forEach(slide => slide.classList.remove('active'));
            indicators.forEach(indicator => indicator.classList.remove('active'));
            
            slides[index].classList.add('active');
            if (indicators[index]) {
                indicators[index].classList.add('active');
            }
            
            currentSlide = index;
        }
        
        if (prevButton) {
            prevButton.addEventListener('click', function() {
                showSlide(currentSlide - 1);
            });
        }
        
        if (nextButton) {
            nextButton.addEventListener('click', function() {
                showSlide(currentSlide + 1);
            });
        }
        
        indicators.forEach((indicator, index) => {
            indicator.addEventListener('click', function() {
                showSlide(index);
            });
        });
        
        // Auto play
        let autoPlayInterval = setInterval(() => {
            showSlide(currentSlide + 1);
        }, 5000);
        
        // Pause on hover
        carousel.addEventListener('mouseenter', () => {
            clearInterval(autoPlayInterval);
        });
        
        carousel.addEventListener('mouseleave', () => {
            autoPlayInterval = setInterval(() => {
                showSlide(currentSlide + 1);
            }, 5000);
        });
    });
    
    // Xử lý search
    const searchForm = document.querySelector('.search-bar form');
    
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            const searchInput = this.querySelector('input');
            
            if (!searchInput.value.trim()) {
                e.preventDefault();
                alert('Vui lòng nhập từ khóa tìm kiếm');
            }
        });
    }
    
    // Xử lý add to cart animation
    const addToCartButtons = document.querySelectorAll('.add-to-cart-form button[type="submit"]');
    
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function() {
            const form = this.closest('form');
            const productId = form.querySelector('input[name="product_id"]').value;
            const productImage = document.querySelector(`.product-image img[src*="${productId}"]`);
            
            if (productImage) {
                const cartIcon = document.querySelector('.fa-shopping-cart').parentElement;
                const flyingImage = productImage.cloneNode(true);
                
                flyingImage.style.position = 'fixed';
                flyingImage.style.zIndex = '1000';
                flyingImage.style.transition = 'all 1s ease';
                flyingImage.style.width = '50px';
                flyingImage.style.height = '50px';
                flyingImage.style.borderRadius = '50%';
                
                const productImageRect = productImage.getBoundingClientRect();
                const cartIconRect = cartIcon.getBoundingClientRect();
                
                flyingImage.style.top = productImageRect.top + 'px';
                flyingImage.style.left = productImageRect.left + 'px';
                
                document.body.appendChild(flyingImage);
                
                setTimeout(() => {
                    flyingImage.style.top = cartIconRect.top + 'px';
                    flyingImage.style.left = cartIconRect.left + 'px';
                    flyingImage.style.opacity = '0';
                    flyingImage.style.transform = 'scale(0.5)';
                }, 10);
                
                setTimeout(() => {
                    document.body.removeChild(flyingImage);
                    
                    // Add animation to cart icon
                    cartIcon.classList.add('bounce');
                    setTimeout(() => {
                        cartIcon.classList
                                            cartIcon.classList.remove('bounce');
                    }, 1000);
                }, 1000);
            }
        });
    });
    
    // Xử lý lazy loading images
    const lazyImages = document.querySelectorAll('img[data-src]');
    
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                    imageObserver.unobserve(img);
                }
            });
        });
        
        lazyImages.forEach(img => {
            imageObserver.observe(img);
        });
    } else {
        // Fallback for browsers that don't support IntersectionObserver
        lazyImages.forEach(img => {
            img.src = img.dataset.src;
        });
    }
    
    // Xử lý form contact
    const contactForm = document.getElementById('contact-form');
    
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const name = document.getElementById('name').value;
            const email = document.getElementById('email').value;
            const message = document.getElementById('message').value;
            
            if (name && email && message) {
                // Simulate form submission
                const submitButton = contactForm.querySelector('button[type="submit"]');
                const originalText = submitButton.textContent;
                
                submitButton.textContent = 'Đang gửi...';
                submitButton.disabled = true;
                
                setTimeout(() => {
                    alert('Cảm ơn bạn đã liên hệ! Chúng tôi sẽ phản hồi sớm nhất có thể.');
                    contactForm.reset();
                    submitButton.textContent = originalText;
                    submitButton.disabled = false;
                }, 1500);
            }
        });
    }
    
    // Xử lý newsletter subscription
    const newsletterForm = document.getElementById('newsletter-form');
    
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const email = document.getElementById('newsletter-email').value;
            
            if (email) {
                // Simulate form submission
                const submitButton = newsletterForm.querySelector('button[type="submit"]');
                const originalText = submitButton.textContent;
                
                submitButton.textContent = 'Đang đăng ký...';
                submitButton.disabled = true;
                
                setTimeout(() => {
                    alert('Cảm ơn bạn đã đăng ký nhận bản tin!');
                    newsletterForm.reset();
                    submitButton.textContent = originalText;
                    submitButton.disabled = false;
                }, 1500);
            }
        });
    }
    
    // Xử lý countdown timer
    const countdownElements = document.querySelectorAll('.countdown');
    
    countdownElements.forEach(countdown => {
        const targetDate = new Date(countdown.dataset.date).getTime();
        
        function updateCountdown() {
            const now = new Date().getTime();
            const distance = targetDate - now;
            
            if (distance < 0) {
                countdown.innerHTML = "Đã kết thúc";
                return;
            }
            
            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
            countdown.innerHTML = `
                <div class="countdown-item">
                    <span class="countdown-value">${days}</span>
                    <span class="countdown-label">Ngày</span>
                </div>
                <div class="countdown-item">
                    <span class="countdown-value">${hours}</span>
                    <span class="countdown-label">Giờ</span>
                </div>
                <div class="countdown-item">
                    <span class="countdown-value">${minutes}</span>
                    <span class="countdown-label">Phút</span>
                </div>
                <div class="countdown-item">
                    <span class="countdown-value">${seconds}</span>
                    <span class="countdown-label">Giây</span>
                </div>
            `;
        }
        
        updateCountdown();
        setInterval(updateCountdown, 1000);
    });
    
    // Xử lý tooltip
    const tooltips = document.querySelectorAll('[data-tooltip]');
    
    tooltips.forEach(element => {
        const tooltipText = element.getAttribute('data-tooltip');
        const tooltip = document.createElement('div');
        tooltip.className = 'tooltip';
        tooltip.textContent = tooltipText;
        
        element.appendChild(tooltip);
        
        element.addEventListener('mouseenter', function() {
            tooltip.style.visibility = 'visible';
            tooltip.style.opacity = '1';
        });
        
        element.addEventListener('mouseleave', function() {
            tooltip.style.visibility = 'hidden';
            tooltip.style.opacity = '0';
        });
    });
    
    // Xử lý password strength indicator
    const passwordInputs = document.querySelectorAll('input[type="password"][data-strength]');
    
    passwordInputs.forEach(input => {
        const strengthIndicator = document.createElement('div');
        strengthIndicator.className = 'password-strength';
        input.parentNode.appendChild(strengthIndicator);
        
        input.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            
            if (password.length >= 8) strength++;
            if (password.match(/[a-z]+/)) strength++;
            if (password.match(/[A-Z]+/)) strength++;
            if (password.match(/[0-9]+/)) strength++;
            if (password.match(/[$@#&!]+/)) strength++;
            
            strengthIndicator.className = 'password-strength';
            
            if (password.length === 0) {
                strengthIndicator.textContent = '';
            } else if (strength < 2) {
                strengthIndicator.classList.add('weak');
                strengthIndicator.textContent = 'Yếu';
            } else if (strength < 4) {
                strengthIndicator.classList.add('medium');
                strengthIndicator.textContent = 'Trung bình';
            } else {
                strengthIndicator.classList.add('strong');
                strengthIndicator.textContent = 'Mạnh';
            }
        });
    });
    
    // Xử lý file upload preview
    const fileInputs = document.querySelectorAll('input[type="file"][data-preview]');
    
    fileInputs.forEach(input => {
        const previewContainer = document.getElementById(input.dataset.preview);
        
        input.addEventListener('change', function() {
            const file = this.files[0];
            
            if (file) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    if (file.type.startsWith('image/')) {
                        previewContainer.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                    } else {
                        previewContainer.innerHTML = `<div class="file-preview">${file.name}</div>`;
                    }
                };
                
                reader.readAsDataURL(file);
            } else {
                previewContainer.innerHTML = '';
            }
        });
    });
    
    // Xử lý quantity buttons
    const quantityGroups = document.querySelectorAll('.quantity-group');
    
    quantityGroups.forEach(group => {
        const input = group.querySelector('input[type="number"]');
        const decreaseBtn = group.querySelector('.quantity-decrease');
        const increaseBtn = group.querySelector('.quantity-increase');
        const min = parseInt(input.min) || 1;
        const max = parseInt(input.max) || 999;
        
        decreaseBtn.addEventListener('click', function() {
            let value = parseInt(input.value);
            if (value > min) {
                input.value = value - 1;
                input.dispatchEvent(new Event('change'));
            }
        });
        
        increaseBtn.addEventListener('click', function() {
            let value = parseInt(input.value);
            if (value < max) {
                input.value = value + 1;
                input.dispatchEvent(new Event('change'));
            }
        });
        
        input.addEventListener('change', function() {
            let value = parseInt(this.value);
            if (isNaN(value) || value < min) {
                this.value = min;
            } else if (value > max) {
                this.value = max;
            }
        });
    });
    
    // Xử lý color picker
    const colorPickers = document.querySelectorAll('.color-picker');
    
    colorPickers.forEach(picker => {
        const colors = picker.querySelectorAll('.color-option');
        const input = picker.querySelector('input[type="hidden"]');
        
        colors.forEach(color => {
            color.addEventListener('click', function() {
                colors.forEach(c => c.classList.remove('selected'));
                this.classList.add('selected');
                input.value = this.dataset.color;
            });
        });
    });
    
    // Xử lý size picker
    const sizePickers = document.querySelectorAll('.size-picker');
    
    sizePickers.forEach(picker => {
        const sizes = picker.querySelectorAll('.size-option');
        const input = picker.querySelector('input[type="hidden"]');
        
        sizes.forEach(size => {
            size.addEventListener('click', function() {
                sizes.forEach(s => s.classList.remove('selected'));
                this.classList.add('selected');
                input.value = this.dataset.size;
            });
        });
    });
    
    // Xử lý rating input
    const ratingInputs = document.querySelectorAll('.rating-input');
    
    ratingInputs.forEach(input => {
        const stars = input.querySelectorAll('.rating-star');
        const hiddenInput = input.querySelector('input[type="hidden"]');
        
        stars.forEach((star, index) => {
            star.addEventListener('click', function() {
                const rating = index + 1;
                hiddenInput.value = rating;
                
                stars.forEach((s, i) => {
                    if (i < rating) {
                        s.classList.add('active');
                    } else {
                        s.classList.remove('active');
                    }
                });
            });
            
            star.addEventListener('mouseenter', function() {
                const rating = index + 1;
                
                stars.forEach((s, i) => {
                    if (i < rating) {
                        s.classList.add('hover');
                    } else {
                        s.classList.remove('hover');
                    }
                });
            });
        });
        
        input.addEventListener('mouseleave', function() {
            stars.forEach(s => s.classList.remove('hover'));
        });
    });
    
    // Xử lý notification
    const notificationContainer = document.createElement('div');
    notificationContainer.className = 'notification-container';
    document.body.appendChild(notificationContainer);
    
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <div class="notification-message">${message}</div>
                <button class="notification-close">&times;</button>
            </div>
        `;
        
        notificationContainer.appendChild(notification);
        
        const closeButton = notification.querySelector('.notification-close');
        closeButton.addEventListener('click', function() {
            notification.classList.add('fade-out');
            setTimeout(() => {
                notificationContainer.removeChild(notification);
            }, 300);
        });
        
        setTimeout(() => {
            notification.classList.add('fade-out');
            setTimeout(() => {
                if (notification.parentNode) {
                    notificationContainer.removeChild(notification);
                }
            }, 300);
        }, 5000);
    }
    
    // Expose showNotification to global scope
    window.showNotification = showNotification;
    
    // Xử lý print button
    const printButtons = document.querySelectorAll('.print-button');
    
    printButtons.forEach(button => {
        button.addEventListener('click', function() {
            const printContent = document.getElementById(this.dataset.print);
            
            if (printContent) {
                const printWindow = window.open('', '_blank');
                
                printWindow.document.write(`
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <title>Print</title>
                        <style>
                            body { font-family: Arial, sans-serif; margin: 20px; }
                            h1, h2, h3 { margin-bottom: 15px; }
                            table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                            th { background-color: #f2f2f2; }
                            .text-right { text-align: right; }
                            .text-center { text-align: center; }
                            .mb-20 { margin-bottom: 20px; }
                            @media print {
                                .no-print { display: none; }
                            }
                        </style>
                    </head>
                    <body>
                        ${printContent.innerHTML}
                    </body>
                    </html>
                `);
                
                printWindow.document.close();
                printWindow.print();
            }
        });
    });
    
    // Xử lý share buttons
    const shareButtons = document.querySelectorAll('.share-button');
    
    shareButtons.forEach(button => {
        button.addEventListener('click', function() {
            const url = window.location.href;
            const title = document.title;
            const platform = this.dataset.platform;
            
            let shareUrl = '';
            
            switch (platform) {
                case 'facebook':
                    shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`;
                    break;
                case 'twitter':
                    shareUrl = `https://twitter.com/intent/tweet?url=${encodeURIComponent(url)}&text=${encodeURIComponent(title)}`;
                    break;
                case 'linkedin':
                    shareUrl = `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(url)}`;
                    break;
                case 'pinterest':
                    shareUrl = `https://pinterest.com/pin/create/button/?url=${encodeURIComponent(url)}&description=${encodeURIComponent(title)}`;
                    break;
                case 'whatsapp':
                    shareUrl = `https://wa.me/?text=${encodeURIComponent(title + ' ' + url)}`;
                    break;
                default:
                    // Copy to clipboard
                    navigator.clipboard.writeText(url).then(() => {
                        showNotification('Đã sao chép liên kết!');
                    }).catch(() => {
                        showNotification('Không thể sao chép liên kết!', 'error');
                    });
                    return;
            }
            
            window.open(shareUrl, '_blank', 'width=600,height=400');
        });
    });
    
    // Xử lý dark mode toggle
    const darkModeToggle = document.querySelector('.dark-mode-toggle');
    
    if (darkModeToggle) {
        // Check for saved user preference, if any
        const darkMode = localStorage.getItem('darkMode') === 'true';
        
        // Apply dark mode if saved preference is true
        if (darkMode) {
            document.body.classList.add('dark-mode');
            darkModeToggle.checked = true;
        }
        
        darkModeToggle.addEventListener('change', function() {
            if (this.checked) {
                document.body.classList.add('dark-mode');
                localStorage.setItem('darkMode', 'true');
            } else {
                document.body.classList.remove('dark-mode');
                localStorage.setItem('darkMode', 'false');
            }
        });
    }
    
    // Xử lý animation on scroll
    const animatedElements = document.querySelectorAll('.animate-on-scroll');
    
    function checkIfInView() {
        const windowHeight = window.innerHeight;
        const windowTopPosition = window.scrollY;
        const windowBottomPosition = windowTopPosition + windowHeight;
        
        animatedElements.forEach(element => {
            const elementHeight = element.offsetHeight;
            const elementTopPosition = element.offsetTop;
            const elementBottomPosition = elementTopPosition + elementHeight;
            
            // Check if element is in viewport
            if (
                (elementBottomPosition >= windowTopPosition) &&
                (elementTopPosition <= windowBottomPosition)
            ) {
                element.classList.add('animated');
            }
        });
    }
    
    // Initial check
    checkIfInView();
    
    // Check on scroll
    window.addEventListener('scroll', checkIfInView);
    
    // Xử lý parallax scrolling
    const parallaxElements = document.querySelectorAll('.parallax');
    
    window.addEventListener('scroll', () => {
        const scrollPosition = window.scrollY;
        
        parallaxElements.forEach(element => {
            const speed = element.dataset.speed || 0.5;
            const offset = scrollPosition * speed;
            element.style.transform = `translateY(${offset}px)`;
        });
    });
    
    // Xử lý video background
    const videoBackgrounds = document.querySelectorAll('.video-background');
    
    videoBackgrounds.forEach(video => {
        const videoElement = video.querySelector('video');
        const playButton = video.querySelector('.play-button');
        const pauseButton = video.querySelector('.pause-button');
        
        if (playButton) {
            playButton.addEventListener('click', () => {
                videoElement.play();
                playButton.style.display = 'none';
                if (pauseButton) pauseButton.style.display = 'block';
            });
        }
        
        if (pauseButton) {
            pauseButton.addEventListener('click', () => {
                videoElement.pause();
                pauseButton.style.display = 'none';
                if (playButton) playButton.style.display = 'block';
            });
        }
    });
    
    // Xử lý form validation with custom messages
    const formsWithValidation = document.querySelectorAll('form[data-validate]');
    
    formsWithValidation.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const fields = form.querySelectorAll('[data-validate]');
            let isValid = true;
            let firstInvalidField = null;
            
            fields.forEach(field => {
                const validationType = field.dataset.validate;
                const errorMessage = field.dataset.errorMessage || 'Vui lòng kiểm tra lại thông tin';
                let fieldIsValid = true;
                
                switch (validationType) {
                    case 'email':
                        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                        fieldIsValid = emailRegex.test(field.value);
                        break;
                    case 'phone':
                        const phoneRegex = /^0[0-9]{9,10}$/;
                        fieldIsValid = phoneRegex.test(field.value);
                        break;
                    case 'required':
                        fieldIsValid = field.value.trim() !== '';
                        break;
                    case 'min-length':
                        const minLength = parseInt(field.dataset.minLength) || 0;
                        fieldIsValid = field.value.length >= minLength;
                        break;
                    case 'max-length':
                        const maxLength = parseInt(field.dataset.maxLength) || 999;
                        fieldIsValid = field.value.length <= maxLength;
                        break;
                    case 'numeric':
                        fieldIsValid = !isNaN(parseFloat(field.value)) && isFinite(field.value);
                        break;
                    case 'pattern':
                        const pattern = new RegExp(field.dataset.pattern);
                        fieldIsValid = pattern.test(field.value);
                        break;
                }
                
                if (!fieldIsValid) {
                    isValid = false;
                    field.classList.add('invalid');
                    
                    // Create or update error message
                    let errorElement = field.nextElementSibling;
                    if (!errorElement || !errorElement.classList.contains('error-message')) {
                        errorElement = document.createElement('div');
                        errorElement.className = 'error-message';
                        field.parentNode.insertBefore(errorElement, field.nextSibling);
                    }
                    errorElement.textContent = errorMessage;
                    
                    if (!firstInvalidField) {
                        firstInvalidField = field;
                    }
                } else {
                    field.classList.remove('invalid');
                    const errorElement = field.nextElementSibling;
                    if (errorElement && errorElement.classList.contains('error-message')) {
                        field.parentNode.removeChild(errorElement);
                    }
                }
            });
            
            if (isValid) {
                form.submit();
            } else if (firstInvalidField) {
                firstInvalidField.focus();
                firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
        
        // Clear validation on input
        const fields = form.querySelectorAll('[data-validate]');
        fields.forEach(field => {
            field.addEventListener('input', function() {
                this.classList.remove('invalid');
                const errorElement = this.nextElementSibling;
                if (errorElement && errorElement.classList.contains('error-message')) {
                    this.parentNode.removeChild(errorElement);
                }
            });
        });
    });
    
    // Xử lý lazy load for iframes
    const lazyIframes = document.querySelectorAll('iframe[data-src]');
    
    if ('IntersectionObserver' in window) {
        const iframeObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const iframe = entry.target;
                    iframe.src = iframe.dataset.src;
                    iframe.removeAttribute('data-src');
                    iframeObserver.unobserve(iframe);
                }
            });
        });
        
        lazyIframes.forEach(iframe => {
            iframeObserver.observe(iframe);
        });
    } else {
        // Fallback for browsers that don't support IntersectionObserver
        lazyIframes.forEach(iframe => {
            iframe.src = iframe.dataset.src;
        });
    }
});