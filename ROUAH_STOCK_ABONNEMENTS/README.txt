ROUAH STOCK — Gestion des stocks et des inventaires
===================================================

Technologies : PHP 8+, PDO, MySQL/MariaDB, HTML, CSS, JavaScript.
Interface inspirée des vidéos ROUAH fournies : barre supérieure vert foncé, navigation latérale,
modules Articles, Stocks, Inventaires et tableau de bord.

INSTALLATION SUR WAMPSERVER
1. Copiez le dossier ROUAH_STOCK dans C:\wamp64\www\
2. Démarrez WampServer et vérifiez que l'icône est verte.
3. Ouvrez http://localhost/phpmyadmin
4. Importez le fichier database.sql.
5. Vérifiez config/database.php (par défaut : hôte localhost, utilisateur root, mot de passe vide).
6. Ouvrez http://localhost/ROUAH_STOCK/install.php une seule fois.
7. Connectez-vous sur http://localhost/ROUAH_STOCK/login.php avec :
   Identifiant : admin@rouah.local
   Mot de passe : Admin123!
8. Après la création du compte, supprimez ou renommez install.php.
   Changez le mot de passe de démonstration avant toute utilisation réelle.

FONCTIONNALITÉS
- Tableau de bord : nombre de produits, quantité totale, alertes, mouvements récents.
- Articles : ajouter, modifier, supprimer et rechercher les produits.
- Stocks : entrées, sorties et ajustements, avec journal des mouvements.
- Inventaires : créer une session, compter les quantités réelles et enregistrer les écarts.
- Protection : PDO et requêtes préparées, mot de passe haché, jeton CSRF, contrôle des quantités.

IMPORTANT
Cette version est une base pédagogique à adapter aux règles exactes de ROUAH.
Testez d'abord avec des données fictives. Pour un déploiement réel, ajoutez HTTPS,
une politique de sauvegarde, des rôles détaillés et une revue de sécurité.
