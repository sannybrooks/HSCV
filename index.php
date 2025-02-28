<?php
// Подключаем конфигурацию базы данных
require_once 'includes/config.php';

// Запрос для получения списка продукции
$sql = "SELECT id, name, description FROM products LIMIT 3";
$stmt = $pdo->query($sql);
$products = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Система управления цехом</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/auth.css">
    <link rel="stylesheet" href="assets/css/products.css">
</head>
<body>
    <!-- Шапка -->
    <?php include 'includes/header.php'; ?>

    <!-- Основной контент -->
    <main class="container">
        <!-- Блок с логотипом и информацией -->
        <section class="hero">
            <img src="assets/images/logo.png" alt="Логотип организации" class="logo">
            <h1>Добро пожаловать в систему управления цехом</h1>
            <p>Мы производим высококачественные сухие строительные смеси уже более 10 лет.</p>
        </section>

        <!-- Окно авторизации -->
        <section class="auth-section">
            <?php include 'templates/auth/login_form.php'; ?>
        </section>

        <!-- Блок с продукцией -->
        <section class="products-section">
            <h2>Наша продукция</h2>
            <div class="products-grid">
                <?php foreach ($products as $product): ?>
                    <?php include 'templates/products/product_card.php'; ?>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <!-- Подвал -->
    <?php include 'includes/footer.php'; ?>

    <!-- Скрипты -->
    <script src="assets/js/main.js"></script>
    <script src="assets/js/auth.js"></script>
</body>
</html>