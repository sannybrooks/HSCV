<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /login.php');
    exit();
}

require_once '../../includes/config.php';

// Получаем список типов продукции
$sql = "SELECT id, name FROM product_types";
$types = $pdo->query($sql)->fetchAll();

// Обработка добавления нового типа
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_type'])) {
    $name = $_POST['name'];

    // Добавление нового типа
    $sql = "INSERT INTO product_types (name) VALUES (:name)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['name' => $name]);

    header('Location: product_types.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление типами продукции</title>
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link rel="stylesheet" href="/assets/css/dashboard.css">
</head>
<body>
    <!-- Шапка -->
    <!-- Шапка -->
    <?php include '../../includes/header.php'; ?>

    <!-- Боковое меню -->
    <?php include '../../includes/sidebar.php'; ?>

    <!-- Основной контент -->
    <main class="main-content">
    <main class="container">
        <h1>Управление типами продукции</h1>

        <!-- Форма добавления нового типа -->
        <form action="product_types.php" method="POST" class="add-type-form">
            <h2>Добавить тип продукции</h2>
            <div class="form-group">
                <label for="name">Название:</label>
                <input type="text" id="name" name="name" required>
            </div>
            <button type="submit" name="add_type" class="btn">Добавить</button>
        </form>

        <!-- Список типов продукции -->
        <h2>Список типов продукции</h2>
        <table>
            <thead>
                <tr>
                    <th>Название</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($types as $type): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($type['name']); ?></td>
                        <td>
                            <a href="edit_product_type.php?id=<?php echo $type['id']; ?>" class="btn">Редактировать</a>
                            <a href="delete_product_type.php?id=<?php echo $type['id']; ?>" class="btn">Удалить</a>
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