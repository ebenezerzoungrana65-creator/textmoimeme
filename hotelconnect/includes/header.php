<?php
require_once __DIR__ . '/../config/helpers.php';
$pageTitle = $pageTitle ?? 'HotelConnect';
$u = current_user();
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($pageTitle) ?> · HotelConnect</title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css">
</head>
<body>

<header class="site-header">
    <div class="container nav-wrap">
        
        <!-- LOGO -->
        <a class="brand" href="<?= BASE_URL ?>/index.php">
            <div class="brand-mark">H</div>
            <div>
                Hotel<span class="brand-light">Connect</span>
                <small>RÉSERVATION D'HÔTELS</small>
            </div>
        </a>

        <!-- MENU -->
        <nav class="nav-links" id="navLinks">
            <a href="<?= BASE_URL ?>/index.php">Accueil</a>
            <?php if ($u): ?>
                <a href="<?= dashboard_url() ?>" class="nav-user">Mon espace</a>
                <a href="<?= BASE_URL ?>/logout.php" style="color: #b91c1c;">Déconnexion</a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>/login.php" class="nav-login">Connexion</a>
                <a href="<?= BASE_URL ?>/register.php" class="nav-cta">S'inscrire</a>
            <?php endif; ?>
        </nav>

        <!-- BOUTON MOBILE -->
        <button class="mobile-menu" onclick="document.getElementById('navLinks').classList.toggle('open')">☰</button>

    </div>
</header>

<main>
<?php foreach (get_flashes() as $f): ?>
    <div class="alert alert-<?= $f['type'] === 'success' ? 'success' : 'danger' ?> container">
        <?= e($f['msg']) ?>
    </div>
<?php endforeach; ?>