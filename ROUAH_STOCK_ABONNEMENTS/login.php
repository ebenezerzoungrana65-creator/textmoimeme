<?php
require __DIR__ . '/config/database.php';
require __DIR__ . '/includes/functions.php';

if (est_connecte()) {
    header('Location: index.php');
    exit;
}
$erreur = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifier_csrf();
    $email = trim($_POST['email'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';
    $requete = $pdo->prepare('SELECT id, nom, email, mot_de_passe, role FROM utilisateurs WHERE email = ? AND actif = 1 LIMIT 1');
    $requete->execute([$email]);
    $utilisateur = $requete->fetch();

    if ($utilisateur && password_verify($mot_de_passe, $utilisateur['mot_de_passe'])) {
        session_regenerate_id(true);
        unset($utilisateur['mot_de_passe']);
        $_SESSION['utilisateur'] = $utilisateur;
        header('Location: index.php');
        exit;
    }
    $erreur = 'Identifiant ou mot de passe incorrect.';
}
?>
<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Connexion - ROUAH STOCK</title><link rel="stylesheet" href="assets/style.css"></head>
<body class="login-page"><section class="login-card">
<div class="login-logo">R</div><h1>ROUAH STOCK</h1><p class="muted">Gestion des stocks et des inventaires</p>
<?php if ($erreur): ?><div class="alert error"><?= e($erreur) ?></div><?php endif; ?>
<form method="post">
<input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
<label>Adresse e-mail</label><input type="email" name="email" required autocomplete="username" placeholder="nom@rouah.ci">
<label>Mot de passe</label><input type="password" name="mot_de_passe" required autocomplete="current-password" placeholder="Votre mot de passe">
<button class="btn full" type="submit">Se connecter</button>
</form><p class="muted small">Application de gestion interne ROUAH</p>
</section></body></html>
