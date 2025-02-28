<?php
require_once 'includes/config.php';

// Получаем ID продукта из запроса
$productId = $_GET['id'] ?? null;
if (!$productId) {
    header('Location: /index.php');
    exit();
}

// Запрос для получения данных о продукте
$sql = "SELECT * FROM products WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $productId]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: /index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?></title>
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link rel="stylesheet" href="/assets/css/products.css">
</head>
<body>
    <!-- Шапка -->
    <?php include 'includes/header.php'; ?>

    <!-- Основной контент -->
    <main class="container">
        <div class="product-details">
            <img src="/assets/images/<?php echo htmlspecialchars($product['preview']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
            <h1><?php echo htmlspecialchars($product['name']); ?></h1>
            <p><?php echo htmlspecialchars($product['description']); ?></p>
            <p><strong>Тип:</strong> <?php echo htmlspecialchars($product['type']); ?></p>
            <p><strong>Торговая марка:</strong> <?php echo htmlspecialchars($product['brand']); ?></p>
            <a href="/index.php" class="btn">Назад</a>
        </div>
    </main>

    <!-- Подвал -->
    <?php include 'includes/footer.php'; ?>

    <!-- Скрипты -->
    <script src="/assets/js/main.js"></script>
</body>
</html>