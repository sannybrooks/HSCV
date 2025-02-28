<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /login.php');
    exit();
}

require_once '../../includes/config.php';

// Получаем список продукции с названием типа
$sql = "SELECT p.id, p.name, p.brand, pt.name AS type_name, p.description, p.preview, p.recipe_code 
        FROM products p
        JOIN product_types pt ON p.type_id = pt.id";
$stmt = $pdo->query($sql);
$products = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление продукцией</title>
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link rel="stylesheet" href="/assets/css/dashboard.css">
</head>
<body>
    <!-- Шапка -->
    <?php include '../../includes/header.php'; ?>

    <!-- Боковое меню -->
    <?php include '../../includes/sidebar.php'; ?>

    <!-- Основной контент -->
    <main class="main-content">
        <h1>Управление продукцией</h1>
        <a href="add_product.php" class="btn">Добавить продукт</a>
        <table>
            <thead>
                <tr>
                    <th>Название</th>
                    <th>Торговая марка</th>
                    <th>Тип продукции</th>
                    <th>Краткое описание</th>
                    <th>Превью</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                        <td><?php echo htmlspecialchars($product['brand']); ?></td>
                        <td><?php echo htmlspecialchars($product['type_name']); ?></td>
                        <td><?php echo htmlspecialchars($product['description']); ?></td>
                        <td><img src="/assets/images/<?php echo htmlspecialchars($product['preview']); ?>" alt="Превью" width="50"></td>
                        <td>
                            <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn">Редактировать</a>
                            <a href="delete_product.php?id=<?php echo $product['id']; ?>" class="btn">Удалить</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>

    <!-- Подвал -->
    <?php include '../../includes/footer.php'; ?>

    <!-- Скрипты -->
    <script src="/assets/js/main.js"></script>
</body>
</html>