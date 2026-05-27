<?php
session_start();
header('Content-Type: application/json');
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Необходимо авторизоваться']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$booking_id = intval($data['booking_id'] ?? 0);
$user_id = $_SESSION['user_id'];

if ($booking_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Неверный ID бронирования']);
    exit;
}

try {
    $check_sql = "SELECT ID_booking, Status FROM booking WHERE ID_booking = ? AND ID_user = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $booking_id, $user_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Бронирование не найдено']);
        exit;
    }
    
    $booking = $result->fetch_assoc();
    
    if ($booking['Status'] === 'cancelled') {
        echo json_encode(['success' => false, 'message' => 'Бронирование уже отменено']);
        exit;
    }
    
    if ($booking['Status'] === 'confirmed') {
        echo json_encode(['success' => false, 'message' => 'Подтвержденное бронирование нельзя отменить через сайт. Позвоните нам для отмены.']);
        exit;
    }
    
    $update_sql = "UPDATE booking SET Status = 'cancelled' WHERE ID_booking = ? AND ID_user = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ii", $booking_id, $user_id);
    
    if ($update_stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Бронирование успешно отменено']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Ошибка при отмене бронирования']);
    }
    
    $update_stmt->close();
    
} catch (Exception $e) {
    error_log("Error cancelling booking: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Ошибка сервера']);
}

$conn->close();
?>