DROP DATABASE IF EXISTS hotelconnect;
CREATE DATABASE hotelconnect CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE hotelconnect;

CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    telephone VARCHAR(30),
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','hotelier','client') NOT NULL DEFAULT 'client',
    actif TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE hotels (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    nom VARCHAR(180) NOT NULL,
    description TEXT,
    adresse VARCHAR(255),
    ville VARCHAR(100) NOT NULL,
    commune VARCHAR(100),
    pays VARCHAR(100) NOT NULL DEFAULT 'Côte d’Ivoire',
    telephone VARCHAR(30),
    email VARCHAR(190),
    categorie ENUM('Hôtel','Résidence','Appartement hôtel','Auberge','Complexe hôtelier','Villa','Resort') DEFAULT 'Hôtel',
    etoiles TINYINT UNSIGNED DEFAULT 0,
    statut ENUM('attente','valide','refuse','suspendu') NOT NULL DEFAULT 'attente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX (ville), INDEX (statut)
) ENGINE=InnoDB;

CREATE TABLE rooms (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    hotel_id BIGINT UNSIGNED NOT NULL,
    nom VARCHAR(150) NOT NULL,
    type VARCHAR(80) NOT NULL DEFAULT 'Chambre double',
    capacite TINYINT UNSIGNED NOT NULL DEFAULT 2,
    prix_nuit DECIMAL(12,2) NOT NULL,
    quantite TINYINT UNSIGNED NOT NULL DEFAULT 1,
    actif TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE reservations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(40) NOT NULL UNIQUE,
    user_id BIGINT UNSIGNED NOT NULL,
    hotel_id BIGINT UNSIGNED NOT NULL,
    room_id BIGINT UNSIGNED NOT NULL,
    date_arrivee DATE NOT NULL,
    date_depart DATE NOT NULL,
    personnes TINYINT UNSIGNED NOT NULL DEFAULT 1,
    nombre_chambres TINYINT UNSIGNED NOT NULL DEFAULT 1,
    prix_nuit DECIMAL(12,2) NOT NULL,
    total DECIMAL(12,2) NOT NULL,
    statut ENUM('attente','confirmee','payee','annulee','refusee') NOT NULL DEFAULT 'attente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (hotel_id) REFERENCES hotels(id),
    FOREIGN KEY (room_id) REFERENCES rooms(id),
    INDEX (statut), INDEX (created_at)
) ENGINE=InnoDB;

-- ========== DONNÉES DE DÉMO ==========
-- Mot de passe pour tous : password123
INSERT INTO users (nom, prenom, email, password, role) VALUES
('Admin', 'Super', 'admin@hotelconnect.ci', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1HlWJRgJbq5UuU2O1a3e4b5c6d7e8f9', 'admin'),
('Kouassi', 'Jean', 'jean@mail.ci', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1HlWJRgJbq5UuU2O1a3e4b5c6d7e8f9', 'hotelier'),
('Diallo', 'Aminata', 'amina@mail.ci', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1HlWJRgJbq5UuU2O1a3e4b5c6d7e8f9', 'hotelier'),
('Traoré', 'Fatou', 'fatou@mail.ci', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1HlWJRgJbq5UuU2O1a3e4b5c6d7e8f9', 'client'),
('Yao', 'Michel', 'michel@mail.ci', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1HlWJRgJbq5UuU2O1a3e4b5c6d7e8f9', 'client');

INSERT INTO hotels (user_id, nom, ville, commune, categorie, etoiles, statut, description) VALUES
(2, 'Hôtel Ivoire Palace', 'Abidjan', 'Cocody', 'Hôtel', 5, 'valide', 'Hôtel de luxe au cœur d\'Abidjan.'),
(2, 'Résidence Les Palmiers', 'Abidjan', 'Marcory', 'Résidence', 4, 'valide', 'Résidence moderne et calme.'),
(3, 'Auberge du Lac', 'Yamoussoukro', 'Centre', 'Auberge', 3, 'valide', 'Vue imprenable sur le lac.'),
(3, 'Villa Baobab', 'Bouaké', 'Air France', 'Villa', 4, 'attente', 'Villa privée avec piscine.'),
(2, 'Resort Assinie', 'Assinie', 'Plage', 'Resort', 5, 'attente', 'Resort face à l\'océan.');

INSERT INTO rooms (hotel_id, nom, type, capacite, prix_nuit, quantite) VALUES
(1, 'Suite Présidentielle', 'Suite', 4, 85000, 2),
(1, 'Chambre Deluxe', 'Double', 2, 45000, 10),
(1, 'Chambre Standard', 'Double', 2, 25000, 15),
(2, 'Studio Meublé', 'Studio', 2, 30000, 5),
(3, 'Dortoir 4 lits', 'Dortoir', 4, 10000, 8);

INSERT INTO reservations (code, user_id, hotel_id, room_id, date_arrivee, date_depart, personnes, nombre_chambres, prix_nuit, total, statut) VALUES
('RES-2026-A1B2', 4, 1, 2, '2026-11-01', '2026-11-05', 2, 1, 45000, 180000, 'confirmee'),
('RES-2026-C3D4', 5, 2, 4, '2026-11-10', '2026-11-12', 2, 1, 30000, 60000, 'attente'),
('RES-2026-E5F6', 4, 3, 5, '2026-12-01', '2026-12-03', 3, 2, 10000, 40000, 'payee'),
('RES-2026-G7H8', 5, 1, 3, '2026-12-15', '2026-12-20', 2, 1, 25000, 125000, 'annulee');