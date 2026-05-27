<?php
session_start();
require_once 'db.php';

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($product_id <= 0) {
    header('Location: catalog.php');
    exit;
}

$stmt = $conn->prepare("SELECT * FROM products WHERE ID_product = ? AND Status = 'available'");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: catalog.php');
    exit;
}

$product = $result->fetch_assoc();

$similar_query = "SELECT * FROM products WHERE Category = ? AND ID_product != ? AND Status = 'available' LIMIT 3";
$similar_stmt = $conn->prepare($similar_query);
$similar_stmt->bind_param("si", $product['Category'], $product_id);
$similar_stmt->execute();
$similar_result = $similar_stmt->get_result();

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $product_id = (int)$_GET['id'];
    
    $viewed = isset($_COOKIE['viewed_products']) ? $_COOKIE['viewed_products'] : '';
    $viewedArray = $viewed ? explode(',', $viewed) : [];
    
    $viewedArray = array_filter($viewedArray, function($id) use ($product_id) {
        return $id != $product_id;
    });
    
    array_unshift($viewedArray, $product_id);
    
    $viewedArray = array_slice($viewedArray, 0, 10);
    
    setcookie('viewed_products', implode(',', $viewedArray), time() + (86400 * 30), "/");
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['Name']); ?> - Electric Dough</title>
    <link rel="stylesheet" href="font.css">
    <link rel="stylesheet" href="css/product.css">
</head>
<body>
    <header class="header">
        <div class="logo">
            <a href="index.php">
                <img src="IMG/logo.png" alt="Логотип бренда" width="65px">
            </a>
        </div>
        <nav class="menu">
            <ul>
                <li><a href="aboutus.php">О нас</a></li>
                <li><a href="catalog.php">Каталог</a></li>
                <li><a href="booking.php">Бронирование</a></li>
                <li><a href="pre-order.php">Предзаказ</a></li>
                <li><a href="contacts.php">Контакты</a></li>
            </ul>
        </nav>
        <div class="account">
            <a href="account.php">
                <img src="IMG/avatar.png" alt="Личный кабинет" width="60px">
            </a>
        </div>
    </header>
    <main>
        <div class="product-detail">
            <div class="product-image-large">
                <img src="IMG/products/<?php echo $product['Image']; ?>" 
                     alt="<?php echo htmlspecialchars($product['Name']); ?>"
                     onerror="this.src='IMG/placeholder.jpg'">
            </div>
            
            <div class="product-info">
                <h1><?php echo htmlspecialchars($product['Name']); ?></h1>
                <div class="product-price"><?php echo $product['Price']; ?> ₽</div>
                
                <?php if ($product['Weight']): ?>
                    <div class="product-weight">Вес: <?php echo $product['Weight']; ?></div>
                <?php endif; ?>
                
                <?php if ($product['Description']): ?>
                    <div class="product-description-short">
                        <strong>Краткое описание:</strong>
                        <p><?php echo nl2br(htmlspecialchars($product['Description'])); ?></p>
                    </div>
                <?php endif; ?>
                
                <?php if ($product['Full_description']): ?>
                    <div class="product-description-full">
                        <strong>Полное описание:</strong>
                        <p><?php echo nl2br(htmlspecialchars($product['Full_description'])); ?></p>
                    </div>
                <?php endif; ?>
                
                <?php if ($product['Ingredients']): ?>
                    <div class="product-ingredients">
                        <strong>Состав:</strong> <?php echo htmlspecialchars($product['Ingredients']); ?>
                    </div>
                <?php endif; ?>
                
                <div class="product-actions">
                    <a href="pre-order.php?product_id=<?php echo $product['ID_product']; ?>" class="preorder-btn">
                        🎸 Заказать сейчас
                    </a>
                    <a href="catalog.php" class="back-to-catalog">← Вернуться в каталог</a>
                </div>
            </div>
        </div>
        
        <?php if ($similar_result->num_rows > 0): ?>
            <div class="similar-products">
                <h2 class="similar-title">Похожие товары</h2>
                <div class="similar-grid">
                    <?php while ($similar = $similar_result->fetch_assoc()): ?>
                        <div class="product_item">
                            <img src="IMG/products/<?php echo $similar['Image']; ?>" 
                                 class="product-image"
                                 alt="<?php echo htmlspecialchars($similar['Name']); ?>"
                                 onerror="this.src='IMG/placeholder.jpg'">
                            <p class="item_name">
                                <?php echo htmlspecialchars($similar['Name']); ?>
                            </p>
                            <div class="product-price"><?php echo $similar['Price']; ?> ₽</div>
                            <a href="product.php?id=<?php echo $similar['ID_product']; ?>">
                                <button>подробнее...</button>
                            </a>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <footer class="footer">
        <p class="company-info">
            &copy; 2026 Electric Dough Rock Bakery
            Все права защищены
        </p>
        <nav class="menu footer-menu">
            <ul>
                <li><a href="aboutus.php">О нас</a></li>
                <li><a href="catalog.php">Каталог</a></li>
                <li><a href="booking.php">Бронирование</a></li>
                <li><a href="pre-order.php">Предзаказ</a></li>
                <li><a href="contacts.php">Контакты</a></li>
            </ul>
        </nav>
        <a href="#top" class="to-top">Наверх ↑</a>
    </footer>
</body>
</html>