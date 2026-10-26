<?php
require __DIR__ . '/config/database.php';
require __DIR__ . '/includes/functions.php';
exiger_connexion();
$titre = 'Mouvements de stock';
$erreur = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifier_csrf();
    $produitId = (int)($_POST['produit_id'] ?? 0);
    $type = $_POST['type'] ?? '';
    $quantite = (float)($_POST['quantite'] ?? 0);
    $motif = trim($_POST['motif'] ?? '');
    if (!in_array($type, ['entree','sortie','ajustement'], true) || $quantite <= 0) {
        $erreur = 'Choisissez un type et une quantité supérieure à zéro.';
    } else {
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare('SELECT id, quantite FROM produits WHERE id=? AND actif=1 FOR UPDATE');
            $stmt->execute([$produitId]);
            $produit = $stmt->fetch();
            if (!$produit) throw new RuntimeException('Article introuvable.');
            $avant = (float)$produit['quantite'];
            if ($type === 'entree') $apres = $avant + $quantite;
            elseif ($type === 'sortie') $apres = $avant - $quantite;
            else $apres = $quantite;
            if ($apres < 0) throw new RuntimeException('Stock insuffisant : cette sortie est impossible.');
            $pdo->prepare('UPDATE produits SET quantite=? WHERE id=?')->execute([$apres,$produitId]);
            $pdo->prepare('INSERT INTO mouvements_stock (produit_id,utilisateur_id,type,quantite,stock_avant,stock_apres,motif) VALUES (?,?,?,?,?,?,?)')
                ->execute([$produitId,$_SESSION['utilisateur']['id'],$type,$quantite,$avant,$apres,$motif]);
            $pdo->commit();
            flash('Mouvement enregistré. Le stock a été mis à jour.');
            header('Location: stocks.php'); exit;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $erreur = $e->getMessage();
        }
    }
}
$articles = $pdo->query('SELECT id,reference,nom,quantite,unite FROM produits WHERE actif=1 ORDER BY nom')->fetchAll();
$mouvements = $pdo->query('SELECT m.*,p.reference,p.nom,u.nom AS utilisateur FROM mouvements_stock m JOIN produits p ON p.id=m.produit_id LEFT JOIN utilisateurs u ON u.id=m.utilisateur_id ORDER BY m.date_mouvement DESC LIMIT 100')->fetchAll();
require __DIR__ . '/includes/header.php';
?>
<div class="page-heading"><div><p class="eyebrow">SUIVI DES QUANTITÉS</p><h1>Mouvements de stock</h1><p class="muted">Chaque entrée ou sortie est conservée dans l'historique.</p></div></div>
<?php if ($erreur): ?><div class="alert error"><?= e($erreur) ?></div><?php endif; ?>
<section class="panel"><div class="panel-title"><h2>Enregistrer un mouvement</h2></div>
<?php if (!$articles): ?><div class="empty">Ajoutez d'abord un article dans le module Articles.</div><?php else: ?>
<form method="post" class="form-grid">
<input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
<div><label>Article *</label><select name="produit_id" required><option value="">Choisir un article</option><?php foreach ($articles as $a): ?><option value="<?= $a['id'] ?>"><?= e($a['reference'].' — '.$a['nom'].' (stock : '.$a['quantite'].' '.$a['unite'].')') ?></option><?php endforeach; ?></select></div>
<div><label>Type d'opération *</label><select name="type" required><option value="entree">Entrée — réception</option><option value="sortie">Sortie — utilisation/vente</option><option value="ajustement">Ajustement — stock physique exact</option></select></div>
<div><label>Quantité *</label><input type="number" name="quantite" min="0.01" step="0.01" required></div>
<div><label>Motif / document</label><input name="motif" maxlength="255" placeholder="Ex. bon de livraison, casse..."></div>
<div class="form-actions"><button class="btn" type="submit">Enregistrer le mouvement</button></div>
</form><p class="hint">Pour « Ajustement », saisissez la quantité physique totale comptée, et non la différence. Les sorties ne peuvent pas rendre le stock négatif.</p>
<?php endif; ?></section>
<section class="panel"><div class="panel-title"><h2>Historique des mouvements</h2></div><div class="table-wrap"><table><thead><tr><th>Date</th><th>Référence</th><th>Article</th><th>Opération</th><th>Qté saisie</th><th>Avant</th><th>Après</th><th>Motif</th><th>Utilisateur</th></tr></thead><tbody>
<?php foreach ($mouvements as $m): ?><tr><td><?= e(date('d/m/Y H:i',strtotime($m['date_mouvement']))) ?></td><td><?= e($m['reference']) ?></td><td><?= e($m['nom']) ?></td><td><span class="pill <?= e($m['type']) ?>"><?= e(ucfirst($m['type'])) ?></span></td><td><?= e($m['quantite']) ?></td><td><?= e($m['stock_avant']) ?></td><td><?= e($m['stock_apres']) ?></td><td><?= e($m['motif'] ?: '—') ?></td><td><?= e($m['utilisateur'] ?: 'Compte supprimé') ?></td></tr><?php endforeach; ?>
<?php if (!$mouvements): ?><tr><td colspan="9" class="empty">Aucun mouvement enregistré.</td></tr><?php endif; ?>
</tbody></table></div></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
