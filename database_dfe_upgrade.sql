-- Mise à jour d'une base existante (MariaDB / XAMPP / WAMP). Peut être relancée sans risque.
USE plateforme_dep;

ALTER TABLE publications ADD COLUMN IF NOT EXISTS cover_image VARCHAR(255) DEFAULT NULL AFTER file_size;

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
