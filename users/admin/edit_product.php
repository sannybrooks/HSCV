<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /login.php');
    exit();
}

require_once '../../includes/config.php';

// Получаем ID продукта из запроса
$productId = $_GET['id'] ?? null;
if (!$productId) {
    header('Location: /users/admin/products.php');
    exit();
}

// Получаем данные о продукте
$sql = "SELECT * FROM products WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $productId]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: /users/admin/products.php');
    exit();
}

// Получаем список типов продукции
$sql = "SELECT id, name FROM product_types";
$types = $pdo->query($sql)->fetchAll();

// Получаем список рецептов для типа продукции
$sql = "SELECT * FROM recipes WHERE type_id = :type_id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['type_id' => $product['type_id']]);
$recipes = $stmt->fetchAll();

// Обработка формы редактирования
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $brand = $_POST['brand'];
    $typeId = $_POST['type_id']; // Используем type_id
    $recipeId = $_POST['recipe_id'];
    $description = $_POST['description'];
    $fullDescription = $_POST['full_description'];

    // Обновление данных в базе
    $sql = "UPDATE products 
            SET name = :name, brand = :brand, type_id = :type_id, recipe_id = :recipe_id, 
                description = :description, full_description = :full_description 
            WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'name' => $name,
        'brand' => $brand,
        'type_id' => $typeId,
        'recipe_id' => $recipeId,
        'description' => $description,
        'full_description' => $fullDescription,
        'id' => $productId
    ]);

    header('Location: /users/admin/products.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактировать продукт</title>
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
        <h1>Редактировать продукт</h1>
        <form action="edit_product.php?id=<?php echo $productId; ?>" method="POST">
            <div class="form-group">
                <label for="name">Название:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="brand">Торговая марка:</label>
                <input type="text" id="brand" name="brand" value="<?php echo htmlspecialchars($product['brand']); ?>" required>
            </div>
            <div class="form-group">
                <label for="type_id">Тип продукции:</label>
                <select id="type_id" name="type_id" required>
                    <?php foreach ($types as $type): ?>
                        <option value="<?php echo $type['id']; ?>" <?php echo $type['id'] === $product['type_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($type['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="recipe_id">Актуальный рецепт:</label>
                <select id="recipe_id" name="recipe_id" required>
                    <?php foreach ($recipes as $recipe): ?>
                        <option value="<?php echo $recipe['id']; ?>" <?php echo $recipe['id'] === $product['recipe_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($recipe['code'] . ' - ' . $recipe['description']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="description">Краткое описание:</label>
                <textarea id="description" name="description" required><?php echo htmlspecialchars($product['description']); ?></textarea>
            </div>
            <div class="form-group">
                <label for="full_description">Полное описание:</label>
                <textarea id="full_description" name="full_description"><?php echo htmlspecialchars($product['full_description']); ?></textarea>
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