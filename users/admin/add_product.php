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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $brand = $_POST['brand'];
    $typeId = $_POST['type_id']; // Используем type_id
    $recipeId = $_POST['recipe_id'];
    $description = $_POST['description'];
    $fullDescription = $_POST['full_description'];

    // Загрузка изображения
    $preview = 'default.jpg'; // По умолчанию
    if (isset($_FILES['preview']) && $_FILES['preview']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../../assets/images/';
        $uploadFile = $uploadDir . basename($_FILES['preview']['name']);
        if (move_uploaded_file($_FILES['preview']['tmp_name'], $uploadFile)) {
            $preview = basename($_FILES['preview']['name']);
        }
    }

    // Добавление продукта в базу данных
    $sql = "INSERT INTO products (name, brand, type_id, recipe_id, description, full_description, preview) 
            VALUES (:name, :brand, :type_id, :recipe_id, :description, :full_description, :preview)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'name' => $name,
        'brand' => $brand,
        'type_id' => $typeId,
        'recipe_id' => $recipeId,
        'description' => $description,
        'full_description' => $fullDescription,
        'preview' => $preview
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
    <title>Добавить продукт</title>
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
        <h1>Добавить продукт</h1>
        <form action="add_product.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">Название:</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="brand">Торговая марка:</label>
                <input type="text" id="brand" name="brand" required>
            </div>
            <div class="form-group">
                <label for="type_id">Тип продукции:</label>
                <select id="type_id" name="type_id" required>
                    <?php foreach ($types as $type): ?>
                        <option value="<?php echo $type['id']; ?>"><?php echo htmlspecialchars($type['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="recipe_id">Актуальный рецепт:</label>
                <select id="recipe_id" name="recipe_id" required>
                    <!-- Рецепты будут подгружаться динамически через JavaScript -->
                </select>
            </div>
            <div class="form-group">
                <label for="description">Краткое описание:</label>
                <textarea id="description" name="description" required></textarea>
            </div>
            <div class="form-group">
                <label for="full_description">Полное описание:</label>
                <textarea id="full_description" name="full_description"></textarea>
            </div>
            <div class="form-group">
                <label for="preview">Превью:</label>
                <input type="file" id="preview" name="preview">
            </div>
            <button type="submit" class="btn">Добавить</button>
        </form>
    </main>

    <!-- Подвал -->
    <?php include '../../includes/footer.php'; ?>

    <!-- Скрипты -->
    <script src="/assets/js/main.js"></script>
    <script>
        // Динамическая подгрузка рецептов при выборе типа продукции
        document.getElementById('type_id').addEventListener('change', function () {
            const typeId = this.value;
            const recipeSelect = document.getElementById('recipe_id');

            // Очищаем список рецептов
            recipeSelect.innerHTML = '';

            // Загружаем рецепты для выбранного типа
            fetch(`/api/get_recipes.php?type_id=${typeId}`)
                .then(response => response.json())
                .then(data => {
                    data.forEach(recipe => {
                        const option = document.createElement('option');
                        option.value = recipe.id;
                        option.textContent = recipe.code + ' - ' + recipe.description;
                        recipeSelect.appendChild(option);
                    });
                });
        });
    </script>
</body>
</html>