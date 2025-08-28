// ==========================
//  MAIN JAVASCRIPT SHOPPINK
// ==========================
document.addEventListener('DOMContentLoaded', function() {
    /* =====================
       DROPDOWN MENU
    ===================== */
    const dropdownTriggers = document.querySelectorAll('.main-nav li > a');
    dropdownTriggers.forEach(trigger => {
        trigger.addEventListener('click', function (e) {
            const parentLi = this.parentElement;
            if (parentLi.querySelector('.dropdown')) {
                e.preventDefault();
                parentLi.classList.toggle('active');
            }
        });
    });
    document.addEventListener('click', function (e) {
        dropdownTriggers.forEach(trigger => {
            const parentLi = trigger.parentElement;
            if (!parentLi.contains(e.target)) {
                parentLi.classList.remove('active');
            }
        });
    });
    /* =====================
       BANNER CAROUSEL
    ===================== */
    const carousel = document.querySelector('.banner-carousel');
    if (carousel) {
        const slides = carousel.querySelectorAll('.banner-slide');
        let currentSlide = 0;
        function showSlide(index) {
            slides.forEach(slide => slide.classList.remove('active'));
            slides[index].classList.add('active');
            currentSlide = index;
        }
        setInterval(() => {
            currentSlide = (currentSlide + 1) % slides.length;
            showSlide(currentSlide);
        }, 5000);
    }
    /* =====================
       QUANTITY CONTROL
    ===================== */
    const quantityControls = document.querySelectorAll('.quantity-control');
    quantityControls.forEach(control => {
        const minusBtn = control.querySelector('.decrease');
        const plusBtn = control.querySelector('.increase');
        const input = control.querySelector('input');
        if (minusBtn) {
            minusBtn.addEventListener('click', function (e) {
                e.preventDefault();
                let value = parseInt(input.value) || 1;
                if (value > 1) input.value = value - 1;
            });
        }
        if (plusBtn) {
            plusBtn.addEventListener('click', function (e) {
                e.preventDefault();
                let value = parseInt(input.value) || 1;
                input.value = value + 1;
            });
        }
    });
    /* =====================
       TABS
    ===================== */
    const tabContainers = document.querySelectorAll('.tabs');
    tabContainers.forEach(container => {
        const triggers = container.querySelectorAll('.tab-trigger');
        const contents = container.querySelectorAll('.tab');
        triggers.forEach(trigger => {
            trigger.addEventListener('click', function () {
                const tabId = this.getAttribute('data-tab');
                triggers.forEach(t => t.classList.remove('active'));
                contents.forEach(c => c.classList.remove('active'));
                this.classList.add('active');
                const target = container.querySelector(`#${tabId}`);
                if (target) target.classList.add('active');
            });
        });
    });
    /* =====================
       MODALS
    ===================== */
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        const closeBtn = modal.querySelector('.modal-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                modal.classList.remove('active');
            });
        }
        modal.addEventListener('click', function (e) {
            if (e.target === modal) {
                modal.classList.remove('active');
            }
        });
    });
    /* =====================
       FORM VALIDATION
    ===================== */
    const forms = document.querySelectorAll('.needs-validation');
    forms.forEach(form => {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
    /* =====================
       IMAGE PREVIEW
    ===================== */
    const fileInputs = document.querySelectorAll('input[type="file"][data-preview]');
    fileInputs.forEach(input => {
        input.addEventListener('change', function () {
            const previewId = this.getAttribute('data-preview');
            const previewEl = document.getElementById(previewId);
            if (previewEl && this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = e => { previewEl.src = e.target.result; };
                reader.readAsDataURL(this.files[0]);
            }
        });
    });
    /* =====================
       BACK TO TOP
    ===================== */
    const backToTop = document.querySelector('.back-to-top');
    if (backToTop) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 300) {
                backToTop.classList.add('active');
            } else {
                backToTop.classList.remove('active');
            }
        });
        backToTop.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }
    /* =====================
       STICKY HEADER
    ===================== */
    const header = document.querySelector('header');
    if (header) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 100) {
                header.classList.add('sticky');
            } else {
                header.classList.remove('sticky');
            }
        });
    }
    /* =====================
       AJAX SEARCH SUGGESTIONS
    ===================== */
    const searchInput = document.getElementById('search-input');
    const suggestionsBox = document.getElementById('search-suggestions');
    if (searchInput && suggestionsBox) {
        searchInput.addEventListener('keyup', function () {
            const query = this.value.trim();
            if (query.length < 2) {
                suggestionsBox.innerHTML = '';
                suggestionsBox.style.display = 'none';
                return;
            }
            fetch('search_suggestions.php?q=' + encodeURIComponent(query))
                .then(response => response.text())
                .then(data => {
                    suggestionsBox.innerHTML = data;
                    suggestionsBox.style.display = 'block';
                });
        });
        // Ẩn khi click ra ngoài
        document.addEventListener('click', function (e) {
            if (!searchInput.contains(e.target) && !suggestionsBox.contains(e.target)) {
                suggestionsBox.style.display = 'none';
            }
        });
    }
});
/* =====================
   SCROLL ANIMATIONS
===================== */
const animatedEls = document.querySelectorAll('.animate');
if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('show');
                observer.unobserve(entry.target); // chỉ chạy 1 lần
            }
        });
    }, { threshold: 0.1 });
    animatedEls.forEach(el => observer.observe(el));
} else {
    // fallback: hiện tất cả nếu trình duyệt cũ
    animatedEls.forEach(el => el.classList.add('show'));
}