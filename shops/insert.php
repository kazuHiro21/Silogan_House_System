<?php
require_once __DIR__ . '/../core/functions.php';
require_login();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['shop_name'] ?? '');
    $owner = trim($_POST['owner_name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $contact = trim($_POST['contact_number'] ?? '');
    if ($name !== '' && $owner !== '') {
        $statement = $conn->prepare('INSERT INTO shops (shop_name, owner_name, address, contact_number, added_by) VALUES (?, ?, ?, ?, ?)');
        $userId = current_user_id();
        $statement->bind_param('ssssi', $name, $owner, $address, $contact, $userId);
        $statement->execute();
        log_activity($conn, $userId, 'insert', 'shop', (int) $conn->insert_id);
        redirect_home('Shop added.');
    }
}
redirect_home('Please enter a shop name and owner.');
