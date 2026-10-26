<?php
require_once __DIR__ . '/../config/helpers.php';
require_role('admin');

// ============ STATISTIQUES ============
$stats = [];

// Nombre d'utilisateurs par rôle
$stats['users_total']     = (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
$stats['users_clients']   = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='client'")->fetchColumn();
$stats['users_hotels']    = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='hotelier'")->fetchColumn();

// Hôtels
$stats['hotels_total']    = (int)$pdo->query('SELECT COUNT(*) FROM hotels')->fetchColumn();
$stats['hotels_valides']  = (int)$pdo->query("SELECT COUNT(*) FROM hotels WHERE statut='valide'")->fetchColumn();
$stats['hotels_attente']  = (int)$pdo->query("SELECT COUNT(*) FROM hotels WHERE statut='attente'")->fetchColumn();

// Réservations
$stats['resa_total']      = (int)$pdo->query('SELECT COUNT(*) FROM reservations')->fetchColumn();
$stats['resa_attente']    = (int)$pdo->query("SELECT COUNT(*) FROM reservations WHERE statut='attente'")->fetchColumn();
$stats['resa_confirmees'] = (int)$pdo->query("SELECT COUNT(*) FROM reservations WHERE statut='confirmee'")->fetchColumn();

// Chiffre d'affaires (réservations confirmées + payées)
$stats['ca_total'] = (float)$pdo->query("
    SELECT COALESCE(SUM(total), 0) FROM reservations 
    WHERE statut IN ('confirmee','payee')
")->fetchColumn();

// Réservations par mois (6 derniers mois)
$mois = $pdo->query("
    SELECT DATE_FORMAT(created_at, '%Y-%m') AS mois, COUNT(*) AS total
    FROM reservations
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY mois
    ORDER BY mois
")->fetchAll();

// 5 dernières réservations
$dernieres = $pdo->query("
    SELECT r.*, u.prenom, u.nom, h.nom AS hotel
    FROM reservations r
    JOIN users u ON u.id = r.user_id
    JOIN hotels h ON h.id = r.hotel_id
    ORDER BY r.created_at DESC
    LIMIT 5
")->fetchAll();

// Hôtels en attente
$hotels_attente = $pdo->query("
    SELECT h.*, u.prenom, u.nom
    FROM hotels h
    JOIN users u ON u.id = h.user_id
    WHERE h.statut = 'attente'
    ORDER BY h.created_at DESC
    LIMIT 5
")->fetchAll();

$pageTitle = 'Dashboard admin';
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title><?= e($pageTitle) ?> · HotelConnect</title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/admin.css">
</head>
<body class="admin-body">

<?php require __DIR__ . '/_sidebar.php'; ?>

<main class="admin-main">

    <!-- HEADER -->
    <div class="admin-header">
        <div>
            <h1>Tableau de bord</h1>
            <p class="muted">Bienvenue, <?= e(current_user()['prenom']) ?> 👋 · <?= date('d M Y') ?></p>
        </div>
        <div class="admin-actions">
            <a class="btn btn-secondary" href="<?= BASE_URL ?>/admin/hotels.php">Gérer les hôtels</a>
            <a class="btn btn-primary" href="<?= BASE_URL ?>/admin/reservations.php">Voir réservations</a>
        </div>
    </div>

    <!-- KPI CARDS -->
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-icon" style="background:#e3f2fd;color:#1976d2;">👥</div>
            <div>
                <div class="kpi-label">Utilisateurs</div>
                <div class="kpi-value"><?= $stats['users_total'] ?></div>
                <div class="kpi-sub"><?= $stats['users_clients'] ?> clients · <?= $stats['users_hotels'] ?> hôteliers</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon" style="background:#fff3e0;color:#f57c00;">🏨</div>
            <div>
                <div class="kpi-label">Hôtels</div>
                <div class="kpi-value"><?= $stats['hotels_total'] ?></div>
                <div class="kpi-sub"><?= $stats['hotels_valides'] ?> validés · <?= $stats['hotels_attente'] ?> en attente</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon" style="background:#f3e5f5;color:#7b1fa2;">📅</div>
            <div>
                <div class="kpi-label">Réservations</div>
                <div class="kpi-value"><?= $stats['resa_total'] ?></div>
                <div class="kpi-sub"><?= $stats['resa_attente'] ?> en attente · <?= $stats['resa_confirmees'] ?> confirmées</div>
            </div>
        </div>
        <div class="kpi-card kpi-card-success">
            <div class="kpi-icon" style="background:#e8f5e9;color:#388e3c;">💰</div>
            <div>
                <div class="kpi-label">Chiffre d'affaires</div>
                <div class="kpi-value"><?= money($stats['ca_total']) ?></div>
                <div class="kpi-sub">Réservations confirmées</div>
            </div>
        </div>
    </div>

    <!-- GRAPHIQUE + ACTIONS RAPIDES -->
    <div class="grid-2">
        <div class="panel">
            <div class="panel-header">
                <h2>Réservations (6 derniers mois)</h2>
            </div>
            <div class="chart-container">
                <?php
                $max = max(array_column($mois, 'total') ?: [1]);
                foreach ($mois as $m):
                    $h = $max > 0 ? ($m['total'] / $max) * 100 : 0;
                ?>
                    <div class="chart-bar-wrap">
                        <div class="chart-bar" style="height:<?= $h ?>%">
                            <span class="chart-value"><?= $m['total'] ?></span>
                        </div>
                        <div class="chart-label"><?= e($m['mois']) ?></div>
                    </div>
                <?php endforeach; ?>
                <?php if (!$mois): ?>
                    <p class="muted">Pas encore de données.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="panel">
            <div class="panel-header">
                <h2>Actions rapides</h2>
            </div>
            <div class="quick-actions">
                <a href="<?= BASE_URL ?>/admin/hotels.php?statut=attente" class="quick-action">
                    <div class="qa-icon" style="background:#fff3e0;color:#f57c00;">⏳</div>
                    <div>
                        <strong>Hôtels en attente</strong>
                        <small><?= $stats['hotels_attente'] ?> à valider</small>
                    </div>
                </a>
                <a href="<?= BASE_URL ?>/admin/reservations.php?statut=attente" class="quick-action">
                    <div class="qa-icon" style="background:#f3e5f5;color:#7b1fa2;">📋</div>
                    <div>
                        <strong>Réservations à traiter</strong>
                        <small><?= $stats['resa_attente'] ?> en attente</small>
                    </div>
                </a>
                <a href="<?= BASE_URL ?>/admin/users.php" class="quick-action">
                    <div class="qa-icon" style="background:#e3f2fd;color:#1976d2;">👥</div>
                    <div>
                        <strong>Gérer les utilisateurs</strong>
                        <small><?= $stats['users_total'] ?> comptes</small>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- DEUX TABLEAUX : DERNIÈRES RÉSA + HÔTELS EN ATTENTE -->
    <div class="grid-2">

        <!-- Dernières réservations -->
        <div class="panel">
            <div class="panel-header">
                <h2>Dernières réservations</h2>
                <a href="<?= BASE_URL ?>/admin/reservations.php" class="text-link">Tout voir →</a>
            </div>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Client</th>
                        <th>Hôtel</th>
                        <th>Total</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dernieres as $r): ?>
                    <tr>
                        <td><code><?= e($r['code']) ?></code></td>
                        <td><?= e($r['prenom']) ?> <?= e($r['nom']) ?></td>
                        <td><?= e($r['hotel']) ?></td>
                        <td><?= money($r['total']) ?></td>
                        <td>
                            <span class="badge badge-<?= e($r['statut']) ?>">
                                <?= e(ucfirst($r['statut'])) ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (!$dernieres): ?>
                        <tr><td colspan="5" class="muted text-center">Aucune réservation.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Hôtels en attente -->
        <div class="panel">
            <div class="panel-header">
                <h2>Hôtels en attente de validation</h2>
                <a href="<?= BASE_URL ?>/admin/hotels.php" class="text-link">Tout voir →</a>
            </div>
            <?php if ($hotels_attente): ?>
                <div class="hotel-waiting-list">
                    <?php foreach ($hotels_attente as $h): ?>
                        <div class="hotel-waiting">
                            <div class="hw-avatar"><?= e(strtoupper(substr($h['nom'], 0, 1))) ?></div>
                            <div class="hw-info">
                                <strong><?= e($h['nom']) ?></strong>
                                <small><?= e($h['ville']) ?> · par <?= e($h['prenom']) ?> <?= e($h['nom']) ?></small>
                            </div>
                            <div class="hw-actions">
                                <a href="<?= BASE_URL ?>/admin/hotels.php?valider=<?= (int)$h['id'] ?>" 
                                   class="btn btn-success btn-sm">✓ Valider</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="muted text-center">Aucun hôtel en attente. 🎉</p>
            <?php endif; ?>
        </div>

    </div>

</main>
</body>
</html>