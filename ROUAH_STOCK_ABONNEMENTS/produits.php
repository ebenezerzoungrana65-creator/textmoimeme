<?php
require __DIR__ . '/config/database.php';
require __DIR__ . '/includes/functions.php';
exiger_connexion();
$titre = 'Articles';
$erreur = '';
$edition = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifier_csrf();
    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'enregistrer') {
            $id = (int)($_POST['id'] ?? 0);
            $reference = trim($_POST['reference'] ?? '');
            $nom = trim($_POST['nom'] ?? '');
            $categorie = ($_POST['categorie_id'] ?? '') !== '' ? (int)$_POST['categorie_id'] : null;
            $unite = trim($_POST['unite'] ?? 'unité');
            $prixAchat = (float)($_POST['prix_achat'] ?? 0);
            $prixVente = (float)($_POST['prix_vente'] ?? 0);
            $seuil = (float)($_POST['seuil_alerte'] ?? 5);
            $description = trim($_POST['description'] ?? '');
            if ($reference === '' || $nom === '' || $prixAchat < 0 || $prixVente < 0 || $seuil < 0) {
                throw new RuntimeException('Remplissez la référence et le nom. Les montants et le seuil doivent être positifs.');
            }
            if ($id > 0) {
                $stmt = $pdo->prepare('UPDATE produits SET reference=?, nom=?, categorie_id=?, unite=?, prix_achat=?, prix_vente=?, seuil_alerte=?, description=? WHERE id=?');
                $stmt->execute([$reference,$nom,$categorie,$unite,$prixAchat,$prixVente,$seuil,$description,$id]);
                flash('Article modifié avec succès.');
            } else {
                $stmt = $pdo->prepare('INSERT INTO produits (reference,nom,categorie_id,unite,prix_achat,prix_vente,seuil_alerte,description) VALUES (?,?,?,?,?,?,?,?)');
                $stmt->execute([$reference,$nom,$categorie,$unite,$prixAchat,$prixVente,$seuil,$description]);
                flash('Article ajouté. Enregistrez une entrée dans Stocks pour définir sa quantité.');
            }
            header('Location: produits.php'); exit;
        }
        if ($action === 'supprimer') {
            $id = (int)($_POST['id'] ?? 0);
            $check = $pdo->prepare('SELECT COUNT(*) FROM mouvements_stock WHERE produit_id=?');
            $check->execute([$id]);
            if ((int)$check->fetchColumn() > 0) {
                $pdo->prepare('UPDATE produits SET actif=0 WHERE id=?')->execute([$id]);
                flash('Article archivé car il possède un historique.');
            } else {
                $pdo->prepare('DELETE FROM produits WHERE id=?')->execute([$id]);
                flash('Article supprimé.');
            }
            header('Location: produits.php'); exit;
        }
    } catch (Throwable $e) {
        $erreur = $e instanceof PDOException && $e->getCode() === '23000'
            ? 'Cette référence existe déjà. Choisissez une référence unique.'
            : $e->getMessage();
    }
}
if (isset($_GET['modifier'])) {
    $stmt = $pdo->prepare('SELECT * FROM produits WHERE id=? AND actif=1');
    $stmt->execute([(int)$_GET['modifier']]);
    $edition = $stmt->fetch();
}
$categories = $pdo->query('SELECT id,nom FROM categories ORDER BY nom')->fetchAll();
$recherche = trim($_GET['q'] ?? '');
$stmt = $pdo->prepare("SELECT p.*, c.nom AS categorie FROM produits p LEFT JOIN categories c ON c.id=p.categorie_id WHERE p.actif=1 AND (p.nom LIKE ? OR p.reference LIKE ?) ORDER BY p.nom");
$stmt->execute(['%'.$recherche.'%','%'.$recherche.'%']);
$produits = $stmt->fetchAll();
require __DIR__ . '/includes/header.php';
?>
<div class="page-heading"><div><p class="eyebrow">CATALOGUE</p><h1>Articles</h1><p class="muted">Références, prix, unités et seuils d'alerte.</p></div></div>
<?php if ($erreur): ?><div class="alert error"><?= e($erreur) ?></div><?php endif; ?>
<section class="panel">
<div class="panel-title"><h2><?= $edition ? 'Modifier un article' : 'Ajouter un article' ?></h2></div>
<form method="post" class="form-grid">
<input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="enregistrer"><input type="hidden" name="id" value="<?= e($edition['id'] ?? 0) ?>">
<div><label>Référence *</label><input name="reference" required maxlength="80" value="<?= e($edition['reference'] ?? '') ?>" placeholder="Ex. ART-001"></div>
<div><label>Nom de l'article *</label><input name="nom" required maxlength="150" value="<?= e($edition['nom'] ?? '') ?>" placeholder="Nom du produit"></div>
<div><label>Catégorie</label><select name="categorie_id"><option value="">Sans catégorie</option><?php foreach ($categories as $c): ?><option value="<?= $c['id'] ?>" <?= (string)($edition['categorie_id'] ?? '')===(string)$c['id']?'selected':'' ?>><?= e($c['nom']) ?></option><?php endforeach; ?></select></div>
<div><label>Unité</label><input name="unite" value="<?= e($edition['unite'] ?? 'unité') ?>" required placeholder="unité, carton, kg..."></div>
<div><label>Prix d'achat (FCFA)</label><input type="number" name="prix_achat" min="0" step="0.01" value="<?= e($edition['prix_achat'] ?? '0') ?>" required></div>
<div><label>Prix de vente (FCFA)</label><input type="number" name="prix_vente" min="0" step="0.01" value="<?= e($edition['prix_vente'] ?? '0') ?>" required></div>
<div><label>Seuil d'alerte</label><input type="number" name="seuil_alerte" min="0" step="0.01" value="<?= e($edition['seuil_alerte'] ?? '5') ?>" required></div>
<div class="span-2"><label>Description</label><input name="description" value="<?= e($edition['description'] ?? '') ?>" placeholder="Détails utiles (facultatif)"></div>
<div class="form-actions"><button class="btn" type="submit"><?= $edition ? 'Enregistrer les modifications' : 'Ajouter l’article' ?></button><?php if ($edition): ?><a class="btn secondary" href="produits.php">Annuler</a><?php endif; ?></div>
</form></section>
<section class="panel"><div class="panel-title"><h2>Liste des articles (<?= count($produits) ?>)</h2><form class="search-form" method="get"><input name="q" value="<?= e($recherche) ?>" placeholder="Rechercher un article..."><button class="btn secondary" type="submit">Rechercher</button></form></div>
<div class="table-wrap"><table><thead><tr><th>Référence</th><th>Article</th><th>Catégorie</th><th>Stock</th><th>Seuil</th><th>Prix vente</th><th>Actions</th></tr></thead><tbody>
<?php foreach ($produits as $p): ?><tr><td><?= e($p['reference']) ?></td><td><b><?= e($p['nom']) ?></b></td><td><?= e($p['categorie'] ?? '—') ?></td><td><span class="<?= $p['quantite'] <= $p['seuil_alerte'] ? 'text-danger' : 'text-success' ?>"><?= e($p['quantite']) ?> <?= e($p['unite']) ?></span></td><td><?= e($p['seuil_alerte']) ?></td><td><?= number_format((float)$p['prix_vente'],0,',',' ') ?> F</td><td class="actions"><a href="produits.php?modifier=<?= $p['id'] ?>">Modifier</a><form method="post" data-confirm="Supprimer ou archiver cet article ?"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="supprimer"><input type="hidden" name="id" value="<?= $p['id'] ?>"><button class="link-danger" type="submit">Supprimer</button></form></td></tr><?php endforeach; ?>
<?php if (!$produits): ?><tr><td colspan="7" class="empty">Aucun article trouvé.</td></tr><?php endif; ?>
</tbody></table></div></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
