<?php
session_start();
require_once 'db.php';

$products_query = "
    SELECT * FROM products 
    WHERE Status = 'available' 
    ORDER BY 
        CASE Category 
            WHEN 'Десерты' THEN 1
            WHEN 'Выпечка' THEN 2
            WHEN 'Напитки' THEN 3
            ELSE 4
        END,
        Name";
$products_result = $conn->query($products_query);

$all_products = [];
while ($product = $products_result->fetch_assoc()) {
    $all_products[] = $product;
}

$products_by_category = [];
foreach ($all_products as $product) {
    $products_by_category[$product['Category']][] = $product;
}

$categories = array_keys($products_by_category);

$products_js = [];
foreach ($all_products as $product) {
    $products_js[] = [
        'id' => $product['ID_product'],
        'name' => $product['Name'],
        'category' => $product['Category'],
        'price' => floatval($product['Price']),
        'image' => $product['Image'],
        'cookingTime' => $product['Category'] == 'Напитки' ? 'fast' : 
                        (strpos($product['Name'], 'Сэндвич') !== false || strpos($product['Name'], 'Пиццетта') !== false ? 'medium' : 'medium')
    ];
}

$current_date = date('Y-m-d H:i:s');
$vinyl_query = "
    SELECT * FROM limited_vinyl 
    WHERE is_active = 1 
    AND start_date <= '$current_date' 
    AND end_date >= '$current_date'
    ORDER BY sort_order ASC
";
$vinyl_result = $conn->query($vinyl_query);
$vinyl_items = [];
while ($vinyl = $vinyl_result->fetch_assoc()) {
    $vinyl_items[] = $vinyl;
}

$nearest_end_date = null;
if (!empty($vinyl_items)) {
    $end_dates = array_column($vinyl_items, 'end_date');
    $nearest_end_date = min($end_dates);
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $product_id = (int)$_GET['id'];
    
    $viewed = isset($_COOKIE['viewed_products']) ? $_COOKIE['viewed_products'] : '';
    $viewedArray = $viewed ? explode(',', $viewed) : [];
    
    $viewedArray = array_filter($viewedArray, function($id) use ($product_id) {
        return $id != $product_id;
    });
    
    array_unshift($viewedArray, $product_id);
    $viewedArray = array_slice($viewedArray, 0, 10);
    
    setcookie('viewed_products', implode(',', $viewedArray), time() + (86400 * 30), "/");
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Каталог</title>
    <link rel="stylesheet" href="font.css">
    <link rel="stylesheet" href="css/variables.css">
    <link rel="stylesheet" href="css/styles.css">
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
        <section class="order-rules">
            <h1 class="block__heading">Правила заказа</h1>
            <div class="order-rules__info">
                <h2 class="block__subtitle">Как сделать заказ в рок‑пекарне</h2>
                <p class="block__intro-phrase">Мы работаем по системе предзаказа — так гарантируем свежесть и своевременность доставки.</p>
                <h3 class="rules-list__title">Пошагово:</h3>
                <div class="order-rules__list">
                    <ol>
                        <li class="rules-list__item">Изучите каталог: просмотрите позиции, ознакомьтесь с описанием и фото в карточках товаров.</li>
                        <li class="rules-list__item">Выберите блюда: отметьте понравившиеся десерты, выпечку и напитки.</li>
                        <li class="rules-list__item">Перейдите к форме предзаказа: нажмите кнопку «Оформить предзаказ» внизу страницы или перейдите на страницу «Предзаказ».</li>
                        <li class="rules-list__item">Заполните форму:
                            <ul>
                                <li class="form-steps__item">укажите дату и время получения заказа;</li>
                                <li class="form-steps__item">добавьте товары в корзину и укажите количество;</li>
                                <li class="form-steps__item">нажмите кнопку «Оформить предзаказ».</li>
                            </ul>
                        </li>
                        <li class="rules-list__item">Заказ создан! Можете отслеживать его статус в Личном кабинете в разделе «Мои предзаказы».</li>
                    </ol>
                </div>
            </div>
        </section>
        <section class="our-products">
            <h2 class="block__subtitle">
                Теперь, когда правила изучены, можно приступать к 
                гастрономическому путешествию по нашему меню.
            </h2>
            <h1 class="block__heading">
                Наша продукция
            </h1>
            <p class="our-products_text">
                Поднимем занавес! Вашему вниманию — главные звёзды вечера: сладкие десерты,<br>
                дерзкая выпечка и напитки, которые задают ритм.
            </p>
            <div class="filters-container">
                <div class="filters-bar">
                    <div class="filter-dropdown">
                        <button class="filter-btn" onclick="toggleDropdown('popularityDropdown')">
                            ПО ПОПУЛЯРНОСТИ <span class="filter-arrow">▼</span>
                        </button>
                        <div id="popularityDropdown" class="dropdown-content">
                            <label class="dropdown-option">
                                <input type="radio" name="sortBy" value="default" checked onchange="applyFilters()">
                                <span>По умолчанию</span>
                            </label>
                            <label class="dropdown-option">
                                <input type="radio" name="sortBy" value="popular" onchange="applyFilters()">
                                <span>Самые популярные</span>
                            </label>
                            <label class="dropdown-option">
                                <input type="radio" name="sortBy" value="price_asc" onchange="applyFilters()">
                                <span>Цена: по возрастанию</span>
                            </label>
                            <label class="dropdown-option">
                                <input type="radio" name="sortBy" value="price_desc" onchange="applyFilters()">
                                <span>Цена: по убыванию</span>
                            </label>
                            <label class="dropdown-option">
                                <input type="radio" name="sortBy" value="name_asc" onchange="applyFilters()">
                                <span>Название: А-Я</span>
                            </label>
                            <label class="dropdown-option">
                                <input type="radio" name="sortBy" value="name_desc" onchange="applyFilters()">
                                <span>Название: Я-А</span>
                            </label>
                        </div>
                    </div>
                    <div class="filter-dropdown">
                        <button class="filter-btn" onclick="toggleDropdown('categoryDropdown')">
                            КАТЕГОРИЯ <span class="filter-arrow">▼</span>
                        </button>
                        <div id="categoryDropdown" class="dropdown-content">
                            <?php foreach ($categories as $category): ?>
                                <label class="dropdown-option">
                                    <input type="checkbox" value="<?php echo htmlspecialchars($category); ?>" onchange="applyFilters()">
                                    <span><?php echo htmlspecialchars($category); ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="filter-dropdown">
                        <button class="filter-btn" onclick="toggleDropdown('priceDropdown')">
                            ЦЕНА, ₽ <span class="filter-arrow">▼</span>
                        </button>
                        <div id="priceDropdown" class="dropdown-content price-dropdown">
                            <div class="price-range-filter">
                                <div class="price-inputs-filter">
                                    <input type="number" id="priceMinFilter" placeholder="от" min="0" class="price-input-filter" oninput="applyFilters()">
                                    <span>—</span>
                                    <input type="number" id="priceMaxFilter" placeholder="до" min="0" class="price-input-filter" oninput="applyFilters()">
                                </div>
                                <div class="price-buttons">
                                    <button class="price-preset" onclick="setPriceRange(0, 300)">до 300 ₽</button>
                                    <button class="price-preset" onclick="setPriceRange(300, 600)">300-600 ₽</button>
                                    <button class="price-preset" onclick="setPriceRange(600, 1000)">600-1000 ₽</button>
                                    <button class="price-preset" onclick="setPriceRange(1000, 2000)">от 1000 ₽</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="filter-dropdown">
                        <button class="filter-btn" onclick="toggleDropdown('timeDropdown')">
                            ВРЕМЯ ПРИГОТОВЛЕНИЯ <span class="filter-arrow">▼</span>
                        </button>
                        <div id="timeDropdown" class="dropdown-content">
                            <label class="dropdown-option">
                                <input type="radio" name="cookingTime" value="all" checked onchange="applyFilters()">
                                <span>Все</span>
                            </label>
                            <label class="dropdown-option">
                                <input type="radio" name="cookingTime" value="fast" onchange="applyFilters()">
                                <span>Быстро (до 15 мин)</span>
                            </label>
                            <label class="dropdown-option">
                                <input type="radio" name="cookingTime" value="medium" onchange="applyFilters()">
                                <span>Средне (15-30 мин)</span>
                            </label>
                            <label class="dropdown-option">
                                <input type="radio" name="cookingTime" value="long" onchange="applyFilters()">
                                <span>Долго (более 30 мин)</span>
                            </label>
                        </div>
                    </div>
                    <button class="reset-filters-btn-filter" onclick="resetAllFilters()">Сбросить все</button>
                </div>
            </div>
            <div id="filteredProductsContainer">
                <?php foreach ($products_by_category as $category => $products): ?>
                    <h2 id="<?php echo strtolower($category); ?>" class="category-name" data-category="<?php echo htmlspecialchars($category); ?>">
                        <?php echo $category; ?>
                    </h2>
                    <?php 
                    $product_chunks = array_chunk($products, 3);
                    foreach ($product_chunks as $chunk): 
                    ?>
                        <div class="products_cards" data-category="<?php echo htmlspecialchars($category); ?>">
                            <?php foreach ($chunk as $product): ?>
                                <div class="product_item" 
                                     data-id="<?php echo $product['ID_product']; ?>"
                                     data-name="<?php echo htmlspecialchars($product['Name']); ?>"
                                     data-category="<?php echo htmlspecialchars($product['Category']); ?>"
                                     data-price="<?php echo $product['Price']; ?>"
                                     data-cooking-time="<?php 
                                        if ($product['Category'] == 'Напитки') echo 'fast';
                                        elseif (strpos($product['Name'], 'Сэндвич') !== false || strpos($product['Name'], 'Пиццетта') !== false) echo 'medium';
                                        elseif ($product['Category'] == 'Выпечка') echo 'medium';
                                        else echo 'medium';
                                     ?>">
                                    <img src="IMG/products/<?php echo $product['Image']; ?>" 
                                         class="product-image"
                                         alt="<?php echo htmlspecialchars($product['Name']); ?>"
                                         onerror="this.src='IMG/placeholder.jpg'">
                                    <p class="item_name">
                                        <?php echo htmlspecialchars($product['Name']); ?>
                                    </p>
                                    <div class="product-price"><?php echo $product['Price']; ?> ₽</div>
                                    <a href="product.php?id=<?php echo $product['ID_product']; ?>">
                                        <button>подробнее...</button>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </div>
        </section>
        <?php if (!empty($vinyl_items)): ?>
        <section class="limited-vinyl__block" id="limitedVinylBlock">
            <h2 class="category-name limited-vinyl__title">Лимитированный винил</h2>
            <p class="our-products_text limited-vinyl__text">Как редкий сингл на концерте - эти позиции в продаже всего неделю. <br>
            Успей забрать свой кусочек рок‑истории, пока таймер не обнулил коллекцию!</p>
            <?php if ($nearest_end_date): ?>
                <div class="vinyl-timer-container">
                    <div class="timer-title">До конца акции осталось:</div>
                    <div class="countdown-timer" id="vinylCountdown">
                        <div class="timer-block">
                            <span class="timer-number" id="timerDays">00</span>
                            <span class="timer-label">дней</span>
                        </div>
                        <div class="timer-block">
                            <span class="timer-number" id="timerHours">00</span>
                            <span class="timer-label">часов</span>
                        </div>
                        <div class="timer-block">
                            <span class="timer-number" id="timerMinutes">00</span>
                            <span class="timer-label">минут</span>
                        </div>
                        <div class="timer-block">
                            <span class="timer-number" id="timerSeconds">00</span>
                            <span class="timer-label">секунд</span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <div class="limited-vinyl__list">
                <?php foreach ($vinyl_items as $vinyl): ?>
                <div class="limited-vinyl__item">
                    <img src="IMG/<?php echo htmlspecialchars($vinyl['image']); ?>" 
                         class="limited-vinyl__image" 
                         alt="<?php echo htmlspecialchars($vinyl['title']); ?>"
                         onerror="this.src='IMG/placeholder.jpg'">
                    <div class="limited-vinyl__content">
                        <h3 class="vinyl__item-title"><?php echo htmlspecialchars($vinyl['title']); ?></h3>
                        <p class="vinyl__item-description">
                            <?php echo nl2br(htmlspecialchars($vinyl['description'])); ?>
                        </p>
                        <h4 class="vinyl-item__price"><?php echo number_format($vinyl['price'], 0, '', ' '); ?> ₽</h4>
                        <button class="vinyl-order-btn" onclick="window.location.href='pre-order.php?vinyl_id=<?php echo $vinyl['id']; ?>'">
                            Заказать
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>
       <section class="the-riff-game__block">
            <h5 class="call-to-action__phrase">Не можете определиться с выбором? Сыграйте рифф!</h5>
            <h2 class="block__subtitle">
                Понимаем — когда выбор большой, принять решение непросто. 
                Поэтому мы создали для вас музыкальный рандомайзер блюд и напитков!
            </h2>
            <p class="the-riff-game__info">
                Нажмите кнопку и рулетка случайным образом выберет для вас одно из блюд
                или напитков нашего рок‑меню. <br>
                Возможно, именно так вы откроете для себя новый фаворит!
            </p>
            <div class="riff-game-container" id="riffGameContainer">
                <div class="riff-game-card" id="riffGameCard">
                    <!-- Приветственное сообщение (пока не нажата кнопка) -->
                    <div class="riff-welcome" id="riffWelcome">
                        <div class="riff-welcome-message">🎸 Готов к риффу?</div>
                        <div class="riff-welcome-hint">Нажми на кнопку, и рулетка выберет блюдо за тебя!</div>
                    </div>
                    <div class="riff-loader" id="riffLoader" style="display: none;">
                        <div class="guitar-animation">🎸</div>
                        <p>Подбираем лучший рифф...</p>
                    </div>
                    <div class="riff-result" id="riffResult" style="display: none;">
                        <div class="riff-product-image">
                            <img id="riffProductImage" src="" alt="Блюдо">
                        </div>
                        <div class="riff-product-info">
                            <h3 class="riff-product-name" id="riffProductName"></h3>
                            <p class="riff-product-category" id="riffProductCategory"></p>
                            <p class="riff-product-price" id="riffProductPrice"></p>
                            <p class="riff-product-description" id="riffProductDescription"></p>
                            <div class="riff-buttons">
                                <a href="#" id="riffOrderLink" class="riff-order-btn">🎸 Заказать</a>
                                <button class="riff-play-again" id="riffPlayAgain">🎲 Сыграть ещё</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <button class="riff-game__btn" id="startRiffBtn">🎸 Сыграть рифф!</button>
        </section>
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
        const productsDataFromPhp = <?php echo json_encode($products_js); ?>;
        const isAuthenticated = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
        const vinylEndDate = <?php echo $nearest_end_date ? json_encode($nearest_end_date) : 'null'; ?>;
    </script>
    <script src="js/catalog-filters.js"></script>
    <script src="js/vinyl-timer.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof initFilters === 'function') {
                initFilters(productsDataFromPhp);
            }
            if (typeof initVinylTimer === 'function' && vinylEndDate) {
                initVinylTimer(vinylEndDate);
            }
        });
    </script>
    <script src="js/riff-game.js"></script>
</body>
</html>