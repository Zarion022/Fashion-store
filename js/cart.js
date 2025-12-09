// Управление корзиной
class Cart {
    constructor() {
        this.items = JSON.parse(localStorage.getItem('cart')) || [];
    }
    
    add(product, quantity = 1, options = {}) {
        const existingItem = this.items.find(item => 
            item.id === product.id && 
            item.size === (options.size || product.size) && 
            item.color === (options.color || product.color)
        );
        
        if (existingItem) {
            existingItem.quantity += quantity;
        } else {
            this.items.push({
                ...product,
                quantity,
                ...options
            });
        }
        
        this.save();
        this.updateUI();
        return true;
    }
    
    remove(index) {
        if (index >= 0 && index < this.items.length) {
            this.items.splice(index, 1);
            this.save();
            this.updateUI();
            return true;
        }
        return false;
    }
    
    updateQuantity(index, quantity) {
        if (index >= 0 && index < this.items.length) {
            if (quantity < 1) quantity = 1;
            if (quantity > 10) quantity = 10;
            
            this.items[index].quantity = quantity;
            this.save();
            this.updateUI();
            return true;
        }
        return false;
    }
    
    clear() {
        this.items = [];
        this.save();
        this.updateUI();
        return true;
    }
    
    getTotal() {
        return this.items.reduce((total, item) => total + (item.price * item.quantity), 0);
    }
    
    getItemCount() {
        return this.items.reduce((total, item) => total + item.quantity, 0);
    }
    
    save() {
        localStorage.setItem('cart', JSON.stringify(this.items));
    }
    
    updateUI() {
        // Обновляем счетчик в шапке
        const totalItems = this.getItemCount();
        document.querySelectorAll('.cart-count').forEach(el => {
            el.textContent = totalItems;
        });
        
        // Обновляем страницу корзины если она открыта
        if (window.location.pathname.includes('cart.html')) {
            this.renderCartPage();
        }
        
        // Обновляем страницу оформления заказа если она открыта
        if (window.location.pathname.includes('checkout.html')) {
            this.renderCheckoutPage();
        }
    }
    
    renderCartPage() {
        const container = document.getElementById('cart-items-container');
        const itemCount = document.getElementById('cart-items-count');
        const checkoutBtn = document.getElementById('checkout-btn');
        
        if (!container) return;
        
        if (this.items.length === 0) {
            container.innerHTML = `
                <div class="cart-empty">
                    <i class="fas fa-shopping-bag"></i>
                    <h3>Twój koszyk jest pusty</h3>
                    <p>Dodaj produkty, aby kontynuować zakupy</p>
                    <a href="catalog.html" class="btn btn-primary">Przejdź do katalogu</a>
                </div>
            `;
            if (itemCount) itemCount.textContent = '0';
            if (checkoutBtn) {
                checkoutBtn.disabled = true;
                checkoutBtn.classList.add('disabled');
            }
            return;
        }
        
        if (itemCount) itemCount.textContent = this.items.length;
        if (checkoutBtn) {
            checkoutBtn.disabled = false;
            checkoutBtn.classList.remove('disabled');
        }
        
        let html = '';
        
        this.items.forEach((item, index) => {
            const itemTotal = (item.price * item.quantity).toFixed(2);
            html += `
                <div class="cart-item" data-index="${index}">
                    <div class="cart-item-image">
                        <img src="${item.image}" alt="${item.name}" loading="lazy">
                    </div>
                    <div class="cart-item-details">
                        <h4 class="cart-item-name">${item.name}</h4>
                        <div class="cart-item-options">
                            ${item.size ? `<span>Rozmiar: ${item.size}</span>` : ''}
                            ${item.color ? `<span>Kolor: ${item.color}</span>` : ''}
                        </div>
                        <div class="cart-item-price">
                            <span class="item-total">${itemTotal} zł</span>
                            <span class="item-unit-price">${item.price.toFixed(2)} zł/szt.</span>
                        </div>
                    </div>
                    <div class="cart-item-quantity">
                        <button class="quantity-btn minus" data-index="${index}">-</button>
                        <input type="number" class="quantity-input" value="${item.quantity}" min="1" max="10" data-index="${index}">
                        <button class="quantity-btn plus" data-index="${index}">+</button>
                    </div>
                    <button class="cart-item-remove" data-index="${index}">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;
        });
        
        container.innerHTML = html;
        
        // Добавляем обработчики
        this.addCartEventListeners();
        this.calculateSummary();
    }
    
    renderCheckoutPage() {
        const container = document.getElementById('order-items');
        const subtotalEl = document.getElementById('order-subtotal');
        const shippingEl = document.getElementById('order-shipping');
        const discountEl = document.getElementById('order-discount');
        const totalEl = document.getElementById('order-total');
        
        if (!container) return;
        
        let html = '';
        let subtotal = 0;
        
        this.items.forEach(item => {
            const itemTotal = item.price * item.quantity;
            subtotal += itemTotal;
            
            html += `
                <div class="order-item">
                    <div class="order-item-image">
                        <img src="${item.image}" alt="${item.name}" loading="lazy">
                        <span class="order-item-quantity">${item.quantity}</span>
                    </div>
                    <div class="order-item-details">
                        <h4>${item.name}</h4>
                        ${item.size ? `<span>Rozmiar: ${item.size}</span>` : ''}
                        ${item.color ? `<span>Kolor: ${item.color}</span>` : ''}
                    </div>
                    <div class="order-item-price">
                        ${itemTotal.toFixed(2)} zł
                    </div>
                </div>
            `;
        });
        
        container.innerHTML = html;
        
        // Рассчитываем доставку
        let shipping = 0;
        const shippingRadio = document.querySelector('input[name="shipping_method"]:checked');
        if (shippingRadio) {
            shipping = shippingRadio.value === 'pickup' ? 0 : 
                      shippingRadio.value === 'courier' ? 15 : 25;
        }
        
        const discount = 0;
        const total = subtotal + shipping - discount;
        
        if (subtotalEl) subtotalEl.textContent = subtotal.toFixed(2) + ' zł';
        if (shippingEl) shippingEl.textContent = shipping.toFixed(2) + ' zł';
        if (discountEl) discountEl.textContent = discount.toFixed(2) + ' zł';
        if (totalEl) totalEl.textContent = total.toFixed(2) + ' zł';
        
        // Обработчик изменения способа доставки
        document.querySelectorAll('input[name="shipping_method"]').forEach(radio => {
            radio.addEventListener('change', () => {
                this.calculateCheckoutSummary();
            });
        });
    }
    
    calculateCheckoutSummary() {
        const subtotal = this.getTotal();
        let shipping = 0;
        const shippingRadio = document.querySelector('input[name="shipping_method"]:checked');
        
        if (shippingRadio) {
            shipping = shippingRadio.value === 'pickup' ? 0 : 
                      shippingRadio.value === 'courier' ? 15 : 25;
        }
        
        const discount = 0;
        const total = subtotal + shipping - discount;
        
        const shippingEl = document.getElementById('order-shipping');
        const totalEl = document.getElementById('order-total');
        
        if (shippingEl) shippingEl.textContent = shipping.toFixed(2) + ' zł';
        if (totalEl) totalEl.textContent = total.toFixed(2) + ' zł';
    }
    
    calculateSummary() {
        const subtotal = this.getTotal();
        
        let shipping = 0;
        const selectedShipping = document.querySelector('input[name="shipping"]:checked');
        if (selectedShipping && selectedShipping.id !== 'pickup') {
            const shippingPrice = selectedShipping.nextElementSibling.querySelector('.shipping-price').textContent;
            shipping = parseFloat(shippingPrice);
        }
        
        let discount = 0;
        const promoCodeInput = document.getElementById('promo-code-input');
        if (promoCodeInput && promoCodeInput.value === 'PROMO10') {
            discount = subtotal * 0.1;
        }
        
        const total = subtotal + shipping - discount;
        
        const subtotalEl = document.getElementById('subtotal');
        const shippingEl = document.getElementById('shipping');
        const discountEl = document.getElementById('discount');
        const totalEl = document.getElementById('total');
        
        if (subtotalEl) subtotalEl.textContent = subtotal.toFixed(2) + ' zł';
        if (shippingEl) shippingEl.textContent = shipping.toFixed(2) + ' zł';
        if (discountEl) discountEl.textContent = discount.toFixed(2) + ' zł';
        if (totalEl) totalEl.textContent = total.toFixed(2) + ' zł';
    }
    
    addCartEventListeners() {
        // Удаление товара
        document.querySelectorAll('.cart-item-remove').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const index = e.currentTarget.getAttribute('data-index');
                if (this.remove(parseInt(index))) {
                    showNotification('Produkt usunięty z koszyka');
                }
            });
        });
        
        // Изменение количества
        document.querySelectorAll('.quantity-btn.minus').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const index = parseInt(e.currentTarget.getAttribute('data-index'));
                const currentQuantity = this.items[index].quantity;
                if (currentQuantity > 1) {
                    this.updateQuantity(index, currentQuantity - 1);
                }
            });
        });
        
        document.querySelectorAll('.quantity-btn.plus').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const index = parseInt(e.currentTarget.getAttribute('data-index'));
                const currentQuantity = this.items[index].quantity;
                if (currentQuantity < 10) {
                    this.updateQuantity(index, currentQuantity + 1);
                }
            });
        });
        
        document.querySelectorAll('.quantity-input').forEach(input => {
            input.addEventListener('change', (e) => {
                const index = parseInt(e.currentTarget.getAttribute('data-index'));
                const newQuantity = parseInt(e.target.value);
                if (!isNaN(newQuantity) && newQuantity >= 1 && newQuantity <= 10) {
                    this.updateQuantity(index, newQuantity);
                } else {
                    e.target.value = this.items[index].quantity;
                }
            });
        });
        
        // Очистка корзины
        const clearBtn = document.querySelector('.clear-cart');
        if (clearBtn) {
            clearBtn.addEventListener('click', () => {
                if (confirm('Czy na pewno chcesz wyczyścić koszyk?')) {
                    this.clear();
                    showNotification('Koszyk został wyczyszczony');
                }
            });
        }
        
        // Промо-код
        const applyPromoBtn = document.getElementById('apply-promo');
        if (applyPromoBtn) {
            applyPromoBtn.addEventListener('click', () => {
                const promoInput = document.getElementById('promo-code-input');
                if (promoInput && promoInput.value === 'PROMO10') {
                    this.calculateSummary();
                    showNotification('Kod promocyjny zastosowany! Rabat 10%');
                } else if (promoInput && promoInput.value) {
                    showNotification('Nieprawidłowy kod promocyjny', 'error');
                }
            });
        }
        
        // Способы доставки
        document.querySelectorAll('input[name="shipping"]').forEach(radio => {
            radio.addEventListener('change', () => {
                this.calculateSummary();
            });
        });
    }
}

// Инициализация корзины
const cart = new Cart();

// Экспортируем для использования в других файлах
window.Cart = cart;

// Функция для показа уведомлений (дублируется для совместимости)
function showNotification(message, type = 'success') {
    // Проверяем, не существует ли уже уведомление
    const existingNotification = document.querySelector('.notification');
    if (existingNotification) {
        existingNotification.remove();
    }
    
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Удаляем через 3 секунды
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateY(-20px)';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    cart.updateUI();
    
    // Обработка промо-кода через Enter
    const promoInput = document.getElementById('promo-code-input');
    if (promoInput) {
        promoInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const applyBtn = document.getElementById('apply-promo');
                if (applyBtn) applyBtn.click();
            }
        });
    }
});