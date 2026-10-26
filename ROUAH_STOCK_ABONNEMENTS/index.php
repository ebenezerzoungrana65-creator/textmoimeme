<?php
require __DIR__ . '/config/database.php';
require __DIR__ . '/includes/functions.php';
exiger_connexion();
$titre = 'Tableau de bord';
$stats = [
    'produits' => (int)$pdo->query('SELECT COUNT(*) FROM produits WHERE actif=1')->fetchColumn(),
    'quantite' => (float)$pdo->query('SELECT COALESCE(SUM(quantite),0) FROM produits WHERE actif=1')->fetchColumn(),
    'alertes' => (int)$pdo->query('SELECT COUNT(*) FROM produits WHERE actif=1 AND quantite <= seuil_alerte')->fetchColumn(),
    'mouvements' => (int)$pdo->query("SELECT COUNT(*) FROM mouvements_stock WHERE DATE(date_mouvement)=CURDATE()")->fetchColumn()
];
$recent = $pdo->query("SELECT m.*, p.reference, p.nom AS produit FROM mouvements_stock m JOIN produits p ON p.id=m.produit_id ORDER BY m.date_mouvement DESC LIMIT 8")->fetchAll();
$alertes = $pdo->query('SELECT reference,nom,quantite,seuil_alerte,unite FROM produits WHERE actif=1 AND quantite <= seuil_alerte ORDER BY quantite ASC LIMIT 8')->fetchAll();
require __DIR__ . '/includes/header.php';
?>
<div class="page-heading"><div><p class="eyebrow">ESPACE DE GESTION</p><h1>Tableau de bord</h1><p class="muted">Vue générale des stocks de ROUAH.</p></div><a class="btn" href="stocks.php">+ Nouveau mouvement</a></div>
<div class="stats-grid">
<div class="stat-card"><span>Articles actifs</span><strong><?= $stats['produits'] ?></strong><small>Références enregistrées</small></div>
<div class="stat-card"><span>Quantité en stock</span><strong><?= number_format($stats['quantite'], 2, ',', ' ') ?></strong><small>Toutes unités confondues</small></div>
<div class="stat-card"><span>Alertes de stock</span><strong class="<?= $stats['alertes'] ? 'text-danger' : 'text-success' ?>"><?= $stats['alertes'] ?></strong><small>Au seuil ou en dessous</small></div>
<div class="stat-card"><span>Mouvements aujourd'hui</span><strong><?= $stats['mouvements'] ?></strong><small>Entrées, sorties, ajustements</small></div>
</div>
<div class="two-columns">
<section class="panel"><div class="panel-title"><h2>Derniers mouvements</h2><a href="stocks.php">Voir tout</a></div>
<?php if (!$recent): ?><div class="empty">Aucun mouvement enregistré pour le moment.</div><?php else: ?>
<div class="table-wrap"><table><thead><tr><th>Date</th><th>Article</th><th>Type</th><th>Quantité</th></tr></thead><tbody>
<?php foreach ($recent as $m): ?><tr><td><?= e(date('d/m/Y H:i', strtotime($m['date_mouvement']))) ?></td><td><b><?= e($m['nom'] ?? $m['produit']) ?></b><small><?= e($m['reference']) ?></small></td><td><span class="pill <?= e($m['type']) ?>"><?= e(ucfirst($m['type'])) ?></span></td><td><?= e($m['quantite']) ?></td></tr><?php endforeach; ?>
</tbody></table></div><?php endif; ?></section>
<section class="panel"><div class="panel-title"><h2>Alertes de stock</h2><a href="produits.php">Articles</a></div>
<?php if (!$alertes): ?><div class="empty">✓ Aucun article sous le seuil d'alerte.</div><?php else: ?>
<?php foreach ($alertes as $a): ?><div class="alert-row"><div><b><?= e($a['nom']) ?></b><small><?= e($a['reference']) ?></small></div><span class="stock-low"><?= e($a['quantite']) ?> <?= e($a['unite']) ?></span></div><?php endforeach; ?>
<?php endif; ?></section></div>
<?php require __DIR__ . '/includes/footer.php'; ?>
