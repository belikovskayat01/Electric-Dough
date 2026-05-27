<?php
include '../db.php';
include 'translations.php';

if (!isset($_GET['table'])) {
    die('Таблица не указана');
}

$table = $conn->real_escape_string($_GET['table']);

if (!isset($columns_map[$table])) {
    die('Таблица не найдена в списке разрешенных');
}

$columns = [];
$result = $conn->query("SHOW COLUMNS FROM `$table`");
while ($row = $result->fetch_assoc()) {
    if (strpos($row['Extra'], 'auto_increment') === false) {
        $columns[] = $row;
    }
}
$foreignKeys = [
    'ID_user' => ['table' => 'users', 'id_field' => 'ID_User', 'name_field' => 'Name'],
    'ID_product' => ['table' => 'products', 'id_field' => 'ID_product', 'name_field' => 'Name'],
    'ID_pre_order' => ['table' => 'pre_orders', 'id_field' => 'ID_pre_order', 'name_field' => 'Order_number'],
];

$enumValues = [
    'users' => [
        'Role' => ['admin' => 'Администратор', 'reader' => 'Читатель', 'user' => 'Пользователь']
    ],
    'booking' => [
        'Status' => ['created' => 'Создано', 'confirmed' => 'Подтверждена', 'cancelled' => 'Отменена']
    ],
    'pre_orders' => [
        'Status' => ['new' => 'Новый', 'confirmed' => 'Подтвержден', 'completed' => 'Выполнен', 'cancelled' => 'Отменен'],
        'Delivery_method' => ['pickup' => 'Самовывоз', 'delivery' => 'Доставка']
    ],
    'products' => [
        'Status' => ['available' => 'Доступен', 'unavailable' => 'Недоступен']
    ],
    'limited_vinyl' => [
        'is_active' => [1 => 'Активна', 0 => 'Неактивна']
    ],
    'rock_quotes' => [
        'is_active' => [1 => 'Активна', 0 => 'Неактивна']
    ]
];

$nullable_fields = ['ID_user', 'Comment', 'Ingredients', 'Full_description', 'Image', 'description'];

$excluded_fields = ['Created_at', 'created_at', 'updated_at'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = [];
    $values = [];

    foreach ($columns as $col) {
        $field = $col['Field'];
        
        if (in_array($field, $excluded_fields)) {
            continue;
        }
        if (isset($_POST[$field]) && $_POST[$field] !== '') {
            $fields[] = "`$field`";
            $value = $conn->real_escape_string($_POST[$field]);
            $values[] = "'$value'";
        } else {
            if (in_array($field, $nullable_fields)) {
                $fields[] = "`$field`";
                $values[] = "NULL";
            }
        }
    }

    if (!empty($fields)) {
        $fields_str = implode(", ", $fields);
        $values_str = implode(", ", $values);

        $sql = "INSERT INTO `$table` ($fields_str) VALUES ($values_str)";
        
        if ($conn->query($sql)) {
            header("Location: index.php?table=$table");
            exit;
        } else {
            echo "Ошибка при добавлении записи: " . $conn->error;
        }
    } else {
        echo "Нет данных для добавления.";
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Добавить запись в <?= htmlspecialchars($table_names[$table] ?? $table) ?></title>
    <link rel="stylesheet" href="font.css">
    <link rel="stylesheet" href="admin-forms.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.8/jquery.inputmask.min.js"></script>
</head>
<body>
<div class="admin-panel">
    <div class="content" style="margin: 20px;">
        <h2>Добавить запись в таблицу «<?= $table_names[$table] ?? $table ?>»</h2>
        <form method="post" id="addForm">
            <?php foreach ($columns as $col): 
                $field = $col['Field'];
                $label = $columns_map[$table][$field] ?? $field;
                $type = $col['Type'];
            
                if (in_array($field, $excluded_fields)) {
                    continue;
                }
                
                if ($table === 'rock_quotes' && $field === 'created_at') {
                    continue;
                }
                
                $is_required = ($col['Null'] === 'NO');
                if (in_array($field, $nullable_fields)) {
                    $is_required = false;
                }
                if ($table === 'rock_quotes' && $field === 'is_active') {
                    $is_required = false;
                }
            ?>
                <div style="margin-bottom: 15px;">
                    <label for="<?= $field ?>"><?= htmlspecialchars($label) ?>:</label><br>
                    
                    <?php 
                    if (isset($enumValues[$table][$field])): 
                    ?>
                        <select name="<?= $field ?>" id="<?= $field ?>" <?= $is_required ? 'required' : '' ?>>
                            <option value="">-- Выберите --</option>
                            <?php foreach ($enumValues[$table][$field] as $value => $display): ?>
                                <option value="<?= $value ?>"><?= $display ?></option>
                            <?php endforeach; ?>
                        </select>
                    
                    <?php 
                    elseif (isset($foreignKeys[$field])): 
                        $fk = $foreignKeys[$field];
                        $options = $conn->query("SELECT `{$fk['id_field']}`, `{$fk['name_field']}` FROM `{$fk['table']}` ORDER BY `{$fk['name_field']}`");
                    ?>
                        <select name="<?= $field ?>" id="<?= $field ?>" <?= $is_required ? 'required' : '' ?>>
                            <option value="">-- Выберите --</option>
                            <?php if (!$is_required): ?>
                                <option value="">-- Оставить пустым --</option>
                            <?php endif; ?>
                            <?php while ($opt = $options->fetch_assoc()): ?>
                                <option value="<?= htmlspecialchars($opt[$fk['id_field']]) ?>">
                                    <?= htmlspecialchars($opt[$fk['name_field']]) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    <?php 
                    elseif (strpos($type, 'text') !== false): 
                    ?>
                        <textarea name="<?= $field ?>" id="<?= $field ?>" rows="4" <?= $is_required ? 'required' : '' ?> placeholder="Введите текст..."></textarea>
                    
                    <?php 
                    elseif (strpos($type, 'int') !== false && strpos($type, 'tinyint') === false): 
                    ?>
                        <input type="number" name="<?= $field ?>" id="<?= $field ?>" <?= $is_required ? 'required' : '' ?>>
                    <?php 
                    elseif (strpos($type, 'tinyint') !== false): 
                    ?>
                        <select name="<?= $field ?>" id="<?= $field ?>" <?= $is_required ? 'required' : '' ?>>
                            <option value="1">Да (активна)</option>
                            <option value="0">Нет (неактивна)</option>
                        </select>
                    
                    <?php 
                    elseif (strpos($type, 'date') !== false): 
                    ?>
                        <input type="date" name="<?= $field ?>" id="<?= $field ?>" <?= $is_required ? 'required' : '' ?>>
                    
                    <?php elseif (strpos($type, 'time') !== false): ?>
                        <input type="time" name="<?= $field ?>" id="<?= $field ?>" <?= $is_required ? 'required' : '' ?>>
                    
                    <?php 
                    elseif ($field === 'Phone'): 
                    ?>
                        <input type="text" 
                               name="<?= $field ?>" 
                               id="<?= $field ?>" 
                               class="phone-mask"
                               <?= $is_required ? 'required' : '' ?>
                               placeholder="+7 (___) ___-__-__">
                        <div class="phone-hint">Формат: +7 (999) 999-99-99</div>
                    
                    <?php 
                    else: 
                    ?>
                        <input type="text" name="<?= $field ?>" id="<?= $field ?>" <?= $is_required ? 'required' : '' ?>>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            <button class="btn" type="submit">Добавить</button>
        </form>
        <br>
        <a href="index.php?table=<?= htmlspecialchars($table) ?>">Назад</a>
    </div>
</div>

<script>
$(document).ready(function() {
    $('.phone-mask').inputmask('+7 (999) 999-99-99', {
        showMaskOnHover: false,
        showMaskOnFocus: true,
        clearIncomplete: true,
        placeholder: "_"
    });
});
</script>
</body>
</html>