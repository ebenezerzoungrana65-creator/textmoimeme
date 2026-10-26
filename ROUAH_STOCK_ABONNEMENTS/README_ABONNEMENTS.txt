ROUAH STOCK — MODULE D'ABONNEMENTS

Tarifs configurés :
- Gratuit : 0 FCFA, limite proposée de 50 produits.
- Premium mensuel : 10 000 FCFA.
- Premium annuel : 100 000 FCFA.
- Paiement : manuel ou Mobile Money avec validation par l'administrateur.

INSTALLATION
1. Faites une copie de sauvegarde de votre base rouah_stock.
2. Ouvrez phpMyAdmin et sélectionnez la base rouah_stock.
3. Importez le fichier abonnements.sql (il modifie le rôle disponible et ajoute les tables plans_abonnement et abonnements).
4. Copiez les fichiers abonnement.php et admin_abonnements.php dans le dossier racine de ROUAH_STOCK.
5. Remplacez includes/header.php et assets/style.css par les versions fournies dans cette archive, ou reportez leurs ajouts dans vos fichiers existants.
6. Connectez-vous. Le menu « Mon abonnement » est disponible. Le menu « Abonnements clients » apparaît pour le rôle admin.
7. Le client soumet une demande Premium. L'administrateur vérifie réellement le paiement, puis clique sur « Activer ».

IMPORTANT
- Le formulaire Mobile Money ne débite pas automatiquement le client. Il enregistre l'opérateur et la référence de transaction ; ROUAH doit vérifier le paiement dans son compte Mobile Money avant l'activation.
- Les identifiants marchands et l'intégration API d'un opérateur ou agrégateur sont nécessaires pour automatiser le paiement.
- Ce module gère les demandes et la validation d'abonnement. Il ne suffit pas à rendre l'application multi-client sécurisée : avant d'ouvrir les comptes clients, il faut ajouter une propriété client_id/utilisateur_id aux produits, mouvements et inventaires, puis filtrer chaque requête par l'utilisateur connecté. Ne mettez pas l'application en production pour plusieurs clients avant cette isolation des données.
- Les fonctions Premium listées comme rapports avancés, exports et multi-magasins sont des fonctions prévues, pas toutes implémentées par ce module.
