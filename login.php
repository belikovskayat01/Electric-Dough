<?php
session_start();
require_once 'includes/security.php';

rateLimit(20, 30);  

header('Content-Type: application/json');
include 'db.php';

$data = json_decode(file_get_contents('php://input'), true);

$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';

if (!$email || !$password) {
    echo json_encode(['success' => false, 'error' => 'Введите email и пароль']);
    exit;
}

$stmt = $conn->prepare("SELECT ID_User, Name, Surname, Email, Phone, Password, Role, is_verified FROM users WHERE Email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Пользователь не найден']);
    exit;
}

$user = $result->fetch_assoc();

if (!password_verify($password, $user['Password'])) {
    echo json_encode(['success' => false, 'error' => 'Неверный пароль']);
    exit;
}

if ($user['is_verified'] != 1) {
    echo json_encode(['success' => false, 'error' => 'Аккаунт не подтвержден. Проверьте вашу почту.']);
    exit;
}

$_SESSION['user_id'] = $user['ID_User'];
$_SESSION['name'] = $user['Name'];
$_SESSION['surname'] = $user['Surname'];
$_SESSION['email'] = $user['Email'];
$_SESSION['role'] = $user['Role'];

if (isset($_COOKIE['viewed_products'])) {
    $_SESSION['viewed_products'] = $_COOKIE['viewed_products'];
}

unset($user['Password']);
echo json_encode(['success' => true, 'user' => $user]);

$stmt->close();
$conn->close();
?>