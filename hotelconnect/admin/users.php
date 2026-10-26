<?php
require_once __DIR__ . '/../config/helpers.php';
require_role('admin');
$users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
$pageTitle = 'Utilisateurs';
?>
<!doctype html>
<html lang="fr"><head><meta charset="utf-8"><title>Utilisateurs</title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/admin.css"></head>
<body class="admin-body">
<?php require __DIR__ . '/_sidebar.php'; ?>
<main class="admin-main">
    <div class="admin-header"><h1>Utilisateurs (<?= count($users) ?>)</h1></div>
    <div class="panel">
        <table class="admin-table">
            <thead><tr><th>Nom</th><th>Email</th><th>Rôle</th><th>Inscrit le</th></tr></thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= e($u['prenom']) ?> <?= e($u['nom']) ?></td>
                    <td><?= e($u['email']) ?></td>
                    <td><span class="badge badge-<?= $u['role'] === 'admin' ? 'confirmee' : ($u['role'] === 'hotelier' ? 'attente' : 'payee') ?>"><?= e($u['role']) ?></span></td>
                    <td><?= e($u['created_at']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>
</body></html>