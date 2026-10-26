CREATE DATABASE IF NOT EXISTS rouah_stock
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE rouah_stock;

CREATE TABLE IF NOT EXISTS utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    role ENUM('admin','gestionnaire','lecture') NOT NULL DEFAULT 'gestionnaire',
    actif TINYINT(1) NOT NULL DEFAULT 1,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL UNIQUE,
    description VARCHAR(255) NULL,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS produits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reference VARCHAR(80) NOT NULL UNIQUE,
    nom VARCHAR(150) NOT NULL,
    categorie_id INT NULL,
    description TEXT NULL,
    unite VARCHAR(30) NOT NULL DEFAULT 'unité',
    prix_achat DECIMAL(12,2) NOT NULL DEFAULT 0,
    prix_vente DECIMAL(12,2) NOT NULL DEFAULT 0,
    quantite DECIMAL(12,2) NOT NULL DEFAULT 0,
    seuil_alerte DECIMAL(12,2) NOT NULL DEFAULT 5,
    actif TINYINT(1) NOT NULL DEFAULT 1,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_produits_categories FOREIGN KEY (categorie_id)
        REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS mouvements_stock (
    id INT AUTO_INCREMENT PRIMARY KEY,
    produit_id INT NOT NULL,
    utilisateur_id INT NULL,
    type ENUM('entree','sortie','ajustement') NOT NULL,
    quantite DECIMAL(12,2) NOT NULL,
    stock_avant DECIMAL(12,2) NOT NULL,
    stock_apres DECIMAL(12,2) NOT NULL,
    motif VARCHAR(255) NULL,
    date_mouvement TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_mouv_produit FOREIGN KEY (produit_id) REFERENCES produits(id),
    CONSTRAINT fk_mouv_user FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS inventaires (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reference VARCHAR(40) NOT NULL UNIQUE,
    libelle VARCHAR(150) NOT NULL,
    statut ENUM('en_cours','termine') NOT NULL DEFAULT 'en_cours',
    utilisateur_id INT NULL,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_cloture DATETIME NULL,
    CONSTRAINT fk_inv_user FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS inventaire_lignes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    inventaire_id INT NOT NULL,
    produit_id INT NOT NULL,
    stock_theorique DECIMAL(12,2) NOT NULL,
    stock_physique DECIMAL(12,2) NULL,
    ecart DECIMAL(12,2) NULL,
    UNIQUE KEY uq_inventaire_produit (inventaire_id, produit_id),
    CONSTRAINT fk_ligne_inventaire FOREIGN KEY (inventaire_id) REFERENCES inventaires(id) ON DELETE CASCADE,
    CONSTRAINT fk_ligne_produit FOREIGN KEY (produit_id) REFERENCES produits(id)
) ENGINE=InnoDB;

INSERT IGNORE INTO categories (nom, description) VALUES
('Fournitures', 'Fournitures et consommables'),
('Matériel informatique', 'Ordinateurs et accessoires'),
('Divers', 'Produits divers');
