<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /login.php');
    exit();
}

require_once '../../includes/config.php';

// Получаем ID типа продукции из запроса
$typeId = $_GET['id'] ?? null;
if (!$typeId) {
    header('Location: /users/admin/product_types.php');
    exit();
}

// Получаем данные о типе продукции
$sql = "SELECT * FROM product_types WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $typeId]);
$type = $stmt->fetch();

if (!$type) {
    header('Location: /users/admin/product_types.php');
    exit();
}

// Обработка формы редактирования
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];

    // Обновление данных в базе
    $sql = "UPDATE product_types SET name = :name WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['name' => $name, 'id' => $typeId]);

    header('Location: /users/admin/product_types.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактировать тип продукции</title>
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
        <h1>Редактировать тип продукции</h1>
        <form action="edit_product_type.php?id=<?php echo $typeId; ?>" method="POST">
            <div class="form-group">
                <label for="name">Название:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($type['name']); ?>" required>
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