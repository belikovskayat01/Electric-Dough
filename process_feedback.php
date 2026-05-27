<?php
session_start();
require_once 'includes/security.php';

rateLimit(20, 30);  

header('Content-Type: application/json');

require_once 'db.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    if (empty($name)) {
        $response['message'] = 'Введите ваше имя';
        echo json_encode($response);
        exit;
    }
    
    if (empty($phone)) {
        $response['message'] = 'Введите телефон';
        echo json_encode($response);
        exit;
    }
    
    $phone_clean = preg_replace('/\D/', '', $phone);
    if (strlen($phone_clean) < 10) {
        $response['message'] = 'Введите корректный номер телефона';
        echo json_encode($response);
        exit;
    }
    
    if (empty($message)) {
        $response['message'] = 'Введите ваше сообщение';
        echo json_encode($response);
        exit;
    }
    
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Введите корректный email';
        echo json_encode($response);
        exit;
    }
    
    $stmt = $conn->prepare("INSERT INTO feedback (name, phone, email, message) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $phone, $email, $message);
    
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Спасибо! Мы свяжемся с вами в ближайшее время.';
    } else {
        $response['message'] = 'Ошибка при отправке. Попробуйте позже.';
    }
    
    $stmt->close();
} else {
    $response['message'] = 'Неверный метод запроса';
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
$conn->close();
?>