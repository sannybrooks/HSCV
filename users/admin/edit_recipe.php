<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /login.php');
    exit();
}

require_once '../../includes/config.php';

$recipeId = $_GET['id'] ?? null;
if (!$recipeId) {
    header('Location: /users/admin/recipes.php');
    exit();
}

// Получаем данные о рецепте
$sql = "SELECT * FROM recipes WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $recipeId]);
$recipe = $stmt->fetch();

if (!$recipe) {
    header('Location: /users/admin/recipes.php');
    exit();
}

// Получаем ингредиенты рецепта
$sql = "SELECT ri.material_id, ri.quantity, m.name, m.unit 
        FROM recipe_ingredients ri
        JOIN materials m ON ri.material_id = m.id
        WHERE ri.recipe_id = :recipe_id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['recipe_id' => $recipeId]);
$ingredients = $stmt->fetchAll();

// Получаем список типов продукции
$types = $pdo->query("SELECT id, name FROM product_types")->fetchAll();

// Получаем список материалов с единицами измерения
$materials = $pdo->query("SELECT id, name, unit FROM materials")->fetchAll();

// Обработка формы редактирования
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = $_POST['code'];
    $typeId = $_POST['type_id'];
    $description = $_POST['description'];
    $ingredients = $_POST['ingredients'];

    // Обновляем рецепт
    $sql = "UPDATE recipes 
            SET code = :code, type_id = :type_id, description = :description 
            WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'code' => $code,
        'type_id' => $typeId,
        'description' => $description,
        'id' => $recipeId
    ]);

    // Удаляем старые ингредиенты
    $sql = "DELETE FROM recipe_ingredients WHERE recipe_id = :recipe_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['recipe_id' => $recipeId]);

    // Добавляем новые ингредиенты
    foreach ($ingredients as $ingredient) {
        if (!isset($ingredient['deleted']) || !$ingredient['deleted']) {
            $sql = "INSERT INTO recipe_ingredients (recipe_id, material_id, quantity) 
                    VALUES (:recipe_id, :material_id, :quantity)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'recipe_id' => $recipeId,
                'material_id' => $ingredient['material_id'],
                'quantity' => $ingredient['quantity']
            ]);
        }
    }

    header('Location: /users/admin/recipes.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактировать рецепт</title>
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
        <h1>Редактировать рецепт</h1>
        <form method="POST">
            <div class="form-group">
                <label for="code">Код рецепта:</label>
                <input type="text" id="code" name="code" value="<?php echo htmlspecialchars($recipe['code']); ?>" required>
            </div>
            <div class="form-group">
                <label for="type_id">Тип продукции:</label>
                <select id="type_id" name="type_id" required>
                    <?php foreach ($types as $type): ?>
                        <option value="<?php echo $type['id']; ?>" <?php echo $type['id'] === $recipe['type_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($type['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="description">Описание:</label>
                <textarea id="description" name="description" required><?php echo htmlspecialchars($recipe['description']); ?></textarea>
            </div>
            <div class="form-group">
                <label for="ingredients">Ингредиенты:</label>
                <div id="ingredients-list">
                    <?php foreach ($ingredients as $index => $ingredient): ?>
                        <div class="ingredient">
                            <select name="ingredients[<?php echo $index; ?>][material_id]" required>
                                <?php foreach ($materials as $material): ?>
                                    <option value="<?php echo $material['id']; ?>" <?php echo $ingredient['material_id'] === $material['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($material['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <input type="number" name="ingredients[<?php echo $index; ?>][quantity]" value="<?php echo $ingredient['quantity']; ?>" step="0.01" required>
                            <button type="button" class="btn delete-ingredient">Удалить</button>
                            <input type="hidden" name="ingredients[<?php echo $index; ?>][deleted]" value="0">
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" id="add-ingredient" class="btn">Добавить ингредиент</button>
            </div>
            <div class="form-group">
                <label>Итого кг.:</label>
                <span id="total-kg">0.00</span>
            </div>
            <button type="submit" class="btn">Сохранить изменения</button>
        </form>
    </main>

    <?php include '../../includes/footer.php'; ?>

    <script>
        // Данные о материалах (ингредиентах) с единицами измерения
        const materials = <?php echo json_encode($materials); ?>;

        // Функция для расчёта суммы ингредиентов с единицей измерения "кг."
        function calculateTotalKg() {
            const ingredientsList = document.getElementById('ingredients-list');
            const ingredients = ingredientsList.querySelectorAll('.ingredient');
            let totalKg = 0;

            ingredients.forEach(ingredient => {
                const materialId = ingredient.querySelector('select').value;
                const quantity = parseFloat(ingredient.querySelector('input[type="number"]').value) || 0;

                // Находим материал по ID
                const material = materials.find(m => m.id == materialId);
                if (material && material.unit === 'кг.') {
                    totalKg += quantity;
                }
            });

            // Обновляем значение "Итого кг."
            document.getElementById('total-kg').textContent = totalKg.toFixed(2);
        }

        // Обработчик изменения ингредиентов
        document.getElementById('ingredients-list').addEventListener('change', function (event) {
            if (event.target.tagName === 'SELECT' || event.target.tagName === 'INPUT') {
                calculateTotalKg();
            }
        });

        // Инициализация суммы при загрузке страницы
        document.addEventListener('DOMContentLoaded', function () {
            calculateTotalKg();
        });

        // Динамическое добавление ингредиентов
        document.getElementById('add-ingredient').addEventListener('click', function () {
            const ingredientsList = document.getElementById('ingredients-list');
            const index = ingredientsList.children.length;

            const newIngredient = document.createElement('div');
            newIngredient.className = 'ingredient';
            newIngredient.innerHTML = `
                <select name="ingredients[${index}][material_id]" required>
                    <?php foreach ($materials as $material): ?>
                        <option value="<?php echo $material['id']; ?>"><?php echo htmlspecialchars($material['name']); ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="number" name="ingredients[${index}][quantity]" step="0.01" required>
                <button type="button" class="btn delete-ingredient">Удалить</button>
                <input type="hidden" name="ingredients[${index}][deleted]" value="0">
            `;
            ingredientsList.appendChild(newIngredient);

            // Пересчитываем сумму после добавления нового ингредиента
            calculateTotalKg();
        });

        // Удаление ингредиентов
        document.addEventListener('click', function (event) {
            if (event.target.classList.contains('delete-ingredient')) {
                const ingredientDiv = event.target.closest('.ingredient');
                const deletedInput = ingredientDiv.querySelector('input[type="hidden"]');
                deletedInput.value = '1'; // Помечаем ингредиент как удалённый
                ingredientDiv.style.display = 'none'; // Скрываем ингредиент

                // Пересчитываем сумму после удаления ингредиента
                calculateTotalKg();
            }
        });
    </script>
</body>
</html>