<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/helpers.php';

if (current_user()) {
    header('Location: ' . dashboard_url());
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $login = trim($_POST['login'] ?? '');
    $pass  = $_POST['password'] ?? '';

    $q = $pdo->prepare('SELECT id,nom,prenom,email,telephone,password,role,actif FROM users WHERE email=? OR telephone=? LIMIT 1');
    $q->execute([$login, $login]);
    $u = $q->fetch();

    if ($u && $u['actif'] && password_verify($pass, $u['password'])) {
        session_regenerate_id(true);
        unset($_SESSION['csrf']);
        unset($u['password'], $u['actif']);
        $_SESSION['user'] = $u;
        header('Location: ' . dashboard_url());
        exit;
    }
    $error = 'Identifiants incorrects ou compte désactivé.';
}

$pageTitle = 'Connexion';
require __DIR__ . '/includes/header.php';
?>

<section class="auth-wrap">
    <div class="auth-card">
        <span class="eyebrow">HEUREUX DE VOUS REVOIR</span>
        <h1>Connexion</h1>
        <p>Connectez-vous à votre espace HotelConnect.</p>
        
        <?php if ($error): ?>
            <div class="alert alert-danger" style="margin-bottom: 20px;"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post">
            <?= csrf_field() ?>
            
            <div class="form-group">
                <label class="form-label">Email ou téléphone</label>
                <input class="form-control" name="login" type="text" required value="<?= e($_POST['login'] ?? '') ?>" placeholder="ex: jean@gmail.com">
            </div>

            <div class="form-group">
                <label class="form-label">Mot de passe</label>
                <input class="form-control" type="password" name="password" required placeholder="••••••••">
            </div>

            <button class="btn btn-primary" type="submit">Se connecter →</button>
        </form>

        <p class="auth-switch">Pas encore de compte ? <a class="text-link" href="register.php">Créer un compte</a></p>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>