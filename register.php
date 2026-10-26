<?php
require_once __DIR__ . '/core/functions.php';

if (!empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $address = trim($_POST['address'] ?? '');
    $ageInput = trim($_POST['age'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($firstName === '' || $lastName === '' || strlen($firstName) > 100 || strlen($lastName) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190 || strlen($password) < 8) {
        $error = 'Enter your name, a valid email, and a password with at least 8 characters.';
    } elseif ($ageInput !== '' && (!ctype_digit($ageInput) || (int) $ageInput < 1 || (int) $ageInput > 120)) {
        $error = 'Enter an age from 1 to 120, or leave it blank.';
    } else {
        $age = $ageInput === '' ? null : (int) $ageInput;
        $hash = password_hash($password, PASSWORD_DEFAULT);
        try {
            $statement = $conn->prepare('INSERT INTO users (first_name, last_name, email, password_hash, address, age) VALUES (?, ?, ?, ?, ?, ?)');
            $statement->bind_param('sssssi', $firstName, $lastName, $email, $hash, $address, $age);
            $statement->execute();
            header('Location: login.php?registered=1');
            exit;
        } catch (mysqli_sql_exception $exception) {
            if ($exception->getCode() === 1062) {
                $error = 'That email is already registered. Try logging in instead.';
            } elseif ($exception->getCode() === 1146) {
                $error = 'The database tables are not set up yet. Import core/schema.sql in phpMyAdmin, then register again.';
            } else {
                $error = 'The account could not be created. Check that MySQL is running and the database is set up.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create account · Jun’s Silogan</title><link rel="stylesheet" href="assets/style.css">
</head>
<body class="auth-page">
    <main class="auth-card">
        <a class="brand-mark" href="login.php">JS</a>
        <p class="eyebrow">JUN’S SILOGAN HOUSE</p>
        <h1>Create your account</h1>
        <p class="muted">Join the team to manage the shop and its menu.</p>
        <?php if ($error !== ''): ?><p class="notice error"><?= escape($error) ?></p><?php endif; ?>
        <form method="post" class="form-stack">
            <div class="field-row">
                <label>First name<input name="first_name" maxlength="100" required value="<?= escape($_POST['first_name'] ?? '') ?>"></label>
                <label>Last name<input name="last_name" maxlength="100" required value="<?= escape($_POST['last_name'] ?? '') ?>"></label>
            </div>
            <label>Email<input type="email" name="email" maxlength="190" required autocomplete="email" value="<?= escape($_POST['email'] ?? '') ?>"></label>
            <label>Password <span class="hint">(8 characters minimum)</span><input type="password" name="password" required minlength="8" autocomplete="new-password"></label>
            <label>Address <span class="hint">(optional)</span><input name="address" maxlength="255" value="<?= escape($_POST['address'] ?? '') ?>"></label>
            <label>Age <span class="hint">(optional)</span><input type="number" name="age" min="1" max="120" value="<?= escape($_POST['age'] ?? '') ?>"></label>
            <button class="button button-primary button-wide">Register</button>
        </form>
        <p class="auth-foot">Already registered? <a href="login.php">Log in</a></p>
    </main>
</body>
</html>
