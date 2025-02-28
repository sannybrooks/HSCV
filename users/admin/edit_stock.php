<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'manager')) {
    header('Location: /login.php');
    exit();
}

require_once '../../includes/config.php';

$stockId = $_GET['id'] ?? null;
if (!$stockId) {
    header('Location: /users/admin/warehouses.php');
    exit();
}

// Получаем данные о записи остатка
$sql = "SELECT ms.id, m.name, m.unit, w.name AS warehouse_name, ms.quantity, ms.min_quantity 
        FROM material_stocks ms
        JOIN materials m ON ms.material_id = m.id
        JOIN warehouses w ON ms.warehouse_id = w.id
        WHERE ms.id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $stockId]);
$stock = $stmt->fetch();

if (!$stock) {
    header('Location: /users/admin/warehouses.php');
    exit();
}

// Обработка формы редактирования
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quantity = $_POST['quantity'];
    $minQuantity = $_POST['min_quantity'];

    // Валидация данных
    if ($quantity < 0 || $minQuantity < 0) {
        $error = "Остаток и минимальный остаток должны быть положительными числами.";
    } else {
        // Обновление данных в базе
        $sql = "UPDATE material_stocks SET quantity = :quantity, min_quantity = :min_quantity WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'quantity' => $quantity,
            'min_quantity' => $minQuantity,
            'id' => $stockId
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
    <title>Редактировать остаток</title>
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
        <h1>Редактировать остаток</h1>
        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form action="edit_stock.php?id=<?php echo $stockId; ?>" method="POST">
            <div class="form-group">
                <label for="name">Наименование:</label>
                <input type="text" id="name" value="<?php echo htmlspecialchars($stock['name']); ?>" disabled>
            </div>
            <div class="form-group">
                <label for="unit">Единица измерения:</label>
                <input type="text" id="unit" value="<?php echo htmlspecialchars($stock['unit']); ?>" disabled>
            </div>
            <div class="form-group">
                <label for="warehouse">Склад:</label>
                <input type="text" id="warehouse" value="<?php echo htmlspecialchars($stock['warehouse_name']); ?>" disabled>
            </div>
            <div class="form-group">
                <label for="quantity">Остаток:</label>
                <input type="number" id="quantity" name="quantity" value="<?php echo htmlspecialchars($stock['quantity']); ?>" step="0.01" required>
            </div>
            <div class="form-group">
                <label for="min_quantity">Минимальный остаток:</label>
                <input type="number" id="min_quantity" name="min_quantity" value="<?php echo htmlspecialchars($stock['min_quantity']); ?>" step="0.01" required>
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