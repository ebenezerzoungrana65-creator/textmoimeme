<?php
require __DIR__ . '/config/database.php';
require __DIR__ . '/includes/functions.php';
exiger_connexion();
$titre = 'Mon abonnement';
$uid = (int)$_SESSION['utilisateur']['id'];
$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifier_csrf();
    $periode = $_POST['periode'] ?? 'mensuel';
    $mode = $_POST['mode_paiement'] ?? 'manuel';
    $operateur = trim($_POST['operateur'] ?? '');
    $reference = trim($_POST['reference_paiement'] ?? '');
    if (!in_array($periode, ['mensuel','annuel'], true) || !in_array($mode, ['manuel','mobile_money'], true)) {
        $erreur = 'Choix invalide.';
    } elseif ($mode === 'mobile_money' && ($operateur === '' || $reference === '')) {
        $erreur = 'Pour Mobile Money, indiquez l’opérateur et la référence de transaction.';
    } else {
        $planStmt = $pdo->prepare("SELECT id, prix_mensuel, prix_annuel FROM plans_abonnement WHERE code='premium' AND actif=1 LIMIT 1");
        $planStmt->execute();
        $plan = $planStmt->fetch();
        if (!$plan) {
            $erreur = 'Le plan Premium est indisponible pour le moment.';
        } else {
            $montant = (float)($periode === 'mensuel' ? $plan['prix_mensuel'] : $plan['prix_annuel']);
            $stmt = $pdo->prepare("INSERT INTO abonnements (utilisateur_id, plan_id, periode, montant, mode_paiement, operateur, reference_paiement, statut) VALUES (?, ?, ?, ?, ?, ?, ?, 'en_attente')");
            $stmt->execute([$uid, $plan['id'], $periode, $montant, $mode, $mode === 'mobile_money' ? $operateur : null, $mode === 'mobile_money' ? $reference : null]);
            flash('Demande Premium envoyée. Elle sera activée après vérification du paiement.');
            header('Location: abonnement.php'); exit;
        }
    }
}

$actifStmt = $pdo->prepare("SELECT a.*, p.nom AS plan_nom FROM abonnements a JOIN plans_abonnement p ON p.id=a.plan_id WHERE a.utilisateur_id=? AND a.statut IN ('actif','en_attente') ORDER BY a.id DESC LIMIT 1");
$actifStmt->execute([$uid]); $monAbonnement = $actifStmt->fetch();
$histStmt = $pdo->prepare("SELECT a.*, p.nom AS plan_nom FROM abonnements a JOIN plans_abonnement p ON p.id=a.plan_id WHERE a.utilisateur_id=? ORDER BY a.id DESC LIMIT 10");
$histStmt->execute([$uid]); $historique = $histStmt->fetchAll();
require __DIR__ . '/includes/header.php';
?>
<div class="page-heading"><div><h1>Mon abonnement</h1><p class="muted">Choisissez la formule adaptée à votre activité.</p></div></div>
<?php if ($erreur): ?><div class="alert error"><?= e($erreur) ?></div><?php endif; ?>
<div class="cards-grid">
 <section class="panel"><h2>Gratuit</h2><p class="price">0 FCFA</p><p>Pour démarrer la gestion de vos stocks.</p><ul><li>Jusqu’à 50 produits</li><li>Entrées et sorties de stock</li><li>Inventaires de base</li><li>Alertes de stock faible</li></ul><p class="muted small">La formule gratuite est disponible par défaut.</p></section>
 <section class="panel"><h2>Premium</h2><p class="price">10 000 FCFA <span class="muted small">/ mois</span></p><p>Ou <strong>100 000 FCFA / an</strong></p><ul><li>Plus de produits (sans limite définie)</li><li>Rapports avancés</li><li>Exports PDF/Excel prévus</li><li>Plusieurs magasins et utilisateurs prévus</li></ul>
 <?php if ($monAbonnement && $monAbonnement['statut']==='en_attente'): ?><div class="alert">Votre demande est en attente de vérification.</div><?php else: ?>
 <form method="post" class="form-grid"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
 <label>Durée<select name="periode"><option value="mensuel">Mensuel — 10 000 FCFA</option><option value="annuel">Annuel — 100 000 FCFA</option></select></label>
 <label>Mode de paiement<select name="mode_paiement" id="modePaiement"><option value="manuel">Paiement manuel (validation par ROUAH)</option><option value="mobile_money">Mobile Money</option></select></label>
 <div id="champsMobile" class="form-grid" style="display:none"><label>Opérateur Mobile Money<input name="operateur" placeholder="Orange Money, MTN, Moov... "></label><label>Référence de transaction<input name="reference_paiement" placeholder="Référence reçue après paiement"></label></div>
 <p class="muted small">Cette demande ne déclenche pas un paiement automatique. Pour Mobile Money, effectuez le paiement selon les instructions communiquées par ROUAH, puis indiquez sa référence.</p><button class="btn" type="submit">Demander Premium</button></form><?php endif; ?></section>
</div>
<section class="panel"><h2>Historique des demandes</h2><div class="table-wrap"><table><thead><tr><th>Formule</th><th>Montant</th><th>Paiement</th><th>Statut</th><th>Date</th></tr></thead><tbody>
<?php if (!$historique): ?><tr><td colspan="5">Aucune demande pour le moment.</td></tr><?php endif; ?>
<?php foreach ($historique as $a): ?><tr><td><?= e($a['plan_nom']) ?> (<?= e($a['periode']) ?>)</td><td><?= number_format((float)$a['montant'],0,',',' ') ?> FCFA</td><td><?= e($a['mode_paiement']) ?><?= $a['operateur'] ? ' — '.e($a['operateur']) : '' ?></td><td><?= e($a['statut']) ?></td><td><?= e($a['date_demande']) ?></td></tr><?php endforeach; ?>
</tbody></table></div></section>
<script>const mode=document.getElementById('modePaiement');const champs=document.getElementById('champsMobile');function actualiser(){champs.style.display=mode.value==='mobile_money'?'grid':'none';}mode.addEventListener('change',actualiser);actualiser();</script>
<?php require __DIR__ . '/includes/footer.php'; ?>
