USE rouah_stock;

ALTER TABLE utilisateurs MODIFY role ENUM('admin','gestionnaire','lecture','client') NOT NULL DEFAULT 'client';

CREATE TABLE IF NOT EXISTS plans_abonnement (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(30) NOT NULL UNIQUE,
  nom VARCHAR(80) NOT NULL,
  prix_mensuel DECIMAL(12,2) NOT NULL DEFAULT 0,
  prix_annuel DECIMAL(12,2) NOT NULL DEFAULT 0,
  limite_produits INT NULL,
  actif TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

INSERT INTO plans_abonnement (code, nom, prix_mensuel, prix_annuel, limite_produits, actif)
VALUES ('gratuit','Gratuit',0,0,50,1), ('premium','Premium',10000,100000,NULL,1)
ON DUPLICATE KEY UPDATE nom=VALUES(nom), prix_mensuel=VALUES(prix_mensuel), prix_annuel=VALUES(prix_annuel), limite_produits=VALUES(limite_produits), actif=1;

CREATE TABLE IF NOT EXISTS abonnements (
  id INT AUTO_INCREMENT PRIMARY KEY,
  utilisateur_id INT NOT NULL,
  plan_id INT NOT NULL,
  periode ENUM('mensuel','annuel') NOT NULL DEFAULT 'mensuel',
  montant DECIMAL(12,2) NOT NULL DEFAULT 0,
  mode_paiement ENUM('gratuit','manuel','mobile_money') NOT NULL DEFAULT 'gratuit',
  operateur VARCHAR(60) NULL,
  reference_paiement VARCHAR(120) NULL,
  statut ENUM('en_attente','actif','refuse','expire') NOT NULL DEFAULT 'en_attente',
  date_debut DATETIME NULL,
  date_fin DATETIME NULL,
  date_demande TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  valide_par INT NULL,
  date_validation DATETIME NULL,
  CONSTRAINT fk_abonnement_utilisateur FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
  CONSTRAINT fk_abonnement_plan FOREIGN KEY (plan_id) REFERENCES plans_abonnement(id),
  CONSTRAINT fk_abonnement_validateur FOREIGN KEY (valide_par) REFERENCES utilisateurs(id) ON DELETE SET NULL,
  INDEX idx_abonnement_user_statut (utilisateur_id, statut)
) ENGINE=InnoDB;
