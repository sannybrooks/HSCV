<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit();
}

require_once 'includes/config.php';

$notificationId = $_GET['id'] ?? null;
if (!$notificationId) {
    header('Location: /users/admin/index.php');
    exit();
}

// Отметка уведомления как прочитанного
$sql = "UPDATE notifications SET is_read = 1 WHERE id = :id AND user_id = :user_id";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    'id' => $notificationId,
    'user_id' => $_SESSION['user_id']
]);

header('Location: /users/admin/index.php');
exit();
?>