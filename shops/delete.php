<?php
require_once __DIR__ . '/../core/functions.php';
require_login();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0);
    if ($id > 0) {
        $userId = current_user_id();
        $conn->begin_transaction();
        try {
            $items = $conn->prepare('SELECT id FROM menu_items WHERE shop_id = ?');
            $items->bind_param('i', $id);
            $items->execute();
            $result = $items->get_result();
            while ($item = $result->fetch_assoc()) {
                log_activity($conn, $userId, 'delete', 'menu_item', (int) $item['id']);
            }
            log_activity($conn, $userId, 'delete', 'shop', $id);
            $delete = $conn->prepare('DELETE FROM shops WHERE id = ?');
            $delete->bind_param('i', $id);
            $delete->execute();
            $conn->commit();
            redirect_home('Shop and its menu items deleted.');
        } catch (Throwable $error) {
            $conn->rollback();
            redirect_home('Could not delete the shop.');
        }
    }
}
redirect_home();
