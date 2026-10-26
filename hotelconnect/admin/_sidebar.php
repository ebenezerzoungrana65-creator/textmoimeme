<?php
$page = basename($_SERVER['PHP_SELF']);
?>
<aside class="admin-sidebar">
    <div class="admin-logo">
        <span class="logo-icon">🏨</span>
        <div>
            <strong>HotelConnect</strong>
            <small>Administration</small>
        </div>
    </div>

    <nav class="admin-nav">
        <a href="<?= BASE_URL ?>/admin/index.php" class="admin-link <?= $page === 'index.php' ? 'active' : '' ?>">
            <span>📊</span> Tableau de bord
        </a>
        <a href="<?= BASE_URL ?>/admin/hotels.php" class="admin-link <?= $page === 'hotels.php' ? 'active' : '' ?>">
            <span>🏨</span> Hôtels
        </a>
        <a href="<?= BASE_URL ?>/admin/reservations.php" class="admin-link <?= $page === 'reservations.php' ? 'active' : '' ?>">
            <span>📅</span> Réservations
        </a>
        <a href="<?= BASE_URL ?>/admin/users.php" class="admin-link <?= $page === 'users.php' ? 'active' : '' ?>">
            <span>👥</span> Utilisateurs
        </a>
        <a href="<?= BASE_URL ?>/logout.php" class="admin-link">
            <span>🚪</span> Déconnexion
        </a>
    </nav>

    <div class="admin-user">
        <div class="avatar-admin"><?= e(strtoupper(current_user()['prenom'][0] ?? 'A')) ?></div>
        <div>
            <strong><?= e(current_user()['prenom'] . ' ' . current_user()['nom']) ?></strong>
            <small>Administrateur</small>
        </div>
    </div>
</aside>