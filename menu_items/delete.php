<?php
require_once __DIR__ . '/../core/functions.php';
require_login();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0);
    if ($id > 0) {
        $userId = current_user_id();
        $conn->begin_transaction();
        try {
            $lookup = $conn->prepare('SELECT item_name FROM menu_items WHERE id = ?');
            $lookup->bind_param('i', $id);
            $lookup->execute();
            $item = $lookup->get_result()->fetch_assoc();
            if (!$item) {
                $conn->rollback();
                redirect_home('Menu item not found.');
            }
            log_activity($conn, $userId, 'delete', 'menu_item', $id, $item['item_name']);
            $delete = $conn->prepare('DELETE FROM menu_items WHERE id = ?');
            $delete->bind_param('i', $id);
            $delete->execute();
            $conn->commit();
            redirect_home('Menu item deleted.');
        } catch (Throwable $error) {
            $conn->rollback();
            error_log('Menu item deletion failed: ' . $error->getMessage());
            redirect_home('Could not delete the menu item.');
        }
    }
}
redirect_home();
