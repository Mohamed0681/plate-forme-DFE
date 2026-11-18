CREATE DATABASE IF NOT EXISTS plateforme_dep CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE plateforme_dep;

CREATE TABLE IF NOT EXISTS users (
 id INT AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(120) NOT NULL,
 email VARCHAR(190) NOT NULL UNIQUE,
 password VARCHAR(255) NOT NULL,
 role ENUM('admin','editor') NOT NULL DEFAULT 'editor',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS categories (
 id INT AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(120) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS news (
 id INT AUTO_INCREMENT PRIMARY KEY,
 title VARCHAR(220) NOT NULL,
 slug VARCHAR(240) NOT NULL UNIQUE,
 summary TEXT,
 content LONGTEXT NOT NULL,
 image VARCHAR(255) DEFAULT NULL,
 status ENUM('draft','published') NOT NULL DEFAULT 'draft',
 published_at DATETIME DEFAULT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS publications (
 id INT AUTO_INCREMENT PRIMARY KEY,
 title VARCHAR(220) NOT NULL,
 description TEXT,
 category_id INT DEFAULT NULL,
 media_type ENUM('document','livre','video','image') NOT NULL DEFAULT 'document',
 extension VARCHAR(10) NOT NULL DEFAULT 'pdf',
 mime_type VARCHAR(120) NOT NULL DEFAULT 'application/pdf',
 file_size BIGINT UNSIGNED NOT NULL DEFAULT 0,
 cover_image VARCHAR(255) DEFAULT NULL,
 file_name VARCHAR(255) NOT NULL,
 original_name VARCHAR(255) NOT NULL,
 published_at DATE DEFAULT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_publication_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS indicators (
 id INT AUTO_INCREMENT PRIMARY KEY,
 label VARCHAR(150) NOT NULL,
 value VARCHAR(80) NOT NULL,
 unit VARCHAR(40) DEFAULT NULL,
 period VARCHAR(40) DEFAULT NULL,
 source VARCHAR(180) DEFAULT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS messages (
 id INT AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(120) NOT NULL,
 email VARCHAR(190) NOT NULL,
 subject VARCHAR(200) NOT NULL,
 message TEXT NOT NULL,
 is_read TINYINT(1) NOT NULL DEFAULT 0,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT IGNORE INTO categories(name) VALUES
('Rapports économiques'),('Plans de développement'),('Notes de conjoncture'),('Textes officiels'),('Livres et ouvrages'),('Vidéos institutionnelles'),('Photothèque');

CREATE TABLE IF NOT EXISTS settings (
 k VARCHAR(60) PRIMARY KEY,
 v TEXT
);

CREATE TABLE IF NOT EXISTS org_members (
 id INT AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(150) NOT NULL DEFAULT '',
 title VARCHAR(190) NOT NULL,
 parent_id INT DEFAULT NULL,
 sort_order INT NOT NULL DEFAULT 0,
 photo VARCHAR(255) DEFAULT NULL,
 CONSTRAINT fk_org_parent FOREIGN KEY (parent_id) REFERENCES org_members(id) ON DELETE CASCADE
);

INSERT IGNORE INTO settings(k, v) VALUES
('director_name', ''),
('director_title', 'Directeur du Financement Extérieur'),
('director_word', 'Mot du directeur à renseigner depuis l’espace administrateur (menu Direction).'),
('address', 'Djibouti-Ville, République de Djibouti'),
('phone', ''), ('email', ''), ('hours', 'Dimanche – jeudi : 7h30 – 14h30'),
('map_lat', '11.5880'), ('map_lng', '43.1450');

INSERT IGNORE INTO org_members(id, name, title, parent_id, sort_order) VALUES
(1, '', 'Directeur', NULL, 1),
(2, '', 'Secrétariat de direction', 1, 1),
(3, '', 'Sous-direction de la mobilisation des ressources', 1, 2),
(4, '', 'Sous-direction de la stratégie de la dette', 1, 3),
(5, '', 'Service du personnel et du matériel', 1, 4);

-- Créer le premier compte administrateur avec create_admin.php, puis supprimer ce fichier.
