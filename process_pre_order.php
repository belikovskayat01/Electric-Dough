<?php
session_start();
require_once 'includes/security.php';

rateLimit(20, 30); 


header('Content-Type: application/json');
require_once 'db.php';

$user_id = null;

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} 

$data = json_decode(file_get_contents('php://input'), true);
if (!$user_id && isset($data['user_id'])) {
    $user_id = intval($data['user_id']);
    $_SESSION['user_id'] = $user_id;
}

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'Необходимо авторизоваться']);
    exit;
}

if (empty($data['pickup_date']) || empty($data['pickup_time']) || empty($data['items'])) {
    echo json_encode(['success' => false, 'message' => 'Не все данные заполнены']);
    exit;
}

$pickup_date = $data['pickup_date'];
$pickup_time = $data['pickup_time'];
$current_date = date('Y-m-d');
$current_time = date('H:i:s');

if ($pickup_date < $current_date) {
    echo json_encode(['success' => false, 'message' => 'Дата не может быть в прошлом']);
    exit;
}

if ($pickup_date == $current_date && $pickup_time < $current_time) {
    echo json_encode(['success' => false, 'message' => 'Время не может быть в прошлом']);
    exit;
}

$hours = intval(explode(':', $pickup_time)[0]);
if ($hours < 8 || $hours >= 23) {
    echo json_encode(['success' => false, 'message' => 'Время должно быть с 08:00 до 23:00']);
    exit;
}

$conn->begin_transaction();

try {
    $order_number = 'PO-' . date('Ymd') . '-' . rand(1000, 9999);
    
    $stmt = $conn->prepare("
        INSERT INTO pre_orders (ID_user, Order_number, Pickup_date, Pickup_time, Total_amount, Status) 
        VALUES (?, ?, ?, ?, ?, 'new')
    ");
    $stmt->bind_param("isssd", $user_id, $order_number, $pickup_date, $pickup_time, $data['total_amount']);
    $stmt->execute();
    $order_id = $conn->insert_id;
    
    $item_stmt = $conn->prepare("
        INSERT INTO pre_order_items (ID_pre_order, Product_name, Quantity, Price) 
        VALUES (?, ?, ?, ?)
    ");
    
    foreach ($data['items'] as $item) {
        $item_stmt->bind_param("isid", $order_id, $item['name'], $item['quantity'], $item['price']);
        $item_stmt->execute();
    }
    
    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Предзаказ оформлен']);
    
} catch (Exception $e) {
    $conn->rollback();
    error_log("Error creating pre-order: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Ошибка при оформлении заказа']);
}

$conn->close();
?>