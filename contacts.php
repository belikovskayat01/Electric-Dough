<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Контакты</title>
  <link rel="stylesheet" href="font.css">
  <link rel="stylesheet" href="css/variables.css">
  <link rel="stylesheet" href="css/contacts.css">
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
    <section class="main-contact-data">
      <h1 class="block__heading">Основные контактные данные</h1>
      <div class="main-contact-data__info">
        <p class="work-schedule information">
          График работы: <br>
          понедельник - четверг с 11.00 до 22.00
          пятница - воскресенье с 9.00 до 00.00
        </p>
        <p class="communication-methods information">
          Связь с заведением:
          Телефон: 8-495-952-82-26
          Почта:electric-dough@mail.ru
        </p>
      </div>
    </section>
    <section class="location__block">
      <h1 class="block__heading">наше расположение</h1>
      <p class="exact-address location-info">Точный адрес: г. Москва, ул. Большая Дмитровка, д. 4, стр. 10</p>
      <div class="route-description">
        <h2 class="route-description__title">Как добраться:</h2>
        <ul class="route-description__list">
          <li class="route-description__item">От станции метро «Тверская»: выйти из метро, следовать по ул. Большая
            Дмитровка в сторону центра. Пройти около 3 минут пешком — наше заведение будет по правой стороне</li>
          <li class="route-description__item">От станции метро «Пушкинская»: выйти из метро, пересесть на автобус № 15
            или № 30, выйти на остановке «Большая Дмитровка». Далее 1 минута пешком</li>
          <li class="route-description__item">На автомобиле: используйте навигационные приложения (2ГИС, Яндекс Карты) с
            точкой назначения «Electric Dough». Бесплатная парковка доступна в 5 минутах ходьбы от заведения (ул.
            Петровка, парковка у ТЦ «Петровский»)</li>
          <li class="route-description__item">На общественном транспорте: автобусы № 15, 30, 55 — остановка «Большая
            Дмитровка»</li>
        </ul>
      </div>
      <div class="map-part">
        <iframe
          src="https://yandex.ru/map-widget/v1/?um=constructor%3A5eede4bc63b6c66a0f81cdde172262ff93bf16e783639654fcb482dc7bf787f4&amp;source=constructor"
          width="959" height="481" frameborder="0"></iframe>
      </div>
    </section>
    <section class="FAQ__block">
      <h1 class="block__heading">Часто задаваемые вопросы</h1>
      <div class="questions__list">
        <div class="question__item">
          <p class="question__text">
            Вопрос: Как сделать предзаказ?
          </p>
          <p class="answer__text">
            Ответ: Вы можете оформить предзаказ на нашем сайте в разделе «Предзаказ» или позвонить по телефону
            8-495-952-82-26 <br>
            Укажите желаемую дату и время получения заказа — мы всё подготовим!
          </p>
        </div>
        <div class="question__item reverse">
          <p class="question__text">
            Вопрос: Принимаете ли вы оплату картой?
          </p>
          <p class="answer__text">
            Ответ: Да, мы принимаем оплату банковскими картами, а также наличными.
          </p>
        </div>
        <div class="question__item">
          <p class="question__text">
            Вопрос: Работает ли заведение в выходные?
          </p>
          <p class="answer__text">
            Ответ: Да, мы работаем без выходных с 8:00 до 23:00.
          </p>
        </div>
        <div class="question__item reverse">
          <p class="question__text">
            Вопрос: Можно ли забронировать столик?
          </p>
          <p class="answer__text">
            Ответ: Да, вы можете забронировать столик, позвонив по номеру +7 (495) 123-45-67. <br>
            Укажите желаемое время и количество гостей — мы зарезервируем для вас лучшее место!
          </p>
        </div>
        <div class="question__item">
          <p class="question__text">
            Вопрос: Есть ли у вас парковка?
          </p>
          <p class="answer__text">
            Ответ: Рядом с заведением нет собственной парковки, но в 5 минутах ходьбы есть бесплатная парковка (ул.
            Петровка, у ТЦ «Петровский»).
          </p>
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
</body>
</html>