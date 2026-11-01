<?php
require_once __DIR__ . '/core/functions.php';
require_login();

$shops = $conn->query('SELECT s.*, creator.first_name AS creator_first, creator.last_name AS creator_last, editor.first_name AS editor_first, editor.last_name AS editor_last FROM shops s LEFT JOIN users creator ON s.added_by = creator.id LEFT JOIN users editor ON s.updated_by = editor.id ORDER BY s.id DESC');
$shopOptions = $conn->query('SELECT id, shop_name FROM shops ORDER BY shop_name');
$menuStatement = $conn->prepare('SELECT m.*, creator.first_name AS creator_first, creator.last_name AS creator_last, editor.first_name AS editor_first, editor.last_name AS editor_last FROM menu_items m LEFT JOIN users creator ON m.added_by = creator.id LEFT JOIN users editor ON m.updated_by = editor.id WHERE m.shop_id = ? ORDER BY m.item_name');
$activity = $conn->query('SELECT a.action, a.record_type, a.record_id, a.record_label, a.happened_at, u.first_name, u.last_name FROM activity_logs a JOIN users u ON a.user_id = u.id ORDER BY a.id DESC LIMIT 10');
$shopCount = $shops->num_rows;
$menuCount = (int) $conn->query('SELECT COUNT(*) AS total FROM menu_items')->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Shop dashboard - Jun's Silogan</title><link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="topbar">
    <a class="wordmark" href="index.php"><span class="brand-mark small-mark">JS</span><span>Silogan<b>House</b></span></a>
    <nav class="main-nav" aria-label="Main navigation"><a class="nav-link active" href="index.php">Dashboard</a><a class="nav-link" href="#record-forms">Add records</a></nav>
    <div class="topbar-right"><span class="welcome">Signed in as <strong><?= escape($_SESSION['user_name']) ?></strong></span><a class="button button-light" href="logout.php">Log out</a></div>
</header>
<main class="page-shell">
    <section class="page-heading">
        <div><p class="eyebrow">JUN'S SILOGAN HOUSE · MANAGEMENT</p><h1>Good food starts with a well-run shop.</h1><p class="page-subtitle">Keep your silogan locations, menu, and team activity in one place.</p></div>
        <a class="button button-primary" href="#record-forms">Add a record</a>
        <div class="dashboard-stats" aria-label="Dashboard summary">
            <div><strong><?= (int) $shopCount ?></strong><span>Shops</span></div>
            <div><strong><?= $menuCount ?></strong><span>Menu items</span></div>
        </div>
    </section>
    <?php if (!empty($_GET['message'])): ?><div class="notice success page-notice"><?= escape($_GET['message']) ?></div><?php endif; ?>

    <section class="section-heading"><div><h2>Available Silogan Shops <span class="count-badge"><?= (int) $shopCount ?></span></h2><p class="section-note">Shop details and their menu items</p></div></section>

    <?php if ($shops->num_rows > 0): ?>
        <div class="shop-grid">
        <?php while ($shop = $shops->fetch_assoc()): ?>
            <article class="shop-card">
                <div class="shop-card-head"><div><span class="card-kicker">SHOP #<?= (int) $shop['id'] ?></span><h3><?= escape($shop['shop_name']) ?></h3></div><a class="text-link" href="shops/update.php?id=<?= (int) $shop['id'] ?>">Edit shop</a></div>
                <div class="shop-details"><span>Owner</span><strong><?= escape($shop['owner_name']) ?></strong><span>Address</span><strong><?= escape($shop['address'] ?: 'Not provided') ?></strong><span>Contact</span><strong><?= escape($shop['contact_number'] ?: 'Not provided') ?></strong></div>
                <p class="audit-line">Added by <?= escape($shop['creator_first'] ? $shop['creator_first'] . ' ' . $shop['creator_last'] : 'Previous user (not recorded)') ?> &middot; Updated <?= escape($shop['last_updated']) ?><?php if ($shop['updated_by']): ?> by <?= escape($shop['editor_first'] . ' ' . $shop['editor_last']) ?><?php endif; ?></p>
                <div class="menu-heading"><h4>Menu</h4></div>
                <?php $menuStatement->bind_param('i', $shop['id']); $menuStatement->execute(); $items = $menuStatement->get_result(); ?>
                <?php if ($items->num_rows > 0): ?>
                    <div class="menu-list">
                    <?php while ($item = $items->fetch_assoc()): ?>
                        <div class="menu-row"><div class="menu-name"><strong><?= escape($item['item_name']) ?></strong><span><?= escape($item['category']) ?><?= $item['description'] ? ' &middot; ' . escape($item['description']) : '' ?></span><small>Added by <?= escape($item['creator_first'] ? $item['creator_first'] . ' ' . $item['creator_last'] : 'Previous user (not recorded)') ?> &middot; Last updated <?= escape($item['last_updated']) ?><?php if ($item['updated_by']): ?> by <?= escape($item['editor_first'] . ' ' . $item['editor_last']) ?><?php endif; ?></small></div><strong class="price">&#8369;<?= number_format((float) $item['price'], 2) ?></strong><div class="row-actions"><a href="menu_items/update.php?id=<?= (int) $item['id'] ?>">Edit</a><form action="menu_items/delete.php" method="post" onsubmit="return confirm('Delete this menu item?')"><input type="hidden" name="id" value="<?= (int) $item['id'] ?>"><button class="link-button danger-text">Delete</button></form></div></div>
                    <?php endwhile; ?>
                    </div>
                <?php else: ?><p class="empty-small">No menu items yet, Add the first one below</p><?php endif; ?>
                <form class="inline-delete" action="shops/delete.php" method="post" onsubmit="return confirm('Delete this shop and all its menu items?')"><input type="hidden" name="id" value="<?= (int) $shop['id'] ?>"><button class="link-button danger-text">Delete shop</button></form>
            </article>
        <?php endwhile; ?>
        </div>
    <?php else: ?><div class="empty-state"><h3>No shops have been added yet</h3><p>Use the shop form below to add your first one</p></div><?php endif; ?>

    <section class="record-section" id="record-forms">
        <div class="section-heading"><div><h2>Add records</h2><p class="section-note">Enter shop details or add a meal to a shop</p></div></div>
        <div class="forms-grid">
        <article class="form-card"><h3>Add a shop</h3><p class="muted">Enter the basic details for your silogan.</p>
            <form action="shops/insert.php" method="post" class="form-stack">
                <label>Shop name<input name="shop_name" required maxlength="255" placeholder="e.g. Jun's Silogan"></label>
                <label>Owner name<input name="owner_name" required maxlength="255"></label>
                <label>Address<input name="address" maxlength="255"></label>
                <label>Contact number<input name="contact_number" maxlength="50"></label>
                <button class="button button-primary">Save shop</button>
            </form>
        </article>
        <article class="form-card"><h3>Add a meal</h3><p class="muted">Add a silog favorite dish to one of your shops</p>
            <?php if ($shopOptions->num_rows > 0): ?>
            <form action="menu_items/insert.php" method="post" class="form-stack">
                <label>Shop<select name="shop_id" required><?php while ($option = $shopOptions->fetch_assoc()): ?><option value="<?= (int) $option['id'] ?>"><?= escape($option['shop_name']) ?></option><?php endwhile; ?></select></label>
                <label>Meal name<input name="item_name" required maxlength="255" placeholder="Tapsilog"></label>
                <div class="field-row"><label>Price (&#8369;)<input type="number" name="price" min="0.01" step="0.01" required></label><label>Category<input name="category" value="silog" maxlength="100"></label></div>
                <label>Description<input name="description" placeholder="Tapa, garlic rice, and egg"></label>
                <button class="button button-dark">Add to menu</button>
            </form>
            <?php else: ?><div class="callout">Add a shop first, then you can attach menu items to it</div><?php endif; ?>
        </article>
        </div>
    </section>
    <section class="activity-card"><div><p class="eyebrow">AUDIT LOGS</p><h2>Recent activity</h2><p class="muted">A record of changes and updates made in the system by a user</p></div>
        <?php if ($activity->num_rows > 0): ?><div class="activity-list">
            <?php while ($entry = $activity->fetch_assoc()): ?>
                <div class="activity-row"><span class="activity-action <?= escape($entry['action']) ?>"><?= escape(ucfirst($entry['action'])) ?></span><span><strong><?= escape($entry['first_name'] . ' ' . $entry['last_name']) ?></strong> <?= escape($entry['action'] === 'insert' ? 'added' : ($entry['action'] === 'update' ? 'updated' : 'deleted')) ?> <?= escape(str_replace('_', ' ', $entry['record_type'])) ?> <strong><?= escape($entry['record_label'] ?: 'record') ?></strong> #<?= (int) $entry['record_id'] ?></span><time><?= escape($entry['happened_at']) ?></time></div>
            <?php endwhile; ?>
        </div><?php else: ?><p class="empty-small">Activity will appear here after you add, edit, or delete a record</p><?php endif; ?>
    </section>
    <footer class="page-footer">Silogan House<span>TATAK SILOGAN NA PANG MASA ITO!</span></footer>
</main>
</body>
</html>
