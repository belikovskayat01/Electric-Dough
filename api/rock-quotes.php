<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../db.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            $id = (int)$_GET['id'];
            $sql = "SELECT id, quote, author, band FROM rock_quotes WHERE id = $id AND is_active = 1";
            $result = $conn->query($sql);
            
            if ($result && $result->num_rows > 0) {
                $quote = $result->fetch_assoc();
                echo json_encode([
                    'success' => true,
                    'data' => $quote
                ], JSON_UNESCAPED_UNICODE);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Цитата не найдена'
                ], JSON_UNESCAPED_UNICODE);
            }
        } 
        elseif (isset($_GET['random'])) {
            $sql = "SELECT id, quote, author, band FROM rock_quotes WHERE is_active = 1 ORDER BY RAND() LIMIT 1";
            $result = $conn->query($sql);
            
            if ($result && $result->num_rows > 0) {
                $quote = $result->fetch_assoc();
                echo json_encode([
                    'success' => true,
                    'data' => $quote
                ], JSON_UNESCAPED_UNICODE);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Цитаты не найдены'
                ], JSON_UNESCAPED_UNICODE);
            }
        }
        else {
            $sql = "SELECT id, quote, author, band, is_active, created_at FROM rock_quotes ORDER BY id DESC";
            $result = $conn->query($sql);
            
            $quotes = [];
            while ($row = $result->fetch_assoc()) {
                $quotes[] = $row;
            }
            
            echo json_encode([
                'success' => true,
                'data' => $quotes
            ], JSON_UNESCAPED_UNICODE);
        }
        break;
        
    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (isset($input['quote']) && isset($input['author']) && isset($input['band'])) {
            $quote = $conn->real_escape_string($input['quote']);
            $author = $conn->real_escape_string($input['author']);
            $band = $conn->real_escape_string($input['band']);
            $is_active = isset($input['is_active']) ? (int)$input['is_active'] : 1;
            
            $sql = "INSERT INTO rock_quotes (quote, author, band, is_active) VALUES ('$quote', '$author', '$band', $is_active)";
            
            if ($conn->query($sql)) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Цитата добавлена',
                    'id' => $conn->insert_id
                ], JSON_UNESCAPED_UNICODE);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Ошибка при добавлении: ' . $conn->error
                ], JSON_UNESCAPED_UNICODE);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Недостаточно данных. Нужны: quote, author, band'
            ], JSON_UNESCAPED_UNICODE);
        }
        break;
        
    case 'PUT':
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (isset($_GET['id'])) {
            $id = (int)$_GET['id'];
            $updates = [];
            
            if (isset($input['quote'])) {
                $updates[] = "quote = '" . $conn->real_escape_string($input['quote']) . "'";
            }
            if (isset($input['author'])) {
                $updates[] = "author = '" . $conn->real_escape_string($input['author']) . "'";
            }
            if (isset($input['band'])) {
                $updates[] = "band = '" . $conn->real_escape_string($input['band']) . "'";
            }
            if (isset($input['is_active'])) {
                $updates[] = "is_active = " . (int)$input['is_active'];
            }
            
            if (!empty($updates)) {
                $sql = "UPDATE rock_quotes SET " . implode(', ', $updates) . " WHERE id = $id";
                
                if ($conn->query($sql)) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Цитата обновлена'
                    ], JSON_UNESCAPED_UNICODE);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Ошибка при обновлении: ' . $conn->error
                    ], JSON_UNESCAPED_UNICODE);
                }
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Нет данных для обновления'
                ], JSON_UNESCAPED_UNICODE);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Не указан ID цитаты'
            ], JSON_UNESCAPED_UNICODE);
        }
        break;
        
    case 'DELETE':
        if (isset($_GET['id'])) {
            $id = (int)$_GET['id'];
            $sql = "DELETE FROM rock_quotes WHERE id = $id";
            
            if ($conn->query($sql)) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Цитата удалена'
                ], JSON_UNESCAPED_UNICODE);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Ошибка при удалении: ' . $conn->error
                ], JSON_UNESCAPED_UNICODE);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Не указан ID цитаты'
            ], JSON_UNESCAPED_UNICODE);
        }
        break;
}

$conn->close();
?>