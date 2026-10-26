<?php
require __DIR__ . '/config/database.php';
$compte = $pdo->prepare('SELECT id FROM utilisateurs WHERE email = ?');
$compte->execute(['admin@rouah.local']);
if (!$compte->fetch()) {
    $hash = password_hash('Admin123!', PASSWORD_DEFAULT);
    $ajout = $pdo->prepare('INSERT INTO utilisateurs (nom,email,mot_de_passe,role) VALUES (?,?,?,?)');
    $ajout->execute(['Administrateur ROUAH', 'admin@rouah.local', $hash, 'admin']);
    $message = 'Compte administrateur créé.';
} else {
    $message = 'Le compte administrateur existe déjà.';
}
?>
<!DOCTYPE html><html lang="fr"><meta charset="UTF-8"><title>Installation ROUAH STOCK</title>
<link rel="stylesheet" href="assets/style.css"><body class="login-page"><section class="login-card">
<h1>Installation terminée</h1><p><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
<p>Identifiant : <b>admin@rouah.local</b><br>Mot de passe : <b>Admin123!</b></p>
<p><b>Important :</b> supprimez install.php puis connectez-vous.</p><a class="btn" href="login.php">Ouvrir la connexion</a>
</section></body></html>
