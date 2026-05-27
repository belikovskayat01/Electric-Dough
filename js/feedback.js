document.addEventListener('DOMContentLoaded', function() {
    const phoneInput = document.getElementById('feedbackPhone');
    if (phoneInput) {
        Inputmask({
            mask: '+7 (999) 999-99-99',
            showMaskOnHover: false,
            showMaskOnFocus: true,
            clearIncomplete: true,
            placeholder: '_',
            definitions: {
                '9': {
                    validator: '[0-9]',
                    cardinality: 1
                }
            }
        }).mask(phoneInput);
    }
    
    const feedbackForm = document.getElementById('feedbackForm');
    
    if (feedbackForm) {
        feedbackForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const name = document.getElementById('feedbackName').value.trim();
            let phone = document.getElementById('feedbackPhone').value.trim();
            const email = document.getElementById('feedbackEmail').value.trim();
            const message = document.getElementById('feedbackMessage').value.trim();
            
            if (!name) {
                showFeedbackPopup('Ошибка', 'Пожалуйста, укажите ваше имя', 'error');
                return;
            }
            
            if (!phone || phone.includes('_')) {
                showFeedbackPopup('Ошибка', 'Пожалуйста, введите номер телефона полностью', 'error');
                return;
            }
            
            if (!message) {
                showFeedbackPopup('Ошибка', 'Пожалуйста, введите сообщение', 'error');
                return;
            }
            
            const submitBtn = feedbackForm.querySelector('.form-button');
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Отправка...';
            
            try {
                const formData = new FormData();
                formData.append('name', name);
                formData.append('phone', phone);
                formData.append('email', email);
                formData.append('message', message);
                
                const response = await fetch('process_feedback.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showFeedbackPopup('Успешно!', result.message, 'success');
                    feedbackForm.reset();
                    if (phoneInput) {
                        phoneInput.value = '';
                        Inputmask({
                            mask: '+7 (999) 999-99-99',
                            showMaskOnHover: false,
                            showMaskOnFocus: true,
                            clearIncomplete: true,
                            placeholder: '_'
                        }).mask(phoneInput);
                    }
                } else {
                    showFeedbackPopup('Ошибка', result.message, 'error');
                }
            } catch (error) {
                console.error('Ошибка:', error);
                showFeedbackPopup('Ошибка', 'Произошла ошибка. Попробуйте позже.', 'error');
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
        });
    }
});
function showFeedbackPopup(title, message, type = 'success') {
    let popup = document.getElementById('feedback-popup');
    
    if (!popup) {
        popup = document.createElement('div');
        popup.id = 'feedback-popup';
        popup.className = 'feedback-popup hidden';
        popup.innerHTML = `
            <div class="feedback-popup-content">
                <div class="feedback-popup-header">
                    <h3 class="feedback-popup-title" id="feedback-popup-title">${title}</h3>
                    <button class="feedback-popup-close" onclick="closeFeedbackPopup()">×</button>
                </div>
                <div class="feedback-popup-body">
                    <p id="feedback-popup-message">${message}</p>
                </div>
                <div class="feedback-popup-footer">
                    <button class="feedback-popup-btn" onclick="closeFeedbackPopup()">OK</button>
                </div>
            </div>
        `;
        document.body.appendChild(popup);
    } else {
        const popupTitle = document.getElementById('feedback-popup-title');
        const popupMessage = document.getElementById('feedback-popup-message');
        if (popupTitle) popupTitle.textContent = title;
        if (popupMessage) popupMessage.textContent = message;
        popup.classList.remove('success', 'error', 'warning');
    }
    
    popup.classList.add(type);
    popup.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeFeedbackPopup() {
    const popup = document.getElementById('feedback-popup');
    if (popup) {
        popup.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeFeedbackPopup();
    }
});

document.addEventListener('click', function(e) {
    const popup = document.getElementById('feedback-popup');
    if (popup && !popup.classList.contains('hidden') && e.target === popup) {
        closeFeedbackPopup();
    }
});