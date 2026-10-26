<?php
require_once __DIR__ . '/config/database.php';

try {
    $nom = 'Administrateur';
    $email = 'admin@rouah.local';
    $motDePasse = password_hash('Admin123!', PASSWORD_DEFAULT);

    $sql = "INSERT INTO utilisateurs
            (nom, email, mot_de_passe, role, actif)
            VALUES (:nom,:email,:mot_de_passe, 'admin', 1)
            ON DUPLICATE KEY UPDATE
                nom = VALUES(nom),
                mot_de_passe = VALUES(mot_de_passe),
                role = 'admin',
                actif = 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nom, $email, $motDePasse]);

    echo "Administrateur prêt. Email : admin@rouah.local";
    echo "<br>Mot de passe : Admin123!";
    echo "<br><a href='login.php'>Aller à la connexion</a>";

} catch (PDOException $e) {
    echo "Erreur : " . htmlspecialchars($e->getMessage());
}
?>
