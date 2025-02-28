<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /login.php');
    exit();
}

require_once '../../includes/config.php';

// Получаем данные для графиков
// 1. Выпуск за день
$sql = "SELECT SUM(actual_quantity) AS total FROM production_logs WHERE DATE(date) = CURDATE()";
$dailyProduction = $pdo->query($sql)->fetchColumn();

// 2. Выпуск за месяц
$sql = "SELECT SUM(actual_quantity) AS total 
        FROM production_logs 
        WHERE MONTH(date) = MONTH(CURDATE())";
$monthlyProduction = $pdo->query($sql)->fetchColumn();

// 3. Расход сырья за неделю (с понедельника по воскресенье)
$sql = "SELECT 
            SUM(CASE WHEN material_id = 1 THEN quantity ELSE 0 END) AS cement,
            SUM(CASE WHEN material_id = 6 THEN quantity ELSE 0 END) AS sand,
            SUM(CASE WHEN material_id = 7 THEN quantity ELSE 0 END) AS flour
        FROM material_movements 
        WHERE movement_type = 'расход' 
        AND movement_date BETWEEN DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY) AND CURDATE()";
$weeklyMaterialUsage = $pdo->query($sql)->fetch();

// 4. Текущие остатки цемента и муки по складам
$sql = "SELECT 
            w.name AS warehouse_name,
            SUM(CASE WHEN material_id = 1 THEN quantity ELSE 0 END) AS cement,
            SUM(CASE WHEN material_id = 7 THEN quantity ELSE 0 END) AS flour
        FROM material_stocks ms
        JOIN warehouses w ON ms.warehouse_id = w.id
        WHERE material_id IN (1, 7)
        GROUP BY w.name";
$currentStocks = $pdo->query($sql)->fetchAll();

// 5. Топ 5 выпускаемых продуктов за месяц
$sql = "SELECT 
            p.name AS product_name,
            SUM(pl.actual_quantity) AS total_quantity
        FROM production_logs pl
        JOIN production_plans pp ON pl.production_plan_id = pp.id
        JOIN products p ON pp.product_id = p.id
        WHERE MONTH(pl.date) = MONTH(CURDATE())
        GROUP BY p.name
        ORDER BY total_quantity DESC
        LIMIT 5";
$topProducts = $pdo->query($sql)->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Главная страница администратора</title>
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <!-- Шапка -->
    <?php include '../../includes/header.php'; ?>

    <!-- Боковое меню -->
    <?php include '../../includes/sidebar.php'; ?>

    <!-- Основной контент -->
    <main class="main-content">
        <h1>Статистика</h1>

        <div class="actions">
            <a href="/users/admin/export_excel.php" class="btn">Экспорт в Excel</a>
            <a href="/users/admin/export_pdf.php" class="btn">Экспорт в PDF</a>
        </div>

        <!-- Строка: Выпуск за месяц -->
        <div class="chart-container full-width">
            <h2>Выпуск за месяц: <?php echo htmlspecialchars($monthlyProduction); ?> мешков</h2>
        </div>

        <!-- Контейнер для двух столбцов -->
        <div class="chart-container-row">
            <!-- Первый столбец -->
            <div class="chart-column">
                <!-- Выпуск за день -->
                <div class="chart-container">
                    <h2>Выпуск за день: <?php echo htmlspecialchars($dailyProduction); ?> мешков</h2>
                    <canvas id="dailyProductionChart"></canvas>
                </div>

                <!-- Топ 5 выпускаемых продуктов -->
                <div class="chart-container">
                    <h2>Топ 5 выпускаемых продуктов</h2>
                    <canvas id="topProductsChart"></canvas>
                </div>
            </div>

            <!-- Второй столбец -->
            <div class="chart-column">
                <!-- Расход сырья за неделю -->
                <div class="chart-container">
                    <h2>Расход сырья за неделю</h2>
                    <canvas id="weeklyMaterialUsageChart"></canvas>
                </div>

                <!-- Текущие остатки цемента и муки по складам -->
                <div class="chart-container">
                    <h2>Текущие остатки цемента и муки по складам</h2>
                    <canvas id="currentStocksChart"></canvas>
                </div>
            </div>
        </div>
    </main>

    <!-- Подвал -->
    <?php include '../../includes/footer.php'; ?>

    <!-- Скрипты для графиков -->
    <script>
        // Функция для обновления данных
        function updateCharts() {
            fetch('/api/get_statistics.php')
                .then(response => response.json())
                .then(data => {
                    // Обновляем данные для графика "Выпуск за день"
                    dailyProductionChart.data.datasets[0].data = [data.dailyProduction];
                    dailyProductionChart.update();

                    // Обновляем данные для графика "Расход сырья за неделю"
                    weeklyMaterialUsageChart.data.datasets[0].data = [
                        data.weeklyMaterialUsage.cement,
                        data.weeklyMaterialUsage.sand,
                        data.weeklyMaterialUsage.flour
                    ];
                    weeklyMaterialUsageChart.update();

                    // Обновляем данные для графика "Текущие остатки"
                    currentStocksChart.data.datasets[0].data = data.currentStocks.map(stock => stock.cement);
                    currentStocksChart.data.datasets[1].data = data.currentStocks.map(stock => stock.flour);
                    currentStocksChart.update();

                    // Обновляем данные для графика "Топ 5 выпускаемых продуктов"
                    topProductsChart.data.labels = data.topProducts.map(product => product.product_name);
                    topProductsChart.data.datasets[0].data = data.topProducts.map(product => product.total_quantity);
                    topProductsChart.update();
                });
        }

        // Обновляем данные каждые 5 минут
        setInterval(updateCharts, 300000);

        // Первоначальная загрузка данных
        updateCharts();
    </script>
    <script>
        // Выпуск за день
        const dailyProductionChart = new Chart(document.getElementById('dailyProductionChart'), {
            type: 'bar',
            data: {
                labels: ['Сегодня'],
                datasets: [{
                    label: 'Выпуск (мешки)',
                    data: [<?php echo $dailyProduction; ?>],
                    backgroundColor: '#ff8c42',
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Расход сырья за неделю
        const weeklyMaterialUsageChart = new Chart(document.getElementById('weeklyMaterialUsageChart'), {
            type: 'bar',
            data: {
                labels: ['Цемент', 'Песок', 'Мука'],
                datasets: [{
                    label: 'Расход (кг)',
                    data: [
                        <?php echo $weeklyMaterialUsage['cement']; ?>,
                        <?php echo $weeklyMaterialUsage['sand']; ?>,
                        <?php echo $weeklyMaterialUsage['flour']; ?>
                    ],
                    backgroundColor: ['#ff8c42', '#e67e22', '#d35400'],
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Текущие остатки цемента и муки по складам
        const currentStocksChart = new Chart(document.getElementById('currentStocksChart'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($currentStocks, 'warehouse_name')); ?>,
                datasets: [
                    {
                        label: 'Цемент (кг)',
                        data: <?php echo json_encode(array_column($currentStocks, 'cement')); ?>,
                        backgroundColor: '#ff8c42',
                    },
                    {
                        label: 'Мука (кг)',
                        data: <?php echo json_encode(array_column($currentStocks, 'flour')); ?>,
                        backgroundColor: '#e67e22',
                    }
                ]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Топ 5 выпускаемых продуктов (круговая диаграмма)
        const topProductsChart = new Chart(document.getElementById('topProductsChart'), {
            type: 'pie', // Изменяем тип на "pie"
            data: {
                labels: <?php echo json_encode(array_column($topProducts, 'product_name')); ?>,
                datasets: [{
                    label: 'Количество выпущенных продуктов',
                    data: <?php echo json_encode(array_column($topProducts, 'total_quantity')); ?>,
                    backgroundColor: [
                        '#ff8c42', // Цвет для первого продукта
                        '#e67e22', // Цвет для второго продукта
                        '#d35400', // Цвет для третьего продукта
                        '#a84300', // Цвет для четвертого продукта
                        '#7a2e00'  // Цвет для пятого продукта
                    ],
                    borderColor: '#fff', // Цвет границы секторов
                    borderWidth: 2 // Толщина границы
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom', // Расположение легенды
                        labels: {
                            font: {
                                size: 14 // Размер шрифта легенды
                            }
                        }
                    },
                    tooltip: {
                        enabled: true, // Включить подсказки
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += context.raw + ' мешков'; // Добавляем единицы измерения
                                return label;
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>