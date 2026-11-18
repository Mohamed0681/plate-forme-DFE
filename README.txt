PLATEFORME INSTITUTIONNELLE PHP / MYSQL — GUIDE D'INSTALLATION

PRÉREQUIS
- XAMPP ou WAMP (PHP 8.1+ recommandé, MySQL/MariaDB)
- Extension PDO MySQL et Fileinfo activées
- Navigateur récent

SITE DFE (V4) — Direction du Financement Extérieur
- Pages publiques : Accueil, Le Directeur (photo + mot), Organigramme, Publications (PDF avec image de couverture), Actualités, Contact (adresse + carte OpenStreetMap).
- Admin (menu à gauche) : Le Directeur, Organigramme, Publications (couverture à l'ajout ou via l'icône image), Actualités, Adresse & carte, Indicateurs, Messages.
- Base de données :
    * Nouvelle installation : importer database.sql.
    * Base existante : importer database_dfe_upgrade.sql (MariaDB/XAMPP/WAMP ; relançable sans risque).
- Dossier uploads/public/ : doit être accessible en écriture (photos, couvertures). Il contient son propre .htaccess.
- Couleurs (identiques admin et site) : variables en haut de assets/css/admin.css et de assets/css/style.css.
- À remplacer : logo (assets/img/logo.png, actuellement celui du MEFI), images/vidéo du hero, textes « domaines d'intervention » de l'accueil, coordonnées GPS dans Admin > Adresse & carte.

ESPACE ADMIN (V3)
- Nouvelle interface : barre latérale verte, barre supérieure (heure, date, bouton Téléverser, mode sombre, profil), cartes et tableaux.
- Couleurs : variables en haut de assets/css/admin.css (--g vert, --y jaune). Gabarit : includes/admin_header.php.

DESIGN ANIMÉ (V2)
- Accueil : hero plein écran avec diaporama d'images (effet zoom lent) + vidéo de fond en boucle.
  Images du diaporama : déposer des .jpg/.png/.webp dans assets/img/hero/ (ordre alphabétique, aucun code à modifier).
  Vidéo de fond : remplacer assets/video/hero.mp4 (ou ajouter hero.webm). Conseil : 1280x720, 10-20 s, moins de 5 Mo, sans son.
  Les images et la vidéo fournies sont des illustrations génériques : à remplacer par vos vraies photos/vidéos.
- Sur mobile / économie de données / « réduire les animations », la vidéo n'est pas chargée.
- L'accueil affiche aussi la dernière vidéo et les 4 dernières images ajoutées dans la médiathèque (admin).
- Animations : apparition au défilement, compteurs des indicateurs, en-tête transparent puis plein, barre de progression.
- Tout est réglable dans la fin de assets/css/style.css (section « V2 ») et assets/js/app.js.

MISE À JOUR D'UNE INSTALLATION EXISTANTE (médiathèque + fond emblème)
1. Remplacer les fichiers du projet par ceux de cette version.
2. Dans phpMyAdmin, base plateforme_dep : importer database_upgrade.sql (UNE seule fois).
3. Pour les vidéos, modifier php.ini (WAMP : icône > PHP > php.ini) puis redémarrer Apache :
     upload_max_filesize = 512M
     post_max_size = 520M
     max_execution_time = 300
     max_input_time = 300
     memory_limit = 256M
4. Fond : assets/img/embleme-fond.png, réglage dans assets/css/style.css (body::before : opacity, taille).

INSTALLATION LOCALE
1. Décompresser le dossier plateforme_dep dans htdocs (XAMPP).
2. Démarrer Apache et MySQL.
3. Ouvrir http://localhost/phpmyadmin
4. Importer database.sql.
5. Ouvrir config/config.php et vérifier DB_HOST, DB_NAME, DB_USER, DB_PASS.
6. Vérifier que le dossier uploads/documents est accessible en écriture par PHP.
7. Ouvrir http://localhost/plateforme_dep/create_admin.php
8. Créer un compte administrateur avec un mot de passe fort (12 caractères minimum).
9. SUPPRIMER immédiatement create_admin.php du dossier.
10. Ouvrir http://localhost/plateforme_dep/ puis se connecter via /admin/login.php.

FONCTIONNALITÉS
- Pages publiques : accueil, missions, organisation, actualités, publications, contact.
- Admin : connexion, tableau de bord, création/suppression d'actualités,
  médiathèque (ajout/suppression de documents, livres, vidéos, images),
  indicateurs, consultation des messages.
- Médiathèque : PDF, DOCX, DOC, XLSX, PPTX, TXT, EPUB, MP4, WEBM, OGV, JPG, PNG, WEBP, GIF.
  Lecteur vidéo intégré, visionneuse d'images, lecture PDF en ligne, téléchargement.
- Requêtes préparées PDO, échappement HTML, jeton CSRF sur formulaires,
  mots de passe hachés, contrôle MIME/extension par type, limites par type (document 50 Mo, livre 100 Mo, vidéo 500 Mo, image 10 Mo),
  fichiers servis uniquement via download.php (accès direct interdit).

À PERSONNALISER AVANT TOUT USAGE RÉEL
- Le nom, logo, couleurs, textes, coordonnées et mentions légales.
- Les missions et organigrammes doivent être validés par l'institution.
- Les indicateurs doivent provenir de sources officielles, avec période et référence.
- Modifier les identifiants MySQL et utiliser HTTPS en production.
- Protéger l'accès à /admin, appliquer les mises à jour et sauvegardes.
- Ajouter limitation des tentatives de connexion, journalisation et politique de confidentialité.
- Ce projet est un socle fonctionnel de démonstration, pas une copie officielle ni un audit
  du site gouvernemental. Ne pas utiliser le logo ou l'identité de l'État sans autorisation.

STRUCTURE
config/       connexion PDO
includes/     fonctions et gabarits communs
admin/        espace de gestion
assets/       CSS et JavaScript
uploads/      fichiers PDF téléversés
database.sql  schéma de base de données
