<?php
session_start();
require_once '../../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'master') {
    header('Location: /login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productionPlanId = $_POST['production_plan_id'];
    $actualQuantity = $_POST['actual_quantity'];
    $date = $_POST['date'];

    $sql = "INSERT INTO production_logs (production_plan_id, actual_quantity, date) 
            VALUES (:production_plan_id, :actual_quantity, :date)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'production_plan_id' => $productionPlanId,
        'actual_quantity' => $actualQuantity,
        'date' => $date
    ]);

    header('Location: logs.php');
    exit();
}
?>