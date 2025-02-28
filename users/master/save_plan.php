<?php
session_start();
require_once '../../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'master') {
    header('Location: /login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $masterId = $_SESSION['user_id'];
    $productId = $_POST['product_id'];
    $plannedQuantity = $_POST['planned_quantity'];
    $date = $_POST['date'];

    $sql = "INSERT INTO production_plans (master_id, product_id, planned_quantity, date) 
            VALUES (:master_id, :product_id, :planned_quantity, :date)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'master_id' => $masterId,
        'product_id' => $productId,
        'planned_quantity' => $plannedQuantity,
        'date' => $date
    ]);

    header('Location: plan.php');
    exit();
}
?>