<?php
session_start();
require_once '../../includes/config.php';

// Проверка авторизации и роли
if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit();
}

// Разрешенные роли
$allowedRoles = ['admin', 'manager', 'master'];
if (!in_array($_SESSION['role'], $allowedRoles)) {
    header('Location: /access_denied.php'); // Страница с сообщением о запрете доступа
    exit();
}

// Получаем ID текущего пользователя и его роль
$currentUserId = $_SESSION['user_id'];
$currentUserRole = $_SESSION['role'];

// Получаем выбранную неделю из параметра
$selectedWeek = $_GET['week'] ?? date('Y-m-d');
$startOfWeek = date('Y-m-d', strtotime('monday this week', strtotime($selectedWeek)));
$endOfWeek = date('Y-m-d', strtotime('sunday this week', strtotime($selectedWeek)));

// Получаем выбранного мастера (если роль admin или manager)
$selectedMasterId = $_GET['master_id'] ?? null;
if ($currentUserRole === 'master') {
    $selectedMasterId = $currentUserId; // Мастер может редактировать только свои планы
}

// Получаем список мастеров (для admin и manager)
$masters = [];
if (in_array($currentUserRole, ['admin', 'manager'])) {
    $sql = "SELECT id, full_name FROM users WHERE role = 'master'";
    $stmt = $pdo->query($sql);
    $masters = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Получаем планы производства на выбранную неделю
$plans = [];
if ($selectedMasterId) {
    $sql = "SELECT pp.id, p.id AS product_id, CONCAT(p.brand, ' ', p.name) AS product_name, pp.planned_quantity, pp.date 
            FROM production_plans pp
            JOIN products p ON pp.product_id = p.id
            WHERE pp.master_id = :master_id
            AND pp.date BETWEEN :start_date AND :end_date
            ORDER BY pp.date";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'master_id' => $selectedMasterId,
        'start_date' => $startOfWeek,
        'end_date' => $endOfWeek
    ]);
    $plans = $stmt->fetchAll();

    // Получаем фактические данные за выбранную неделю
    $sql = "SELECT pl.production_plan_id, SUM(pl.actual_quantity) AS total_actual 
            FROM production_logs pl
            WHERE pl.date BETWEEN :start_date AND :end_date
            GROUP BY pl.production_plan_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'start_date' => $startOfWeek,
        'end_date' => $endOfWeek
    ]);
    $logs = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // production_plan_id => total_actual

    // Добавляем остаток плана в массив $plans
    foreach ($plans as &$plan) {
        $plan['remaining'] = $plan['planned_quantity'] - ($logs[$plan['id']] ?? 0);
    }
    unset($plan); // Убираем ссылку на последний элемент
}

// Обработка формы добавления/редактирования плана
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = $_POST['product_id'];
    $plannedQuantity = $_POST['planned_quantity'];
    $date = $_POST['date'];

    // Проверяем, существует ли уже план на эту дату
    $sql = "SELECT id FROM production_plans WHERE master_id = :master_id AND product_id = :product_id AND date = :date";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'master_id' => $selectedMasterId,
        'product_id' => $productId,
        'date' => $date
    ]);
    $existingPlan = $stmt->fetch();

    if ($existingPlan) {
        // Обновляем существующий план
        $sql = "UPDATE production_plans SET planned_quantity = :planned_quantity WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'planned_quantity' => $plannedQuantity,
            'id' => $existingPlan['id']
        ]);
    } else {
        // Добавляем новый план
        $sql = "INSERT INTO production_plans (master_id, product_id, planned_quantity, date) 
                VALUES (:master_id, :product_id, :planned_quantity, :date)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'master_id' => $selectedMasterId,
            'product_id' => $productId,
            'planned_quantity' => $plannedQuantity,
            'date' => $date
        ]);
    }

    // Перенаправляем, чтобы избежать повторной отправки формы
    header("Location: edit_plan.php?week=$selectedWeek&master_id=$selectedMasterId");
    exit();
}

// Получаем список продуктов
$sql = "SELECT id, CONCAT(brand, ' ', name) AS product_name FROM products";
$stmt = $pdo->query($sql);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактирование плана производства</title>
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body>
    <!-- Шапка -->
    <?php include '../../includes/header.php'; ?>

    <!-- Боковое меню -->
    <?php include '../../includes/sidebar.php'; ?>

    <!-- Основной контент -->
    <main class="main-content">
    <h1>Редактирование плана производства</h1>

    <!-- Навигация по неделям -->
    <div class="week-navigation">
        <a href="edit_plan.php?week=<?= date('Y-m-d', strtotime('-1 week', strtotime($startOfWeek))) ?>&master_id=<?= $selectedMasterId ?>" class="btn">Прошлая неделя</a>
        <a href="edit_plan.php?week=<?= date('Y-m-d') ?>&master_id=<?= $selectedMasterId ?>" class="btn">Текущая неделя</a>
        <a href="edit_plan.php?week=<?= date('Y-m-d', strtotime('+1 week', strtotime($startOfWeek))) ?>&master_id=<?= $selectedMasterId ?>" class="btn">Следующая неделя</a>
    </div>

    <!-- Выбор мастера (для admin и manager) -->
    <?php if (in_array($currentUserRole, ['admin', 'manager'])): ?>
        <form method="GET" action="edit_plan.php">
            <label for="master_id">Мастер:</label>
            <select name="master_id" id="master_id" onchange="this.form.submit()">
                <option value="">Выберите мастера</option>
                <?php foreach ($masters as $master): ?>
                    <option value="<?= $master['id'] ?>" <?= $selectedMasterId == $master['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($master['full_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="hidden" name="week" value="<?= $selectedWeek ?>">
        </form>
    <?php endif; ?>

    <!-- Форма добавления/редактирования плана -->
    <?php if ($selectedMasterId): ?>
        <h2>План на неделю с <?= date('d.m.Y', strtotime($startOfWeek)) ?> по <?= date('d.m.Y', strtotime($endOfWeek)) ?></h2>
        <form method="POST" action="edit_plan.php?week=<?= $selectedWeek ?>&master_id=<?= $selectedMasterId ?>">
            <label for="product_id">Продукт:</label>
            <select name="product_id" id="product_id" required>
                <option value="">Выберите продукт</option>
                <?php foreach ($products as $product): ?>
                    <option value="<?= $product['id'] ?>"><?= htmlspecialchars($product['product_name']) ?></option>
                <?php endforeach; ?>
            </select>

            <label for="planned_quantity">Плановое количество:</label>
            <input type="number" name="planned_quantity" id="planned_quantity" min="0" required>

            <label for="date">Дата:</label>
            <input type="date" name="date" id="date" value="<?= $startOfWeek ?>" min="<?= $startOfWeek ?>" max="<?= $endOfWeek ?>" required>

            <button type="submit" class="btn">Сохранить</button>
        </form>

        <!-- Таблица с текущими планами -->
        <table>
            <thead>
                <tr>
                    <th>Продукт</th>
                    <th>Плановое количество</th>
                    <th>Остаток плана</th> <!-- Новая колонка -->
                    <th>Дата</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($plans as $plan): ?>
                    <tr>
                        <td><?= htmlspecialchars($plan['product_name']) ?></td>
                        <td><?= htmlspecialchars($plan['planned_quantity']) ?></td>
                        <td><?= htmlspecialchars($plan['remaining']) ?></td> <!-- Остаток плана -->
                        <td><?= date('d.m.Y', strtotime($plan['date'])) ?></td>
                        <td>
                            <a href="edit_plan.php?week=<?= $selectedWeek ?>&master_id=<?= $selectedMasterId ?>&edit=<?= $plan['id'] ?>" class="btn">Редактировать</a>
                            <a href="delete_plan.php?id=<?= $plan['id'] ?>&week=<?= $selectedWeek ?>&master_id=<?= $selectedMasterId ?>" class="btn btn-danger">Удалить</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    </main>
</body>
</html>