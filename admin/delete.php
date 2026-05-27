<?php
include '../db.php';

if (!isset($_GET['table']) || !isset($_GET['id']) || !isset($_GET['key'])) {
    die("Недостаточно данных для удаления.");
}

$table = $conn->real_escape_string($_GET['table']);
$id = $conn->real_escape_string($_GET['id']);
$key = $conn->real_escape_string($_GET['key']);

$checkTable = $conn->query("SHOW TABLES LIKE '$table'");
if ($checkTable->num_rows === 0) {
    die("Таблица не найдена.");
}

$checkKey = $conn->query("SHOW COLUMNS FROM `$table` LIKE '$key'");
if ($checkKey->num_rows === 0) {
    die("Поле '$key' не найдено в таблице.");
}

$sql = "DELETE FROM `$table` WHERE `$key` = '$id'";

if ($conn->query($sql)) {
    header("Location: index.php?table=$table");
    exit;
} else {
    echo "Ошибка при удалении: " . $conn->error;
}
?>