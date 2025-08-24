// Modern JavaScript - Psikolog Sitesi
document.addEventListener('DOMContentLoaded', function() {
    // Tüm modülleri başlat
    initializeFormValidations();
    initializeModals();
    initializeAjaxHandlers();
    initializeDateTimePickers();
    initializeLoadingIndicators();
    initializeAnimations();
    initializeMobileMenu();
    initializeScrollEffects();
});

// Form validasyonları
function initializeFormValidations() {
    const forms = document.querySelectorAll('form[data-validate]');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
            }
        });
        
        // Real-time validasyon
        const inputs = form.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.addEventListener('blur', () => validateField(input));
            input.addEventListener('input', () => clearFieldError(input));
        });
    });
}

function validateForm(form) {
    let isValid = true;
    const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
    
    inputs.forEach(input => {
        if (!validateField(input)) {
            isValid = false;
        }
    });
    
    // Email validasyonu
    const emailInputs = form.querySelectorAll('input[type="email"]');
    emailInputs.forEach(input => {
        if (input.value && !isValidEmail(input.value)) {
            showFieldError(input, 'Geçerli bir email adresi giriniz.');
            isValid = false;
        }
    });
    
    // Telefon validasyonu
    const phoneInputs = form.querySelectorAll('input[name="telefon"]');
    phoneInputs.forEach(input => {
        if (input.value && !isValidPhone(input.value)) {
            showFieldError(input, 'Geçerli bir telefon numarası giriniz.');
            isValid = false;
        }
    });
    
    return isValid;
}

function validateField(field) {
    const value = field.value.trim();
    
    if (field.hasAttribute('required') && !value) {
        showFieldError(field, 'Bu alan zorunludur.');
        return false;
    }
    
    // Minimum uzunluk kontrolü
    if (field.hasAttribute('minlength')) {
        const minLength = parseInt(field.getAttribute('minlength'));
        if (value.length < minLength) {
            showFieldError(field, `En az ${minLength} karakter olmalıdır.`);
            return false;
        }
    }
    
    // Maksimum uzunluk kontrolü
    if (field.hasAttribute('maxlength')) {
        const maxLength = parseInt(field.getAttribute('maxlength'));
        if (value.length > maxLength) {
            showFieldError(field, `En fazla ${maxLength} karakter olmalıdır.`);
            return false;
        }
    }
    
    clearFieldError(field);
    return true;
}

function showFieldError(field, message) {
    clearFieldError(field);
    
    field.classList.add('error');
    const errorDiv = document.createElement('div');
    errorDiv.className = 'field-error';
    errorDiv.textContent = message;
    
    field.parentNode.appendChild(errorDiv);
    
    // Animasyon efekti
    errorDiv.style.opacity = '0';
    errorDiv.style.transform = 'translateY(-10px)';
    setTimeout(() => {
        errorDiv.style.transition = 'all 0.3s ease';
        errorDiv.style.opacity = '1';
        errorDiv.style.transform = 'translateY(0)';
    }, 10);
}

function clearFieldError(field) {
    field.classList.remove('error');
    const existingError = field.parentNode.querySelector('.field-error');
    if (existingError) {
        existingError.style.opacity = '0';
        existingError.style.transform = 'translateY(-10px)';
        setTimeout(() => existingError.remove(), 300);
    }
}

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function isValidPhone(phone) {
    const phoneRegex = /^[\+]?[0-9\s\-\(\)]{10,}$/;
    return phoneRegex.test(phone);
}

// Modal işlemleri
function initializeModals() {
    // Modal açma
    const modalTriggers = document.querySelectorAll('[data-modal]');
    modalTriggers.forEach(trigger => {
        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            const modalId = this.getAttribute('data-modal');
            openModal(modalId);
        });
    });
    
    // Modal kapatma
    const modalCloses = document.querySelectorAll('.modal .close, .modal-overlay');
    modalCloses.forEach(close => {
        close.addEventListener('click', function() {
            closeModal(this.closest('.modal'));
        });
    });
    
    // ESC tuşu ile modal kapatma
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const openModal = document.querySelector('.modal.show');
            if (openModal) {
                closeModal(openModal);
            }
        }
    });
}

function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
        
        // Animasyon efekti
        const content = modal.querySelector('.modal-content');
        if (content) {
            content.style.transform = 'scale(0.7)';
            content.style.opacity = '0';
            setTimeout(() => {
                content.style.transition = 'all 0.3s ease';
                content.style.transform = 'scale(1)';
                content.style.opacity = '1';
            }, 10);
        }
    }
}

function closeModal(modal) {
    if (modal) {
        const content = modal.querySelector('.modal-content');
        if (content) {
            content.style.transform = 'scale(0.7)';
            content.style.opacity = '0';
        }
        
        setTimeout(() => {
            modal.classList.remove('show');
            document.body.style.overflow = '';
        }, 300);
    }
}

// AJAX işlemleri
function initializeAjaxHandlers() {
    // Randevu formu AJAX
    const appointmentForm = document.getElementById('appointment-form');
    if (appointmentForm) {
        appointmentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            submitAppointmentForm(this);
        });
    }
    
    // Blog formu AJAX
    const blogForm = document.getElementById('blog-form');
    if (blogForm) {
        blogForm.addEventListener('submit', function(e) {
            e.preventDefault();
            submitBlogForm(this);
        });
    }
    
    // İletişim formu AJAX
    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            submitContactForm(this);
        });
    }
}

function submitAppointmentForm(form) {
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    
    // Loading göster
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="loading"></span> Gönderiliyor...';
    
    fetch('process_appointment.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Randevunuz başarıyla alındı! En kısa sürede size dönüş yapacağız.', 'success');
            form.reset();
        } else {
            showAlert(data.message || 'Bir hata oluştu. Lütfen tekrar deneyin.', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Bir hata oluştu. Lütfen tekrar deneyin.', 'error');
    })
    .finally(() => {
        // Loading gizle
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    });
}

function submitBlogForm(form) {
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    
    // Loading göster
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="loading"></span> Kaydediliyor...';
    
    fetch('process_blog.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Blog yazısı başarıyla kaydedildi!', 'success');
            if (data.redirect) {
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 1500);
            }
        } else {
            showAlert(data.message || 'Bir hata oluştu. Lütfen tekrar deneyin.', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Bir hata oluştu. Lütfen tekrar deneyin.', 'error');
    })
    .finally(() => {
        // Loading gizle
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    });
}

function submitContactForm(form) {
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    const messageDiv = document.getElementById('formMessage');
    const originalText = submitBtn.textContent;
    
    // Loading göster
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="loading"></span> Gönderiliyor...';
    messageDiv.textContent = '';
    messageDiv.className = '';
    
    fetch('process_message.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            messageDiv.textContent = data.message;
            messageDiv.style.color = '#48bb78';
            form.reset();
        } else {
            messageDiv.textContent = data.message;
            messageDiv.style.color = '#f56565';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        messageDiv.textContent = 'Bir hata oluştu. Lütfen tekrar deneyin.';
        messageDiv.style.color = '#f56565';
    })
    .finally(() => {
        // Loading gizle
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    });
}

// Tarih ve saat seçicileri
function initializeDateTimePickers() {
    // Randevu tarihi minimum bugün
    const dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(input => {
        const today = new Date().toISOString().split('T')[0];
        input.setAttribute('min', today);
    });
    
    // Saat seçici için özel stil
    const timeSelects = document.querySelectorAll('select[name="appointment_time"]');
    timeSelects.forEach(select => {
        select.addEventListener('change', function() {
            if (this.value) {
                this.style.color = '#1a202c';
            } else {
                this.style.color = '#718096';
            }
        });
    });
}

// Loading göstergeleri
function initializeLoadingIndicators() {
    // Loading spinner animasyonu
    const loadingSpinners = document.querySelectorAll('.loading');
    loadingSpinners.forEach(spinner => {
        spinner.innerHTML = '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 2V6M12 18V22M4.93 4.93L7.76 7.76M16.24 16.24L19.07 19.07M2 12H6M18 12H22M4.93 19.07L7.76 16.24M16.24 7.76L19.07 4.93" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
    });
}

// Animasyonlar
function initializeAnimations() {
    // Scroll animasyonları
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-in');
            }
        });
    }, observerOptions);
    
    // Animasyon yapılacak elementler
    const animateElements = document.querySelectorAll('.card, .blog-item, .team-member, .stat-item');
    animateElements.forEach(el => {
        observer.observe(el);
    });
}

// Mobil menü
function initializeMobileMenu() {
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const navMenu = document.querySelector('.nav-menu');
    
    if (mobileMenuToggle && navMenu) {
        mobileMenuToggle.addEventListener('click', function() {
            navMenu.classList.toggle('active');
            this.classList.toggle('active');
        });
        
        // Menü dışına tıklandığında kapat
        document.addEventListener('click', function(e) {
            if (!navMenu.contains(e.target) && !mobileMenuToggle.contains(e.target)) {
                navMenu.classList.remove('active');
                mobileMenuToggle.classList.remove('active');
            }
        });
    }
}

// Scroll efektleri
function initializeScrollEffects() {
    // Header scroll efekti
    const header = document.querySelector('.header');
    if (header) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 100) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
    }
    
    // Smooth scroll
    const smoothScrollLinks = document.querySelectorAll('a[href^="#"]');
    smoothScrollLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

// Alert sistemi
function showAlert(message, type = 'info') {
    // Mevcut alert'leri temizle
    const existingAlerts = document.querySelectorAll('.alert-popup');
    existingAlerts.forEach(alert => alert.remove());
    
    const alert = document.createElement('div');
    alert.className = `alert-popup alert-${type}`;
    alert.innerHTML = `
        <div class="alert-content">
            <span class="alert-message">${message}</span>
            <button class="alert-close">&times;</button>
        </div>
    `;
    
    // Stil ekle
    alert.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10000;
        background: white;
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.15);
        padding: 1rem 1.5rem;
        border-left: 4px solid;
        transform: translateX(400px);
        transition: all 0.3s ease;
        max-width: 400px;
    `;
    
    // Tip'e göre renk
    const colors = {
        success: '#48bb78',
        error: '#f56565',
        warning: '#ed8936',
        info: '#667eea'
    };
    
    alert.style.borderLeftColor = colors[type] || colors.info;
    
    document.body.appendChild(alert);
    
    // Animasyon
    setTimeout(() => {
        alert.style.transform = 'translateX(0)';
    }, 10);
    
    // Kapatma butonu
    const closeBtn = alert.querySelector('.alert-close');
    closeBtn.addEventListener('click', () => {
        alert.style.transform = 'translateX(400px)';
        setTimeout(() => alert.remove(), 300);
    });
    
    // Otomatik kapatma
    setTimeout(() => {
        if (alert.parentNode) {
            alert.style.transform = 'translateX(400px)';
            setTimeout(() => alert.remove(), 300);
        }
    }, 5000);
}

// Tablo işlemleri
function sortTable(table, column) {
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    const header = table.querySelector(`th[data-sort="${column}"]`);
    
    if (!header) return;
    
    const isAscending = header.classList.contains('sort-asc');
    
    // Sıralama yönünü değiştir
    table.querySelectorAll('th').forEach(th => th.classList.remove('sort-asc', 'sort-desc'));
    header.classList.add(isAscending ? 'sort-desc' : 'sort-asc');
    
    // Satırları sırala
    rows.sort((a, b) => {
        const aValue = a.querySelector(`td[data-column="${column}"]`).textContent;
        const bValue = b.querySelector(`td[data-column="${column}"]`).textContent;
        
        if (isAscending) {
            return bValue.localeCompare(aValue, 'tr');
        } else {
            return aValue.localeCompare(bValue, 'tr');
        }
    });
    
    // Tabloyu güncelle
    rows.forEach(row => tbody.appendChild(row));
}

function searchTable(table, searchTerm) {
    const rows = table.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const matches = text.includes(searchTerm.toLowerCase());
        row.style.display = matches ? '' : 'none';
    });
}

// Sayfalama
function paginateTable(table, itemsPerPage = 10) {
    const rows = Array.from(table.querySelectorAll('tbody tr'));
    const totalPages = Math.ceil(rows.length / itemsPerPage);
    
    // Sayfalama kontrollerini oluştur
    const paginationContainer = document.createElement('div');
    paginationContainer.className = 'pagination';
    
    for (let i = 1; i <= totalPages; i++) {
        const button = document.createElement('button');
        button.textContent = i;
        button.addEventListener('click', () => showPage(table, i, itemsPerPage));
        paginationContainer.appendChild(button);
    }
    
    table.parentNode.appendChild(paginationContainer);
    
    // İlk sayfayı göster
    showPage(table, 1, itemsPerPage);
}

function showPage(table, page, itemsPerPage) {
    const rows = Array.from(table.querySelectorAll('tbody tr'));
    const start = (page - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    
    rows.forEach((row, index) => {
        row.style.display = (index >= start && index < end) ? '' : 'none';
    });
    
    // Aktif sayfa butonunu güncelle
    const buttons = table.parentNode.querySelectorAll('.pagination button');
    buttons.forEach((btn, index) => {
        btn.classList.toggle('active', index + 1 === page);
    });
}

// Resim önizleme
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = input.parentNode.querySelector('.image-preview');
            if (preview) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Form temizleme
function clearForm(form) {
    form.reset();
    
    // Özel alanları temizle
    const imagePreviews = form.querySelectorAll('.image-preview');
    imagePreviews.forEach(preview => {
        preview.src = '';
        preview.style.display = 'none';
    });
    
    // Hata mesajlarını temizle
    const errors = form.querySelectorAll('.field-error');
    errors.forEach(error => error.remove());
    
    // Error class'larını temizle
    const errorFields = form.querySelectorAll('.error');
    errorFields.forEach(field => field.classList.remove('error'));
}

// Tarih formatla
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('tr-TR', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

// Telefon formatla
function formatPhone(phone) {
    return phone.replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3');
}

// Çıkış onayı
function confirmLogout() {
    if (confirm('Çıkış yapmak istediğinizden emin misiniz?')) {
        window.location.href = 'logout.php';
    }
}

// Sayfa yenileme
function refreshPage() {
    window.location.reload();
}

// URL parametreleri
function getUrlParameter(name) {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(name);
}

function addUrlParameter(name, value) {
    const url = new URL(window.location);
    url.searchParams.set(name, value);
    window.history.pushState({}, '', url);
}

// Blog silme
function deleteBlog(blogId) {
    if (confirm('Bu blog yazısını silmek istediğinizden emin misiniz?')) {
        fetch('delete_blog.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                id: blogId,
                csrf_token: generateCSRFTokenForAdmin()
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('Blog yazısı başarıyla silindi!', 'success');
                setTimeout(() => window.location.reload(), 1000);
            } else {
                showAlert(data.message || 'Silme işlemi başarısız!', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Bir hata oluştu!', 'error');
        });
    }
}

// Randevu durumu güncelleme
function updateAppointmentStatus(appointmentId, status) {
    fetch('update_appointment_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            id: appointmentId,
            status: status,
            csrf_token: generateCSRFTokenForAdmin()
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Randevu durumu güncellendi!', 'success');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showAlert(data.message || 'Güncelleme başarısız!', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Bir hata oluştu!', 'error');
    });
}

// CSRF token oluşturma (admin için)
function generateCSRFTokenForAdmin() {
    // Bu fonksiyon admin panelinde kullanılır
    // Gerçek uygulamada sunucudan alınmalı
    return Math.random().toString(36).substr(2, 9);
}

// Sayfa yüklendiğinde çalışacak ek işlemler
window.addEventListener('load', function() {
    // Lazy loading için Intersection Observer
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('lazy');
                observer.unobserve(img);
            }
        });
    });
    
    const lazyImages = document.querySelectorAll('img[data-src]');
    lazyImages.forEach(img => imageObserver.observe(img));
    
    // Smooth scroll polyfill
    if (!('scrollBehavior' in document.documentElement.style)) {
        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/smoothscroll-polyfill@0.4.4/dist/smoothscroll.min.js';
        document.head.appendChild(script);
    }
}); 