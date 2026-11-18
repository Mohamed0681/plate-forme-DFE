-- À exécuter UNE SEULE FOIS dans phpMyAdmin (base plateforme_dep) si la base existe déjà.
USE plateforme_dep;

ALTER TABLE publications
  ADD COLUMN media_type ENUM('document','livre','video','image') NOT NULL DEFAULT 'document' AFTER category_id,
  ADD COLUMN extension  VARCHAR(10)  NOT NULL DEFAULT 'pdf'              AFTER media_type,
  ADD COLUMN mime_type  VARCHAR(120) NOT NULL DEFAULT 'application/pdf'  AFTER extension,
  ADD COLUMN file_size  BIGINT UNSIGNED NOT NULL DEFAULT 0               AFTER mime_type;

INSERT IGNORE INTO categories(name) VALUES ('Livres et ouvrages'),('Vidéos institutionnelles'),('Photothèque');
