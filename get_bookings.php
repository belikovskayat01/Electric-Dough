<?php
session_start();
header('Content-Type: application/json');
require_once 'db.php';

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

if ($user_id <= 0) {
    echo json_encode([]);
    exit;
}
$current_date = date('Y-m-d');
$current_time = date('H:i:s');

error_log("=== Фильтрация бронирований ===");
error_log("Текущая дата: " . $current_date);
error_log("Текущее время: " . $current_time);

$sql = "SELECT * FROM booking WHERE ID_user = ? AND Status != 'cancelled' ORDER BY Booking_date ASC, Booking_time ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$all_bookings = [];
while ($row = $result->fetch_assoc()) {
    $all_bookings[] = $row;
}

error_log("Всего неотмененных бронирований: " . count($all_bookings));

$active_bookings = [];

foreach ($all_bookings as $booking) {
    $booking_date = $booking['Booking_date'];
    $booking_time = $booking['Booking_time'];
    
    error_log("Проверка брони ID {$booking['ID_booking']}: дата $booking_date, время $booking_time");
    
    $is_past = false;
    
    if ($booking_date < $current_date) {
        $is_past = true;
        error_log("  → Дата прошла: $booking_date < $current_date");
    }
    elseif ($booking_date == $current_date) {
        if ($booking_time < $current_time) {
            $is_past = true;
            error_log("  → Время прошло: $booking_time < $current_time");
        } else {
            error_log("  → Время актуально: $booking_time >= $current_time");
        }
    } else {
        error_log("  → Дата в будущем: $booking_date > $current_date");
    }

    if (!$is_past) {
        $active_bookings[] = $booking;
        error_log("  → Бронь активна, добавляем");
    } else {
        error_log("  → Бронь прошла, пропускаем");
    }
}

error_log("Активных бронирований: " . count($active_bookings));

echo json_encode($active_bookings);

$stmt->close();
$conn->close();
?>