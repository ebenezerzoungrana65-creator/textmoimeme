<?php
require_once __DIR__ . '/../config/helpers.php';
require_role('admin');
$q = $pdo->query(" SELECT r.*, u.prenom, u.nom, h.nom AS hotel
    FROM reservations r
    JOIN users u ON u.id = r.user_id
    JOIN hotels h ON h.id = r.hotel_id
    ORDER BY r.created_at DESC
");
$reservations = $q->fetchAll();
$pageTitle = 'Réservations';
?>
<!doctype html>
<html lang="fr"><head><meta charset="utf-8"><title>Réservations</title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/admin.css"></head>
<body class="admin-body">
<?php require __DIR__ . '/_sidebar.php'; ?>
<main class="admin-main">
    <div class="admin-header"><h1>Réservations (<?= count($reservations) ?>)</h1></div>
    <div class="panel">
        <table class="admin-table">
            <thead>
                <tr><th>Code</th><th>Client</th><th>Hôtel</th><th>Dates</th><th>Total</th><th>Statut</th></tr>
            </thead>
            <tbody>
                <?php foreach ($reservations as $r): ?>
                <tr>
                    <td><code><?= e($r['code']) ?></code></td>
                    <td><?= e($r['prenom']) ?> <?= e($r['nom']) ?></td>
                    <td><?= e($r['hotel']) ?></td>
                    <td><?= e($r['date_arrivee']) ?> → <?= e($r['date_depart']) ?></td>
                    <td><strong><?= money($r['total']) ?></strong></td>
                    <td><span class="badge badge-<?= e($r['statut']) ?>"><?= e($r['statut']) ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>
</body></html>