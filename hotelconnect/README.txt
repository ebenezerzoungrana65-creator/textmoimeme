HOTELCONNECT — INSTALLATION WAMPSERVER

Prérequis : WampServer avec PHP 8+ et MySQL/MariaDB.

1. Copiez le dossier hotelconnect dans C:\wamp64\www\hotelconnect.
2. Démarrez WampServer et ouvrez phpMyAdmin : http://localhost/phpmyadmin
3. Importez le fichier database.sql. Il crée la base hotelconnect et les tables nécessaires.
4. Vérifiez config/config.php : DB_HOST=localhost, DB_NAME=hotelconnect, DB_USER=root, DB_PASS='' (valeur habituelle Wamp local, à adapter si votre mot de passe MySQL est différent).
5. Ouvrez http://localhost/hotelconnect/installer_admin.php pour créer votre compte administrateur. Choisissez un mot de passe fort. Supprimez ensuite installer_admin.php.
6. Ouvrez http://localhost/hotelconnect/ pour accéder au site.
7. Créez un compte hôtelier via « Inscrire mon hôtel », puis connectez-vous comme administrateur pour valider l’hôtel. L’hôtelier peut ensuite ajouter ses chambres.
8. Créez un compte client pour rechercher et réserver les chambres publiées.

RÔLES
- admin : validation et supervision des hôtels.
- hotelier : profil hôtel, chambres, consultation des réservations.
- client : recherche, demande de réservation, historique et annulation selon statut.

IMPORTANT
- Les réservations de cette version sont des demandes et le paiement se fait à l’hôtel. Aucun paiement Mobile Money/carte n’est intégré.
- Les photos multiples, récupération par email/SMS, messagerie, calendrier visuel, factures PDF et intégration de paiement restent à développer.
- Pour activer les photos, créez des dossiers uploads/hotels et uploads/rooms accessibles en écriture par PHP. Cette première version n’a pas encore de formulaire d’upload d’images.
- Avant une mise en production : HTTPS, sauvegardes, limitation des tentatives de connexion, vérification email/téléphone, politique de confidentialité, tests de sécurité et règles de disponibilité plus avancées.
