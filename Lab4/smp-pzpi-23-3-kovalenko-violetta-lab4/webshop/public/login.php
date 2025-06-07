<?php
require_once 'includes/header.php';

$cred = include 'credential.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['username'] ?? '');
    $pass = $_POST['password'] ?? '';
    if ($user === '' || $pass === '') {
        $error = 'Всі поля обов\'язкові!';
    } elseif ($user === $cred['userName'] && $pass === $cred['password']) {
        $_SESSION['user'] = $user;
        $_SESSION['login_time'] = date('Y-m-d H:i:s');
        header('Location: /index.php');
        exit;
    } else {
        $error = 'Невірний логін або пароль!';
    }
}
?>
<div style="max-width:400px;margin:2em auto;padding:2em;background:#fff;border-radius:8px;box-shadow:0 2px 8px #ccc;">
    <h2>Вхід</h2>
    <?php if ($error): ?><div class="error" style="margin-bottom:1em;"> <?= htmlspecialchars($error) ?> </div><?php endif; ?>
    <form method="post">
        <label>Ім'я користувача:<br><input type="text" name="username" required style="width:100%"></label><br><br>
        <label>Пароль:<br><input type="password" name="password" required style="width:100%"></label><br><br>
        <button type="submit">Login</button>
    </form>
</div>
<?php require_once 'includes/footer.php'; ?> 