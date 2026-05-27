<?php
session_start();
header('Content-Type: application/json');

$response = [
    'authenticated' => isset($_SESSION['user_id']),
    'user_id' => $_SESSION['user_id'] ?? null,
    'name' => $_SESSION['name'] ?? null,
    'surname' => $_SESSION['surname'] ?? null,
    'email' => $_SESSION['email'] ?? null,
    'role' => $_SESSION['role'] ?? null
];

echo json_encode($response);
?>