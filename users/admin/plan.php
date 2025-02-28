<?php
session_start();
require_once '../../includes/config.php';

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit();
}

// Получаем ID пользователя и его роль
$userId = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Определяем текущую неделю (по умолчанию)
$currentWeek = $_GET['week'] ?? date('Y-m-d'); // Если неделя не указана, используем текущую дату
$startOfWeek = date('Y-m-d', strtotime('monday this week', strtotime($currentWeek)));
$endOfWeek = date('Y-m-d', strtotime('sunday this week', strtotime($currentWeek)));

// Навигация по неделям
$prevWeek = date('Y-m-d', strtotime('-1 week', strtotime($startOfWeek)));
$nextWeek = date('Y-m-d', strtotime('+1 week', strtotime($startOfWeek)));

// Получаем планы производства на выбранную неделю
if ($role === 'admin' || $role === 'manager') {
    // Для администратора и менеджера получаем данные для всех мастеров
    $sql = "SELECT pp.id, p.id AS product_id, CONCAT(p.brand, ' ', p.name) AS product_name, pp.planned_quantity, pp.date, u.full_name AS master_name
            FROM production_plans pp
            JOIN products p ON pp.product_id = p.id
            JOIN users u ON pp.master_id = u.id
            WHERE pp.date BETWEEN :start_date AND :end_date
            ORDER BY u.full_name, pp.date";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'start_date' => $startOfWeek,
        'end_date' => $endOfWeek
    ]);
} else {
    // Для мастера получаем данные только для текущего мастера
    $sql = "SELECT pp.id, p.id AS product_id, CONCAT(p.brand, ' ', p.name) AS product_name, pp.planned_quantity, pp.date
            FROM production_plans pp
            JOIN products p ON pp.product_id = p.id
            WHERE pp.master_id = :master_id
            AND pp.date BETWEEN :start_date AND :end_date
            ORDER BY pp.date";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'master_id' => $userId,
        'start_date' => $startOfWeek,
        'end_date' => $endOfWeek
    ]);
}
$plans = $stmt->fetchAll();

// Группируем планы по продукту и мастеру (если администратор или менеджер)
$groupedPlans = [];
foreach ($plans as $plan) {
    $productName = $plan['product_name'];
    $masterName = ($role === 'admin' || $role === 'manager') ? $plan['master_name'] : null;

    $key = ($role === 'admin' || $role === 'manager') ? $masterName . '|' . $productName : $productName;

    if (!isset($groupedPlans[$key])) {
        $groupedPlans[$key] = [
            'master_name' => $masterName,
            'product_id' => $plan['product_id'],
            'product_name' => $productName,
            'planned_quantity' => $plan['planned_quantity'],
            'actual_quantities' => [0, 0, 0, 0, 0, 0, 0], // Пн-Вс
            'remaining' => $plan['planned_quantity']
        ];
    }
}

// Получаем фактические данные за выбранную неделю
if ($role === 'admin' || $role === 'manager') {
    // Для администратора и менеджера получаем данные для всех мастеров
    $sql = "SELECT pl.production_plan_id, pl.actual_quantity, pl.date, p.id AS product_id, CONCAT(p.brand, ' ', p.name) AS product_name, u.full_name AS master_name
            FROM production_logs pl
            JOIN production_plans pp ON pl.production_plan_id = pp.id
            JOIN products p ON pp.product_id = p.id
            JOIN users u ON pp.master_id = u.id
            WHERE pl.date BETWEEN :start_date AND :end_date";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'start_date' => $startOfWeek,
        'end_date' => $endOfWeek
    ]);
} else {
    // Для мастера получаем данные только для текущего мастера
    $sql = "SELECT pl.production_plan_id, pl.actual_quantity, pl.date, p.id AS product_id, CONCAT(p.brand, ' ', p.name) AS product_name
            FROM production_logs pl
            JOIN production_plans pp ON pl.production_plan_id = pp.id
            JOIN products p ON pp.product_id = p.id
            WHERE pp.master_id = :master_id
            AND pl.date BETWEEN :start_date AND :end_date";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'master_id' => $userId,
        'start_date' => $startOfWeek,
        'end_date' => $endOfWeek
    ]);
}
$logs = $stmt->fetchAll();

// Заполняем фактические данные
foreach ($logs as $log) {
    $dayOfWeek = date('N', strtotime($log['date'])) - 1; // 0 (Пн) - 6 (Вс)
    $productName = $log['product_name'];
    $masterName = ($role === 'admin' || $role === 'manager') ? $log['master_name'] : null;

    $key = ($role === 'admin' || $role === 'manager') ? $masterName . '|' . $productName : $productName;

    if (isset($groupedPlans[$key])) {
        $groupedPlans[$key]['actual_quantities'][$dayOfWeek] += $log['actual_quantity'];
        $groupedPlans[$key]['remaining'] -= $log['actual_quantity'];
    }
}

// Формируем заголовки для дней недели
$weekDays = [];
for ($i = 0; $i < 7; $i++) {
    $date = date('d.m.Y', strtotime($startOfWeek . " +$i days"));
    $weekDays[] = $date;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Главная страница <?= $role === 'admin' ? 'администратора' : ($role === 'manager' ? 'менеджера' : 'мастера') ?></title>
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Шапка -->
    <?php include '../../includes/header.php'; ?>

    <!-- Боковое меню -->
    <?php include '../../includes/sidebar.php'; ?>

    <!-- Основной контент -->
    <main class="main-content">
        <h1>Главная страница <?= $role === 'admin' ? 'администратора' : ($role === 'manager' ? 'менеджера' : 'мастера') ?></h1>

        <!-- Навигация по неделям -->
        <div class="week-navigation">
            <a href="plan.php?week=<?= $prevWeek ?>" class="btn">Прошлая неделя</a>
            <a href="plan.php?week=<?= date('Y-m-d') ?>" class="btn">Текущая неделя</a>
            <a href="plan.php?week=<?= $nextWeek ?>" class="btn">Следующая неделя</a>
        </div>

        <!-- Таблица с планом и фактическими данными -->
        <form id="productionForm" action="save_facts.php" method="POST">
            <input type="hidden" name="week" value="<?= $startOfWeek ?>">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <?php if ($role === 'admin' || $role === 'manager'): ?>
                                <th>Мастер</th>
                            <?php endif; ?>
                            <th>Продукт</th>
                            <th>План</th>
                            <?php foreach ($weekDays as $day): ?>
                                <th><?= $day ?></th>
                            <?php endforeach; ?>
                            <th>Остаток</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($groupedPlans as $key => $data): ?>
                            <tr data-product="<?= htmlspecialchars($data['product_name']) ?>">
                                <?php if ($role === 'admin' || $role === 'manager'): ?>
                                    <td><?= htmlspecialchars($data['master_name']) ?></td>
                                <?php endif; ?>
                                <td><?= htmlspecialchars($data['product_name']) ?></td>
                                <td><?= htmlspecialchars($data['planned_quantity']) ?></td>
                                <?php for ($i = 0; $i < 7; $i++): ?>
                                    <td>
                                        <div class="input-report-container">
                                            <input type="number" 
                                                name="actual_quantities[<?= htmlspecialchars($key) ?>][<?= $i ?>]" 
                                                value="<?= $data['actual_quantities'][$i] ?>"
                                                min="0"
                                                class="actual-input"
                                                data-day="<?= $i ?>"
                                                <?= ($role === 'admin' || $role === 'manager') ? 'readonly' : '' ?>>
                                        </div>
                                    </td>
                                <?php endfor; ?>
                                <td class="remaining"><?= htmlspecialchars($data['remaining']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php if ($role !== 'admin' && $role !== 'manager'): ?>
                <div class="form-actions">
                    <button type="submit" class="btn">Сохранить</button>
                </div>
            <?php endif; ?>
        </form>
    </main>

    <!-- Подвал -->
    <?php include '../../includes/footer.php'; ?>

    <!-- Скрипты -->
    <script src="/assets/js/main.js"></script>
    <script>
        // Функция для пересчета остатка
        function updateRemaining(row) {
            const plannedQuantity = parseInt(row.querySelector('td:nth-child(2)').textContent, 10);
            const inputs = row.querySelectorAll('.actual-input');
            let totalActual = 0;

            inputs.forEach(input => {
                totalActual += parseInt(input.value || 0, 10);
            });

            const remaining = plannedQuantity - totalActual;
            row.querySelector('.remaining').textContent = remaining;
        }

        // Обработка изменения значений в полях ввода
        document.querySelectorAll('.actual-input').forEach(input => {
            input.addEventListener('input', function () {
                const row = this.closest('tr');
                updateRemaining(row);
            });
        });
    </script>
</body>
</html>