<?php
session_start();
require_once 'db.php';

$is_authenticated = isset($_SESSION['user_id']);
$user_id = $is_authenticated ? $_SESSION['user_id'] : null;
$user_name = '';

if ($is_authenticated) {
    if (isset($_SESSION['name'])) {
        $user_name = $_SESSION['name'] . ' ' . ($_SESSION['surname'] ?? '');
    } else {
        $stmt = $conn->prepare("SELECT Name, Surname FROM users WHERE ID_User = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($user = $result->fetch_assoc()) {
            $user_name = $user['Name'] . ' ' . ($user['Surname'] ?? '');
        }
        $stmt->close();
    }
}

function getNumEnding($num) {
    $num = $num % 100;
    if ($num > 10 && $num < 20) {
        return 'гостей';
    }
    $num = $num % 10;
    if ($num == 1) {
        return 'гость';
    } elseif ($num >= 2 && $num <= 4) {
        return 'гостя';
    } else {
        return 'гостей';
    }
}

?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Бронирование</title>
    <link rel="stylesheet" href="font.css">
    <link rel="stylesheet" href="css/variables.css">
    <link rel="stylesheet" href="css/booking.css">
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
        <h1 class="block__heading">Забронировать столик</h1>
        <h2 class="block__subheading">
            Выберите место на нашей сцене или доверьте выбор нам — мы подберём идеальный столик под ваше настроение!
        </h2>
        <div class="booking-container">
            <div class="booking-form-wrapper">
                <form id="bookingForm" class="booking-form" method="POST" action="process_booking.php">
                    <div class="form-group">
                        <label for="name">Ваше имя</label>
                        <input type="text" id="name" name="name"
                            value="<?php echo htmlspecialchars(trim($user_name)); ?>" placeholder="Введите ваше имя"
                            <?php echo $is_authenticated ? 'readonly' : 'required' ; ?>>
                    </div>
                    <div class="form-group">
                        <label for="phone">Телефон</label>
                        <input type="tel" id="phone" name="phone" required placeholder="+7 (___) ___-__-__">
                    </div>
                    <div class="form-group">
                        <label for="booking_date">Дата брони</label>
                        <input type="date" id="booking_date" name="booking_date" min="<?php echo date('Y-m-d'); ?>"
                            max="<?php echo date('Y-m-d', strtotime('+30 days')); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="booking_time">Время</label>
                        <input type="time" id="booking_time" name="booking_time" min="08:00" max="22:00" required>
                    </div>
                    <div class="form-group">
                        <label for="table_selection">Выбор столика</label>
                        
                        <div id="table_selection_buttons" class="table-selection-control">
                            <button type="button" class="table-icon-btn" id="openTableModal">
                                <img src="IMG/booking_icon.png" alt="Выбрать стол" class="table-selection__image">
                            </button>
                            <button type="button" class="any-table-btn" id="anyTableBtn">
                                Не имеет значения
                            </button>
                        </div>
                        
                        <div id="selected_table_display" class="selected-table-display" style="display: none;">
                            <div class="selected-table-info-card">
                                <span class="selected-table-icon">🍽️</span>
                                <div class="selected-table-details">
                                    <span class="selected-table-label">Выбран столик:</span>
                                    <span class="selected-table-number" id="selected_table_number_display"></span>
                                </div>
                                <button type="button" class="change-table-btn" id="changeTableBtn" title="Изменить выбор">✎</button>
                                <button type="button" class="clear-table-selection" id="clearTableSelection" title="Очистить выбор">✕</button>
                            </div>
                        </div>
                        
                        <input type="hidden" id="selected_table_id" name="selected_table" value="">
                    </div>
                                        <div class="form-group">
                        <label for="guests">Количество гостей</label>
                        <select id="guests" name="guests" required>
                            <option value="">Выберите количество</option>
                            <?php for($i = 1; $i <= 6; $i++): ?>
                            <option value="<?php echo $i; ?>">
                                <?php echo $i; ?>
                                <?php echo getNumEnding($i); ?>
                            </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="comment">Комментарий <span class="optional">*</span></label>
                        <textarea id="comment" name="comment" rows="4"
                            placeholder="Особые пожелания, аллергии, специальный случай..."></textarea>
                    </div>
                    <div class="form-group form-submit">
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            Забронировать
                        </button>
                    </div>
                </form>
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
    <div id="booking-popup" class="popup hidden">
        <div class="popup-content">
            <div class="popup-header">
                <h3 class="popup-title" id="popup-title">Уведомление</h3>
                <button class="popup-close" onclick="closePopup()">×</button>
            </div>
            <div class="popup-body">
                <p id="popup-message"></p>
            </div>
            <div class="popup-footer">
                <button class="popup-btn" onclick="closePopup()">OK</button>
            </div>
        </div>
    </div>
<div id="table-modal" class="table-modal hidden">
    <div class="table-modal-content">
        <div class="table-modal-header">
            <h2>Выберите свое место на сцене</h2>
            <button class="table-modal-close" id="closeTableModal">×</button>
        </div>
        <div class="table-legend">
            <div class="legend-item">
                <span class="legend-dot free"></span>
                <span>Свободен</span>
            </div>
            <div class="legend-item">
                <span class="legend-dot busy"></span>
                <span>Занят</span>
            </div>
            <div class="legend-item">
                <span class="legend-dot selected"></span>
                <span>Выбран Вами</span>
            </div>
        </div>
        <div class="restaurant-map">
            <div id="tablesContainer">
                <div class="loading-tables">Загрузка столиков...</div>
            </div>
            <div class="bar-area">
                <div class="bar-stools" id="barStoolsContainer"></div>
                <div class="bar-label">🍸 Барная стойка</div>
            </div>
        </div>
        <div class="table-modal-footer">
            <button class="cancel-table-btn" id="cancelTableBtn">Отменить выбор</button>
            <button class="confirm-table-btn" id="confirmTableBtn">Подтвердить выбор</button>
        </div>
    </div>
</div>
<script src="js/booking.js"></script>
</body>
</html>