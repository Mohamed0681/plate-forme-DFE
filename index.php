<?php
require_once __DIR__.'/config/config.php';
require_once __DIR__.'/includes/functions.php';
$pageTitle='Accueil'; $bodyClass='has-hero'; $hero=hero_media();
$video=$pdo->query("SELECT id,title,description FROM publications WHERE media_type='video' ORDER BY id DESC LIMIT 1")->fetch();
$gallery=$pdo->query("SELECT id,title FROM publications WHERE media_type='image' ORDER BY id DESC LIMIT 4")->fetchAll();
$dirName=setting('director_name'); $dirRole=setting('director_title','Directeur'); $dirPhoto=setting('director_photo'); $dirWord=setting('director_word');
$news=$pdo->query("SELECT id,title,slug,summary,published_at FROM news WHERE status='published' ORDER BY published_at DESC, id DESC LIMIT 3")->fetchAll();
$docs=$pdo->query("SELECT id,title,description,extension,cover_image,published_at,file_size FROM publications WHERE media_type IN ('document','livre') ORDER BY id DESC LIMIT 4")->fetchAll();
$indicators=$pdo->query("SELECT label,value,unit,period FROM indicators ORDER BY id DESC LIMIT 4")->fetchAll();
include __DIR__.'/includes/header.php';
?>
<section class="hero">
 <div class="hero-media" aria-hidden="true">
  <div class="hero-slides"><?php foreach($hero['images'] as $i=>$img): ?><div class="slide<?= $i===0?' active':'' ?>" style="background-image:url('<?=e($img)?>')"></div><?php endforeach; ?></div>
  <?php if($hero['video']): ?><video class="hero-video" muted loop playsinline preload="metadata"<?= $hero['images']?' poster="'.e($hero['images'][0]).'"':'' ?>><source src="<?=e($hero['video']['src'])?>" type="<?=e($hero['video']['type'])?>"></video><?php endif; ?>
  <div class="hero-overlay"></div>
 </div>
 <div class="container hero-inner">
 <span class="eyebrow">RÉPUBLIQUE DE DJIBOUTI</span><h1>Direction du<br><em>Financement Extérieur</em></h1>
 <p>Mobilisation, suivi et gestion des financements extérieurs au service du développement du pays.</p>
 <div class="hero-actions"><a class="btn btn-light" href="publications.php">Consulter les publications</a><a class="btn btn-outline" href="organigramme.php">Voir l’organigramme</a></div>
 </div>
 <?php if(count($hero['images'])>1): ?><div class="hero-dots" aria-hidden="true"><?php foreach($hero['images'] as $i=>$img): ?><button type="button" tabindex="-1"<?= $i===0?' class="active"':'' ?>></button><?php endforeach; ?></div><?php endif; ?>
 <span class="scroll-hint" aria-hidden="true"></span>
</section>
<?php if($indicators): ?>
<section class="section section-stats"><div class="container"><div class="stats-grid"><?php foreach($indicators as $item): ?><article class="stat-card"><span><?=e($item['label'])?></span><strong><?=e($item['value'])?> <small><?=e($item['unit'])?></small></strong><small><?=e($item['period'])?></small></article><?php endforeach; ?></div></div></section>
<?php endif; ?>
<section class="section"><div class="container director-teaser">
 <div class="director-photo" data-reveal="left"><?php if($dirPhoto): ?><img src="<?=e(public_img_url($dirPhoto))?>" alt="<?=e($dirName ?: $dirRole)?>"><?php else: ?><?=e(initials($dirName ?: 'DFE'))?><?php endif; ?></div>
 <div data-reveal="right"><span class="eyebrow">MOT DU DIRECTEUR</span>
  <blockquote><?=e(mb_strlen($dirWord)>320 ? rtrim(mb_substr($dirWord,0,320)).'…' : $dirWord)?></blockquote>
  <cite><?=e($dirName ?: $dirRole)?><small><?=e($dirRole)?></small></cite>
  <a class="btn" href="direction.php">Lire le mot du Directeur</a></div>
</div></section>
<section class="section section-soft"><div class="container"><div class="section-heading"><div><span class="eyebrow">INSTITUTION</span><h2>Nos domaines d’intervention</h2></div><a class="text-link" href="organigramme.php">Organigramme →</a></div>
<div class="feature-grid"><article class="feature"><div class="feature-icon">01</div><h3>Mobilisation des ressources</h3><p>Identifier et mobiliser les financements extérieurs en appui aux priorités nationales.</p></article><article class="feature"><div class="feature-icon">02</div><h3>Stratégie de la dette</h3><p>Suivre l’endettement extérieur et contribuer à une stratégie de dette soutenable.</p></article><article class="feature"><div class="feature-icon">03</div><h3>Suivi des financements</h3><p>Suivre les accords, décaissements et projets financés par les partenaires.</p></article></div>
<p class="muted" style="margin-top:18px;font-size:13px">Textes à valider et à remplacer par les missions officielles de la Direction.</p>
</div></section>
<?php if($docs): ?>
<section class="section"><div class="container"><div class="section-heading"><div><span class="eyebrow">DOCUMENTATION</span><h2>Publications récentes</h2></div><a class="text-link" href="publications.php">Toutes les publications →</a></div>
<div class="pub-grid"><?php foreach($docs as $d): $cover=public_img_url($d['cover_image'] ?? null); ?>
<article class="pub-card"><a class="pub-cover" href="download.php?id=<?=(int)$d['id']?><?= $d['extension']==='pdf' ? '&inline=1' : '' ?>"<?= $d['extension']==='pdf' ? ' target="_blank" rel="noopener"' : '' ?>><?php if($cover): ?><img src="<?=e($cover)?>" alt="Couverture : <?=e($d['title'])?>" loading="lazy"><?php else: ?><span class="ph"><?=e(strtoupper($d['extension']))?></span><?php endif; ?><span class="pub-badge"><?=e(strtoupper($d['extension']))?></span></a>
<div class="pub-body"><small><?=e($d['published_at'] ? date('d/m/Y',strtotime($d['published_at'])) : '')?> · <?=e(format_size((int)$d['file_size']))?></small><h3><?=e($d['title'])?></h3><div class="pub-actions"><a class="btn" href="download.php?id=<?=(int)$d['id']?>">Télécharger</a></div></div></article>
<?php endforeach; ?></div></div></section>
<?php endif; ?>
<?php if($news): ?>
<section class="section section-soft"><div class="container"><div class="section-heading"><div><span class="eyebrow">ACTUALITÉS</span><h2>Dernières actualités</h2></div><a class="text-link" href="actualites.php">Toutes les actualités →</a></div><div class="cards-grid">
<?php foreach($news as $n): ?><article class="content-card"><div class="card-body"><span class="date"><?=e($n['published_at'] ? date('d/m/Y',strtotime($n['published_at'])) : '')?></span><h3><?=e($n['title'])?></h3><p><?=e($n['summary'])?></p><a class="text-link" href="article.php?slug=<?=urlencode($n['slug'])?>">Lire la suite →</a></div></article><?php endforeach; ?>
</div></div></section>
<?php endif; ?>
<?php if($video): ?>
<section class="parallax-band"><div class="container band-grid">
 <div data-reveal="left"><span class="eyebrow">EN IMAGES</span><h2><?=e($video['title'])?></h2><p><?=e($video['description'] ?: 'Découvrez notre présentation vidéo institutionnelle.')?></p><a class="btn btn-outline" href="publications.php?type=video">Toutes les vidéos</a></div>
 <div class="band-video" data-reveal="right"><video controls preload="metadata" playsinline><source src="<?=e(url('download.php?id='.(int)$video['id'].'&inline=1'))?>" type="video/mp4"></video></div>
</div></section>
<?php endif; ?>
<?php if($gallery): ?>
<section class="section"><div class="container"><div class="section-heading"><div><span class="eyebrow">PHOTOTHÈQUE</span><h2>Galerie</h2></div><a class="text-link" href="publications.php?type=image">Toutes les images →</a></div><div class="image-grid">
<?php foreach($gallery as $g): $src=url('download.php?id='.(int)$g['id'].'&inline=1'); ?><a class="image-card" href="<?=e($src)?>" data-lightbox data-caption="<?=e($g['title'])?>"><img src="<?=e($src)?>" alt="<?=e($g['title'])?>" loading="lazy"><span><?=e($g['title'])?></span></a><?php endforeach; ?>
</div></div></section>
<?php endif; ?>
<section class="parallax-band centered"><div class="container" data-reveal><span class="eyebrow">CONTACT</span><h2>Une question, une demande d’information ?</h2><p>Notre équipe vous répond dans les meilleurs délais.</p><div class="hero-actions"><a class="btn btn-light" href="contact.php">Nous écrire</a></div></div></section>
<?php include __DIR__.'/includes/footer.php'; ?>
