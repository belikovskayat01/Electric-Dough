<?php
session_start();
require_once 'db.php';

$is_authenticated = isset($_SESSION['user_id']);
$user_id = $is_authenticated ? $_SESSION['user_id'] : null;

$products_query = "SELECT * FROM products WHERE Status = 'available' ORDER BY Category, Name";
$products_result = $conn->query($products_query);
$products = [];
while ($row = $products_result->fetch_assoc()) {
    $products[] = $row;
}

$selected_product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;

$conn->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Предзаказ</title>
    <link rel="stylesheet" href="font.css">
    <link rel="stylesheet" href="css/variables.css">
    <link rel="stylesheet" href="css/pre-order.css">
    <script>
        const selectedProductId = <?php echo $selected_product_id; ?>;
        const userId = <?php echo $user_id ? $user_id : 'null'; ?>;
        const isAuthenticated = <?php echo $is_authenticated ? 'true' : 'false'; ?>;
    </script>
</head>
<body>
    <header class="header">
        <div class="logo">
            <a href="index.php">
                <img src="IMG/logo.png" alt="Логотип бренда" width="65px">
            </a>
        </div>
        <nav class="menu">
            <ul>
                <li><a href="aboutus.php">О нас</a></li>
                <li><a href="catalog.php">Каталог</a></li>
                <li><a href="booking.php">Бронирование</a></li>
                <li><a href="pre-order.php">Предзаказ</a></li>
                <li><a href="contacts.php">Контакты</a></li>
            </ul>
        </nav>
        <div class="account">
            <a href="account.php">
                <img src="IMG/avatar.png" alt="Личный кабинет" width="60px">
            </a>
        </div>
    </header>
    <main>
        <h1 class="page__title">Оформить новый предзаказ</h1>

        <h2 class="page__subtitle">
            Планируйте свой идеальный завтрак, обед или ужин заранее! 
            С системой предзаказов вы сможете:
            <ul class="available-functions__list">
                <li class="available-function__item">выбрать любимые блюда из нашего рок-меню без спешки;</li>
                <li class="available-function__item">указать удобное время и дату получения;</li>
                <li class="available-function__item">сэкономить время на ожидание — просто заберите готовый заказ!</li>
            </ul>
        </h2>
        <h2 class="page__subtitle block__intro-phrase">
            Ваш гастрономический сет уже ждёт композиции — 
            осталось только выбрать блюда! 
        </h2>
        
        <?php if (!$is_authenticated): ?>
            <div class="auth-warning">
                <p>Чтобы оформить предзаказ, необходимо авторизоваться</p>
                <a href="account.php" class="primary-btn">Войти в личный кабинет</a>
            </div>
        <?php else: ?>
            
            <!-- Форма создания нового предзаказа -->
            <section class="new-preorder">
                <div class="preorder-form-container">
                    <form id="preorder-form" class="preorder-form">
                        <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="pickup-date">Дата получения</label>
                                <input type="date" id="pickup-date" name="pickup_date" 
                                       min="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="pickup-time">Время получения</label>
                                <input type="time" id="pickup-time" name="pickup_time" 
                                       min="08:00" max="22:00" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Выберите товары из каталога:</label>
                            <div class="catalog-link">
                                <a href="catalog.php" class="secondary-btn">Перейти в каталог</a>
                            </div>
                            
                            <div class="products-selector">
                                <?php foreach ($products as $product): 
                                    $selected = ($product['ID_product'] == $selected_product_id) ? 'selected-product' : '';
                                    // Путь к изображению
                                    $image_path = !empty($product['Image']) ? 'IMG/products/' . $product['Image'] : 'IMG/placeholder.jpg';
                                ?>
                                <div class="product-select-item <?php echo $selected; ?>" data-id="<?php echo $product['ID_product']; ?>">
                                    <div class="product-image">
                                        <img src="<?php echo $image_path; ?>" alt="<?php echo htmlspecialchars($product['Name']); ?>" 
                                             onerror="this.src='IMG/placeholder.jpg'">
                                    </div>
                                    <div class="product-info">
                                        <div class="product-details">
                                            <span class="product-name"><?php echo htmlspecialchars($product['Name']); ?></span>
                                            <span class="product-price"><?php echo $product['Price']; ?> руб.</span>
                                        </div>
                                        <div class="product-controls">
                                            <div class="quantity-control">
                                                <button type="button" class="quantity-btn minus" onclick="changeQuantity(this, -1)">-</button>
                                                <input type="number" class="quantity-input" value="<?php echo ($product['ID_product'] == $selected_product_id) ? '1' : '0'; ?>" 
                                                       min="0" max="10" 
                                                       data-id="<?php echo $product['ID_product']; ?>"
                                                       data-name="<?php echo htmlspecialchars($product['Name']); ?>"
                                                       data-price="<?php echo $product['Price']; ?>">
                                                <button type="button" class="quantity-btn plus" onclick="changeQuantity(this, 1)">+</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <div class="preorder-total">
                            <span class="total-amount__title">Итого:</span>
                            <span id="total-amount" class="total-amount">0 рублей</span>
                        </div>
                        <button type="button" class="submit-preorder" onclick="submitPreOrder()">
                            Оформить предзаказ
                        </button>
                    </form>
                </div>
            </section>
        <?php endif; ?>
        <div id="successModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <span class="modal-close" onclick="closeModal()">&times;</span>
                    <h2>🎸 Предзаказ успешно создан!</h2>
                </div>
                <div class="modal-body">
                    <p>Ваш предзаказ принят в обработку.</p>
                    <p>Вы можете следить за его статусом в Личном кабинете.</p>
                </div>
                <div class="modal-footer">
                    <a href="account.php" class="modal-btn">Перейти в личный кабинет</a>
                    <button onclick="closeModal()" class="modal-btn secondary">Остаться на странице</button>
                </div>
            </div>
        </div>
    </main>
    <footer class="footer">
        <p class="company-info">
            &copy; 2026 Electric Dough Rock Bakery
            Все права защищены
        </p>
        <nav class="menu footer-menu">
            <ul>
                <li><a href="aboutus.php">О нас</a></li>
                <li><a href="catalog.php">Каталог</a></li>
                <li><a href="booking.php">Бронирование</a></li>
                <li><a href="pre-order.php">Предзаказ</a></li>
                <li><a href="contacts.php">Контакты</a></li>
            </ul>
        </nav>
        <a href="#top" class="to-top">Наверх ↑</a>
    </footer>
    <script>
        function changeQuantity(btn, delta) {
            const input = btn.parentElement.querySelector('.quantity-input');
            let value = parseInt(input.value) + delta;
            
            if (value < 0) value = 0;
            if (value > 10) value = 10;
            
            input.value = value;
            updateTotal();
            const productItem = btn.closest('.product-select-item');
            if (value > 0) {
                productItem.classList.add('has-quantity');
            } else {
                productItem.classList.remove('has-quantity');
            }
        }
        
        function updateTotal() {
            let total = 0;
            const quantityInputs = document.querySelectorAll('.quantity-input');
            quantityInputs.forEach(input => {
                const quantity = parseInt(input.value);
                const price = parseInt(input.dataset.price);
                if (quantity > 0) {
                    total += quantity * price;
                }
            });
            document.getElementById('total-amount').innerText = total + ' рублей';
        }
        
        function showModal() {
            const modal = document.getElementById('successModal');
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden'; 
        }
        
        function closeModal() {
            const modal = document.getElementById('successModal');
            modal.style.display = 'none';
            document.body.style.overflow = 'auto'; 
        }
        
        window.onclick = function(event) {
            const modal = document.getElementById('successModal');
            if (event.target == modal) {
                closeModal();
            }
        }
        
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeModal();
            }
        });

        function submitPreOrder() {
            const pickupDate = document.getElementById('pickup-date').value;
            const pickupTime = document.getElementById('pickup-time').value;
            
            const items = [];
            const quantityInputs = document.querySelectorAll('.quantity-input');
            let totalAmount = 0;
            
            quantityInputs.forEach(input => {
                const quantity = parseInt(input.value);
                if (quantity > 0) {
                    items.push({
                        id: input.dataset.id,
                        name: input.dataset.name,
                        quantity: quantity,
                        price: parseInt(input.dataset.price)
                    });
                    totalAmount += quantity * parseInt(input.dataset.price);
                }
            });
            
            if (items.length === 0) {
                alert('Пожалуйста, выберите хотя бы один товар');
                return;
            }
            
            if (!pickupDate || !pickupTime) {
                alert('Пожалуйста, укажите дату и время получения');
                return;
            }
            
            const submitBtn = document.querySelector('.submit-preorder');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Оформление...';
            
            fetch('process_pre_order.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    user_id: userId,
                    pickup_date: pickupDate,
                    pickup_time: pickupTime,
                    items: items,
                    total_amount: totalAmount
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showModal();
                    document.getElementById('preorder-form').reset();
                    quantityInputs.forEach(input => {
                        input.value = 0;
                    });
                    document.querySelectorAll('.product-select-item').forEach(item => {
                        item.classList.remove('has-quantity');
                    });
                    updateTotal();
                } else {
                    alert('Ошибка: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Произошла ошибка при оформлении предзаказа');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Оформить предзаказ';
            });
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            updateTotal();
            
            if (selectedProductId > 0) {
                const selectedItem = document.querySelector(`.product-select-item[data-id="${selectedProductId}"]`);
                if (selectedItem) {
                    selectedItem.classList.add('has-quantity');
                }
            }
        });
    </script>
</body>
</html>