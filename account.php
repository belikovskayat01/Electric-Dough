<?php
session_start();
require_once 'db.php';
require_once 'includes/loyalty.php'; 

$is_authenticated = isset($_SESSION['user_id']);
$user_data = null;
$user_role = null;

$loyalty_data = getNewUserLoyaltyData();

if ($is_authenticated) {
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("SELECT ID_User, Name, Surname, Email, Phone, Role, is_verified FROM users WHERE ID_User = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user_data = $result->fetch_assoc();
        $user_role = $user_data['Role'];
        $is_verified = $user_data['is_verified'] ?? 0;
    }
    $stmt->close();

    $orders_count = getUserTotalOrdersCount($conn, $user_id);
    
    $pre_orders_stmt = $conn->prepare("SELECT COUNT(*) as pre_count FROM pre_orders WHERE ID_user = ?");
    $pre_orders_stmt->bind_param("i", $user_id);
    $pre_orders_stmt->execute();
    $pre_orders_result = $pre_orders_stmt->get_result();
    $pre_orders_row = $pre_orders_result->fetch_assoc();
    $pre_orders_count = $pre_orders_row['pre_count'];
    $pre_orders_stmt->close();
    

$is_new_user = ($is_verified == 0 && $orders_count == 0 && $pre_orders_count == 0);

if ($is_new_user) {
    $loyalty_data = getNewUserLoyaltyData();
} elseif ($orders_count == 0) {
    $loyalty_data = getNewUserLoyaltyData();
} else {
    $loyalty_data = calculateLoyaltyData($orders_count);
}
    
    $bookings = [];
    if ($user_role !== 'admin') {
        $user_name = $user_data['Name'] . ' ' . ($user_data['Surname'] ?? '');
        
        $booking_stmt = $conn->prepare("
            SELECT b.*, rt.table_number 
            FROM booking b
            LEFT JOIN restaurant_tables rt ON b.ID_table = rt.ID_table
            WHERE (b.ID_user = ? OR b.Name = ?)
            AND b.Status != 'cancelled'
            ORDER BY b.Booking_date DESC, b.Booking_time DESC
        ");
        $booking_stmt->bind_param("is", $user_id, $user_name);
        $booking_stmt->execute();
        $booking_result = $booking_stmt->get_result();
        
        while ($row = $booking_result->fetch_assoc()) {
            if (is_null($row['ID_user']) && $user_id) {
                $update_stmt = $conn->prepare("UPDATE booking SET ID_user = ? WHERE ID_booking = ?");
                $update_stmt->bind_param("ii", $user_id, $row['ID_booking']);
                $update_stmt->execute();
                $update_stmt->close();
                $row['ID_user'] = $user_id;
            }
            $bookings[] = $row;
        }
        $booking_stmt->close();
    }

    $pre_orders = [];
    if ($user_role !== 'admin') {
        $pre_order_stmt = $conn->prepare("
            SELECT po.*, 
                   GROUP_CONCAT(
                       CONCAT(
                           poi.Product_name, '|',
                           poi.Quantity, '|',
                           poi.Price, '|',
                           COALESCE(p.Image, 'default.jpg')
                       )
                   ) as items_data
            FROM pre_orders po
            LEFT JOIN pre_order_items poi ON po.ID_pre_order = poi.ID_pre_order
            LEFT JOIN products p ON p.Name COLLATE utf8mb4_general_ci = poi.Product_name
            WHERE po.ID_user = ?
            GROUP BY po.ID_pre_order
            ORDER BY po.Created_at DESC
        ");
        $pre_order_stmt->bind_param("i", $user_id);
        $pre_order_stmt->execute();
        $pre_order_result = $pre_order_stmt->get_result();
        
        while ($row = $pre_order_result->fetch_assoc()) {
            $items = [];
            if ($row['items_data']) {
                $items_raw = explode(',', $row['items_data']);
                foreach ($items_raw as $item_str) {
                    $parts = explode('|', $item_str);
                    if (count($parts) >= 4) {
                        $items[] = [
                            'product_name' => $parts[0],
                            'quantity' => (int)$parts[1],
                            'price' => (float)$parts[2],
                            'total' => (float)$parts[1] * (float)$parts[2],
                            'image' => $parts[3]
                        ];
                    }
                }
            }
            $row['items'] = $items;
            $pre_orders[] = $row;
        }
        $pre_order_stmt->close();
    }
}

$viewed_products = [];

if ($is_authenticated) {
    $viewed_ids = [];
    if (isset($_SESSION['viewed_products']) && !empty($_SESSION['viewed_products'])) {
        $viewed_ids = explode(',', $_SESSION['viewed_products']);
    } 
    elseif (isset($_COOKIE['viewed_products']) && !empty($_COOKIE['viewed_products'])) {
        $viewed_ids = explode(',', $_COOKIE['viewed_products']);
        $_SESSION['viewed_products'] = $_COOKIE['viewed_products'];
    }
    
    $viewed_ids = array_filter(array_unique($viewed_ids));
    $viewed_ids = array_slice($viewed_ids, 0, 3);
    
    if (!empty($viewed_ids)) {
        $placeholders = implode(',', array_fill(0, count($viewed_ids), '?'));
        $stmt = $conn->prepare("SELECT ID_product, Name, Price, Image, Category FROM products WHERE ID_product IN ($placeholders) AND Status = 'available'");
        $stmt->bind_param(str_repeat('i', count($viewed_ids)), ...$viewed_ids);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $products_map = [];
        while ($row = $result->fetch_assoc()) {
            $products_map[$row['ID_product']] = $row;
        }
        
        foreach ($viewed_ids as $id) {
            if (isset($products_map[$id])) {
                $viewed_products[] = $products_map[$id];
            }
        }
        $stmt->close();
    }
} else {
    if (isset($_COOKIE['viewed_products']) && !empty($_COOKIE['viewed_products'])) {
        $viewed_ids = explode(',', $_COOKIE['viewed_products']);
        $viewed_ids = array_filter(array_unique($viewed_ids));
        $viewed_ids = array_slice($viewed_ids, 0, 3);
        
        if (!empty($viewed_ids)) {
            $placeholders = implode(',', array_fill(0, count($viewed_ids), '?'));
            $stmt = $conn->prepare("SELECT ID_product, Name, Price, Image, Category FROM products WHERE ID_product IN ($placeholders) AND Status = 'available'");
            $stmt->bind_param(str_repeat('i', count($viewed_ids)), ...$viewed_ids);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $products_map = [];
            while ($row = $result->fetch_assoc()) {
                $products_map[$row['ID_product']] = $row;
            }
            
            foreach ($viewed_ids as $id) {
                if (isset($products_map[$id])) {
                    $viewed_products[] = $products_map[$id];
                }
            }
            $stmt->close();
        }
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный кабинет</title>
    <link rel="stylesheet" href="font.css">
    <link rel="stylesheet" href="css/variables.css">
    <link rel="stylesheet" href="css/personal.css">
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
        <div class="profile-container">
            <h1 class="block__heading">Мой профиль</h1>

            <?php if (!$is_authenticated): ?>
            <div class="not-authorized">
                <p class="not-authorized__message">Вы не авторизованы. <br> Пожалуйста, войдите в систему.</p>
                <button class="primary-btn" onclick="showAuthModal()">Войти</button>
            </div>
            <?php else: ?>
            <div class="profile-content">
                <!-- Левая карточка -->
                <div class="profile-card">
                    <div class="profile-image">
                        <img src="IMG/avatar.png" alt="Аватар">
                    </div>
                    <div class="profile-info">
                        <div class="info-row">
                            <label class="label">Имя</label>
                            <input type="text" class="value-input"
                                value="<?php echo htmlspecialchars($user_data['Name'] ?? ''); ?>" readonly>
                        </div>
                        <div class="info-row">
                            <label class="label">Фамилия</label>
                            <input type="text" class="value-input"
                                value="<?php echo htmlspecialchars($user_data['Surname'] ?? ''); ?>" readonly>
                        </div>
                        <div class="info-row">
                            <label class="label">Почта</label>
                            <input type="email" class="value-input"
                                value="<?php echo htmlspecialchars($user_data['Email'] ?? ''); ?>" readonly>
                        </div>
                        <div class="info-row">
                            <label class="label">Телефон</label>
                            <input type="tel" class="value-input"
                                value="<?php echo htmlspecialchars($user_data['Phone'] ?? ''); ?>" readonly>
                        </div>
                    </div>
                </div>
                <div class="loyalty-card <?php echo $loyalty_data['level_class']; ?>">
                    <p class="loyalty-title">ВАШ УРОВЕНЬ В ПРОГРАММЕ ЛОЯЛЬНОСТИ</p>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo $loyalty_data['progress_width']; ?>%;"></div>
                        <div class="progress-dot" style="left: <?php echo $loyalty_data['dot_position']; ?>%;"></div>
                    </div>
                    <div class="progress-points">
                        <span>0</span>
                        <?php if ($loyalty_data['level_class'] == 'star'): ?>
                        <span>250</span>
                        <span>1500</span>
                        <?php elseif ($loyalty_data['level_class'] == 'legend'): ?>
                        <span>250</span>
                        <span>1500+</span>
                        <?php else: ?>
                        <span>250</span>
                        <span>1500</span>
                        <?php endif; ?>
                    </div>
                    <p class="loyalty-level">ВАШ УРОВЕНЬ -
                        <?php echo $loyalty_data['level']; ?>
                    </p>
                    <?php if ($loyalty_data['discount'] > 0): ?>
                    <p class="loyalty-discount">СКИДКА
                        <?php echo $loyalty_data['discount']; ?>%
                    </p>
                    <?php endif; ?>
                    <div class="stats">
                        <div class="stat-item">
                            <span class="stat-number">
                                <?php echo $loyalty_data['orders_count']; ?>
                            </span>
                            <span class="stat-label">ЗАКАЗОВ</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number">
                                <?php echo $loyalty_data['points_earned']; ?>
                            </span>
                            <span class="stat-label">НАКОПЛЕНО БАЛЛОВ</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number">
                                <?php echo $loyalty_data['points_spent']; ?>
                            </span>
                            <span class="stat-label">ПОТРАЧЕНО БАЛЛОВ</span>
                        </div>
                    </div>
                    <?php if ($loyalty_data['points_to_next'] > 0): ?>
                    <div class="next-level">
                        <span class="next-number">
                            <?php echo $loyalty_data['points_to_next']; ?>
                        </span>
                        <span class="next-text">ДО СЛЕДУЮЩЕГО УРОВНЯ</span>
                    </div>
                    <?php elseif ($loyalty_data['level_class'] == 'legend'): ?>
                    <div class="next-level">
                        <span class="next-number">⭐</span>
                        <span class="next-text">МАКСИМАЛЬНЫЙ УРОВЕНЬ</span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="profile-actions">
                <button class="action-btn" onclick="handleLogout()">Выйти</button>
                <?php if ($user_role === 'admin'): ?>
                <button class="action-btn" onclick="window.location.href='admin/index.php'">
                    Перейти в админ панель
                </button>
                <?php endif; ?>
            </div>
            <?php if ($user_role !== 'admin'): ?>
            <section class="user-history">
                <div class="history-header">
                    <h2 class="block__heading history-title">бронирования и предзаказы</h2>
                    <div class="toggle-buttons">
                        <button class="toggle-btn active" data-tab="bookings-tab">
                            📅 Посмотреть бронирования
                        </button>
                        <button class="toggle-btn" data-tab="preorders-tab">
                            🍰 Посмотреть предзаказы
                        </button>
                    </div>
                </div>
                <div id="bookings-tab" class="history-tab active">
                    <div class="user-booking">
                        <?php if (empty($bookings)): ?>
                        <div class="empty-state">
                            <div class="empty-state-content">
                                <div class="empty-icon">📅</div>
                                <p class="empty-title">У вас пока нет бронирований</p>
                                <p class="empty-description">Хотите провести вечер в уютной атмосфере?</p>
                                <a href="booking.php" class="empty-btn">Забронировать столик</a>
                            </div>
                        </div>
                        <?php else: ?>
                        <?php foreach ($bookings as $booking): ?>
                        <div class="booking-card" id="booking-<?php echo $booking['ID_booking']; ?>">
                            <div class="booking-header">
                                <span class="booking-number">Бронирование №
                                    <?php echo $booking['ID_booking']; ?>
                                </span>
                            </div>
                            <div class="booking-body">
                                <div class="booking-info-row">
                                    <label class="booking-label">Дата</label>
                                    <input type="text" class="booking-input"
                                        value="<?php echo date('d.m.Y', strtotime($booking['Booking_date'])); ?>"
                                        readonly>
                                </div>
                                <div class="booking-info-row">
                                    <label class="booking-label">Время</label>
                                    <input type="text" class="booking-input"
                                        value="<?php echo date('H:i', strtotime($booking['Booking_time'])); ?>"
                                        readonly>
                                </div>
                                <div class="booking-info-row">
                                    <label class="booking-label">Количество гостей</label>
                                    <input type="text" class="booking-input"
                                        value="<?php echo $booking['Guests']; ?> чел." readonly>
                                </div>
                                <div class="booking-info-row">
                                    <label class="booking-label">Столик</label>
                                    <input type="text" class="booking-input booking-table-number" value="<?php 
                                                       if (!empty($booking['table_number'])) {
                                                           echo 'Стол №' . $booking['table_number'];
                                                       } else {
                                                           echo 'Не выбран';
                                                       }
                                                   ?>" readonly>
                                </div>
                                <div class="booking-info-row">
                                    <label class="booking-label">Статус</label>
                                    <input type="text"
                                        class="booking-input booking-status-input <?php echo $booking['Status'] == 'confirmed' ? 'status-confirmed' : 'status-created'; ?>"
                                        value="<?php 
                                                       $statuses = ['created' => 'Создано', 'confirmed' => 'Подтверждена', 'cancelled' => 'Отменена'];
                                                       echo $statuses[$booking['Status']] ?? $booking['Status'];
                                                   ?>" readonly>
                                </div>
                                <div class="booking-info-row booking-comment-row">
                                    <label class="booking-label">Особые пожелания</label>
                                    <textarea class="booking-input booking-textarea" readonly
                                        rows="3"><?php echo htmlspecialchars($booking['Comment'] ?? 'Нет'); ?></textarea>
                                </div>
                                <?php if ($booking['Status'] != 'confirmed' && $booking['Status'] != 'cancelled'): ?>
                                <div class="booking-actions">
                                    <button class="cancel-btn"
                                        onclick="showCancelConfirmation(<?php echo $booking['ID_booking']; ?>)">
                                        Отменить бронирование
                                    </button>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div id="preorders-tab" class="history-tab">
                    <div class="preorders-section">
                        <?php if (empty($pre_orders)): ?>
                        <div class="empty-state">
                            <div class="empty-state-content">
                                <div class="empty-icon">🍰</div>
                                <p class="empty-title">У вас пока нет предзаказов</p>
                                <p class="empty-description">Хотите заранее заказать наши вкусности?</p>
                                <a href="pre-order.php" class="empty-btn">Сделать предзаказ</a>
                            </div>
                        </div>
                        <?php else: ?>
                        <?php foreach ($pre_orders as $order): 
                            $status_text = [
                                'new' => 'Новый',
                                'confirmed' => 'Подтвержден',
                                'completed' => 'Выполнен',
                                'cancelled' => 'Отменен'
                            ];
                            $status_class = [
                                'new' => 'status-new',
                                'confirmed' => 'status-confirmed',
                                'completed' => 'status-completed',
                                'cancelled' => 'status-cancelled'
                            ];
                        ?>
                        <div class="preorder-card">
                            <div class="preorder-header">
                                <span class="order-number">Заказ №
                                    <?php echo htmlspecialchars($order['Order_number']); ?>
                                </span>
                            </div>
                            <div class="preorder-body">
                                <div class="preorder-info-grid">
                                    <div class="info-item">
                                        <span class="info-label">Дата получения:</span>
                                        <span class="info-value">
                                            <?php echo date('d.m.Y', strtotime($order['Pickup_date'])); ?>
                                        </span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Статус:</span>
                                        <span
                                            class="order-status <?php echo $status_class[$order['Status']] ?? 'status-new'; ?>">
                                            <?php echo $status_text[$order['Status']] ?? ($order['Status'] == 'new' ? 'Новый' : $order['Status']); ?>
                                        </span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Время получения:</span>
                                        <span class="info-value">
                                            <?php echo date('H:i', strtotime($order['Pickup_time'])); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="preorder-items">
                                    <div class="preorder-items-title">Состав заказа:</div>
                                    <div class="items-list">
                                        <?php foreach ($order['items'] as $item): ?>
                                        <div class="order-item">
                                            <div class="item-image">
                                                <img src="IMG/products/<?php echo htmlspecialchars($item['image']); ?>"
                                                    alt="<?php echo htmlspecialchars($item['product_name']); ?>"
                                                    onerror="this.src='IMG/default-product.jpg'">
                                            </div>
                                            <div class="item-info">
                                                <div class="item-name">
                                                    <?php echo htmlspecialchars($item['product_name']); ?>
                                                </div>
                                                <div class="item-price">
                                                    <?php echo number_format($item['price'], 0, '', ' '); ?> ₽
                                                </div>
                                            </div>
                                            <div class="item-quantity">
                                                <span class="quantity-label">Кол-во:</span>
                                                <span class="quantity-value">
                                                    <?php echo $item['quantity']; ?> шт.
                                                </span>
                                            </div>
                                            <div class="item-total">
                                                <?php echo number_format($item['total'], 0, '', ' '); ?> ₽
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <div class="preorder-total">
                                    <span class="total-label">Итоговая стоимость:</span>
                                    <span class="preorder-total-amount">
                                        <?php echo number_format($order['Total_amount'], 0, '', ' '); ?> ₽
                                    </span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
            <?php endif; ?>
            <?php endif; ?>
        </div>
        <?php if ($is_authenticated): ?>
        <section class="history-viewed">
            <div class="history-viewed-header">
                <h2 class="block__heading">История просмотров</h2>
            </div>
            <?php if (!empty($viewed_products)): ?>
            <div class="history-viewed-grid">
                <?php foreach ($viewed_products as $product): ?>
                <div class="history-product-item">
                    <a href="product.php?id=<?php echo $product['ID_product']; ?>" class="history-product-link">
                        <div class="history-product-image">
                            <img src="IMG/products/<?php echo htmlspecialchars($product['Image'] ?? 'default.jpg'); ?>"
                                alt="<?php echo htmlspecialchars($product['Name']); ?>"
                                onerror="this.src='IMG/placeholder.jpg'">
                        </div>
                        <p class="history-product-name">
                            <?php echo htmlspecialchars($product['Name']); ?>
                        </p>
                        <div class="history-product-price">
                            <?php echo number_format($product['Price'], 0, '', ' '); ?> ₽
                        </div>
                        <button class="history-product-btn">подробнее...</button>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="history-viewed-footer">
                <button class="clear-history-btn" onclick="clearHistory()">Очистить историю</button>
            </div>
            <?php else: ?>
            <div class="history-viewed-empty">
                <p class="empty-history-message">У вас пока нет истории просмотров</p>
                <p class="empty-history-suggestion">Перейдите в <a href="catalog.php"
                        class="empty-history-link">каталог</a>, чтобы добавить товары в историю</p>
            </div>
            <?php endif; ?>
        </section>
        <?php endif; ?>
    </main>

    <footer class="footer">
        <p class="company-info">&copy; 2026 Electric Dough Rock Bakery Все права защищены</p>
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
        const userData = <?php echo json_encode($user_data); ?>;
        const isAuthenticated = <?php echo $is_authenticated ? 'true' : 'false'; ?>;
        const userRole = <?php echo json_encode($user_role); ?>;
    </script>
    <script src="js/personal.js"></script>
    <div id="auth-modal" class="modal <?php echo !$is_authenticated ? '' : 'hidden'; ?>">
        <div class="modal-content">
            <div class="modal-header">
                <button class="close-btn" onclick="closeAuthModal()">×</button>
            </div>
            <div id="login-form" class="tab-form active">
                <h2 class="modal-title">Вход в систему</h2>
                <div class="form-group">
                    <label for="login-email">Электронная почта</label>
                    <input type="email" id="login-email" placeholder="example@mail.ru">
                </div>
                <div class="form-group password-group">
                    <label for="login-password">Пароль</label>
                    <div class="password-wrapper">
                        <input type="password" id="login-password" placeholder="Введите пароль" maxlength="25">
                        <img src="IMG/hide-pass-icon.png" alt="Показать пароль" class="toggle-password"
                            onclick="togglePassword('login-password', this)">
                    </div>
                </div>
                <button class="primary-btn login-btn" onclick="handleLogin()">Войти</button>
                <div class="form-links">
                    <span class="link-text" onclick="showTab('register')">Еще нет аккаунта? Зарегистрироваться</span>
                </div>
                <div id="login-error-toast" class="toast hidden"></div>
            </div>
            <div id="register-form" class="tab-form">
                <h2 class="modal-title">Создать аккаунт</h2>
                <div class="form-group">
                    <label for="reg-name">Имя</label>
                    <input type="text" id="reg-name" placeholder="Введите имя">
                </div>
                <div class="form-group">
                    <label for="reg-surname">Фамилия</label>
                    <input type="text" id="reg-surname" placeholder="Введите фамилию">
                </div>
                <div class="form-group">
                    <label for="reg-email">Электронная почта</label>
                    <input type="email" id="reg-email" placeholder="example@mail.ru">
                </div>
                <div class="form-group">
                    <label for="reg-phone">Телефон</label>
                    <input type="tel" id="reg-phone" placeholder="+7 (___) ___-__-__">
                </div>
                <div class="form-group password-group">
                    <label for="reg-password">Пароль</label>
                    <div class="password-wrapper">
                        <input type="password" id="reg-password" placeholder="Введите пароль">
                        <img src="IMG/hide-pass-icon.png" alt="Показать пароль" class="toggle-password"
                            onclick="togglePassword('reg-password', this)">
                    </div>
                </div>
                <button class="primary-btn register-btn" onclick="handleRegister()">Зарегистрироваться</button>
                <div class="form-links">
                    <span class="link-text" onclick="showTab('login')">Уже есть аккаунт? Войти</span>
                </div>
                <div id="register-error-toast" class="toast hidden"></div>
            </div>
        </div>
    </div>
    <div id="cancel-popup" class="popup hidden">
        <div class="popup-content cancel-popup-content">
            <div class="popup-header">
                <h3 class="popup-title">Подтверждение отмены</h3>
                <button class="popup-close" onclick="closeCancelPopup()">×</button>
            </div>
            <div class="popup-body">
                <p id="cancel-message">Вы уверены, что хотите отменить бронирование?</p>
                <div id="booking-details" class="booking-details"></div>
            </div>
            <div class="popup-footer popup-footer-buttons">
                <button class="popup-btn cancel-no-btn" onclick="closeCancelPopup()">Нет, оставить</button>
                <button class="popup-btn cancel-yes-btn" onclick="confirmCancel()">Да, отменить</button>
            </div>
        </div>
    </div>
    <div id="result-popup" class="popup hidden">
        <div class="popup-content">
            <div class="popup-header">
                <h3 class="popup-title" id="result-popup-title">Результат</h3>
                <button class="popup-close" onclick="closeResultPopup()">×</button>
            </div>
            <div class="popup-body">
                <p id="result-popup-message"></p>
            </div>
            <div class="popup-footer">
                <button class="popup-btn" id="result-popup-btn" onclick="closeResultPopup()">OK</button>
            </div>
        </div>
    </div>
</body>
</html>