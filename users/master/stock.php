<?php
session_start();
require_once '../../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'master') {
    header('Location: /login.php');
    exit();
}

// Получаем остатки сырья на складе
$sql = "SELECT m.name, ms.quantity, ms.min_quantity 
        FROM material_stocks ms
        JOIN materials m ON ms.material_id = m.id
        WHERE ms.warehouse_id = :warehouse_id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['warehouse_id' => 1]); // Укажите ID склада мастера
$stocks = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Учет сырья</title>
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
        <h1>Учет сырья</h1>

        <!-- Таблица с остатками сырья -->
        <table>
            <thead>
                <tr>
                    <th>Наименование</th>
                    <th>Остаток</th>
                    <th>Минимальный остаток</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($stocks as $stock): ?>
                    <tr>
                        <td><?= htmlspecialchars($stock['name']) ?></td>
                        <td><?= htmlspecialchars($stock['quantity']) ?></td>
                        <td><?= htmlspecialchars($stock['min_quantity']) ?></td>
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