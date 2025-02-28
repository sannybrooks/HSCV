<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'manager' && $_SESSION['role'] !== 'master')) {
    header('Location: /login.php');
    exit();
}

require_once '../../includes/config.php';

// Получаем данные о сырье и остатках на складах
$sql = "SELECT 
            m.name AS material_name, 
            m.unit, 
            SUM(CASE WHEN w.id = 1 THEN ms.quantity ELSE 0 END) AS warehouse_1,
            SUM(CASE WHEN w.id = 2 THEN ms.quantity ELSE 0 END) AS warehouse_2,
            SUM(ms.quantity) AS total_quantity,  -- Сумма остатков по всем складам
            MIN(ms.min_quantity) AS min_quantity  -- Минимальный остаток
        FROM materials m
        LEFT JOIN material_stocks ms ON m.id = ms.material_id
        LEFT JOIN warehouses w ON ms.warehouse_id = w.id
        GROUP BY m.name, m.unit";
$stmt = $pdo->query($sql);
$materials = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление складами</title>
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
        <h1>Управление складами</h1>

        <!-- Таблица с данными -->
        <table>
            <thead>
                <tr>
                    <th>Наименование</th>
                    <th>Единица измерения</th>
                    <th>Остаток (Склад 1)</th>
                    <th>Остаток (Склад 2)</th>
                    <th>Сумма Итого</th>
                    <th>Минимальный остаток</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($materials as $material): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($material['material_name']); ?></td>
                        <td><?php echo htmlspecialchars($material['unit']); ?></td>
                        <td><?php echo htmlspecialchars($material['warehouse_1']); ?></td>
                        <td><?php echo htmlspecialchars($material['warehouse_2']); ?></td>
                        <td><?php echo htmlspecialchars($material['total_quantity']); ?></td>
                        <td><?php echo htmlspecialchars($material['min_quantity']); ?></td>
                        <td>
                            <a href="edit_stock.php?id=<?php echo $material['id']; ?>" class="btn">Редактировать</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Кнопки для добавления и импорта (только для администратора и менеджера) -->
        <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'manager'): ?>
            <div class="actions">
                <a href="add_material.php" class="btn">Добавить сырье</a>
                <a href="edit_stock.php" class="btn">Редактировать остатки</a>
                <a href="add_movement.php" class="btn">Приход/Расход</a>
                <a href="movement_report.php" class="btn">Движение остатков</a>
                <a href="import_materials.php" class="btn">Импорт из Excel</a>
            </div>
        <?php endif; ?>
    </main>

    <!-- Подвал -->
    <?php include '../../includes/footer.php'; ?>

    <!-- Скрипты -->
    <script src="/assets/js/main.js"></script>
</body>
</html>