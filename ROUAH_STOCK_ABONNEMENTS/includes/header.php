<?php
require_once __DIR__ . '/functions.php';
exiger_connexion();
$page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($titre ?? 'ROUAH STOCK') ?></title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="topbar">
  <a class="brand" href="index.php"><span class="brand-mark">R</span><span>Rouah <b>Stock</b></span></a>
  <div class="top-right"><span><?= e($_SESSION['utilisateur']['nom'] ?? '') ?></span><a class="logout" href="logout.php">Déconnexion</a></div>
</header>
<div class="layout">
<aside class="sidebar">
  <a class="<?= $page==='index.php'?'active':'' ?>" href="index.php">⌂ <span>Tableau de bord</span></a>
  <a class="<?= $page==='produits.php'?'active':'' ?>" href="produits.php">▦ <span>Articles</span></a>
  <a class="<?= $page==='stocks.php'?'active':'' ?>" href="stocks.php">⇄ <span>Stocks</span></a>
  <a class="<?= $page==='inventaires.php'?'active':'' ?>" href="inventaires.php">☷ <span>Inventaires</span></a>
  <a class="<?= $page==='abonnement.php'?'active':'' ?>" href="abonnement.php">♙ <span>Mon abonnement</span></a>
  <?php if (($_SESSION['utilisateur']['role'] ?? '') === 'admin'): ?>
  <a class="<?= $page==='admin_abonnements.php'?'active':'' ?>" href="admin_abonnements.php">▤ <span>Abonnements clients</span></a>
  <?php endif; ?>
</aside>
<main class="content">
<?php afficher_flash(); ?>
