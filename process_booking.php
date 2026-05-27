<?php
session_start();
require_once 'includes/security.php';

rateLimit(20, 30); 

header('Content-Type: application/json');

require_once 'db.php';

$response = ['success' => false, 'message' => 'Неизвестная ошибка'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $name = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $booking_date = $_POST['booking_date'] ?? '';
    $booking_time = $_POST['booking_time'] ?? '';
    $guests = intval($_POST['guests'] ?? 0);
    $comment = $_POST['comment'] ?? '';
    $table_id = !empty($_POST['selected_table']) ? intval($_POST['selected_table']) : null;
    
    if (strlen($booking_time) == 5) {
        $booking_time = $booking_time . ':00';
    }
    
    if (empty($name)) {
        $response['message'] = 'Введите имя';
        echo json_encode($response);
        exit;
    }
    
    if (empty($phone)) {
        $response['message'] = 'Введите телефон';
        echo json_encode($response);
        exit;
    }
    
    if ($table_id) {
        $sql = "INSERT INTO booking (Name, Phone, Booking_date, Booking_time, Guests, Comment, Status, ID_table) 
                VALUES (?, ?, ?, ?, ?, ?, 'created', ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssiss", $name, $phone, $booking_date, $booking_time, $guests, $comment, $table_id);
    } else {
        $sql = "INSERT INTO booking (Name, Phone, Booking_date, Booking_time, Guests, Comment, Status) 
                VALUES (?, ?, ?, ?, ?, ?, 'created')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssis", $name, $phone, $booking_date, $booking_time, $guests, $comment);
    }
    
    if ($stmt->execute()) {
        $response = [
            'success' => true,
            'message' => 'Бронирование успешно отправлено!'
        ];
    } else {
        $response['message'] = 'Ошибка БД: ' . $stmt->error;
    }
    
    $stmt->close();
} else {
    $response['message'] = 'Неверный метод запроса';
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
$conn->close();
?>