<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Главная страница</title>
    <link rel="stylesheet" href="font.css">
    <link rel="stylesheet" href="css/variables.css">
    <link rel="stylesheet" href="css/main.css">
     <script src="https://cdn.jsdelivr.net/npm/inputmask@5.0.8/dist/inputmask.min.js"></script>
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
        <section class="main-block">
            <h1 class="main-block__title">electric dough</h1>
            <h2 class="main-block__information">
                Хотите почувствовать
                рок не только ушами,
                но и вкусом? <br>
                Вы на правильном пути!
            </h2>
            <div class="to-page__btn">
                <a href="catalog.php">
                    посмотреть меню
                </a>
            </div>
        </section>
        <section class="aboutus-block">
            <h3 class="block__heading">коротко о нас</h3>
            <div class="aboutus-block__content">
                <img src="IMG/bakery-env.jpeg" class="aboutus-block__image"
                    alt="Фотография обстановки в рок-пекарне Electric Dough">
                <div class="aboutus-block__info">
                    <p class="aboutus-block__text">
                        Electric Dough не просто место, где пекут.
                        Это пространство, где рок и выпечка
                        сливаются в единый ритм. Каждая наша булочка — словно нота в
                        мощной гитарной партии, а атмосфера
                        пронизана духом бунтарства и творчества.
                        Мы создаём вкусы, как создают хиты:
                        с душой, драйвом и безупречным
                        сочетанием ингредиентов.
                    </p>
                    <button class="know-more">
                        <a href="aboutus.php">
                            Узнать подробнее...
                        </a>
                    </button>
                </div>
            </div>
        </section>
        <section class="rock-legend-quote">
            <h3 class="block__heading">цитата рок-легенды</h3>
            <p class="text-explanation">
                Пока вы тут осматриваетесь, ловите порцию вдохновения от легенд
            </p>
            <div class="quotes-generation">
                <div class="quote-card" id="quoteCard">
                    <div class="quote-loading" id="quoteLoading">
                        <div class="spinner-small"></div>
                        <p>Загрузка цитаты...</p>
                    </div>
                    <p class="quote-text" id="quoteText" style="display: none;"></p>
                    <p class="quote-author" id="quoteAuthor" style="display: none;"></p>
                    <p class="quote-band" id="quoteBand" style="display: none;"></p>
                </div>
                <button class="next-quote" id="nextQuoteBtn">
                    🎸 Хочу другую цитату
                </button>
            </div>
        </section>
        <section class="sales-hits">
            <h3 class="block__heading">Хиты продаж</h3>
            <div class="sales-hits_cards">
                <div class="sales-hits__item">
                    <img src="IMG/products/cheesecake_bsb.jpeg" alt=" Чизкейк 'Black Sabbath'" class="item_image">
                    <p class="item_name">
                        Чизкейк Black Sabbath
                    </p>
                    <p class="item_price">
                        200 рублей
                    </p>
                    <a href="product.php?id=3">
                        <button class="about-item__btn">Подробнее...</button>
                    </a>
                </div>
                <div class="sales-hits__item">
                    <img src="IMG/products/bohemianrhapsody.jpeg" alt="Торт 'Богемская Рапсодия'" class="item_image">
                    <p class="item_name">
                        Пирог Bohemian Rhapsody
                    </p>
                    <p class="item_price">
                        420 рублей
                    </p>
                    <a href="product.php?id=1">
                        <button class="about-item__btn">Подробнее...</button>
                    </a>
                </div>
                <div class="sales-hits__item">

                    <img src="IMG/products/AC_DC.png" alt="Эклер 'AC/DC's High Voltage'" class="item_image">
                    <p class="item_name">
                        Эклеры AC/DC's High Voltage
                    </p>
                    <p class="item_price">
                        180 рублей
                    </p>
                    <a href="product.php?id=2">
                        <button class="about-item__btn">Подробнее...</button>
                    </a>
                </div>
            </div>
            <button class="to-page__btn different">
                <a href="catalog.php">
                    Смотреть весь каталог
                </a>
            </button>
        </section>
        <section class="feedback">
            <p class="text-explanation">
                Остались какие-то вопросы, или появились предложения?
            </p>
            <h3 class="block__heading">Свяжитесь с нами!</h3>
            <div class="contact-card">
                <div class="contact-info">
                    <h4 class="contact-title">
                        Заполните форму
                    </h4>
                    <p class="contact-description">
                        Оставьте свои контакты, и мы свяжемся с вами в ближайшее время
                    </p>
                </div>
                <div class="contact-form-wrapper">
                    <form class="contact-form" id="feedbackForm">
                        <input class="form-input" type="text" id="feedbackName" placeholder="Ваше имя*" required>
                        <input class="form-input" type="tel" id="feedbackPhone" placeholder="Телефон*" required>
                        <input class="form-input" type="email" id="feedbackEmail" placeholder="Почта">
                        <textarea class="form-textarea" id="feedbackMessage" placeholder="Ваше сообщение" required></textarea>
                        <button class="form-button" type="submit">отправить</button>
                    </form>
                </div>
            </div>
        </section>
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
    </main>
<script src="js/quotes-api.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof initQuotesBlock === 'function') {
            initQuotesBlock();
        }
    });
</script>
<script src="js/feedback.js"></script>
</body>
</html>