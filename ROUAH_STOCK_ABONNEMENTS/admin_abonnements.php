<?php
require __DIR__ . '/config/database.php';
require __DIR__ . '/includes/functions.php';
exiger_connexion();
if (($_SESSION['utilisateur']['role'] ?? '') !== 'admin') { http_response_code(403); exit('Accès réservé à l’administrateur.'); }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifier_csrf();
    $id = (int)($_POST['abonnement_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    $stmt = $pdo->prepare("SELECT a.*, p.code FROM abonnements a JOIN plans_abonnement p ON p.id=a.plan_id WHERE a.id=? LIMIT 1");
    $stmt->execute([$id]); $a = $stmt->fetch();
    if ($a && in_array($action, ['activer','refuser'], true)) {
        if ($action === 'activer') {
            $jours = $a['periode'] === 'annuel' ? 365 : 30;
            $pdo->prepare("UPDATE abonnements SET statut='expire' WHERE utilisateur_id=? AND statut='actif'")->execute([$a['utilisateur_id']]);
            $pdo->prepare("UPDATE abonnements SET statut='actif', date_debut=NOW(), date_fin=DATE_ADD(NOW(), INTERVAL ? DAY), valide_par=?, date_validation=NOW() WHERE id=?")
                ->execute([$jours, (int)$_SESSION['utilisateur']['id'], $id]);
            flash('Abonnement activé.');
        } else {
            $pdo->prepare("UPDATE abonnements SET statut='refuse', valide_par=?, date_validation=NOW() WHERE id=?")->execute([(int)$_SESSION['utilisateur']['id'], $id]);
            flash('Demande refusée.', 'error');
        }
    }
    header('Location: admin_abonnements.php'); exit;
}
$titre='Gestion des abonnements';
$liste=$pdo->query("SELECT a.*, u.nom, u.email, p.nom AS plan_nom FROM abonnements a JOIN utilisateurs u ON u.id=a.utilisateur_id JOIN plans_abonnement p ON p.id=a.plan_id ORDER BY (a.statut='en_attente') DESC, a.id DESC")->fetchAll();
require __DIR__ . '/includes/header.php';
?>
<div class="page-heading"><div><h1>Gestion des abonnements</h1><p class="muted">Vérifiez les paiements avant d’activer Premium.</p></div></div>
<section class="panel"><div class="table-wrap"><table><thead><tr><th>Client</th><th>Formule</th><th>Montant</th><th>Paiement</th><th>Référence</th><th>Statut</th><th>Action</th></tr></thead><tbody>
<?php if (!$liste): ?><tr><td colspan="7">Aucune demande reçue.</td></tr><?php endif; ?>
<?php foreach($liste as $a): ?><tr><td><?= e($a['nom']) ?><br><span class="muted small"><?= e($a['email']) ?></span></td><td><?= e($a['plan_nom']) ?> — <?= e($a['periode']) ?></td><td><?= number_format((float)$a['montant'],0,',',' ') ?> FCFA</td><td><?= e($a['mode_paiement']) ?><?= $a['operateur'] ? '<br>'.e($a['operateur']) : '' ?></td><td><?= e($a['reference_paiement'] ?? '—') ?></td><td><?= e($a['statut']) ?></td><td><?php if($a['statut']==='en_attente'): ?><form method="post" class="inline-form"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="abonnement_id" value="<?= (int)$a['id'] ?>"><button class="btn small-btn" name="action" value="activer" data-confirm="Confirmer que le paiement a été vérifié ?">Activer</button><button class="btn danger small-btn" name="action" value="refuser">Refuser</button></form><?php else: ?>—<?php endif; ?></td></tr><?php endforeach; ?>
</tbody></table></div></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
