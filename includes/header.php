<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Система управления цехом</title>
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header>
        <div class="header-container">
            <!-- Логотип -->
            <div class="logo">
                <img src="/assets/images/logo.png" alt="Логотип организации">
                <span>Цех строительных смесей</span>
            </div>

            <!-- Навигация -->
            <nav>
                <ul>
                    <li><a href="/index.php">Главная</a></li>
                    <li><a href="#products">Продукция</a></li>
                    <li><a href="#about">О нас</a></li>
                </ul>
            </nav>

            <!-- Блок с ФИО и ролью пользователя -->
            <div class="user-info">
            <span>Вы вошли как:</span>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <span><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                    <span>(<?php echo htmlspecialchars($_SESSION['role']); ?>)</span>
                <?php endif; ?>
            </div>

            <!-- Кнопка входа/выхода -->
            <div class="auth">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="/logout.php" class="btn">Выйти</a>
                <?php else: ?>
                    <a href="/login.php" class="btn">Войти</a>
                <?php endif; ?>
            </div>
        </div>
    </header>
</body>
</html>