// Основные функции сайта
document.addEventListener('DOMContentLoaded', function() {
    initMobileMenu();
    initSearch();
    updateCartCounter();
    initSmoothScroll();
    initProductCards();
});

function initMobileMenu() {
    const mobileBtn = document.querySelector('.mobile-menu-btn');
    const mobileMenu = document.querySelector('.mobile-menu');
    const closeBtn = document.querySelector('.mobile-menu-close');
    
    if (mobileBtn && mobileMenu) {
        mobileBtn.addEventListener('click', () => {
            mobileMenu.classList.add('active');
            document.body.style.overflow = 'hidden';
        });
        
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                mobileMenu.classList.remove('active');
                document.body.style.overflow = '';
            });
        }
        
        // Закрыть при клике на ссылку
        document.querySelectorAll('.mobile-nav-list a').forEach(link => {
            link.addEventListener('click', () => {
                mobileMenu.classList.remove('active');
                document.body.style.overflow = '';
            });
        });
    }
}

function initSearch() {
    const searchBtn = document.querySelector('.search-btn');
    const searchModal = document.querySelector('.search-modal');
    const searchClose = document.querySelector('.search-close');
    const searchInput = document.querySelector('.search-input');
    
    if (searchBtn && searchModal) {
        searchBtn.addEventListener('click', () => {
            searchModal.style.display = 'flex';
            setTimeout(() => searchInput?.focus(), 100);
        });
        
        if (searchClose) {
            searchClose.addEventListener('click', () => {
                searchModal.style.display = 'none';
            });
        }
        
        // Закрыть при клике вне поиска
        searchModal.addEventListener('click', (e) => {
            if (e.target === searchModal) {
                searchModal.style.display = 'none';
            }
        });
        
        // Обработка поиска
        if (searchInput) {
            searchInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    const query = searchInput.value.trim();
                    if (query) {
                        window.location.href = `catalog.html?search=${encodeURIComponent(query)}`;
                    }
                }
            });
        }
    }
}

function updateCartCounter() {
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    const totalItems = cart.reduce((total, item) => total + item.quantity, 0);
    document.querySelectorAll('.cart-count').forEach(el => {
        el.textContent = totalItems;
    });
}

function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href !== '#') {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    const headerHeight = document.querySelector('.header')?.offsetHeight || 0;
                    const targetPosition = target.offsetTop - headerHeight;
                    window.scrollTo({
                        top: targetPosition,
                        behavior: 'smooth'
                    });
                }
            }
        });
    });
}

function initProductCards() {
    // Ленивая загрузка изображений
    const lazyImages = document.querySelectorAll('img[loading="lazy"]');
    
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    imageObserver.unobserve(img);
                }
            });
        });
        
        lazyImages.forEach(img => imageObserver.observe(img));
    }
    
    // Обработчики для кнопок "Добавить в корзину"
    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-add-to-cart')) {
            const btn = e.target.closest('.btn-add-to-cart');
            const productId = btn.getAttribute('data-id');
            
            // Находим карточку продукта
            const productCard = btn.closest('.product-card');
            const productName = productCard?.querySelector('.product-name')?.textContent || 'Товар';
            const productPrice = productCard?.querySelector('.current-price')?.textContent || '0';
            const productImage = productCard?.querySelector('img')?.src || '';
            
            const product = {
                id: productId,
                name: productName,
                price: parseFloat(productPrice.replace(' zł', '').replace(',', '.')),
                image: productImage,
                quantity: 1
            };
            
            addToCart(product);
            
            // Анимация добавления
            const originalHTML = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check"></i> Dodano';
            btn.classList.add('btn-success');
            btn.disabled = true;
            
            setTimeout(() => {
                btn.innerHTML = originalHTML;
                btn.classList.remove('btn-success');
                btn.disabled = false;
            }, 2000);
        }
    });
}

function addToCart(product) {
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    
    // Проверяем, есть ли уже такой товар
    const existingItem = cart.find(item => 
        item.id === product.id && 
        item.size === product.size && 
        item.color === product.color
    );
    
    if (existingItem) {
        existingItem.quantity += product.quantity;
    } else {
        cart.push(product);
    }
    
    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartCounter();
    showNotification('Produkt dodany do koszyka!');
}

function showNotification(message, type = 'success') {
    // Удаляем старые уведомления
    document.querySelectorAll('.notification').forEach(n => n.remove());
    
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Анимация появления
    setTimeout(() => {
        notification.style.opacity = '1';
        notification.style.transform = 'translateY(0)';
    }, 10);
    
    // Удаляем через 3 секунды
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateY(-20px)';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Добавляем стили для уведомлений
const notificationStyles = document.createElement('style');
notificationStyles.textContent = `
    .notification {
        position: fixed;
        top: 100px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 1000;
        opacity: 0;
        transform: translateY(-20px);
        transition: all 0.3s ease;
    }
    
    .notification.success {
        background: #4CAF50;
        color: white;
    }
    
    .notification.error {
        background: #f44336;
        color: white;
    }
    
    .notification-content {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .notification i {
        font-size: 20px;
    }
    
    @media (max-width: 768px) {
        .notification {
            top: 80px;
            left: 20px;
            right: 20px;
        }
    }
`;

document.head.appendChild(notificationStyles);

// Инициализация при загрузке страницы
window.addEventListener('load', function() {
    document.body.classList.remove('loading');
});