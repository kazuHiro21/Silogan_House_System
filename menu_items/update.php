<?php
require_once __DIR__ . '/../core/functions.php';
require_login();
$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['item_name'] ?? '');
    $price = (float) ($_POST['price'] ?? 0);
    $category = trim($_POST['category'] ?? 'silog') ?: 'silog';
    $description = trim($_POST['description'] ?? '');
    if ($id > 0 && $name !== '' && $price > 0) {
        $userId = current_user_id();
        $conn->begin_transaction();
        try {
            $statement = $conn->prepare('UPDATE menu_items SET item_name = ?, price = ?, category = ?, description = ?, updated_by = ? WHERE id = ?');
            $statement->bind_param('sdssii', $name, $price, $category, $description, $userId, $id);
            $statement->execute();
            log_activity($conn, $userId, 'update', 'menu_item', $id, $name);
            $conn->commit();
            redirect_home('Menu item updated.');
        } catch (Throwable $error) {
            $conn->rollback();
            error_log('Menu item update failed: ' . $error->getMessage());
            redirect_home('Could not update the menu item.');
        }
    }
}
$statement = $conn->prepare('SELECT * FROM menu_items WHERE id = ?');
$statement->bind_param('i', $id);
$statement->execute();
$item = $statement->get_result()->fetch_assoc();
if (!$item) { redirect_home('Menu item not found.'); }
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Edit menu item · Jun’s Silogan</title><link rel="stylesheet" href="../assets/style.css"></head>
<body class="auth-page"><main class="auth-card edit-card"><a class="back-link" href="../index.php">← Back to shop board</a><p class="eyebrow">MENU DETAILS</p><h1>Edit meal</h1>
<form method="post" class="form-stack"><input type="hidden" name="id" value="<?= (int) $item['id'] ?>"><label>Meal name<input name="item_name" required value="<?= escape($item['item_name']) ?>"></label><div class="field-row"><label>Price (₱)<input type="number" name="price" min="0.01" step="0.01" required value="<?= escape($item['price']) ?>"></label><label>Category<input name="category" value="<?= escape($item['category']) ?>"></label></div><label>Description<input name="description" value="<?= escape($item['description']) ?>"></label><button class="button button-primary">Save changes</button></form></main></body></html>
