<?php
session_start();
require_once '../../includes/config.php';

// Проверка авторизации и роли
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'master') {
    header('Location: /login.php');
    exit();
}

// Получаем ID мастера
$masterId = $_SESSION['user_id'];

// Определяем текущую неделю (по умолчанию)
$currentWeek = $_GET['week'] ?? date('Y-m-d'); // Если неделя не указана, используем текущую дату
$startOfWeek = date('Y-m-d', strtotime('monday this week', strtotime($currentWeek)));
$endOfWeek = date('Y-m-d', strtotime('sunday this week', strtotime($currentWeek)));

// Навигация по неделям
$prevWeek = date('Y-m-d', strtotime('-1 week', strtotime($startOfWeek)));
$nextWeek = date('Y-m-d', strtotime('+1 week', strtotime($startOfWeek)));

// Получаем планы производства на выбранную неделю
$sql = "SELECT pp.id, p.id AS product_id, CONCAT(p.brand, ' ', p.name) AS product_name, pp.planned_quantity, pp.date 
        FROM production_plans pp
        JOIN products p ON pp.product_id = p.id
        WHERE pp.master_id = :master_id
        AND pp.date BETWEEN :start_date AND :end_date
        ORDER BY pp.date";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    'master_id' => $masterId,
    'start_date' => $startOfWeek,
    'end_date' => $endOfWeek
]);
$plans = $stmt->fetchAll();

// Группируем планы по продукту
$groupedPlans = [];
foreach ($plans as $plan) {
    $productName = $plan['product_name'];
    if (!isset($groupedPlans[$productName])) {
        $groupedPlans[$productName] = [
            'product_id' => $plan['product_id'], // Добавляем product_id
            'planned_quantity' => $plan['planned_quantity'],
            'actual_quantities' => [0, 0, 0, 0, 0, 0, 0], // Пн-Вс
            'remaining' => $plan['planned_quantity']
        ];
    }
}

// Получаем фактические данные за выбранную неделю
$sql = "SELECT pl.production_plan_id, pl.actual_quantity, pl.date, p.id AS product_id, CONCAT(p.brand, ' ', p.name) AS product_name
        FROM production_logs pl
        JOIN production_plans pp ON pl.production_plan_id = pp.id
        JOIN products p ON pp.product_id = p.id
        WHERE pp.master_id = :master_id
        AND pl.date BETWEEN :start_date AND :end_date";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    'master_id' => $masterId,
    'start_date' => $startOfWeek,
    'end_date' => $endOfWeek
]);
$logs = $stmt->fetchAll();

// Заполняем фактические данные
foreach ($logs as $log) {
    $dayOfWeek = date('N', strtotime($log['date'])) - 1; // 0 (Пн) - 6 (Вс)
    $productName = $log['product_name'];
    if (isset($groupedPlans[$productName])) {
        $groupedPlans[$productName]['actual_quantities'][$dayOfWeek] += $log['actual_quantity'];
        $groupedPlans[$productName]['remaining'] -= $log['actual_quantity'];
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
    <title>Главная страница мастера</title>
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
        <h1>Главная страница мастера</h1>

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
                            <th>Продукт</th>
                            <th>План</th>
                            <?php foreach ($weekDays as $day): ?>
                                <th><?= $day ?></th>
                            <?php endforeach; ?>
                            <th>Остаток</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($groupedPlans as $productName => $data): ?>
                            <tr data-product="<?= htmlspecialchars($productName) ?>">
                                <td><?= htmlspecialchars($productName) ?></td>
                                <td><?= htmlspecialchars($data['planned_quantity']) ?></td>
                                <?php for ($i = 0; $i < 7; $i++): ?>
                                    <td>
                                        <div class="input-report-container">
                                            <input type="number" 
                                                name="actual_quantities[<?= htmlspecialchars($productName) ?>][<?= $i ?>]" 
                                                value="<?= $data['actual_quantities'][$i] ?>"
                                                min="0"
                                                class="actual-input"
                                                data-day="<?= $i ?>">
                                            <button type="button" 
                                                    class="btn-report" 
                                                    data-product-id="<?= $data['product_id'] ?>"
                                                    data-date="<?= date('Y-m-d', strtotime($startOfWeek . " +$i days")) ?>" 
                                                    onclick="openReport(this)">
                                                <i class="fas fa-file-alt"></i>
                                            </button>
                                        </div>
                                    </td>
                                <?php endfor; ?>
                                <td class="remaining"><?= htmlspecialchars($data['remaining']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn">Сохранить</button>
                <button type="button" class="btn" onclick="openMaterialsList('<?= $startOfWeek ?>')">
                    Список необходимого сырья и материалов
                </button>
            </div>
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

        // Функция для открытия отчета
        function openReport(button) {
            const productId = button.getAttribute('data-product-id');
            const date = button.getAttribute('data-date');

            // Открываем отчет в новом окне
            const reportUrl = `report.php?product_id=${productId}&date=${date}`;
            window.open(reportUrl, '_blank', 'width=800,height=800');
        }

        function openMaterialsList(startOfWeek) {
            const materialsUrl = `materials_list.php?week=${startOfWeek}`;
            window.open(materialsUrl, '_blank', 'width=800,height=800');
        }
    </script>
</body>
</html>
