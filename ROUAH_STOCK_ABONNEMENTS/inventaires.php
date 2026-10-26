<?php
require __DIR__ . '/config/database.php';
require __DIR__ . '/includes/functions.php';
exiger_connexion();
$titre = 'Inventaires';
$erreur = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifier_csrf();
    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'creer') {
            $libelle = trim($_POST['libelle'] ?? '');
            if ($libelle === '') throw new RuntimeException('Donnez un nom à cet inventaire.');
            $pdo->beginTransaction();
            $reference = 'INV-' . date('Ymd-His');
            $pdo->prepare('INSERT INTO inventaires (reference,libelle,utilisateur_id) VALUES (?,?,?)')
                ->execute([$reference,$libelle,$_SESSION['utilisateur']['id']]);
            $id = (int)$pdo->lastInsertId();
            $pdo->exec('INSERT INTO inventaire_lignes (inventaire_id,produit_id,stock_theorique) SELECT '.(int)$id.',id,quantite FROM produits WHERE actif=1');
            $pdo->commit();
            flash('Inventaire créé. Vous pouvez maintenant saisir les quantités comptées.');
            header('Location: inventaires.php?ouvrir='.$id); exit;
        }
        if ($action === 'compter') {
            $id = (int)($_POST['inventaire_id'] ?? 0);
            $pdo->beginTransaction();
            $check = $pdo->prepare("SELECT id FROM inventaires WHERE id=? AND statut='en_cours' FOR UPDATE");
            $check->execute([$id]);
            if (!$check->fetch()) throw new RuntimeException('Cet inventaire est introuvable ou déjà clôturé.');
            $ids = $_POST['ligne_id'] ?? [];
            $physiques = $_POST['stock_physique'] ?? [];
            $maj = $pdo->prepare('UPDATE inventaire_lignes SET stock_physique=?, ecart=? WHERE id=? AND inventaire_id=?');
            foreach ($ids as $index => $ligneId) {
                if (!isset($physiques[$index]) || $physiques[$index] === '') continue;
                $physique = (float)$physiques[$index];
                if ($physique < 0) throw new RuntimeException('Une quantité comptée ne peut pas être négative.');
                $l = $pdo->prepare('SELECT stock_theorique FROM inventaire_lignes WHERE id=? AND inventaire_id=?');
                $l->execute([(int)$ligneId,$id]);
                $ligne = $l->fetch();
                if ($ligne) $maj->execute([$physique,$physique-(float)$ligne['stock_theorique'],(int)$ligneId,$id]);
            }
            $pdo->commit();
            flash('Comptage enregistré.');
            header('Location: inventaires.php?ouvrir='.$id); exit;
        }
        if ($action === 'cloturer') {
            $id = (int)($_POST['inventaire_id'] ?? 0);
            $pdo->beginTransaction();
            $check = $pdo->prepare("SELECT id FROM inventaires WHERE id=? AND statut='en_cours' FOR UPDATE");
            $check->execute([$id]);
            if (!$check->fetch()) throw new RuntimeException('Inventaire introuvable ou déjà clôturé.');
            $incomplets = $pdo->prepare('SELECT COUNT(*) FROM inventaire_lignes WHERE inventaire_id=? AND stock_physique IS NULL');
            $incomplets->execute([$id]);
            if ((int)$incomplets->fetchColumn() > 0) throw new RuntimeException('Comptez tous les articles avant de clôturer.');
            $lignes = $pdo->prepare('SELECT il.*,p.nom,p.quantite AS stock_actuel FROM inventaire_lignes il JOIN produits p ON p.id=il.produit_id WHERE il.inventaire_id=?');
            $lignes->execute([$id]);
            foreach ($lignes->fetchAll() as $l) {
                $avant = (float)$l['stock_actuel'];
                $physique = (float)$l['stock_physique'];
                if ($physique !== $avant) {
                    $pdo->prepare('UPDATE produits SET quantite=? WHERE id=?')->execute([$physique,$l['produit_id']]);
                    $pdo->prepare("INSERT INTO mouvements_stock (produit_id,utilisateur_id,type,quantite,stock_avant,stock_apres,motif) VALUES (?,?, 'ajustement',?,?,?,?)")
                        ->execute([$l['produit_id'],$_SESSION['utilisateur']['id'],abs($physique-$avant),$avant,$physique,'Régularisation inventaire '.$id]);
                }
            }
            $pdo->prepare("UPDATE inventaires SET statut='termine',date_cloture=NOW() WHERE id=?")->execute([$id]);
            $pdo->commit();
            flash('Inventaire clôturé. Les stocks ont été ajustés selon le comptage physique.');
            header('Location: inventaires.php'); exit;
        }
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $erreur = $e->getMessage();
    }
}
$ouvert = null; $lignes = [];
if (isset($_GET['ouvrir'])) {
    $stmt = $pdo->prepare('SELECT * FROM inventaires WHERE id=?');
    $stmt->execute([(int)$_GET['ouvrir']]); $ouvert = $stmt->fetch();
    if ($ouvert) {
        $stmt = $pdo->prepare('SELECT il.*,p.reference,p.nom,p.unite FROM inventaire_lignes il JOIN produits p ON p.id=il.produit_id WHERE il.inventaire_id=? ORDER BY p.nom');
        $stmt->execute([$ouvert['id']]); $lignes = $stmt->fetchAll();
    }
}
$inventaires = $pdo->query('SELECT i.*,u.nom AS utilisateur,(SELECT COUNT(*) FROM inventaire_lignes il WHERE il.inventaire_id=i.id) AS nb_articles,(SELECT COUNT(*) FROM inventaire_lignes il WHERE il.inventaire_id=i.id AND il.stock_physique IS NOT NULL) AS nb_comptes FROM inventaires i LEFT JOIN utilisateurs u ON u.id=i.utilisateur_id ORDER BY i.date_creation DESC')->fetchAll();
require __DIR__ . '/includes/header.php';
?>
<div class="page-heading"><div><p class="eyebrow">COMPTAGE PHYSIQUE</p><h1>Inventaires</h1><p class="muted">Comparez le stock théorique au stock réellement compté.</p></div></div>
<?php if ($erreur): ?><div class="alert error"><?= e($erreur) ?></div><?php endif; ?>
<?php if ($ouvert && $ouvert['statut']==='en_cours'): ?>
<section class="panel"><div class="panel-title"><div><h2><?= e($ouvert['libelle']) ?></h2><p class="muted"><?= e($ouvert['reference']) ?> · Saisissez le comptage physique.</p></div><a href="inventaires.php" class="btn secondary">Retour</a></div>
<form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="compter"><input type="hidden" name="inventaire_id" value="<?= $ouvert['id'] ?>">
<div class="table-wrap"><table><thead><tr><th>Référence</th><th>Article</th><th>Stock théorique</th><th>Stock physique compté</th><th>Écart</th></tr></thead><tbody>
<?php foreach ($lignes as $l): ?><tr><td><?= e($l['reference']) ?></td><td><?= e($l['nom']) ?></td><td><?= e($l['stock_theorique']) ?> <?= e($l['unite']) ?></td><td><input class="count-input" type="number" name="stock_physique[]" min="0" step="0.01" value="<?= e($l['stock_physique'] ?? '') ?>" placeholder="Quantité comptée"><input type="hidden" name="ligne_id[]" value="<?= $l['id'] ?>"></td><td><?= $l['ecart']===null?'—':e(($l['ecart']>0?'+':'').$l['ecart']) ?></td></tr><?php endforeach; ?>
<?php if (!$lignes): ?><tr><td colspan="5" class="empty">Aucun article actif à compter.</td></tr><?php endif; ?>
</tbody></table></div><div class="form-actions"><button class="btn" type="submit">Enregistrer le comptage</button></div></form>
<form method="post" data-confirm="Clôturer l'inventaire et appliquer les quantités physiques aux stocks ?"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="cloturer"><input type="hidden" name="inventaire_id" value="<?= $ouvert['id'] ?>"><button class="btn danger" type="submit">Clôturer et régulariser les stocks</button></form>
</section>
<?php elseif ($ouvert): ?><div class="alert success">Cet inventaire est déjà clôturé.</div><?php endif; ?>
<section class="panel"><div class="panel-title"><h2>Créer un inventaire</h2></div><form method="post" class="form-grid"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="creer"><div class="span-2"><label>Nom de l'inventaire *</label><input name="libelle" required placeholder="Ex. Inventaire général — octobre 2026"></div><div class="form-actions"><button class="btn" type="submit">+ Démarrer l'inventaire</button></div></form></section>
<section class="panel"><div class="panel-title"><h2>Historique des inventaires</h2></div><div class="table-wrap"><table><thead><tr><th>Référence</th><th>Libellé</th><th>Date</th><th>Progression</th><th>Statut</th><th>Action</th></tr></thead><tbody>
<?php foreach ($inventaires as $i): ?><tr><td><?= e($i['reference']) ?></td><td><?= e($i['libelle']) ?></td><td><?= e(date('d/m/Y H:i',strtotime($i['date_creation']))) ?></td><td><?= e($i['nb_comptes']) ?> / <?= e($i['nb_articles']) ?></td><td><span class="pill <?= $i['statut']==='termine'?'success':'warning' ?>"><?= $i['statut']==='termine'?'Terminé':'En cours' ?></span></td><td><a href="inventaires.php?ouvrir=<?= $i['id'] ?>"><?= $i['statut']==='termine'?'Consulter':'Continuer' ?></a></td></tr><?php endforeach; ?>
<?php if (!$inventaires): ?><tr><td colspan="6" class="empty">Aucun inventaire. Créez votre premier inventaire ci-dessus.</td></tr><?php endif; ?>
</tbody></table></div></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
