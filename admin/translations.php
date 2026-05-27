<?php
$table_names = [
    'booking' => 'Бронирования',
    'pre_order_items' => 'Пункты предзаказов',
    'pre_orders' => 'Предзаказы',
    'products' => 'Товары',
    'users' => 'Пользователи',
    'limited_vinyl' => 'Лимитированный винил',
    'rock_quotes' => 'Цитаты рок-легенд',
    'restaurant_tables' => 'Столы' ,
    'email_verifications' => 'Подтверждения почты',
    'feedback' => 'Обратная связь'
];

$columns_map = [
    'booking' => [
        'ID_booking' => 'ID Бронирования',
        'ID_user' => 'ID Пользователя',
        'Name' => 'Имя бронирования',
        'Phone' => 'Телефон',
        'Booking_date' => 'Дата бронирования',
        'Booking_time' => 'Время бронирования',
        'Guests' => 'Количество гостей',
        'Comment' => 'Особые пожелания',
        'Status' => 'Статус'
    ],
    'pre_order_items' => [
        'ID_item' => 'ID Пункта',
        'ID_pre_order' => 'ID_Предзаказа',
        'Product_name' => 'Название продукта',
        'Quantity' => 'Количество',
        'Price' => 'Стоимость'
    ],
   'pre_orders' => [
        'ID_pre_order' => 'ID Предзаказа',
        'ID_user' => 'ID Пользователя',
        'Order_number' => 'Номер заказа',
        'Status' => 'Статус',
        'Delivery_method' => 'Метод доставки',
        'Pickup_date' => 'Дата получения',
        'Pickup_time' => 'Время получения',
        'Total_amount' => 'Общая стоимость',
        'Created_at' => 'Создана'
   ],
   'products' => [
        'ID_product' => 'ID Товара',
        'Name' => 'Название',
        'Description' => 'Описание',
        'Full_description' => 'Расширенное описание',
        'Price' => 'Стоимость',
        'Category' => 'Категория',
        'Image' => 'Путь к изображению',
        'Ingredients' => 'Ингредиенты',
        'Weight' => 'Вес',
        'Status' => 'Статус',
        'Created_at' => 'Создана'
   ],
   'users'=> [
        'ID_User' => 'ID Пользователя',
        'Name' => 'Имя',
        'Surname' => 'Фамилия',
        'Email' => 'Электронная почта',
        'Phone' => 'Телефон',
        'Password' => 'Пароль',
        'Role' => 'Роль'
    ],
    'limited_vinyl' => [
        'id' => 'ID',
        'title' => 'Название',
        'description' => 'Описание',
        'price' => 'Цена',
        'image' => 'Изображение',
        'start_date' => 'Дата начала',
        'end_date' => 'Дата окончания',
        'is_active' => 'Активна',
        'sort_order' => 'Порядок сортировки',
        'created_at' => 'Дата создания',
        'updated_at' => 'Дата обновления'
    ],
    'rock_quotes' => [
        'id' => 'ID',
        'quote' => 'Цитата',
        'author' => 'Автор',
        'band' => 'Группа',
        'is_active' => 'Активна',
        'created_at' => 'Дата создания'
    ],
    'restaurant_tables' => [
        'ID_table' => 'ID',
        'table_number' => 'Номер стола',
        'seats' => 'Количество мест'
    ],
    'email_verifications' => [
        'id' => 'ID',
        'email' => 'Электронная почта',
        'token' => 'Токен',
        'expires_at' => 'Срок действия',
        'created_at' => 'Дата создания'
    ],
    'feedback' => [
        'id' => 'ID',
        'name' => 'Иия',
        'phone' => 'Телефон',
        'email' => 'Электронная почта',
        'message' => 'Сообщение',
        'created_at' => 'Дата создания',
        'is_read' => 'Статус прочтения'
    ]

];

$enum_values_map = [
    'users' => [
        'Role' => [
            'admin' => 'Администратор',
            'reader' => 'Читатель',
            'user' => 'Пользователь'
        ]
    ],
    'booking' => [
        'Status' => [
            'created' => 'Создано',
            'confirmed' => 'Подтверждена',
            'cancelled' => 'Отменена'
        ]
    ],
    'pre_orders' => [
        'Status' => [
            'new' => 'Новый',
            'confirmed' => 'Подтвержден',
            'completed' => 'Выполнен',
            'cancelled' => 'Отменен'
        ],
        'Delivery_method' => [
            'pickup' => 'Самовывоз',
            'delivery' => 'Доставка'
        ]
    ],
    'products' => [
        'Status' => [
            'available' => 'Доступен',
            'unavailable' => 'Недоступен'
        ]
    ],
    'rock_quotes' => [
        'is_active' => [
            1 => 'Активна',
            0 => 'Неактивна'
        ]
    ],
    'feedback' => [
        'is_read' => [
            1 => 'Сообщение прочитано',
            0 => 'Сообщение не прочитано'
        ]
    ]
];
?>