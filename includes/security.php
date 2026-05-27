<?php
session_start();

function rateLimit($limit = 20, $timeWindow = 30) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $key = 'requests_' . $ip;
    
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = ['count' => 1, 'start_time' => time()];
        return true;
    }
    
    $currentTime = time();
    $timePassed = $currentTime - $_SESSION[$key]['start_time'];
    
    if ($timePassed > $timeWindow) {
        $_SESSION[$key] = ['count' => 1, 'start_time' => $currentTime];
        return true;
    }
    
    if ($_SESSION[$key]['count'] >= $limit) {
        http_response_code(429);
        die('Слишком много запросов. Подождите.');
    }
    
    $_SESSION[$key]['count']++;
    return true;
}
?>