<?php
session_start();
header('Content-Type: application/json');

if (isset($_SESSION['viewed_products'])) {
    unset($_SESSION['viewed_products']);
}

setcookie('viewed_products', '', time() - 3600, '/');

echo json_encode(['success' => true]);
?>