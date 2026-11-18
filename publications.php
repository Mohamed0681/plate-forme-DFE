<?php
require_once __DIR__.'/config/config.php';
require_once __DIR__.'/includes/functions.php';
require_once __DIR__.'/includes/media.php';
$pageTitle = 'Publications';
$types = media_types();
$q = trim($_GET['q'] ?? '');
$type = $_GET['type'] ?? '';
if (!isset($types[$type])) $type = '';

$where = []; $params = [];
if ($type !== '') { $where[] = 'p.media_type = ?'; $params[] = $type; }
if ($q !== '')    { $where[] = '(p.title LIKE ? OR p.description LIKE ?)'; $params[] = "%$q%"; $params[] = "%$q%"; }
$sql = "SELECT p.*, c.name category FROM publications p LEFT JOIN categories c ON c.id=p.category_id"
     . ($where ? ' WHERE '.implode(' AND ', $where) : '') . " ORDER BY p.id DESC";
$st = $pdo->prepare($sql); $st->execute($params); $items = $st->fetchAll();

$counts = $pdo->query("SELECT media_type, COUNT(*) n FROM publications GROUP BY media_type")->fetchAll(PDO::FETCH_KEY_PAIR);
$videos = array_filter($items, fn($d) => $d['media_type'] === 'video');
$images = array_filter($items, fn($d) => $d['media_type'] === 'image');
$files  = array_filter($items, fn($d) => in_array($d['media_type'], ['document','livre'], true));
include __DIR__.'/includes/header.php';
?>
<section class="page-banner"><div class="container"><span class="eyebrow">DOCUMENTATION</span><h1>Publications</h1><p>Rapports, documents, vidéos et images à consulter ou à télécharger.</p></div></section>
<section class="section"><div class="container">
 <nav class="media-tabs" aria-label="Filtrer par type">
  <a href="?<?= e(http_build_query(array_filter(['q'=>$q]))) ?>" class="<?= $type === '' ? 'active' : '' ?>">Tout</a>
  <?php foreach ($types as $k => $t): ?>
   <a href="?<?= e(http_build_query(array_filter(['type'=>$k,'q'=>$q]))) ?>" class="<?= $type === $k ? 'active' : '' ?>"><?= e($t['plural']) ?> <span><?= (int)($counts[$k] ?? 0) ?></span></a>
  <?php endforeach; ?>
 </nav>
 <form class="search-form" method="get">
  <?php if ($type): ?><input type="hidden" name="type" value="<?= e($type) ?>"><?php endif; ?>
  <input name="q" value="<?= e($q) ?>" placeholder="Rechercher un contenu...">
  <button class="btn">Rechercher</button>
 </form>

 <?php if ($videos): ?>
  <h2 class="media-title">Vidéos</h2>
  <div class="video-grid">
   <?php foreach ($videos as $d): ?>
    <figure class="video-card">
     <video controls preload="metadata" playsinline>
      <source src="<?= e(url('download.php?id='.(int)$d['id'].'&inline=1')) ?>" type="<?= e(media_mime_for_ext($d['extension'])) ?>">
      Votre navigateur ne lit pas cette vidéo.
     </video>
     <figcaption><strong><?= e($d['title']) ?></strong><small><?= e($d['description']) ?> · <?= e(format_size((int)$d['file_size'])) ?></small>
      <a class="text-link" href="<?= e(url('download.php?id='.(int)$d['id'])) ?>">Télécharger</a></figcaption>
    </figure>
   <?php endforeach; ?>
  </div>
 <?php endif; ?>

 <?php if ($images): ?>
  <h2 class="media-title">Images</h2>
  <div class="image-grid">
   <?php foreach ($images as $d): $src = url('download.php?id='.(int)$d['id'].'&inline=1'); ?>
    <a class="image-card" href="<?= e($src) ?>" data-lightbox data-caption="<?= e($d['title']) ?>">
     <img src="<?= e($src) ?>" alt="<?= e($d['title']) ?>" loading="lazy">
     <span><?= e($d['title']) ?></span>
    </a>
   <?php endforeach; ?>
  </div>
 <?php endif; ?>

 <?php if ($files): ?>
  <h2 class="media-title">Publications</h2>
  <div class="pub-grid">
   <?php foreach ($files as $d): $t = $types[$d['media_type']]; $cover = public_img_url($d['cover_image'] ?? null); ?>
    <article class="pub-card">
     <a class="pub-cover" href="<?= e(url('download.php?id='.(int)$d['id'].($d['extension'] === 'pdf' ? '&inline=1' : ''))) ?>"<?= $d['extension'] === 'pdf' ? ' target="_blank" rel="noopener"' : '' ?>>
      <?php if ($cover): ?><img src="<?= e($cover) ?>" alt="Couverture : <?= e($d['title']) ?>" loading="lazy"><?php else: ?><span class="ph"><?= e(strtoupper($d['extension'])) ?></span><?php endif; ?>
      <span class="pub-badge"><?= e(strtoupper($d['extension'])) ?></span>
     </a>
     <div class="pub-body">
      <small><?= e($d['category'] ?? $t['label']) ?><?= $d['published_at'] ? ' · '.e(date('d/m/Y', strtotime($d['published_at']))) : '' ?> · <?= e(format_size((int)$d['file_size'])) ?></small>
      <h3><?= e($d['title']) ?></h3>
      <?php if ($d['description']): ?><p><?= e($d['description']) ?></p><?php endif; ?>
      <div class="pub-actions">
       <?php if ($d['extension'] === 'pdf'): ?><a class="btn btn-ghost" target="_blank" rel="noopener" href="<?= e(url('download.php?id='.(int)$d['id'].'&inline=1')) ?>">Lire</a><?php endif; ?>
       <a class="btn" href="<?= e(url('download.php?id='.(int)$d['id'])) ?>">Télécharger</a>
      </div>
     </div>
    </article>
   <?php endforeach; ?>
  </div>
 <?php endif; ?>

 <?php if (!$items): ?><p class="muted">Aucun contenu trouvé.</p><?php endif; ?>
</div></section>
<?php include __DIR__.'/includes/footer.php'; ?>
