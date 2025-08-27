// JavaScript chính cho ShopPink

document.addEventListener('DOMContentLoaded', function() {
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
    
    // Xử lý carousel trên trang chủ
    const carousel = document.querySelector('.banner-carousel');
    if (carousel) {
        const slides = carousel.querySelectorAll('.banner-slide');
        const prevBtn = carousel.querySelector('.carousel-prev');
        const nextBtn = carousel.querySelector('.carousel-next');
        let currentSlide = 0;
        
        function showSlide(index) {
            slides.forEach(slide => slide.classList.remove('active'));
            slides[index].classList.add('active');
            currentSlide = index;
        }
        
        if (prevBtn) {
            prevBtn.addEventListener('click', function() {
                currentSlide = (currentSlide - 1 + slides.length) % slides.length;
                showSlide(currentSlide);
            });
        }
        
        if (nextBtn) {
            nextBtn.addEventListener('click', function() {
                currentSlide = (currentSlide + 1) % slides.length;
                showSlide(currentSlide);
            });
        }
        
        // Tự động chuyển slide
        setInterval(function() {
            currentSlide = (currentSlide + 1) % slides.length;
            showSlide(currentSlide);
        }, 5000);
    }
    
    // Xử lý tăng/giảm số lượng
    const quantityControls = document.querySelectorAll('.quantity-control');
    
    quantityControls.forEach(control => {
        const decreaseBtn = control.querySelector('a:first-child');
        const increaseBtn = control.querySelector('a:last-child');
        const quantityInput = control.querySelector('input');
        
        if (decreaseBtn && quantityInput) {
            decreaseBtn.addEventListener('click', function(e) {
                e.preventDefault();
                let value = parseInt(quantityInput.value);
                if (value > 1) {
                    quantityInput.value = value - 1;
                }
            });
        }
        
        if (increaseBtn && quantityInput) {
            increaseBtn.addEventListener('click', function(e) {
                e.preventDefault();
                let value = parseInt(quantityInput.value);
                quantityInput.value = value + 1;
            });
        }
    });
    
    // Xử lý tabs
    const tabs = document.querySelectorAll('.tabs');
    
    tabs.forEach(tabContainer => {
        const tabTriggers = tabContainer.querySelectorAll('.tab-trigger');
        const tabContents = tabContainer.querySelectorAll('.tab-content');
        
        tabTriggers.forEach(trigger => {
            trigger.addEventListener('click', function() {
                const tabId = this.getAttribute('data-tab');
                
                tabTriggers.forEach(t => t.classList.remove('active'));
                tabContents.forEach(c => c.classList.remove('active'));
                
                this.classList.add('active');
                document.getElementById(tabId).classList.add('active');
            });
        });
    });
    
    // Xử lý modal
    const modals = document.querySelectorAll('.modal');
    
    modals.forEach(modal => {
        const closeBtn = modal.querySelector('.modal-close');
        
        if (closeBtn) {
            closeBtn.addEventListener('click', function() {
                modal.classList.remove('active');
            });
        }
        
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.classList.remove('active');
            }
        });
    });
    
    // Xử lý form validation
    const forms = document.querySelectorAll('.needs-validation');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            form.classList.add('was-validated');
        }, false);
    });
    
    // Xử lý ảnh preview
    const imageInputs = document.querySelectorAll('input[type="file"][data-preview]');
    
    imageInputs.forEach(input => {
        input.addEventListener('change', function() {
            const previewId = this.getAttribute('data-preview');
            const preview = document.getElementById(previewId);
            
            if (preview && this.files && this.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                };
                
                reader.readAsDataURL(this.files[0]);
            }
        });
    });
    
    // Xử lý back to top button
    const backToTopBtn = document.querySelector('.back-to-top');
    
    if (backToTopBtn) {
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                backToTopBtn.classList.add('active');
            } else {
                backToTopBtn.classList.remove('active');
            }
        });
        
        backToTopBtn.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
    
    // Xử lý sticky header
    const header = document.querySelector('header');
    
    if (header) {
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 100) {
                header.classList.add('sticky');
            } else {
                header.classList.remove('sticky');
            }
        });
    }
});