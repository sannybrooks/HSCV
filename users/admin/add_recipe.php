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

// Получаем список материалов
$materials = $pdo->query("SELECT id, name FROM materials")->fetchAll();

// Обработка формы добавления рецепта
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = $_POST['code'];
    $typeId = $_POST['type_id'];
    $description = $_POST['description'];
    $ingredients = $_POST['ingredients'];

    // Добавляем рецепт
    $sql = "INSERT INTO recipes (code, description, type_id) VALUES (:code, :description, :type_id)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'code' => $code,
        'description' => $description,
        'type_id' => $typeId
    ]);

    $recipeId = $pdo->lastInsertId();

    // Добавляем ингредиенты
    foreach ($ingredients as $ingredient) {
        $sql = "INSERT INTO recipe_ingredients (recipe_id, material_id, quantity) 
                VALUES (:recipe_id, :material_id, :quantity)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'recipe_id' => $recipeId,
            'material_id' => $ingredient['material_id'],
            'quantity' => $ingredient['quantity']
        ]);
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
    <title>Добавить рецепт</title>
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
        <h1>Добавить рецепт</h1>
        <form method="POST">
            <div class="form-group">
                <label for="code">Код рецепта:</label>
                <input type="text" id="code" name="code" required>
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
                <label for="description">Описание:</label>
                <textarea id="description" name="description" required></textarea>
            </div>
            <div class="form-group">
                <label for="ingredients">Ингредиенты:</label>
                <div id="ingredients-list">
                    <div class="ingredient">
                        <select name="ingredients[0][material_id]" required>
                            <?php foreach ($materials as $material): ?>
                                <option value="<?php echo $material['id']; ?>"><?php echo htmlspecialchars($material['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="number" name="ingredients[0][quantity]" step="0.01" required>
                    </div>
                </div>
                <button type="button" id="add-ingredient" class="btn">Добавить ингредиент</button>
            </div>
            <div class="form-group">
                <label>Итого кг.:</label>
                <span id="total-kg">0.00</span>
            </div>
            <button type="submit" class="btn">Сохранить</button>
        </form>
    </main>

    <?php include '../../includes/footer.php'; ?>

    <script>
        const materials = <?php echo json_encode($materials); ?>;

        function calculateTotalKg() {
            const ingredientsList = document.getElementById('ingredients-list');
            const ingredients = ingredientsList.querySelectorAll('.ingredient');
            let totalKg = 0;

            ingredients.forEach(ingredient => {
                const materialId = ingredient.querySelector('select').value;
                const quantity = parseFloat(ingredient.querySelector('input[type="number"]').value) || 0;

                const material = materials.find(m => m.id == materialId);
                if (material && material.unit === 'кг.') {
                    totalKg += quantity;
                }
            });

            document.getElementById('total-kg').textContent = totalKg.toFixed(2);
        }

        document.getElementById('ingredients-list').addEventListener('change', function (event) {
            if (event.target.tagName === 'SELECT' || event.target.tagName === 'INPUT') {
                calculateTotalKg();
            }
        });

        document.addEventListener('DOMContentLoaded', function () {
            calculateTotalKg();
        });

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

            calculateTotalKg();
        });

        document.addEventListener('click', function (event) {
            if (event.target.classList.contains('delete-ingredient')) {
                const ingredientDiv = event.target.closest('.ingredient');
                const deletedInput = ingredientDiv.querySelector('input[type="hidden"]');
                deletedInput.value = '1';
                ingredientDiv.style.display = 'none';

                calculateTotalKg();
            }
        });
    </script>
</body>
</html>