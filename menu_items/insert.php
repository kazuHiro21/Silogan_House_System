<?php
require_once __DIR__ . '/../core/functions.php';
require_login();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shopId = (int) ($_POST['shop_id'] ?? 0);
    $name = trim($_POST['item_name'] ?? '');
    $price = (float) ($_POST['price'] ?? 0);
    $category = trim($_POST['category'] ?? 'silog') ?: 'silog';
    $description = trim($_POST['description'] ?? '');
    if ($shopId > 0 && $name !== '' && $price > 0) {
        $userId = current_user_id();
        $statement = $conn->prepare('INSERT INTO menu_items (shop_id, item_name, price, category, description, added_by) VALUES (?, ?, ?, ?, ?, ?)');
        $statement->bind_param('isdssi', $shopId, $name, $price, $category, $description, $userId);
        $statement->execute();
        log_activity($conn, $userId, 'insert', 'menu_item', (int) $conn->insert_id);
        redirect_home('Menu item added.');
    }
}
redirect_home('Please choose a shop and enter a meal name and price.');
