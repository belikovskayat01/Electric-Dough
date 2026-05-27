<?php
session_start();
require_once 'includes/security.php';

rateLimit(20, 30);  


header('Content-Type: application/json');
include 'db.php';
require_once 'send_verification_email.php';

$data = json_decode(file_get_contents('php://input'), true);

$name = trim($data['name'] ?? '');
$surname = trim($data['surname'] ?? '');
$email = trim($data['email'] ?? '');
$phone = trim($data['phone'] ?? '');
$password = $data['password'] ?? '';

if (!$name || !$surname || !$email || !$phone || !$password) {
    echo json_encode(['success' => false, 'error' => 'Все поля обязательны']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'error' => 'Неверный формат email']);
    exit;
}

$phone_clean = preg_replace('/\D/', '', $phone);
if (strlen($phone_clean) !== 11) {
    echo json_encode(['success' => false, 'error' => 'Неверный формат телефона']);
    exit;
}

if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'error' => 'Пароль должен быть не менее 6 символов']);
    exit;
}

$stmt = $conn->prepare("SELECT ID_User, is_verified FROM users WHERE Email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    if ($user['is_verified'] == 1) {
        echo json_encode(['success' => false, 'error' => 'Пользователь с таким email уже существует']);
        exit;
    } else {
        $delete_stmt = $conn->prepare("DELETE FROM users WHERE Email = ? AND is_verified = 0");
        $delete_stmt->bind_param("s", $email);
        $delete_stmt->execute();
        $delete_stmt->close();
    }
}
$stmt->close();

$verification_token = bin2hex(random_bytes(32));
$token_expires = date('Y-m-d H:i:s', strtotime('+24 hours'));
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$role = 'user';
$is_verified = 0;
$sql = "INSERT INTO users (Name, Surname, Email, Phone, Password, Role, is_verified, verification_token) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssssss", $name, $surname, $email, $phone, $hashedPassword, $role, $is_verified, $verification_token);

if ($stmt->execute()) {
    $user_id = $conn->insert_id;
    
    $token_stmt = $conn->prepare("INSERT INTO email_verifications (email, token, expires_at) VALUES (?, ?, ?)");
    $token_stmt->bind_param("sss", $email, $verification_token, $token_expires);
    $token_stmt->execute();
    $token_stmt->close();
    
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $verification_link = $protocol . '://' . $host . '/verify_email.php?token=' . $verification_token;
    
    $email_sent = sendVerificationEmail($email, $name, $verification_link);
    
    if ($email_sent) {
        echo json_encode([
            'success' => true, 
            'message' => 'Регистрация успешна! На вашу почту отправлена ссылка для подтверждения.'
        ]);
    } else {
        echo json_encode([
            'success' => true, 
            'message' => 'Регистрация успешна! Подтвердите email по ссылке:',
            'debug_link' => $verification_link
        ]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Ошибка при регистрации: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>