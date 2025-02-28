<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'manager')) {
    header('Location: /login.php');
    exit();
}

require_once '../../includes/config.php';

// Получаем ID сырья из запроса
$materialId = $_GET['id'] ?? null;
if (!$materialId) {
    header('Location: /users/admin/warehouses.php');
    exit();
}

// Получаем данные о сырье
$sql = "SELECT * FROM raw_materials WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $materialId]);
$material = $stmt->fetch();

if (!$material) {
    header('Location: /users/admin/warehouses.php');
    exit();
}

// Получаем список складов
$sql = "SELECT id, name FROM warehouses";
$warehouses = $pdo->query($sql)->fetchAll();

// Обработка формы редактирования
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $unit = $_POST['unit'];
    $warehouseId = $_POST['warehouse_id'];
    $currentQuantity = $_POST['current_quantity'];
    $minQuantity = $_POST['min_quantity'];

    // Валидация данных
    if (empty($name) || empty($unit) || empty($warehouseId) || $currentQuantity < 0 || $minQuantity < 0) {
        $error = "Пожалуйста, заполните все поля корректно.";
    } else {
        // Обновление данных в базе
        $sql = "UPDATE raw_materials 
                SET name = :name, unit = :unit, warehouse_id = :warehouse_id, 
                    current_quantity = :current_quantity, min_quantity = :min_quantity 
                WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'name' => $name,
            'unit' => $unit,
            'warehouse_id' => $warehouseId,
            'current_quantity' => $currentQuantity,
            'min_quantity' => $minQuantity,
            'id' => $materialId
        ]);

        header('Location: /users/admin/warehouses.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактировать сырье</title>
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
        <h1>Редактировать сырье</h1>
        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form action="edit_material.php?id=<?php echo $materialId; ?>" method="POST">
            <div class="form-group">
                <label for="name">Наименование:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($material['name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="unit">Единица измерения:</label>
                <select id="unit" name="unit" required>
                    <option value="кг" <?php echo $material['unit'] === 'кг' ? 'selected' : ''; ?>>кг</option>
                    <option value="шт" <?php echo $material['unit'] === 'шт' ? 'selected' : ''; ?>>шт</option>
                    <option value="м" <?php echo $material['unit'] === 'м' ? 'selected' : ''; ?>>м</option>
                </select>
            </div>
            <div class="form-group">
                <label for="warehouse_id">Склад:</label>
                <select id="warehouse_id" name="warehouse_id" required>
                    <?php foreach ($warehouses as $warehouse): ?>
                        <option value="<?php echo $warehouse['id']; ?>" <?php echo $warehouse['id'] === $material['warehouse_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($warehouse['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="current_quantity">Остаток:</label>
                <input type="number" id="current_quantity" name="current_quantity" value="<?php echo htmlspecialchars($material['current_quantity']); ?>" step="0.01" required>
            </div>
            <div class="form-group">
                <label for="min_quantity">Минимальный остаток:</label>
                <input type="number" id="min_quantity" name="min_quantity" value="<?php echo htmlspecialchars($material['min_quantity']); ?>" step="0.01" required>
            </div>
            <button type="submit" class="btn">Сохранить изменения</button>
        </form>
    </main>

    <!-- Подвал -->
    <?php include '../../includes/footer.php'; ?>

    <!-- Скрипты -->
    <script src="/assets/js/main.js"></script>
</body>
</html>