<?php
session_start();
require_once '../../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'master') {
    header('Location: /login.php');
    exit();
}

// Получаем отчет по использованному сырью
$sql = "SELECT m.name, SUM(mm.quantity) AS total_used 
        FROM material_movements mm
        JOIN materials m ON mm.material_id = m.id
        WHERE mm.movement_type = 'расход' 
        AND mm.movement_date BETWEEN :start_date AND :end_date
        GROUP BY m.name";
$stmt = $pdo->prepare($sql);
$stmt->execute(['start_date' => date('Y-m-01'), 'end_date' => date('Y-m-t')]);
$usedMaterials = $stmt->fetchAll();

// Получаем отчет по выпущенной продукции
$sql = "SELECT p.name, SUM(pl.actual_quantity) AS total_produced 
        FROM production_logs pl
        JOIN products p ON pl.product_id = p.id
        WHERE pl.date BETWEEN :start_date AND :end_date
        GROUP BY p.name";
$stmt = $pdo->prepare($sql);
$stmt->execute(['start_date' => date('Y-m-01'), 'end_date' => date('Y-m-t')]);
$producedProducts = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Отчеты</title>
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link rel="stylesheet" href="/assets/css/dashboard.css">
</head>
<body>
    <!-- Шапка -->
    <?php include '../../includes/header.php'; ?>

    <!-- Основной контент -->
    <main class="container">
        <h1>Отчеты</h1>

        <!-- Отчет по использованному сырью -->
        <h2>Использованное сырье</h2>
        <table>
            <thead>
                <tr>
                    <th>Наименование</th>
                    <th>Использовано</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usedMaterials as $material): ?>
                    <tr>
                        <td><?= htmlspecialchars($material['name']) ?></td>
                        <td><?= htmlspecialchars($material['total_used']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Отчет по выпущенной продукции -->
        <h2>Выпущенная продукция</h2>
        <table>
            <thead>
                <tr>
                    <th>Продукция</th>
                    <th>Количество</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($producedProducts as $product): ?>
                    <tr>
                        <td><?= htmlspecialchars($product['name']) ?></td>
                        <td><?= htmlspecialchars($product['total_produced']) ?></td>
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