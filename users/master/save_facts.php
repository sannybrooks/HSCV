<?php
session_start();
require_once '../../includes/config.php';

// Проверка авторизации и роли
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'master') {
    header('Location: /login.php');
    exit();
}

// Получаем данные из формы
$week = $_POST['week']; // Неделя, для которой сохраняются данные
$actualQuantities = $_POST['actual_quantities']; // Данные по выпуску

// Перебираем данные по продуктам
foreach ($actualQuantities as $productName => $days) {
    // Получаем ID плана производства для данного продукта и недели
    $sql = "SELECT pp.id 
            FROM production_plans pp
            JOIN products p ON pp.product_id = p.id
            WHERE CONCAT(p.brand, ' ', p.name) = :product_name
            AND pp.master_id = :master_id
            AND pp.date = :start_date"; // План на неделю привязан к началу недели
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'product_name' => $productName,
        'master_id' => $_SESSION['user_id'],
        'start_date' => $week // План на неделю привязан к началу недели
    ]);
    $plan = $stmt->fetch();

    if ($plan) {
        $productionPlanId = $plan['id'];

        // Перебираем данные по дням недели (0-6, Пн-Вс)
        for ($dayIndex = 0; $dayIndex < 7; $dayIndex++) {
            // Определяем дату для текущего дня недели
            $date = date('Y-m-d', strtotime($week . " +$dayIndex days"));

            // Получаем количество продукции для текущего дня
            $quantity = $days[$dayIndex] ?? 0; // Если значение отсутствует, используем 0

            // Проверяем, существует ли уже запись в production_logs для этого плана и даты
            $sql = "SELECT id FROM production_logs 
                    WHERE production_plan_id = :production_plan_id 
                    AND date = :date";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'production_plan_id' => $productionPlanId,
                'date' => $date
            ]);
            $log = $stmt->fetch();

            if ($quantity > 0) {
                // Если количество продукции больше нуля, обновляем или создаем запись
                if ($log) {
                    // Если запись существует, обновляем её
                    $sql = "UPDATE production_logs 
                            SET actual_quantity = :actual_quantity
                            WHERE id = :id";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        'actual_quantity' => $quantity,
                        'id' => $log['id']
                    ]);
                } else {
                    // Если записи нет, создаем новую
                    $sql = "INSERT INTO production_logs (production_plan_id, actual_quantity, date)
                            VALUES (:production_plan_id, :actual_quantity, :date)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        'production_plan_id' => $productionPlanId,
                        'actual_quantity' => $quantity,
                        'date' => $date
                    ]);
                }
            } else {
                // Если количество продукции равно нулю, удаляем запись, если она существует
                if ($log) {
                    $sql = "DELETE FROM production_logs 
                            WHERE id = :id";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        'id' => $log['id']
                    ]);
                }
            }
        }
    }
}

// Перенаправляем обратно на страницу мастера
header('Location: plan.php?week=' . urlencode($week));
exit();