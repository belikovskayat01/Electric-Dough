<?php
session_start();
require_once 'db.php';

$token = $_GET['token'] ?? '';

if (empty($token)) {
    die("Недействительная ссылка подтверждения");
}

$stmt = $conn->prepare("SELECT * FROM email_verifications WHERE token = ? AND expires_at > NOW()");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Ссылка подтверждения недействительна или истек срок действия.");
}

$verification = $result->fetch_assoc();
$email = $verification['email'];

$update_stmt = $conn->prepare("UPDATE users SET is_verified = 1, verification_token = NULL WHERE Email = ?");
$update_stmt->bind_param("s", $email);
$update_stmt->execute();
$update_stmt->close();

$delete_stmt = $conn->prepare("DELETE FROM email_verifications WHERE token = ?");
$delete_stmt->bind_param("s", $token);
$delete_stmt->execute();
$delete_stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Подтверждение email</title>
    <link rel="stylesheet" href="font.css">
    <link rel="stylesheet" href="css/variables.css">
    <style>
        body {
            font-family: var(--main-font-family);
            background: linear-gradient(135deg, #1a0a0a 0%, #0d0505 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .verify-container {
            background: var(--dark-brown-color);
            border-radius: var(--main-border-radius);
            padding: 50px;
            text-align: center;
            max-width: 500px;
            animation: fadeInUp 0.5s ease;
        }
        .verify-icon { font-size: 64px; margin-bottom: 20px; }
        .verify-title { color: var(--accent-font-color); font-size: 28px; margin-bottom: 20px; }
        .verify-message { color: #ccc; margin-bottom: 30px; line-height: 1.6; }
        .verify-btn {
            background: var(--dark-red-color);
            color: var(--accent-font-color);
            border: none;
            border-radius: 8px;
            padding: 12px 30px;
            font-size: 16px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }
        .verify-btn:hover { background: var(--button-hover-color); transform: translateY(-2px); }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="verify-container">
        <div class="verify-icon">✅</div>
        <h1 class="verify-title">Email подтвержден!</h1>
        <p class="verify-message">Ваш email успешно подтвержден. Теперь вы можете войти в свой аккаунт.</p>
        <a href="account.php" class="verify-btn">Войти в аккаунт</a>
    </div>
</body>
</html>