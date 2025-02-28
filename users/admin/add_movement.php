<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'manager')) {
    header('Location: /login.php');
    exit();
}

require_once '../../includes/config.php';

// Получаем список сырья и складов
$materials = $pdo->query("SELECT id, name FROM materials")->fetchAll();
$warehouses = $pdo->query("SELECT id, name FROM warehouses")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $materialId = $_POST['material_id'];
    $warehouseId = $_POST['warehouse_id'];
    $movementType = $_POST['movement_type'];
    $quantity = $_POST['quantity'];
    $movementDate = $_POST['movement_date'];

    // Валидация данных
    if (empty($materialId) || empty($warehouseId) || empty($movementType) || empty($quantity) || empty($movementDate)) {
        $error = "Пожалуйста, заполните все поля.";
    } else {
        // Проверка остатков при расходе
        if ($movementType === 'расход') {
            $sql = "SELECT SUM(quantity) AS total FROM material_stocks WHERE material_id = :material_id AND warehouse_id = :warehouse_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['material_id' => $materialId, 'warehouse_id' => $warehouseId]);
            $stock = $stmt->fetch();

            if ($stock['total'] < $quantity) {
                $error = "Недостаточно сырья на складе.";
            }
        }

        if (!isset($error)) {
            // Добавление движения
            $sql = "INSERT INTO material_movements (material_id, warehouse_id, movement_type, quantity, movement_date) 
                    VALUES (:material_id, :warehouse_id, :movement_type, :quantity, :movement_date)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'material_id' => $materialId,
                'warehouse_id' => $warehouseId,
                'movement_type' => $movementType,
                'quantity' => $quantity,
                'movement_date' => $movementDate
            ]);

            // Обновление остатков на складе
            if ($movementType === 'приход') {
                $sql = "INSERT INTO material_stocks (material_id, warehouse_id, quantity) 
                        VALUES (:material_id, :warehouse_id, :quantity)
                        ON DUPLICATE KEY UPDATE quantity = quantity + :quantity";
            } else {
                $sql = "UPDATE material_stocks SET quantity = quantity - :quantity 
                        WHERE material_id = :material_id AND warehouse_id = :warehouse_id";
            }
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'material_id' => $materialId,
                'warehouse_id' => $warehouseId,
                'quantity' => $quantity
            ]);

            header('Location: /users/admin/warehouses.php');
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавить движение сырья</title>
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
    <div class="user-form">
        <h1>Добавить движение сырья</h1>
        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form action="add_movement.php" method="POST">
            <div class="form-group">
                <label for="material_id">Сырье:</label>
                <select id="material_id" name="material_id" required>
                    <?php foreach ($materials as $material): ?>
                        <option value="<?php echo $material['id']; ?>"><?php echo htmlspecialchars($material['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="warehouse_id">Склад:</label>
                <select id="warehouse_id" name="warehouse_id" required>
                    <?php foreach ($warehouses as $warehouse): ?>
                        <option value="<?php echo $warehouse['id']; ?>"><?php echo htmlspecialchars($warehouse['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="movement_type">Тип движения:</label>
                <select id="movement_type" name="movement_type" required>
                    <option value="приход">Приход</option>
                    <option value="расход">Расход</option>
                </select>
            </div>
            <div class="form-group">
                <label for="quantity">Количество:</label>
                <input type="number" id="quantity" name="quantity" step="0.01" required>
            </div>
            <div class="form-group">
                <label for="movement_date">Дата:</label>
                <input type="date" id="movement_date" name="movement_date" required>
            </div>
            <button type="submit" class="btn">Добавить</button>
        </form>
    </div>
    </main>

    <!-- Подвал -->
    <?php include '../../includes/footer.php'; ?>

    <!-- Скрипты -->
    <script src="/assets/js/main.js"></script>
</body>
</html>