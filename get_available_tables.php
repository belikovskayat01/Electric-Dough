<?php
session_start();
header('Content-Type: application/json');
require_once 'db.php';

$date = $_GET['date'] ?? '';
$time = $_GET['time'] ?? '';
$guests = intval($_GET['guests'] ?? 1);

if (!$date || !$time) {
    echo json_encode(['success' => false, 'message' => 'Не указаны дата или время']);
    exit;
}

$sql = "SELECT ID_table as id, table_number, seats as capacity FROM restaurant_tables ORDER BY table_number";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

$all_tables = [];
while ($row = $result->fetch_assoc()) {
    $all_tables[] = $row;
}

$booked_sql = "SELECT ID_table FROM booking WHERE Booking_date = ? AND Booking_time = ? AND Status != 'cancelled' AND ID_table IS NOT NULL";
$booked_stmt = $conn->prepare($booked_sql);
$booked_stmt->bind_param("ss", $date, $time);
$booked_stmt->execute();
$booked_result = $booked_stmt->get_result();

$booked_tables = [];
while ($row = $booked_result->fetch_assoc()) {
    $booked_tables[] = $row['ID_table'];
}

$booked_chairs = [];

echo json_encode([
    'success' => true,
    'tables' => $all_tables,
    'booked' => $booked_tables,
    'bookedChairs' => $booked_chairs
]);

$stmt->close();
$booked_stmt->close();
$conn->close();
?>