<?php
require_once __DIR__ . '/../core/functions.php';
require_login();
$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['shop_name'] ?? '');
    $owner = trim($_POST['owner_name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $contact = trim($_POST['contact_number'] ?? '');
    if ($id > 0 && $name !== '' && $owner !== '') {
        $userId = current_user_id();
        $statement = $conn->prepare('UPDATE shops SET shop_name = ?, owner_name = ?, address = ?, contact_number = ?, updated_by = ? WHERE id = ?');
        $statement->bind_param('ssssii', $name, $owner, $address, $contact, $userId, $id);
        $statement->execute();
        log_activity($conn, $userId, 'update', 'shop', $id);
        redirect_home('Shop updated.');
    }
}
$statement = $conn->prepare('SELECT * FROM shops WHERE id = ?');
$statement->bind_param('i', $id);
$statement->execute();
$shop = $statement->get_result()->fetch_assoc();
if (!$shop) { redirect_home('Shop not found.'); }
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Edit shop · Jun’s Silogan</title><link rel="stylesheet" href="../assets/style.css"></head>
<body class="auth-page"><main class="auth-card edit-card"><a class="back-link" href="../index.php">← Back to shop board</a><p class="eyebrow">SHOP DETAILS</p><h1>Edit shop</h1>
<form method="post" class="form-stack"><input type="hidden" name="id" value="<?= (int) $shop['id'] ?>"><label>Shop name<input name="shop_name" required value="<?= escape($shop['shop_name']) ?>"></label><label>Owner name<input name="owner_name" required value="<?= escape($shop['owner_name']) ?>"></label><label>Address<input name="address" value="<?= escape($shop['address']) ?>"></label><label>Contact number<input name="contact_number" value="<?= escape($shop['contact_number']) ?>"></label><button class="button button-primary">Save changes</button></form></main></body></html>
