<?php
require_once __DIR__ . '/../core/functions.php';
require_login();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0);
    if ($id > 0) {
        $userId = current_user_id();
        $conn->begin_transaction();
        try {
            $log = $conn->prepare('INSERT INTO activity_logs (user_id, action, record_type, record_id) SELECT ?, \'delete\', \'menu_item\', id FROM menu_items WHERE id = ?');
            $log->bind_param('ii', $userId, $id);
            $log->execute();
            $delete = $conn->prepare('DELETE FROM menu_items WHERE id = ?');
            $delete->bind_param('i', $id);
            $delete->execute();
            $conn->commit();
            redirect_home('Menu item deleted.');
        } catch (Throwable $error) {
            $conn->rollback();
            redirect_home('Could not delete the menu item.');
        }
    }
}
redirect_home();
