<?php if (isset($error)): ?>
    <div class="error-message">
        <?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>

<form action="login.php" method="POST" class="auth-form">
    <h2>Авторизация</h2>
    <div class="form-group">
        <label for="login">Логин:</label>
        <input type="text" id="login" name="login" required>
    </div>
    <div class="form-group">
        <label for="password">Пароль:</label>
        <input type="password" id="password" name="password" required>
    </div>
    <button type="submit" class="btn">Войти</button>
</form>