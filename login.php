<?php
require_once __DIR__ . '/core/functions.php';

if (!empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
        $error = 'Enter a valid email and your password.';
    } else {
        try {
            $statement = $conn->prepare('SELECT id, first_name, last_name, password_hash FROM users WHERE email = ?');
            $statement->bind_param('s', $email);
            $statement->execute();
            $user = $statement->get_result()->fetch_assoc();

            if (!$user) {
                $error = 'No account was found for that email. Please register first.';
            } elseif (!password_verify($password, $user['password_hash'])) {
                $error = 'The password is incorrect. Please try again.';
            } else {
                session_regenerate_id(true);
                $_SESSION['user_id'] = (int) $user['id'];
                $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                header('Location: index.php');
                exit;
            }
        } catch (mysqli_sql_exception $exception) {
            if ($exception->getCode() === 1146) {
                $error = 'The database tables are not set up yet. Import core/schema.sql in phpMyAdmin, then register again.';
            } else {
                $error = 'The database could not process your login. Check the database setup and try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Log in · Jun’s Silogan</title><link rel="stylesheet" href="assets/style.css">
</head>
<body class="auth-page">
    <main class="auth-card login-card">
        <a class="brand-mark" href="login.php">JS</a>
        <p class="eyebrow">JUN’S SILOGAN HOUSE</p>
        <h1>Good to see you.</h1>
        <p class="muted">Log in to manage your silog shop.</p>
        <?php if (isset($_GET['registered'])): ?><p class="notice success">Account created. You can log in now.</p><?php endif; ?>
        <?php if ($error !== ''): ?><p class="notice error"><?= escape($error) ?></p><?php endif; ?>
        <form method="post" class="form-stack">
            <label>Email<input type="email" name="email" required autocomplete="username" value="<?= escape($_POST['email'] ?? '') ?>"></label>
            <label>Password<input type="password" name="password" required autocomplete="current-password"></label>
            <button class="button button-primary button-wide">Log in</button>
        </form>
        <p class="auth-foot">New here? <a href="register.php">Create an account</a></p>
    </main>
</body>
</html>
