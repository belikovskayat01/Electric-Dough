<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once '../db.php';

$sql = "SELECT ID_product, Name, Price, Image, Category, Description 
        FROM products 
        WHERE Status = 'available' 
        ORDER BY RAND() 
        LIMIT 1";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $product = $result->fetch_assoc();
    
    $imagePath = !empty($product['Image']) ? 'IMG/products/' . $product['Image'] : 'IMG/placeholder.jpg';
    
    echo json_encode([
        'success' => true,
        'product' => [
            'id' => $product['ID_product'],
            'name' => $product['Name'],
            'price' => floatval($product['Price']),
            'image' => $imagePath,
            'category' => $product['Category'],
            'description' => $product['Description'] ?? 'Великолепный выбор от Electric Dough!'
        ]
    ], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Товары не найдены'
    ], JSON_UNESCAPED_UNICODE);
}

$conn->close();
?>