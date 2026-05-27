<?php

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function validateInt($value, $default = 0) {
    if (is_numeric($value)) {
        return (int)$value;
    }
    return $default;
}

function validateString($value, $maxLength = 255, $allowEmpty = false) {
    if ($allowEmpty && empty($value)) {
        return '';
    }
    
    $value = trim($value);
    if (strlen($value) > $maxLength) {
        $value = substr($value, 0, $maxLength);
    }
    
    $value = preg_replace('/[<>\"\'\(\)]/', '', $value);
    
    return $value;
}

function validateEmail($email) {
    $email = trim($email);
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return $email;
    }
    return '';
}

function validatePhone($phone) {
    $phone = preg_replace('/\D/', '', $phone);
    if (strlen($phone) >= 10 && strlen($phone) <= 15) {
        return $phone;
    }
    return '';
}

function escapeLike($string) {
    $search = array('%', '_', '\\');
    $replace = array('\\%', '\\_', '\\\\');
    return str_replace($search, $replace, $string);
}
?>