<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /login.php');
    exit();
}

require_once '../../includes/config.php';

// Получаем данные для отчета
$sql = "SELECT p.name, SUM(pl.actual_quantity) AS total_quantity 
        FROM production_logs pl
        JOIN products p ON pl.product_id = p.id
        GROUP BY p.name";
$stmt = $pdo->query($sql);
$reportData = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Отчет по выпущенной продукции</title>
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link rel="stylesheet" href="/assets/css/dashboard.css">
</head>
<body>
    <!-- Шапка -->
    <?php include '../../includes/header.php'; ?>

    <!-- Боковое меню -->
    <?php include '../../includes/sidebar.php'; ?>

    <main class="main-content">
        <h1>Отчет по выпущенной продукции</h1>
        <table>
            <thead>
                <tr>
                    <th>Продукт</th>
                    <th>Количество</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reportData as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['total_quantity']); ?></td>
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