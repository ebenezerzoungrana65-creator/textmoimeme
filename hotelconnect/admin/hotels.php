<?php
require_once __DIR__ . '/../config/helpers.php';
require_role('admin');

// Valider un hôtel
if (isset($_GET['valider'])) {
    $id = (int)$_GET['valider'];
    $pdo->prepare("UPDATE hotels SET statut='valide' WHERE id=?")->execute([$id]);
    flash('success', 'Hôtel validé avec succès.');
    redirect(BASE_URL . '/admin/hotels.php');
}
if (isset($_GET['refuser'])) {
    $id = (int)$_GET['refuser'];
    $pdo->prepare("UPDATE hotels SET statut='refuse' WHERE id=?")->execute([$id]);
    flash('warning', 'Hôtel refusé.');
    redirect(BASE_URL . '/admin/hotels.php');
}

$statut = $_GET['statut'] ?? '';
$sql = "SELECT h.*, u.prenom, u.nom FROM hotels h JOIN users u ON u.id=h.user_id";
$params = [];
if ($statut !== '') {
    $sql .= " WHERE h.statut = ?";
    $params[] = $statut;
}
$sql .= " ORDER BY h.created_at DESC";
$q = $pdo->prepare($sql);
$q->execute($params);
$hotels = $q->fetchAll();

$pageTitle = 'Gestion des hôtels';
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title><?= e($pageTitle) ?></title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/admin.css">
</head>
<body class="admin-body">
<?php require __DIR__ . '/_sidebar.php'; ?>
<main class="admin-main">
    <div class="admin-header">
        <h1>Hôtels</h1>
        <div class="admin-actions">
            <a class="btn btn-secondary <?= $statut === '' ? 'active' : '' ?>" href="?">Tous</a>
            <a class="btn btn-secondary" href="?statut=attente">En attente</a>
            <a class="btn btn-secondary" href="?statut=valide">Validés</a>
        </div>
    </div>

    <?php foreach (get_flashes() as $f): ?>
        <div class="flash flash-<?= e($f['type']) ?>"><?= e($f['msg']) ?></div>
    <?php endforeach; ?>

    <div class="panel">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Ville</th>
                    <th>Propriétaire</th>
                    <th>Catégorie</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($hotels as $h): ?>
                <tr>
                    <td><strong><?= e($h['nom']) ?></strong></td>
                    <td><?= e($h['ville']) ?></td>
                    <td><?= e($h['prenom']) ?> <?= e($h['nom']) ?></td>
                    <td><?= e($h['categorie']) ?></td>
                    <td><span class="badge badge-<?= e($h['statut']) ?>"><?= e($h['statut']) ?></span></td>
                    <td>
                        <?php if ($h['statut'] === 'attente'): ?>
                            <a class="btn btn-success btn-sm" href="?valider=<?= (int)$h['id'] ?>">✓ Valider</a>
                            <a class="btn btn-danger btn-sm" href="?refuser=<?= (int)$h['id'] ?>">✗ Refuser</a>
                        <?php else: ?>
                            <a class="btn btn-secondary btn-sm" href="<?= BASE_URL ?>/hotel.php?id=<?= (int)$h['id'] ?>">Voir</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (!$hotels): ?>
                    <tr><td colspan="6" class="text-center muted">Aucun hôtel.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>
</body>
</html>