<?php 
include '../db.php';
include 'translations.php';

function translateEnumValue($table, $column, $value) {
    global $enum_values_map;

    if ($value === null || $value === '') {
        return '—'; 
    }
    
    if (isset($enum_values_map[$table][$column][$value])) {
        return $enum_values_map[$table][$column][$value];
    }
    
    return $value;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Админ-панель BookStation</title>
    <link rel="stylesheet" href="../font.css">
    <link rel="stylesheet" href="../css/variables.css">
    <link rel="stylesheet" href="admin.css">
</head>
<body>
<div class="admin-panel">
    <div class="sidebar">
        <div class="logo">
            <a href="../index.php">
                <img src="../IMG/logo.png" alt="Логотип Electric Dough" style="cursor: pointer;">
            </a>
            <span class="company-name">Рок-пекарня Electric Dough</span>
        </div>
        <ul>
            <li class="group-title">Таблицы</li>
                <?php
                $current_table = $_GET['table'] ?? '';

                $tables_result = $conn->query("
                    SELECT TABLE_NAME 
                    FROM INFORMATION_SCHEMA.TABLES 
                    WHERE TABLE_TYPE = 'BASE TABLE' AND TABLE_SCHEMA = DATABASE()
                ");

                while ($row = $tables_result->fetch_row()) {
                    $table_name = $row[0];
                    $label = $table_names[$table_name] ?? ucfirst($table_name);
                    $active_class = ($table_name === $current_table) ? 'active' : '';
                    echo "<li><a class='$active_class' href='?table=$table_name'>$label</a></li>";
                }
                ?>
            </ul>
        </div>
        <div class="content">
            <div class="toolbar">
                <?php if ($current_table): ?>
                    <a href="add.php?table=<?= htmlspecialchars($current_table) ?>">
                        <button class="btn">Добавить</button>
                    </a>
                    <button id="editBtn" class="btn" disabled>Изменить</button>
                    <button id="deleteBtn" class="btn" disabled>Удалить</button>
                <?php endif; ?>
            </div>
            <div class="data-area<?= $current_table === 'products' ? ' products-table' : '' ?>">
    <div class="table-wrapper">
        <?php
        if ($current_table) {
            $table = $conn->real_escape_string($current_table);
            $query = "SELECT * FROM `$table`";
            $result = $conn->query($query);

            if ($result && $result->num_rows > 0) {
                echo "<table id='dataTable'>";
                echo "<thead><tr>";

                $columnNames = [];
                while ($field = $result->fetch_field()) {
                    $col_name = $field->name;
                    $translated = $columns_map[$table][$col_name] ?? $col_name;
                    $columnNames[] = $col_name;

                    if ($col_name === 'CoverImagePath') {
                        echo "<th style='width:80px;'>" . htmlspecialchars($translated) . "</th>";
                    } else {
                        if ($col_name === $columnNames[0]) {
                            echo "<th data-key='{$col_name}'>" . htmlspecialchars($translated) . "</th>";
                        } else {
                            echo "<th>" . htmlspecialchars($translated) . "</th>";
                        }
                    }
                }

                echo "</tr></thead><tbody>";

                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    foreach ($columnNames as $col_name) {
                        $value = $row[$col_name];
                        if ($col_name === 'CoverImagePath') {
                            $imagePath = $value ?? '';
                            echo "<td style='width:80px;'><img src='" . htmlspecialchars($imagePath) . "' alt='Обложка' style='width:60px; height:auto; object-fit:contain; display:block; margin:0 auto;'></td>";
                        } else {
                            $displayValue = translateEnumValue($current_table, $col_name, $value);
                            echo "<td>" . htmlspecialchars($displayValue) . "</td>";
                        }
                    }
                    echo "</tr>";
                }

                echo "</tbody></table>";
            } else {
                echo "<p>Таблица пуста или не существует.</p>";
            }
        } else {
            echo "<p>Выберите таблицу для отображения данных</p>";
        }
        ?>
    </div>
</div>
</div>

<script>
    const editBtn = document.getElementById('editBtn');
    const deleteBtn = document.getElementById('deleteBtn');
    const table = document.getElementById('dataTable');
    let selectedRow = null;
    let selectedId = null;
    let selectedKey = null;

    const currentTable = <?= json_encode($current_table); ?>;

    if (table) {
        table.querySelectorAll('tbody tr').forEach(row => {
            row.addEventListener('click', () => {
                if (selectedRow) {
                    selectedRow.classList.remove('selected');
                }
                selectedRow = row;
                row.classList.add('selected');

                const cells = row.querySelectorAll('td');
                if (cells.length === 0) return;

                selectedId = cells[0].textContent.trim();

                const firstTh = table.querySelector('thead th[data-key]');
                selectedKey = firstTh ? firstTh.getAttribute('data-key') : null;

                editBtn.disabled = false;
                deleteBtn.disabled = false;
            });
        });
    }

    editBtn.onclick = () => {
        if (!selectedId || !selectedKey) return;
        window.location.href = `edit.php?table=${encodeURIComponent(currentTable)}&id=${encodeURIComponent(selectedId)}&key=${encodeURIComponent(selectedKey)}`;
    };

    deleteBtn.onclick = () => {
        if (!selectedId || !selectedKey) return;
        if (confirm('Удалить запись?')) {
            window.location.href = `delete.php?table=${encodeURIComponent(currentTable)}&id=${encodeURIComponent(selectedId)}&key=${encodeURIComponent(selectedKey)}`;
        }
    };
</script>
</body>
</html>