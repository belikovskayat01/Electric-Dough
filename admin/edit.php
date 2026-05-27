<?php
include '../db.php';
include 'translations.php';

if (!isset($_GET['table'], $_GET['id'], $_GET['key'])) {
    die("Недостаточно данных.");
}

$table = $conn->real_escape_string($_GET['table']);
$key = $_GET['key'];
$id = $_GET['id'];

$key_esc = $conn->real_escape_string($key);
$id_esc = $conn->real_escape_string($id);
$where_value = is_numeric($id) ? $id_esc : "'$id_esc'";

$sql = "SELECT * FROM `$table` WHERE `$key_esc` = $where_value";
$result = $conn->query($sql);

if (!$result || $result->num_rows === 0) {
    die("Запись не найдена.");
}

$row = $result->fetch_assoc();

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
    foreach ($_POST as $column => $value) {
        if ($column === $key) continue;
        
        if (in_array($column, $excluded_fields)) {
            continue;
        }
        
        $col_esc = $conn->real_escape_string($column);
        
        if ($column === 'is_active') {
            $val_esc = $conn->real_escape_string($value);
            $fields[] = "`$col_esc` = '$val_esc'";
        }
        elseif ($value === '' && in_array($column, $nullable_fields)) {
            $fields[] = "`$col_esc` = NULL";
        } else {
            $val_esc = $conn->real_escape_string($value);
            $fields[] = "`$col_esc` = '$val_esc'";
        }
    }

    if (!empty($fields)) {
        $sql_update = "UPDATE `$table` SET " . implode(', ', $fields) . " WHERE `$key_esc` = $where_value";

        if ($conn->query($sql_update)) {
            header("Location: index.php?table=$table");
            exit;
        } else {
            $error = "Ошибка: " . $conn->error;
        }
    }
}

$columns_info = [];
$columns_result = $conn->query("SHOW COLUMNS FROM `$table`");
while ($col = $columns_result->fetch_assoc()) {
    $columns_info[$col['Field']] = $col;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Изменить запись в <?= htmlspecialchars($table_names[$table] ?? $table) ?></title>
    <link rel="stylesheet" href="../font.css">
    <link rel="stylesheet" href="../css/variables.css">
    <link rel="stylesheet" href="admin-forms.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.8/jquery.inputmask.min.js"></script>
</head>
<body>
<div class="admin-panel">
    <div class="content">
        <h2>✏️ Изменить запись в «<?= htmlspecialchars($table_names[$table] ?? $table) ?>»</h2>
        
        <?php if (isset($error)): ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="post" id="editForm">
            <?php
            foreach ($row as $column => $value) {
                if ($column === $key || in_array($column, $excluded_fields)) {
                    continue;
                }
                
                $label = $columns_map[$table][$column] ?? $column;
                $field_info = $columns_info[$column] ?? null;
                $field_type = $field_info['Type'] ?? '';
                
                $is_required = ($field_info && $field_info['Null'] === 'NO');
                if (in_array($column, $nullable_fields)) {
                    $is_required = false;
                }
                
                if ($column === 'is_active') {
                    $checked = ($value == 1) ? 'checked' : '';
                    echo '<div class="form-group">';
                    echo '<div class="checkbox-group">';
                    echo "<input type='hidden' name='$column' value='0'>";
                    echo "<input type='checkbox' name='$column' id='$column' value='1' $checked>";
                    echo "<label for='$column'>✅ " . htmlspecialchars($label) . "</label>";
                    echo '</div>';
                    echo '</div>';
                    continue;
                }
                
                echo '<div class="form-group">';
                echo "<label for='$column'>📌 " . htmlspecialchars($label) . ":</label>";
                
                if (isset($enumValues[$table][$column])) {
                    echo "<select name='$column' id='$column'>";
                    echo "<option value=''>-- Выберите --</option>";
                    foreach ($enumValues[$table][$column] as $enumValue => $enumLabel) {
                        $selected = ($value == $enumValue) ? 'selected' : '';
                        echo "<option value='$enumValue' $selected>$enumLabel</option>";
                    }
                    echo "</select>";
                }
                elseif (isset($foreignKeys[$column])) {
                    $fk = $foreignKeys[$column];
                    $options = $conn->query("SELECT `{$fk['id_field']}`, `{$fk['name_field']}` FROM `{$fk['table']}` ORDER BY `{$fk['name_field']}`");
                    
                    echo "<select name='$column' id='$column'>";
                    echo "<option value=''>-- Выберите --</option>";
                    if (!$is_required) {
                        $selected = ($value === null || $value === '') ? 'selected' : '';
                        echo "<option value='' $selected>-- Оставить пустым --</option>";
                    }
                    if ($options && $options->num_rows > 0) {
                        while ($option = $options->fetch_assoc()) {
                            $selected = ($option[$fk['id_field']] == $value) ? 'selected' : '';
                            echo "<option value='" . htmlspecialchars($option[$fk['id_field']]) . "' $selected>" .
                                 htmlspecialchars($option[$fk['name_field']]) .
                                 "</option>";
                        }
                    }
                    echo "</select>";
                }
                elseif (strpos($field_type, 'text') !== false) {
                    echo "<textarea name='$column' id='$column' rows='5'>" . htmlspecialchars($value ?? '') . "</textarea>";
                }
                elseif (strpos($field_type, 'int') !== false && strpos($field_type, 'decimal') === false) {
                    echo "<input type='number' name='$column' id='$column' value='" . htmlspecialchars($value ?? '') . "'>";
                }
                elseif (strpos($field_type, 'decimal') !== false) {
                    echo "<input type='number' step='0.01' name='$column' id='$column' value='" . htmlspecialchars($value ?? '') . "'>";
                }
                elseif (strpos($field_type, 'datetime') !== false) {
                    $date_value = !empty($value) ? date('Y-m-d\TH:i', strtotime($value)) : '';
                    echo "<input type='datetime-local' name='$column' id='$column' value='$date_value'>";
                }
                elseif (strpos($field_type, 'date') !== false) {
                    $date_value = !empty($value) ? date('Y-m-d', strtotime($value)) : '';
                    echo "<input type='date' name='$column' id='$column' value='$date_value'>";
                }
                elseif (strpos($field_type, 'time') !== false) {
                    echo "<input type='time' name='$column' id='$column' value='" . htmlspecialchars($value ?? '') . "'>";
                }
                elseif ($column === 'Phone') {
                    echo "<input type='text' 
                                 name='$column' 
                                 id='$column' 
                                 class='phone-mask'
                                 value='" . htmlspecialchars($value ?? '') . "'
                                 placeholder='+7 (___) ___-__-__'>";
                    echo "<div class='phone-hint'>📞 Формат: +7 (999) 999-99-99</div>";
                }
                else {
                    echo "<input type='text' name='$column' id='$column' value='" . htmlspecialchars($value ?? '') . "'>";
                }
                
                echo '</div>';
            }
            ?>
            <hr>
            <button class="btn" type="submit">💾 Сохранить изменения</button>
            <div style="text-align: center; margin-top: 15px;">
                <a href="index.php?table=<?= urlencode($table) ?>" class="back-link">← Назад к списку</a>
            </div>
        </form>
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
    
    $('#editForm').on('submit', function() {
        $('.phone-mask').each(function() {
            let phone = $(this).val();
            phone = phone.replace(/\D/g, '');
            $(this).val(phone);
        });
    });
});
</script>
</body>
</html>