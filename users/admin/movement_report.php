<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'manager')) {
    header('Location: /login.php');
    exit();
}

require_once '../../includes/config.php';

// Обработка формы выбора периода
$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate = $_GET['end_date'] ?? date('Y-m-t');

// Получаем данные о движении сырья за период
$sql = "SELECT m.name AS material_name, w.name AS warehouse_name, mm.movement_type, mm.quantity, mm.movement_date 
        FROM material_movements mm
        JOIN materials m ON mm.material_id = m.id
        JOIN warehouses w ON mm.warehouse_id = w.id
        WHERE mm.movement_date BETWEEN :start_date AND :end_date
        ORDER BY mm.movement_date DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute(['start_date' => $startDate, 'end_date' => $endDate]);
$movements = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Отчет о движении сырья</title>
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
        <h1>Отчет о движении сырья</h1>

        <!-- Форма выбора периода -->
        <form action="movement_report.php" method="GET" class="filter-form">
            <div class="form-group">
                <label for="start_date">Начальная дата:</label>
                <input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($startDate); ?>" required>
            </div>
            <div class="form-group">
                <label for="end_date">Конечная дата:</label>
                <input type="date" id="end_date" name="end_date" value="<?php echo htmlspecialchars($endDate); ?>" required>
            </div>
            <button type="submit" class="btn">Показать</button>
        </form>

        <!-- Таблица с движением сырья -->
        <table>
            <thead>
                <tr>
                    <th>Дата</th>
                    <th>Сырье</th>
                    <th>Склад</th>
                    <th>Тип движения</th>
                    <th>Количество</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($movements as $movement): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($movement['movement_date']); ?></td>
                        <td><?php echo htmlspecialchars($movement['material_name']); ?></td>
                        <td><?php echo htmlspecialchars($movement['warehouse_name']); ?></td>
                        <td><?php echo htmlspecialchars($movement['movement_type']); ?></td>
                        <td><?php echo htmlspecialchars($movement['quantity']); ?></td>
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