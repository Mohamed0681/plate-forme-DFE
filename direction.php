<?php
require_once __DIR__.'/config/config.php'; require_once __DIR__.'/includes/functions.php';
$pageTitle = 'Le Directeur';
$name = setting('director_name'); $role = setting('director_title', 'Directeur'); $photo = setting('director_photo'); $word = setting('director_word');
include __DIR__.'/includes/header.php';
?>
<section class="page-banner banner-missions"><div class="container"><span class="eyebrow">LA DIRECTION</span><h1>Le Directeur</h1><p>Présentation du Directeur et mot d’accueil.</p></div></section>
<section class="section"><div class="container director">
 <aside class="director-card" data-reveal="left">
  <div class="director-photo"><?php if ($photo): ?><img src="<?= e(public_img_url($photo)) ?>" alt="<?= e($name ?: $role) ?>"><?php else: ?><?= e(initials($name ?: 'DFE')) ?><?php endif; ?></div>
  <div class="meta"><h2><?= e($name ?: $role) ?></h2><p class="role"><?= e($role) ?></p></div>
 </aside>
 <article data-reveal="right">
  <span class="eyebrow">MOT DU DIRECTEUR</span>
  <h2 style="margin:6px 0 24px;font-size:30px">Mot du Directeur</h2>
  <div class="word"><?php foreach (preg_split('/\R{2,}/', trim($word)) as $para): ?><p><?= nl2br(e($para)) ?></p><?php endforeach; ?></div>
  <div class="signature"><strong><?= e($name ?: $role) ?></strong><span><?= e($role) ?></span></div>
 </article>
</div></section>
<?php include __DIR__.'/includes/footer.php'; ?>
