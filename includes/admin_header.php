<?php
/** Gabarit de l'espace administrateur : barre latérale + barre supérieure. */
require_once __DIR__ . '/functions.php';
if (!function_exists('ai')) {
    function ai(string $n): string {
        static $p = [
            'grid'    => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>',
            'news'    => '<path d="M4 4h13a1 1 0 0 1 1 1v14a2 2 0 0 0 2 2H6a2 2 0 0 1-2-2V4z"/><path d="M18 8h2a1 1 0 0 1 1 1v10a2 2 0 0 1-2 2"/><path d="M8 8h6M8 12h6M8 16h4"/>',
            'folder'  => '<path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>',
            'chart'   => '<path d="M3 3v18h18"/><path d="M8 17V9M13 17V5M18 17v-6"/>',
            'inbox'   => '<path d="M22 12h-6l-2 3h-4l-2-3H2"/><path d="M5.5 5h13l3.5 7v6a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2v-6z"/>',
            'globe'   => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18"/>',
            'upload'  => '<path d="M12 19V5M5 12l7-7 7 7"/>',
            'moon'    => '<path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"/>',
            'logout'  => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>',
            'trash'   => '<path d="M3 6h18M8 6V4h8v2M6 6l1 14h10l1-14M10 10v6M14 10v6"/>',
            'plus'    => '<path d="M12 5v14M5 12h14"/>',
            'menu'    => '<path d="M4 6h16M4 12h16M4 18h16"/>',
            'eye'     => '<path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>',
            'download'=> '<path d="M12 5v14M5 12l7 7 7-7"/>',
            'check'   => '<path d="M5 12l5 5 9-10"/>',
            'user'    => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
            'sitemap' => '<rect x="9" y="3" width="6" height="5" rx="1"/><rect x="3" y="16" width="6" height="5" rx="1"/><rect x="15" y="16" width="6" height="5" rx="1"/><path d="M12 8v4M6 16v-4h12v4"/>',
            'pin'     => '<path d="M12 21s7-6.2 7-12a7 7 0 0 0-14 0c0 5.8 7 12 7 12z"/><circle cx="12" cy="9" r="2.5"/>',
            'image'   => '<rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="9" cy="10" r="1.6"/><path d="M21 16l-5-5-9 9"/>',
            'edit'    => '<path d="M4 20h4L19 9l-4-4L4 16z"/>',
            'clock'   => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        ];
        return '<svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($p[$n] ?? '') . '</svg>';
    }
}
$cur = basename($_SERVER['SCRIPT_NAME'] ?? '');
$unread = 0;
try { $unread = (int)$pdo->query("SELECT COUNT(*) n FROM messages WHERE is_read=0")->fetch()['n']; } catch (Throwable $e) {}
$nav = [
    ['dashboard.php',    'Tableau de bord', 'grid'],
    ['direction.php',    'Le Directeur',    'user'],
    ['organigramme.php', 'Organigramme',    'sitemap'],
    ['publications.php', 'Publications',    'folder'],
    ['news.php',         'Actualités',      'news'],
    ['settings.php',     'Adresse & carte', 'pin'],
    ['indicators.php',   'Indicateurs',     'chart'],
    ['messages.php',     'Messages',        'inbox'],
];
$userName = $_SESSION['user_name'] ?? 'Administrateur';
$initials = strtoupper(mb_substr(implode('', array_map(fn($w) => mb_substr($w, 0, 1), array_slice(preg_split('/\s+/', trim($userName)), 0, 2))), 0, 2));
$months = ['janvier','février','mars','avril','mai','juin','juillet','août','septembre','octobre','novembre','décembre'];
$days = ['dimanche','lundi','mardi','mercredi','jeudi','vendredi','samedi'];
$dateFr = ucfirst($days[(int)date('w')]) . ' ' . date('j') . ' ' . $months[(int)date('n') - 1] . ' ' . date('Y');
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle ?? 'Administration') ?> | Administration</title>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">
<link rel="stylesheet" href="<?= e(url('assets/css/admin.css')) ?>">
<script>try{var t=localStorage.getItem('adm-theme');if(t)document.documentElement.setAttribute('data-theme',t)}catch(e){}</script>
<script defer src="<?= e(url('assets/js/admin.js')) ?>"></script>
</head>
<body class="admin">
<div class="adm-shell">
 <aside class="adm-side" id="adm-side">
  <a class="adm-logo" href="<?= e(url('admin/dashboard.php')) ?>"><img src="<?= e(url('assets/img/logo.png')) ?>" alt="Logo" width="96" height="96"></a>
  <div class="adm-brand">DFE.ADMIN</div>
  <nav aria-label="Administration">
   <?php foreach ($nav as [$file, $label, $icon]): ?>
    <a href="<?= e(url('admin/' . $file)) ?>" class="<?= $cur === $file ? 'active' : '' ?>"><?= ai($icon) ?><span><?= e($label) ?></span><?php if ($file === 'messages.php' && $unread): ?><b class="pill"><?= $unread ?></b><?php endif; ?></a>
   <?php endforeach; ?>
   <p class="adm-group">SITE</p>
   <a href="<?= e(url('index.php')) ?>" target="_blank" rel="noopener"><?= ai('globe') ?><span>Voir le site public</span></a>
  </nav>
 </aside>
 <div class="adm-scrim" id="adm-scrim"></div>
 <div class="adm-main">
  <header class="adm-top">
   <button class="adm-burger" id="adm-burger" aria-label="Menu" type="button"><?= ai('menu') ?></button>
   <div class="adm-clock"><strong id="adm-time"><?= date('H:i') ?></strong><small><?= e($dateFr) ?></small></div>
   <div class="adm-top-right">
    
    <button class="icon-btn" id="adm-theme" type="button" aria-label="Mode sombre"><?= ai('moon') ?></button>
    <span class="adm-sep"></span>
    <div class="adm-user"><span class="avatar"><?= e($initials) ?></span><span class="uname"><?= e($userName) ?></span></div>
    <a class="icon-btn" href="<?= e(url('admin/logout.php')) ?>" aria-label="Déconnexion" title="Déconnexion"><?= ai('logout') ?></a>
   </div>
  </header>
  <main class="adm-content">
