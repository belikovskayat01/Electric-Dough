<?php
session_start();
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['viewed_products'])) {
    $viewed_products = $data['viewed_products'];
    
    if (isset($_SESSION['user_id'])) {
        $_SESSION['viewed_products'] = $viewed_products;
    }
    
    setcookie('viewed_products', $viewed_products, time() + (86400 * 30), '/');
    
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'No data']);
}
?>