<?php
session_start(); // Начинаем сессию

// Подключение к базе данных
require_once 'includes/config.php';

// Обработка данных формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = $_POST['login'];
    $password = $_POST['password'];

    // Поиск пользователя в базе данных
    $stmt = $pdo->prepare("SELECT * FROM users WHERE login = :login");
    $stmt->execute(['login' => $login]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Авторизация успешна
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['full_name'] = $user['full_name']; // Добавляем ФИО в сессию

        // Перенаправление в личный кабинет
        switch ($user['role']) {
            case 'admin':
                header('Location: users/admin/index.php');
                break;
            case 'manager':
                header('Location: users/manager/index.php');
                break;
            case 'master':
                header('Location: users/master/index.php');
                break;
            case 'accountant':
                header('Location: users/accountant/index.php');
                break;
            case 'worker':
                header('Location: users/worker/index.php');
                break;
            default:
                header('Location: index.php');
                break;
        }
        exit();
    } else {
        // Ошибка авторизации
        $error = "Неверный логин или пароль";
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Авторизация</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body>
    <!-- Шапка -->
    <?php include 'includes/header.php'; ?>

    <!-- Основной контент -->
    <main class="container">
        <!-- Форма авторизации -->
        <section class="auth-section">
            <?php include 'templates/auth/login_form.php'; ?>
        </section>
    </main>

    <!-- Подвал -->
    <?php include 'includes/footer.php'; ?>

    <!-- Скрипты -->
    <script src="assets/js/main.js"></script>
    <script src="assets/js/auth.js"></script>
</body>
</html>