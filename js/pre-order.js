console.log('pre-order.js загружен');
console.log('Initial values:', { selectedProductId, userId, isAuthenticated });

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM загружен');
    
    initDatePicker();
    initTimeValidation();
    initSelectedProduct();
    updateTotal();
});

function initDatePicker() {
    const dateInput = document.getElementById('pickup-date');
    if (dateInput) {
        const today = new Date().toISOString().split('T')[0];
        dateInput.min = today;
        console.log('Date picker initialized with min:', today);
    } else {
        console.log('Date input not found');
    }
}

function initTimeValidation() {
    const timeInput = document.getElementById('pickup-time');
    if (timeInput) {
        timeInput.addEventListener('change', function(e) {
            const time = e.target.value;
            const [hours] = time.split(':').map(Number);
            
            if (hours < 8 || hours >= 23) {
                alert('Время получения должно быть с 08:00 до 23:00');
                e.target.value = '';
            }
        });
        console.log('Time validation initialized');
    } else {
        console.log('Time input not found');
    }
}

function initSelectedProduct() {
    if (selectedProductId && selectedProductId > 0) {
        console.log('Looking for selected product:', selectedProductId);
        const selectedItem = document.querySelector(`.product-select-item[data-id="${selectedProductId}"]`);
        if (selectedItem) {
            selectedItem.scrollIntoView({ behavior: 'smooth', block: 'center' });
            selectedItem.classList.add('selected-product');
            console.log('Found and scrolled to selected product');
        } else {
            console.log('Selected product not found in DOM');
        }
    }
}

function changeQuantity(btn, delta) {
    console.log('changeQuantity called with delta:', delta);
    const input = btn.parentElement.querySelector('.quantity-input');
    if (!input) {
        console.error('Quantity input not found');
        return;
    }
    
    let value = parseInt(input.value) + delta;
    
    if (value < 0) value = 0;
    if (value > 10) value = 10;
    
    input.value = value;
    
    const item = input.closest('.product-select-item');
    if (item) {
        if (value > 0) {
            item.classList.add('has-quantity');
        } else {
            item.classList.remove('has-quantity');
        }
    }
    
    updateTotal();
}

function addProduct(productId) {
    console.log('addProduct called with id:', productId);
    const item = document.querySelector(`.product-select-item[data-id="${productId}"]`);
    if (item) {
        const input = item.querySelector('.quantity-input');
        if (input) {
            input.value = 1;
            item.classList.add('has-quantity');
            updateTotal();
            showNotification('Товар добавлен в предзаказ');
        }
    }
}

function updateTotal() {
    const inputs = document.querySelectorAll('.quantity-input');
    if (!inputs.length) {
        console.log('No quantity inputs found');
        return;
    }
    
    let total = 0;
    let hasItems = false;
    
    inputs.forEach(input => {
        const quantity = parseInt(input.value);
        if (quantity > 0) {
            hasItems = true;
            const price = parseFloat(input.dataset.price);
            if (!isNaN(price)) {
                total += price * quantity;
            }
        }
    });
    
    const totalElement = document.getElementById('total-amount');
    if (totalElement) {
        totalElement.textContent = total + ' руб.';
        console.log('Total updated:', total);
    } else {
        console.log('Total element not found');
    }
    
    const submitBtn = document.querySelector('.submit-preorder');
    if (submitBtn) {
        if (hasItems) {
            submitBtn.classList.add('active');
        } else {
            submitBtn.classList.remove('active');
        }
    }
}

function showNotification(message) {
    const notification = document.createElement('div');
    notification.className = 'notification';
    notification.textContent = message;
    
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background-color: #28a745;
        color: white;
        padding: 15px 25px;
        border-radius: 8px;
        font-family: "Inter", sans-serif;
        z-index: 9999;
        animation: slideIn 0.3s ease;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

function submitPreOrder() {
    console.log('submitPreOrder called');
    
    if (!userId || !isAuthenticated) {
        alert('Необходимо авторизоваться');
        window.location.href = 'account.php';
        return;
    }
    
    const dateInput = document.getElementById('pickup-date');
    const timeInput = document.getElementById('pickup-time');
    
    if (!dateInput || !dateInput.value) {
        alert('Выберите дату получения');
        if (dateInput) dateInput.focus();
        return;
    }
    
    if (!timeInput || !timeInput.value) {
        alert('Выберите время получения');
        if (timeInput) timeInput.focus();
        return;
    }
    
    const items = [];
    const inputs = document.querySelectorAll('.quantity-input');
    
    inputs.forEach(input => {
        const quantity = parseInt(input.value);
        if (quantity > 0) {
            items.push({
                id: input.dataset.id,
                name: input.dataset.name,
                quantity: quantity,
                price: parseFloat(input.dataset.price)
            });
        }
    });
    
    if (items.length === 0) {
        alert('Выберите хотя бы один товар');
        return;
    }
    
    const totalElement = document.getElementById('total-amount');
    const totalAmount = totalElement ? parseInt(totalElement.textContent) : 0;
    
    const orderData = {
        user_id: userId,
        pickup_date: dateInput.value,
        pickup_time: timeInput.value,
        items: items,
        total_amount: totalAmount
    };
    
    console.log('Sending order data:', orderData);
    
    const submitBtn = document.querySelector('.submit-preorder');
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Оформление...';
    }
    
    fetch('process_pre_order.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(orderData)
    })
    .then(res => {
        console.log('Response status:', res.status);
        return res.json();
    })
    .then(result => {
        console.log('Response data:', result);
        if (result.success) {
            alert('Предзаказ успешно оформлен!');
            window.location.reload();
        } else {
            alert('Ошибка: ' + result.message);
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Оформить предзаказ';
            }
        }
    })
    .catch(err => {
        console.error('Error:', err);
        alert('Ошибка соединения');
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Оформить предзаказ';
        }
    });
}