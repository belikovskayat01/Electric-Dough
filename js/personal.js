let currentBookingId = null;

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

document.addEventListener("DOMContentLoaded", () => {
  console.log("personal.js загружен");
  
  initTabs();
  
  const authModal = document.getElementById("auth-modal");
  if (!authModal) {
    console.error("Модальное окно авторизации не найдено!");
  }

  if (typeof isAuthenticated !== 'undefined' && isAuthenticated) {
    console.log("Пользователь авторизован через PHP");
    if (authModal) authModal.classList.add("hidden");
    
    const logoutBtn = document.getElementById('logout-button');
    if (logoutBtn) logoutBtn.style.display = 'inline-block';
    
    if (userRole === 'admin') {
      const adminBtn = document.getElementById('admin-panel-button');
      if (adminBtn) adminBtn.style.display = 'inline-block';
    }
  } else {
    console.log("Пользователь не авторизован через PHP, проверяем localStorage");
    const userStr = localStorage.getItem("user");
    if (userStr) {
      try {
        const userDataLocal = JSON.parse(userStr);
        if (authModal) authModal.classList.add("hidden");
        console.log("Данные загружены из localStorage");
        
        const logoutBtn = document.getElementById('logout-button');
        if (logoutBtn) logoutBtn.style.display = 'inline-block';
        
        if (userDataLocal.Role === 'admin') {
          const adminBtn = document.getElementById('admin-panel-button');
          if (adminBtn) adminBtn.style.display = 'inline-block';
        }
      } catch(e) {
        console.error("Ошибка парсинга localStorage:", e);
        if (authModal) authModal.classList.remove("hidden");
      }
    } else {
      if (authModal) authModal.classList.remove("hidden");
      const logoutBtn = document.getElementById('logout-button');
      if (logoutBtn) logoutBtn.style.display = 'none';
    }
  }

  const phoneInput = document.getElementById("reg-phone");
  if (phoneInput) {
    phoneInput.addEventListener("input", formatPhoneInput);
    console.log("Маска телефона настроена");
  }

  setupPopupHandlers();
  
  console.log("Инициализация завершена");
});

function initTabs() {
  const toggleBtns = document.querySelectorAll('.toggle-btn');
  const tabs = document.querySelectorAll('.history-tab');
  
  if (toggleBtns.length === 0) return;
  
  toggleBtns.forEach(btn => {
    btn.addEventListener('click', function() {
      const tabId = this.getAttribute('data-tab');
      
      toggleBtns.forEach(b => b.classList.remove('active'));
      this.classList.add('active');
      tabs.forEach(tab => tab.classList.remove('active'));
      const activeTab = document.getElementById(tabId);
      if (activeTab) {
        activeTab.classList.add('active');
      }
      localStorage.setItem('activeHistoryTab', tabId);
    });
  });
  
  const savedTab = localStorage.getItem('activeHistoryTab');
  if (savedTab) {
    const savedBtn = document.querySelector(`.toggle-btn[data-tab="${savedTab}"]`);
    if (savedBtn) {
      savedBtn.click();
    }
  }
}

function setupPopupHandlers() {
  document.addEventListener('click', function(e) {
    const cancelPopup = document.getElementById('cancel-popup');
    const resultPopup = document.getElementById('result-popup');
    
    if (cancelPopup && !cancelPopup.classList.contains('hidden')) {
      if (e.target === cancelPopup) {
        closeCancelPopup();
      }
    }
    
    if (resultPopup && !resultPopup.classList.contains('hidden')) {
      if (e.target === resultPopup) {
        closeResultPopup();
      }
    }
  });

  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      const cancelPopup = document.getElementById('cancel-popup');
      const resultPopup = document.getElementById('result-popup');
      
      if (cancelPopup && !cancelPopup.classList.contains('hidden')) {
        closeCancelPopup();
      }
      
      if (resultPopup && !resultPopup.classList.contains('hidden')) {
        closeResultPopup();
      }
    }
  });
}

function formatPhoneInput(e) {
  let input = e.target;
  let value = input.value.replace(/\D/g, "");

  if (!value.startsWith("7")) value = "7" + value;
  if (value.length > 11) value = value.slice(0, 11);

  let formatted = "+7";
  if (value.length > 1) formatted += " (" + value.slice(1, 4);
  if (value.length >= 4) formatted += ") " + value.slice(4, 7);
  if (value.length >= 7) formatted += "-" + value.slice(7, 9);
  if (value.length >= 9) formatted += "-" + value.slice(9, 11);

  input.value = formatted;
}

function showTab(tabName) {
  document.querySelectorAll(".tab-form").forEach((form) =>
    form.classList.remove("active")
  );
  document.getElementById(`${tabName}-form`).classList.add("active");
}

function showErrorToast(message, formId = "register-form") {
  const form = document.getElementById(formId);
  if (!form) {
    console.error(`Форма с id "${formId}" не найдена`);
    showDynamicToast(message);
    return;
  }
  
  let toast = form.querySelector("#register-error-toast");
  if (!toast && formId === "login-form") {
    toast = form.querySelector("#login-error-toast");
  }
  
  if (!toast) {
    console.error("Toast элемент не найден в форме");
    showDynamicToast(message);
    return;
  }

  toast.textContent = message; 
  toast.classList.remove("hidden");
  toast.classList.add("show");
  
  setTimeout(() => {
    hideErrorToast(formId);
  }, 3000);
}

function showDynamicToast(message) {
  const oldToast = document.querySelector('.dynamic-toast');
  if (oldToast) oldToast.remove();
  
  let toast = document.createElement('div');
  toast.className = 'toast dynamic-toast';
  toast.textContent = message;  
  document.body.appendChild(toast);
  
  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transition = 'opacity 0.3s';
    setTimeout(() => toast.remove(), 300);
  }, 3000);
}

function hideErrorToast(formId = "register-form") {
  const form = document.getElementById(formId);
  if (!form) return;
  
  let toast = form.querySelector("#register-error-toast");
  if (!toast && formId === "login-form") {
    toast = form.querySelector("#login-error-toast");
  }
  
  if (!toast) return;

  toast.classList.remove("show");
  toast.classList.add("hidden");
}

function hideErrorToast(formId = "register-form") {
  const form = document.getElementById(formId);
  if (!form) return;
  
  const toast = form.querySelector("#error-toast") || 
                 form.querySelector("#login-error-toast") || 
                 form.querySelector("#register-error-toast");
  
  if (!toast) return;

  toast.classList.remove("show");
  toast.classList.add("hidden");
}

function handleRegister() {
  const nameInput = document.getElementById("reg-name");
  const surnameInput = document.getElementById("reg-surname");
  const emailInput = document.getElementById("reg-email");
  const phoneInput = document.getElementById("reg-phone");
  const passwordInput = document.getElementById("reg-password");

  if (!nameInput || !surnameInput || !emailInput || !phoneInput || !passwordInput) {
    console.error("Не все поля регистрации найдены");
    showDynamicToast("Ошибка: не все поля найдены на странице");
    return;
  }

  hideErrorToast("register-form");

  const name = nameInput.value.trim();
  const surname = surnameInput.value.trim();
  const email = emailInput.value.trim();
  const phone = phoneInput.value.trim();
  const password = passwordInput.value.trim();

  if (!name) {
    showErrorToast("Введите имя");
    nameInput.focus();
    return;
  }
  if (!/^[a-zA-Zа-яА-ЯёЁ-]+$/.test(name)) {
    showErrorToast("Имя должно содержать только буквы, без пробелов и спец. символов");
    nameInput.focus();
    return;
  }

  if (!surname) {
    showErrorToast("Введите фамилию");
    surnameInput.focus();
    return;
  }
  if (!/^[a-zA-Zа-яА-ЯёЁ-]+$/.test(surname)) {
    showErrorToast("Фамилия должна содержать только буквы, без пробелов и спец. символов");
    surnameInput.focus();
    return;
  }

  if (!email) {
    showErrorToast("Введите email");
    emailInput.focus();
    return;
  }
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
    showErrorToast('Email должен содержать символы "@" и "."');
    emailInput.focus();
    return;
  }

  const phoneDigits = phone.replace(/\D/g, "");
  if (!phoneDigits) {
    showErrorToast("Введите телефон");
    phoneInput.focus();
    return;
  }
  if (phoneDigits.length !== 11 || !phoneDigits.startsWith("7")) {
    showErrorToast("Введите телефон в формате +7 (XXX) XXX-XX-XX");
    phoneInput.focus();
    return;
  }

  if (!password) {
    showErrorToast("Введите пароль");
    passwordInput.focus();
    return;
  }
  if (password.length < 6) {
    showErrorToast("Пароль должен быть не менее 6 символов");
    passwordInput.focus();
    return;
  }
  if (password.length > 25) {
    showErrorToast("Пароль не должен превышать 25 символов");
    passwordInput.focus();
    return;
  }
  if (/\s/.test(password)) {
    showErrorToast("Пароль не должен содержать пробелов");
    passwordInput.focus();
    return;
  }

  const requestData = {
    name: name,
    surname: surname,
    email: email,
    phone: phone,
    password: password,
  };

  console.log("Отправка данных:", requestData);

  fetch("register.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(requestData),
  })
    .then(async (res) => {
      console.log("Статус ответа:", res.status);
      
      if (res.status === 429) {
        throw new Error("Слишком много попыток регистрации. Подождите 30 секунд.");
      }
      
      const text = await res.text();
      console.log("Текст ответа:", text);
      
      try {
        const data = JSON.parse(text);
        return data;
      } catch (e) {
        console.error("Ошибка парсинга JSON:", e);
        throw new Error("Сервер вернул неверный формат данных");
      }
    })
    .then((result) => {
      console.log("Результат:", result);
      if (result.success) {
        showEmailVerificationPopup(result.message || "На вашу почту отправлена ссылка для подтверждения.");
        
        nameInput.value = "";
        surnameInput.value = "";
        emailInput.value = "";
        phoneInput.value = "";
        passwordInput.value = "";
           
      } else {
        showErrorToast("Ошибка регистрации: " + escapeHtml(result.error || "Неизвестная ошибка"));
      }
    })
    .catch((err) => {
      console.error("Ошибка регистрации:", err);
      let errorMessage = err.message;
      if (err.message.includes("429") || err.message.includes("Слишком много")) {
        errorMessage = "Слишком много попыток регистрации. Подождите 30 секунд перед следующей попыткой.";
      }
      showErrorToast("Ошибка: " + escapeHtml(errorMessage));
    });
}

function showEmailVerificationPopup(message) {
  let existingPopup = document.getElementById('email-verification-popup');
  if (existingPopup) {
    existingPopup.remove();
  }
  
  const popup = document.createElement('div');
  popup.id = 'email-verification-popup';
  popup.className = 'email-verification-popup';
  popup.innerHTML = `
    <div class="email-verification-overlay"></div>
    <div class="email-verification-content">
      <div class="email-verification-icon">📧</div>
      <h3 class="email-verification-title">Подтвердите email</h3>
      <p class="email-verification-message">${escapeHtml(message)}</p>
      <p class="email-verification-instruction">Перейдите по ссылке в письме, чтобы активировать аккаунт.</p>
      <p class="email-verification-note">После подтверждения вы сможете войти в систему.</p>
      <div class="email-verification-buttons">
        <button class="email-verification-btn ok-btn" onclick="closeEmailVerificationPopup()">Понятно</button>
        <button class="email-verification-btn login-btn" onclick="closeEmailVerificationPopupAndShowLogin()">Перейти ко входу</button>
      </div>
    </div>
  `;
  
  document.body.appendChild(popup);
  document.body.style.overflow = 'hidden';
  
  const overlay = popup.querySelector('.email-verification-overlay');
  if (overlay) {
    overlay.addEventListener('click', closeEmailVerificationPopup);
  }
  
  document.addEventListener('keydown', closePopupOnEscape);
}

function closeEmailVerificationPopup() {
  const popup = document.getElementById('email-verification-popup');
  if (popup) {
    popup.remove();
  }
  document.body.style.overflow = 'auto';
  document.removeEventListener('keydown', closePopupOnEscape);
}

function closeEmailVerificationPopupAndShowLogin() {
  closeEmailVerificationPopup();
  showAuthModal();
  showTab('login');
}

function closePopupOnEscape(e) {
  if (e.key === 'Escape') {
    closeEmailVerificationPopup();
  }
}

function handleLogin() {
  const emailInput = document.getElementById("login-email");
  const passwordInput = document.getElementById("login-password");

  if (!emailInput || !passwordInput) {
    console.error("Поля входа не найдены");
    return;
  }

  hideErrorToast("login-form");

  const email = emailInput.value.trim();
  const password = passwordInput.value.trim();

  if (!email) {
    showErrorToast("Введите email", "login-form");
    emailInput.focus();
    return;
  }
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
    showErrorToast('Email должен содержать символы "@" и "."', "login-form");
    emailInput.focus();
    return;
  }

  if (!password) {
    showErrorToast("Введите пароль", "login-form");
    passwordInput.focus();
    return;
  }

  fetch("login.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
      email: email,
      password: password,
    }),
  })
    .then((res) => {
      if (res.status === 429) {
        throw new Error("Слишком много попыток входа. Подождите 30 секунд.");
      }
      if (!res.ok) {
        throw new Error(`HTTP error! status: ${res.status}`);
      }
      return res.json();
    })
    .then((result) => {
      if (result.success) {
        localStorage.setItem("isLoggedIn", "true");
        localStorage.setItem("user", JSON.stringify(result.user));

        if (result.user.Role === "admin") {
          window.location.href = "admin/index.php";
        } else {
          window.location.reload();
        }
      } else {
        showErrorToast("Ошибка входа: " + escapeHtml(result.error || "Неизвестная ошибка"), "login-form");
      }
    })
    .catch((err) => {
      console.error("Ошибка входа:", err);
      let errorMessage = err.message;
      if (err.message.includes("429") || err.message.includes("Слишком много")) {
        errorMessage = "Слишком много попыток входа. Подождите 30 секунд перед следующей попыткой.";
      }
      showErrorToast("Ошибка входа: " + escapeHtml(errorMessage), "login-form");
    });
}

function handleLogout() {
  localStorage.removeItem("isLoggedIn");
  localStorage.removeItem("user");
  
  fetch('logout.php')
    .then(() => {
      window.location.href = 'account.php';
    })
    .catch(() => {
      window.location.reload();
    });
}

function showAuthModal() {
  const modal = document.getElementById('auth-modal');
  if (modal) {
    modal.classList.remove('hidden');
  } else {
    console.error("Модальное окно авторизации не найдено");
  }
}

function skipAuth() {
  localStorage.setItem("authSkipped", "true");
  window.location.href = "index.php";
}

function closeAuthModal() {
  const modal = document.getElementById('auth-modal');
  if (modal) {
    modal.classList.add('hidden');
    clearAuthForms();
  }
}

function clearAuthForms() {
  const loginEmail = document.getElementById('login-email');
  const loginPassword = document.getElementById('login-password');
  const regName = document.getElementById('reg-name');
  const regSurname = document.getElementById('reg-surname');
  const regEmail = document.getElementById('reg-email');
  const regPhone = document.getElementById('reg-phone');
  const regPassword = document.getElementById('reg-password');
  
  if (loginEmail) loginEmail.value = '';
  if (loginPassword) loginPassword.value = '';
  if (regName) regName.value = '';
  if (regSurname) regSurname.value = '';
  if (regEmail) regEmail.value = '';
  if (regPhone) regPhone.value = '';
  if (regPassword) regPassword.value = '';
  
  hideAllToasts();
}

function hideAllToasts() {
  const toasts = document.querySelectorAll('.toast');
  toasts.forEach(toast => {
    toast.classList.add('hidden');
    toast.classList.remove('show');
  });
}

function showCancelConfirmation(bookingId) {
  currentBookingId = bookingId;
  
  const bookingCard = document.getElementById(`booking-${bookingId}`);
  if (!bookingCard) {
    console.error(`Карточка бронирования с id booking-${bookingId} не найдена`);
    return;
  }
  
  const inputs = bookingCard.querySelectorAll('.booking-input, .value-input');
  const labels = bookingCard.querySelectorAll('.booking-label, .label');
  const details = [];
  
  for (let i = 0; i < Math.min(labels.length, inputs.length); i++) {
    let label = labels[i].textContent.replace(':', '').trim();
    let value = inputs[i].value || inputs[i].textContent || '';
    
    if (label !== 'Статус' && label !== 'статус') {
      details.push(`${escapeHtml(label)}: ${escapeHtml(value)}`);
    }
  }
  
  const bookingDetails = document.getElementById('booking-details');
  if (bookingDetails) {
    bookingDetails.innerHTML = '';
    details.forEach(line => {
      const p = document.createElement('p');
      p.textContent = line;
      bookingDetails.appendChild(p);
    });
  }
  
  const popup = document.getElementById('cancel-popup');
  if (popup) {
    popup.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
  }
}

function confirmCancel() {
  if (!currentBookingId) return;
  
  const yesBtn = document.querySelector('.cancel-yes-btn');
  const noBtn = document.querySelector('.cancel-no-btn');
  const originalText = yesBtn ? yesBtn.textContent : 'Да, отменить';
  
  if (yesBtn) {
    yesBtn.disabled = true;
    yesBtn.textContent = 'Отмена...';
  }
  if (noBtn) noBtn.disabled = true;
  
  fetch('cancel_booking.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ booking_id: currentBookingId })
  })
  .then(res => {
    if (!res.ok) {
      throw new Error(`HTTP error! status: ${res.status}`);
    }
    return res.json();
  })
  .then(result => {
    closeCancelPopup();
    
    if (result.success) {
      showResultPopup('Успех!', 'Бронирование успешно отменено', 'success');
      const bookingCard = document.getElementById(`booking-${currentBookingId}`);
      if (bookingCard) {
        bookingCard.remove();
      }
      const userBooking = document.querySelector('.user-booking');
      if (userBooking && userBooking.children.length === 0) {
        userBooking.innerHTML = '';
        const emptyState = document.createElement('div');
        emptyState.className = 'empty-state';
        emptyState.innerHTML = `
          <div class="empty-state-content">
            <div class="empty-icon">📅</div>
            <p class="empty-title">У вас пока нет активных бронирований</p>
            <p class="empty-description">Хотите провести вечер в уютной атмосфере?</p>
            <a href="booking.php" class="empty-btn">Забронировать столик</a>
          </div>
        `;
        userBooking.appendChild(emptyState);
      }
    } else {
      showResultPopup('Ошибка', escapeHtml(result.message || 'Неизвестная ошибка'), 'error');
    }
  })
  .catch(err => {
    console.error("Ошибка отмены бронирования:", err);
    closeCancelPopup();
    let errorMessage = 'Ошибка соединения: ' + err.message;
    if (err.message.includes("429")) {
      errorMessage = 'Слишком много запросов. Подождите 30 секунд.';
    }
    showResultPopup('Ошибка', escapeHtml(errorMessage), 'error');
  })
  .finally(() => {
    if (yesBtn) {
      yesBtn.disabled = false;
      yesBtn.textContent = originalText;
    }
    if (noBtn) noBtn.disabled = false;
  });
}

function closeCancelPopup() {
  const popup = document.getElementById('cancel-popup');
  if (popup) {
    popup.classList.add('hidden');
  }
  document.body.style.overflow = 'auto';
  currentBookingId = null;
}

function showResultPopup(title, message, type = 'success') {
  const popup = document.getElementById('result-popup');
  const popupTitle = document.getElementById('result-popup-title');
  const popupMessage = document.getElementById('result-popup-message');
  const popupBtn = document.getElementById('result-popup-btn');
  
  if (!popup || !popupTitle || !popupMessage) {
    console.error("Элементы popup не найдены");
    showDynamicToast(`${title}: ${message}`);
    return;
  }
  popupTitle.textContent = escapeHtml(title);
  popupMessage.textContent = escapeHtml(message);
  
  popup.classList.remove('success', 'error', 'warning');
  popup.classList.add(type);
  
  if (popupBtn) {
    if (type === 'error') {
      popupBtn.style.backgroundColor = '#dc3545';
    } else if (type === 'success') {
      popupBtn.style.backgroundColor = '#28a745';
    } else {
      popupBtn.style.backgroundColor = '#4F0F0E';
    }
  }
  
  popup.classList.remove('hidden');
  document.body.style.overflow = 'hidden';
}

function closeResultPopup() {
  const popup = document.getElementById('result-popup');
  if (popup) {
    popup.classList.add('hidden');
  }
  document.body.style.overflow = 'auto';
}

function clearHistory() {
    if (confirm('Вы уверены, что хотите очистить историю просмотров?')) {
        const btn = document.querySelector('.clear-history-btn');
        const originalText = btn ? btn.textContent : 'Очистить';
        
        if (btn) {
            btn.textContent = 'Очистка...';
            btn.disabled = true;
        }
        
        localStorage.removeItem('viewed_products');
        document.cookie = 'viewed_products=; path=/; expires=Thu, 01 Jan 1970 00:00:01 GMT;';
        
        fetch('clear_history.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                showDynamicToast('Ошибка при очистке истории');
                if (btn) {
                    btn.textContent = originalText;
                    btn.disabled = false;
                }
            }
        })
        .catch(error => {
            console.error('Ошибка:', error);
            window.location.reload();
        });
    }
}
function togglePassword(inputId, iconElement) {
    const input = document.getElementById(inputId);
    if (input.type === 'password') {
        input.type = 'text';
        iconElement.src = 'IMG/open-pass-icon.png';
    } else {
        input.type = 'password';
        iconElement.src = 'IMG/hide-pass-icon.png';
    }
}

function showPopup(title, message, type = 'success') {
    showResultPopup(title, message, type);
}

function addToViewedProducts(productId) {
    let viewed = localStorage.getItem('viewed_products');
    let viewedArray = viewed ? viewed.split(',') : [];
    
    viewedArray = viewedArray.filter(id => id != productId);
    
    viewedArray.unshift(productId.toString());
    
    viewedArray = viewedArray.slice(0, 10);
    
    localStorage.setItem('viewed_products', viewedArray.join(','));
    
    document.cookie = `viewed_products=${viewedArray.join(',')}; path=/; max-age=${60 * 60 * 24 * 30}`;
    
    if (typeof isAuthenticated !== 'undefined' && isAuthenticated) {
        fetch('save_viewed_products.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ viewed_products: viewedArray.join(',') })
        }).catch(err => console.error('Ошибка сохранения истории:', err));
    }
}