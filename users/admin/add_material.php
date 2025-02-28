<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'manager')) {
    header('Location: /login.php');
    exit();
}

require_once '../../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $unit = $_POST['unit'];

    // Валидация данных
    if (empty($name) || empty($unit)) {
        $error = "Пожалуйста, заполните все поля.";
    } else {
        // Добавление нового сырья
        $sql = "INSERT INTO materials (name, unit) VALUES (:name, :unit)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['name' => $name, 'unit' => $unit]);

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
    <title>Добавить сырье</title>
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
        <h1>Добавить сырье</h1>
        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form action="add_material.php" method="POST">
            <div class="form-group">
                <label for="name">Наименование:</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="unit">Единица измерения:</label>
                <select id="unit" name="unit" required>
                    <option value="кг">кг</option>
                    <option value="шт">шт</option>
                    <option value="м">м</option>
                </select>
            </div>
            <button type="submit" class="btn">Добавить</button>
        </form>
    </main>

    <!-- Подвал -->
    <?php include '../../includes/footer.php'; ?>

    <!-- Скрипты -->
    <script src="/assets/js/main.js"></script>
</body>
</html>