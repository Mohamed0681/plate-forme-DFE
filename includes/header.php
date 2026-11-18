<?php
require_once __DIR__ . '/functions.php';
$current = basename($_SERVER['SCRIPT_NAME'] ?? '');
$inAdmin = strpos($_SERVER['SCRIPT_NAME'] ?? '', '/admin/') !== false;
$navItems = [
    'index.php'         => 'Accueil',
    'direction.php'     => 'Le Directeur',
    'organigramme.php'  => 'Organigramme',
    'publications.php'  => 'Publications',
    'actualites.php'    => 'Actualités',
    'contact.php'       => 'Contact',
];
?>
<!doctype html>
<html lang="fr">
<head>
<script>document.documentElement.classList.add('js')</script>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle ?? SITE_NAME) ?> | <?= e(SITE_NAME) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">
<link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>">
<script defer src="<?= e(url('assets/js/app.js')) ?>"></script>
</head>
<body class="<?= e(trim(($bodyClass ?? '') && !is_logged_in() ? $bodyClass : '')) ?>">
<?php if (is_logged_in()): ?>
<div class="admin-bar">
 <div class="container">
  <nav aria-label="Administration">
   <a href="<?= e(url('admin/dashboard.php')) ?>" class="<?= $current === 'dashboard.php' ? 'active' : '' ?>">Tableau de bord</a>
   <a href="<?= e(url('admin/news.php')) ?>" class="<?= $current === 'news.php' ? 'active' : '' ?>">Actualités</a>
   <a href="<?= e(url('admin/publications.php')) ?>" class="<?= $inAdmin && $current === 'publications.php' ? 'active' : '' ?>">Médiathèque</a>
   <a href="<?= e(url('admin/indicators.php')) ?>" class="<?= $current === 'indicators.php' ? 'active' : '' ?>">Indicateurs</a>
   <a href="<?= e(url('admin/messages.php')) ?>" class="<?= $current === 'messages.php' ? 'active' : '' ?>">Messages</a>
  </nav>
  <span><?= e($_SESSION['user_name'] ?? '') ?> · <a href="<?= e(url('admin/logout.php')) ?>">Déconnexion</a></span>
 </div>
</div>
<?php endif; ?>
<header class="site-header">
 <div class="container nav-wrap">
  <a class="brand" href="<?= e(url('index.php')) ?>"><img class="brand-logo" src="<?= e(url('assets/img/logo.png')) ?>" alt="Logo" width="52" height="52"><span class="brand-text"><?= e(SITE_NAME) ?><small>République de Djibouti</small></span></a>
  <button class="menu-toggle" aria-label="Ouvrir le menu" aria-expanded="false">☰</button>
  <nav class="main-nav" aria-label="Navigation principale">
   <?php foreach ($navItems as $file => $label): ?>
   <a href="<?= e(url($file)) ?>"<?= (!$inAdmin && $current === $file) ? ' class="active" aria-current="page"' : '' ?>><?= e($label) ?></a>
   <?php endforeach; ?>
  </nav>
 </div>
</header>
<main>
