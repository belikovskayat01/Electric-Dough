-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1:3306
-- Время создания: Фев 20 2026 г., 22:54
-- Версия сервера: 8.0.30
-- Версия PHP: 7.4.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `rock-bakery`
--

-- --------------------------------------------------------

--
-- Структура таблицы `booking`
--

CREATE TABLE `booking` (
  `ID_booking` int NOT NULL,
  `ID_user` int DEFAULT NULL,
  `Name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `Phone` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Booking_date` date NOT NULL,
  `Booking_time` time NOT NULL,
  `Guests` int NOT NULL,
  `Comment` text COLLATE utf8mb4_general_ci NOT NULL,
  `Status` enum('created','confirmed','cancelled') COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `booking`
--

INSERT INTO `booking` (`ID_booking`, `ID_user`, `Name`, `Phone`, `Booking_date`, `Booking_time`, `Guests`, `Comment`, `Status`) VALUES
(8, NULL, 'Татьяна', '+7 (906) 778-54-74', '2026-02-28', '14:30:00', 7, 'нет', 'created'),
(9, NULL, 'Татьяна', '+7 (906) 796-81-71', '2026-03-16', '14:30:00', 5, 'нет', 'created'),
(10, NULL, 'Татьяна', '+7 (906) 796-81-71', '2026-03-10', '14:40:00', 4, '', 'created'),
(11, NULL, 'Татьяна', '+7 (905) 575-58-84', '2026-02-28', '14:40:00', 3, '', 'created'),
(12, 2, 'Анна-Мария Рейнсберг', '+7 (906) 787-95-54', '2026-03-14', '16:00:00', 4, '', 'confirmed'),
(13, 3, 'Виктор Цой', '+7 (900) 686-57-74', '2026-03-01', '14:40:00', 6, '', 'cancelled'),
(14, 2, 'Анна-Мария Рейнсберг', '+7 (905) 536-57-78', '2026-02-28', '17:20:00', 6, 'Непереносимость лактозы', 'cancelled'),
(15, 2, 'Анна-Мария Рейнсберг', '+7 (968) 554-71-23', '2026-03-01', '17:00:00', 9, '', 'cancelled'),
(16, NULL, 'Антон', '+7 (968) 789-94-75', '2026-03-12', '18:25:00', 2, '', 'created'),
(17, NULL, 'Борис', '+7 (905) 774-85-77', '2026-03-01', '14:20:00', 3, '', 'created'),
(18, NULL, 'Василий', '+7 (963) 774-85-95', '2026-02-24', '17:00:00', 1, '', 'created'),
(19, 1, 'Татьяна Беликова', '+7 (977) 877-37-29', '2026-03-14', '18:30:00', 6, '', 'cancelled'),
(20, 1, 'Татьяна Беликова', '+7 (905) 575-32-26', '2026-03-01', '14:00:00', 4, '', 'cancelled'),
(21, 1, 'Татьяна Беликова', '+7 (906) 557-48-84', '2026-03-17', '20:00:00', 6, 'Хотелось бы столик у окна', 'cancelled'),
(22, 2, 'Анна-Мария Рейнсберг', '+7 (906) 588-74-45', '2026-02-22', '14:30:00', 7, '', 'cancelled'),
(23, 2, 'Анна-Мария Рейнсберг', '+7 (905) 774-85-54', '2026-02-20', '12:35:00', 5, '', 'confirmed'),
(24, 3, 'Виктор Цой', '+7 (905) 744-85-54', '2026-02-20', '13:00:00', 9, '', 'cancelled'),
(25, 2, 'Анна-Мария Рейнсберг', '+7 (987) 748-54-47', '2026-02-25', '11:30:00', 9, 'Хотелось бы столик у окна', 'created'),
(26, 3, 'Виктор Цой', '+7 (906) 774-85-54', '2026-02-20', '13:15:00', 2, '', 'confirmed');

-- --------------------------------------------------------

--
-- Структура таблицы `pre_orders`
--

CREATE TABLE `pre_orders` (
  `ID_pre_order` int NOT NULL,
  `ID_user` int NOT NULL,
  `Order_number` varchar(50) NOT NULL,
  `Status` enum('new','confirmed','completed','cancelled') DEFAULT 'new',
  `Delivery_method` enum('pickup','delivery') DEFAULT 'pickup',
  `Pickup_date` date DEFAULT NULL,
  `Pickup_time` time DEFAULT NULL,
  `Total_amount` decimal(10,2) NOT NULL,
  `Created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `pre_orders`
--

INSERT INTO `pre_orders` (`ID_pre_order`, `ID_user`, `Order_number`, `Status`, `Delivery_method`, `Pickup_date`, `Pickup_time`, `Total_amount`, `Created_at`) VALUES
(1, 1, 'PO-20240220-001', 'new', 'pickup', '2024-02-25', '14:00:00', '750.00', '2026-02-20 15:52:57'),
(2, 1, 'PO-20240219-002', 'confirmed', 'pickup', '2024-02-26', '16:30:00', '520.00', '2026-02-20 15:52:57'),
(3, 1, 'PO-20240218-003', 'completed', 'pickup', '2024-02-20', '12:00:00', '980.00', '2026-02-20 15:52:57'),
(4, 1, 'PO-20260220-4584', 'new', 'pickup', '2026-03-17', '14:30:00', '980.00', '2026-02-20 16:07:24');

-- --------------------------------------------------------

--
-- Структура таблицы `pre_order_items`
--

CREATE TABLE `pre_order_items` (
  `ID_item` int NOT NULL,
  `ID_pre_order` int NOT NULL,
  `Product_name` varchar(255) NOT NULL,
  `Quantity` int NOT NULL,
  `Price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `pre_order_items`
--

INSERT INTO `pre_order_items` (`ID_item`, `ID_pre_order`, `Product_name`, `Quantity`, `Price`) VALUES
(1, 1, 'Лимонад Беспечный ангел', 2, '250.00'),
(2, 1, 'Пирог Bohemian Rhapsody', 1, '250.00'),
(3, 2, 'Чизкейк Black Sabbath', 1, '520.00'),
(4, 3, 'Эклеры AC/DC\'s High Voltage', 3, '180.00'),
(5, 3, 'Латте Wind of Change', 2, '220.00'),
(6, 4, 'Гриль-чиз In the End', 1, '450.00'),
(7, 4, 'Лимонад Беспечный ангел', 1, '250.00'),
(8, 4, 'Мохито Engel', 1, '280.00');

-- --------------------------------------------------------

--
-- Структура таблицы `products`
--

CREATE TABLE `products` (
  `ID_product` int NOT NULL,
  `Name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `Description` text COLLATE utf8mb4_general_ci,
  `Full_description` text COLLATE utf8mb4_general_ci,
  `Price` decimal(10,2) NOT NULL,
  `Category` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `Image` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Ingredients` text COLLATE utf8mb4_general_ci,
  `Weight` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Status` enum('available','unavailable') COLLATE utf8mb4_general_ci DEFAULT 'available',
  `Created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `products`
--

INSERT INTO `products` (`ID_product`, `Name`, `Description`, `Full_description`, `Price`, `Category`, `Image`, `Ingredients`, `Weight`, `Status`, `Created_at`) VALUES
(1, 'Пирог Bohemian Rhapsody', 'Классический яблочный пирог с карамелью, украшенный свежим безе и ягодами.', 'Наш фирменный пирог, вдохновленный легендарной песней. Нежное слоеное тесто, карамелизированные яблоки, воздушное безе и свежие ягоды создают неповторимую симфонию вкуса.', '420.00', 'Десерты', 'bohemianrhapsody.jpeg', 'Яблоки, карамель, безе, ягоды, слоеное тесто', '180г', 'available', '2026-02-20 11:26:43'),
(2, 'Эклеры AC/DC\'s High Voltage', 'Заварные эклеры в ассортименте с неоновой глазурью снаружи и приятной ягодной начинкой', 'Яркие, как сцена AC/DC. Нежные заварные эклеры с ягодной начинкой и неоновой глазурью, которая светится под ультрафиолетом. Идеальны для рок-вечеринки!', '180.00', 'Десерты', 'AC_DC.png', 'Заварное тесто, ягодная начинка, глазурь', '150г', 'available', '2026-02-20 11:26:43'),
(3, 'Чизкейк Black Sabbath', 'Шоколадный чизкейк с вишнёвым соусом и свежими вишенками', 'Тяжелый, как металл, и нежный, как бархат. Плотная шоколадная основа, кремовая сырная начинка с какао, вишневый соус с кислинкой и свежие ягоды.', '200.00', 'Десерты', 'cheesecake_bsb.jpeg', 'Сливочный сыр, шоколад, вишня, яйца, сахар', '220г', 'available', '2026-02-20 11:26:43'),
(4, 'Кекс Nirvana\'s Heart', 'Влажный шоколадный кекс в форме сердца с жидкой малиновой «сердцевиной».', 'Подарите кусочек Nirvana своим близким. Нежный шоколадный кекс с жидкой малиновой начинкой внутри. Подается с малиновым кули и свежими ягодами.', '380.00', 'Десерты', 'nirvana_heart.jpeg', 'Шоколад, малина, мука, яйца, масло', '150г', 'available', '2026-02-20 11:26:43'),
(5, 'Тарт Nothing Else Matters', 'Шоколадный тарт с соленой карамелью и золотой пыльцой. Простота и совершенство.', 'Минимализм, доведенный до совершенства. Хрустящая песочная основа, шелковистый шоколадный ганаш, соленая карамель и финальный акцент — съедобная золотая пыльца.', '600.00', 'Десерты', 'nothing_else.jpeg', 'Шоколад, карамель, соль, мука, масло', '200г', 'available', '2026-02-20 11:26:43'),
(6, 'Профитроли November Rain', 'Заварные шарики, политые белым шоколадом и гранатовым соусом, словно под дождём.', 'Нежные заварные шарики с ванильным кремом, утопающие в белом шоколаде под гранатовым \"дождём\".', '490.00', 'Десерты', 'november-rain.jpeg', 'Заварное тесто, ванильный крем, белый шоколад, гранат', '250г', 'available', '2026-02-20 11:26:43'),
(7, 'Чизкейк Californication', 'Классический чизкейк с соусом из манго-маракуйи и кокосовой стружкой.', 'Солнечный, яркий, дерзкий — как сам Калифорнийский рок. Классическая сырная основа, тропический соус манго-маракуйя и кокосовая стружка.', '550.00', 'Десерты', 'californication.jpeg', 'Сливочный сыр, манго, маракуйя, кокос', '230г', 'available', '2026-02-20 11:26:43'),
(8, 'Панна-котта Enjoy the Silence', 'Идеально белая, ванильная панна-котта с хрустящей карамельной «тишиной» сверху.', 'Идеально белая, нежная, молчаливая... Пока не разобьешь карамельную корочку. Контраст текстур и вкусов.', '420.00', 'Десерты', 'panna-cotta.jpeg', 'Сливки, ваниль, карамель, желатин', '180г', 'available', '2026-02-20 11:26:43'),
(9, 'Пирог Killer Queen', 'Изысканный персиковый тарт с розмарином, подается с шариком розового мороженого.', 'Королевский десерт для настоящей королевы. Нежный персиковый тарт с ароматом розмарина, в компании с розовым мороженым.', '650.00', 'Десерты', 'killer-queen.jpeg', 'Персик, розмарин, мороженое, миндаль', '210г', 'available', '2026-02-20 11:26:43'),
(10, 'Сэндвич Teen Spirit', 'Горячий панини с курицей-гриль, моцареллой, шампиньонами и дымным соусом.', 'Энергия молодости в каждом укусе! Хрустящий панини, сочная курица-гриль, нежная моцарелла, шампиньоны и наш фирменный дымный соус.', '380.00', 'Выпечка', 'teen-spirit.jpeg', 'Панини, курица, моцарелла, шампиньоны, дымный соус', '280г', 'available', '2026-02-20 11:26:43'),
(11, 'Сэндвич We are Champions', 'Трёхэтажный классический клаб-сэндвич с индейкой, беконом, салатом и тройным соусом.', 'Чемпионский сэндвич для чемпионов! Три слоя тостов с индейкой, хрустящим беконом, свежим салатом и тройным соусом.', '520.00', 'Выпечка', 'we-are-champions.jpeg', 'Тост, индейка, бекон, салат, соусы', '350г', 'available', '2026-02-20 11:26:43'),
(12, 'Гриль-чиз In the End', 'Сырный сендвич с тремя видами сыра и трюфельным маслом на закваске.', 'В конце концов, главное — это сыр. Три вида расплавленного сыра на хрустящем хлебе, обжаренном на трюфельном масле.', '450.00', 'Выпечка', 'the-end.jpeg', 'Чеддер, моцарелла, пармезан, трюфельное масло', '240г', 'available', '2026-02-20 11:26:43'),
(13, 'Киш Yellow Submarine', 'Открытый пирог с яйцом, сыром и ярким миксом из кукурузы, сладкого перца и цукини.', 'Желтая подводная лодка спешит к вам с овощным миксом! Нежная заливка из яиц и сыра скрывает под собой яркие овощи.', '390.00', 'Выпечка', 'submarine.jpeg', 'Яйца, сыр, кукуруза, перец, цукини', '260г', 'available', '2026-02-20 11:26:43'),
(14, 'Пирог We will Rock YOU', 'Классический английский «стеклянный» пирог с хрустящим слоеным тестом.', 'Традиционный английский пирог, который точно вас раскачает! Сочная мясная начинка под хрустящей слоеной «крышкой».', '580.00', 'Выпечка', 'we-will-rock-you.jpeg', 'Говядина, слоеное тесто, овощи, специи', '320г', 'available', '2026-02-20 11:26:43'),
(15, 'Пиццетта Back IN Black', 'Маленькая пицца на тонком тесте с чернилами каракатицы, морепродуктами и страчателлой.', 'Загадочная черная пицца, вдохновленная альбомом AC/DC. Тонкое тесто с чернилами каракатицы, морепродукты, нежная страчателла.', '690.00', 'Выпечка', 'back-IN-black.jpeg', 'Тесто, чернила каракатицы, морепродукты, страчателла', '280г', 'available', '2026-02-20 11:26:43'),
(16, 'Латте Wind of Change', 'Нежный латте с сиропом из груши-конференц и щепоткой молотой корицы.', 'Ветер перемен приносит новые вкусы. Нежный латте с грушевым сиропом и корицей. Пейте и меняйтесь к лучшему!', '320.00', 'Напитки', 'wind-of-change.jpeg', 'Эспрессо, молоко, грушевый сироп, корица', '300мл', 'available', '2026-02-20 11:26:43'),
(17, 'Раф Smoke on the water', 'Ванильный раф со сливками. Главная изюминка — несколько капель копчёного сиропа.', 'Дым над водой в вашей чашке. Нежный раф с ароматом ванили и легкой ноткой копчения.', '350.00', 'Напитки', 'smoke-on-the-water.jpeg', 'Эспрессо, сливки, ваниль, копченый сироп', '300мл', 'available', '2026-02-20 11:26:43'),
(18, 'Гляссе The Final Countdown', 'Холодный кофе гляссе на основе двойного эспрессо с карамельным топпингом и взбитыми сливками.', 'Финальный отсчет начинается! Освежающий холодный кофе с карамелью и шапкой взбитых сливок.', '290.00', 'Напитки', 'the-final-countdown.jpeg', 'Эспрессо, карамель, сливки, молоко', '250мл', 'available', '2026-02-20 11:26:43'),
(19, 'Лимонад Беспечный ангел', 'Бруснично-клюквенный морс, разбавленный газированной водой и хвойным сиропом.', 'Легкий, как полет ангела. Освежающий лимонад с кислинкой брусники и клюквы, с необычным хвойным послевкусием.', '250.00', 'Напитки', 'bespechny_angel.jpeg', 'Брусника, клюква, хвойный сироп, газировка', '400мл', 'available', '2026-02-20 11:26:43'),
(20, 'Мохито Engel', 'Классический мохито с добавлением сиропа бузины и серебряной пыльцой.', 'Ангельски чистый вкус классического мохито с цветочными нотками бузины и мерцающей серебряной пыльцой.', '280.00', 'Напитки', 'mojito.jpeg', 'Лайм, мята, бузина, спрайт, серебряная пыльца', '400мл', 'available', '2026-02-20 11:26:43'),
(21, 'Смузи Zombie', 'Ярко-зеленый смузи из киви, шпината, банана и лайма с добавлением энергетика.', 'Напиток, который поднимет даже мертвого! Освежающий зеленый смузи с бодрящим эффектом.', '320.00', 'Напитки', 'zombie.jpeg', 'Киви, шпинат, банан, лайм, энергетик', '350мл', 'available', '2026-02-20 11:26:43');

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `ID_User` int NOT NULL,
  `Name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `Surname` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `Email` varchar(80) COLLATE utf8mb4_general_ci NOT NULL,
  `Phone` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `Password` varchar(40) COLLATE utf8mb4_general_ci NOT NULL,
  `Role` enum('admin','reader') COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`ID_User`, `Name`, `Surname`, `Email`, `Phone`, `Password`, `Role`) VALUES
(1, 'Татьяна', 'Беликова', 'belikovaat@yandex.ru', '+7 (906) 796-81-71', '123456789', ''),
(2, 'Анна-Мария', 'Рейнсберг', 'rain@mail.ru', '+7 (906) 578-94-47', 'rain123', ''),
(3, 'Виктор', 'Цой', 'tsoyvictor@mail.ru', '+7 (800) 555-35-35', 'qwerty123', '');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `booking`
--
ALTER TABLE `booking`
  ADD PRIMARY KEY (`ID_booking`),
  ADD KEY `booking_ibfk_1` (`ID_user`);

--
-- Индексы таблицы `pre_orders`
--
ALTER TABLE `pre_orders`
  ADD PRIMARY KEY (`ID_pre_order`),
  ADD KEY `ID_user` (`ID_user`);

--
-- Индексы таблицы `pre_order_items`
--
ALTER TABLE `pre_order_items`
  ADD PRIMARY KEY (`ID_item`),
  ADD KEY `ID_pre_order` (`ID_pre_order`);

--
-- Индексы таблицы `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`ID_product`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`ID_User`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `booking`
--
ALTER TABLE `booking`
  MODIFY `ID_booking` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT для таблицы `pre_orders`
--
ALTER TABLE `pre_orders`
  MODIFY `ID_pre_order` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `pre_order_items`
--
ALTER TABLE `pre_order_items`
  MODIFY `ID_item` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT для таблицы `products`
--
ALTER TABLE `products`
  MODIFY `ID_product` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `ID_User` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `booking`
--
ALTER TABLE `booking`
  ADD CONSTRAINT `booking_ibfk_1` FOREIGN KEY (`ID_user`) REFERENCES `users` (`ID_User`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `pre_orders`
--
ALTER TABLE `pre_orders`
  ADD CONSTRAINT `pre_orders_ibfk_1` FOREIGN KEY (`ID_user`) REFERENCES `users` (`ID_User`);

--
-- Ограничения внешнего ключа таблицы `pre_order_items`
--
ALTER TABLE `pre_order_items`
  ADD CONSTRAINT `pre_order_items_ibfk_1` FOREIGN KEY (`ID_pre_order`) REFERENCES `pre_orders` (`ID_pre_order`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
