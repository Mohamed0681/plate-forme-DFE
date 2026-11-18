<?php
require_once __DIR__.'/../config/config.php'; require_once __DIR__.'/../includes/functions.php'; require_once __DIR__.'/../includes/media.php'; require_login();
$pageTitle='Tableau de bord';
$cards=[
 ['Actualités','news.php',(int)$pdo->query("SELECT COUNT(*) n FROM news")->fetch()['n'],'article'],
 ['Publications','publications.php',(int)$pdo->query("SELECT COUNT(*) n FROM publications")->fetch()['n'],'contenu'],
 ['Messages non lus','messages.php',(int)$pdo->query("SELECT COUNT(*) n FROM messages WHERE is_read=0")->fetch()['n'],'message'],
 ['Indicateurs','indicators.php',(int)$pdo->query("SELECT COUNT(*) n FROM indicators")->fetch()['n'],'indicateur'],
];
$recent=$pdo->query("SELECT p.*, c.name category FROM publications p LEFT JOIN categories c ON c.id=p.category_id ORDER BY p.id DESC LIMIT 8")->fetchAll();
$types=media_types();
include __DIR__.'/../includes/admin_header.php'; ?>
<div class="page-title"><h1>Tableau de bord</h1><p>Bienvenue, <?=e($_SESSION['user_name'] ?? '')?>.</p></div>
<div class="cards4">
<?php foreach($cards as [$label,$link,$n,$unit]): ?><a class="sum-card" href="<?=e($link)?>"><h3><?=e($label)?></h3><span><?=$n?> <?=e($unit)?><?=$n>1?'s':''?></span></a><?php endforeach; ?>
</div>
<div class="toolbar">
 <a class="btn-g" href="news.php"><?=ai('plus')?>Nouvelle actualité</a>
 <a class="btn-o" href="publications.php"><?=ai('upload')?>Ajouter un média</a>
 <a class="btn-o" href="indicators.php"><?=ai('chart')?>Indicateur</a>
 <a class="btn-o" href="messages.php"><?=ai('inbox')?>Messages</a>
</div>
<div class="table-wrap"><table class="table"><thead><tr><th>Titre</th><th>Type</th><th>Catégorie</th><th>Date d’ajout</th><th>Taille</th><th class="actions">Actions</th></tr></thead><tbody>
<?php foreach($recent as $d): $t=$types[$d['media_type']]??$types['document']; ?>
<tr><td><span class="t-title"><span class="chip <?=e(['video'=>'vid','image'=>'img','livre'=>'liv'][$d['media_type']] ?? ($d['extension']==='pdf'?'pdf':''))?>"><?=e(strtoupper($d['extension']))?></span><?=e($d['title'])?></span></td><td><?=e($t['label'])?></td><td><?=e($d['category'] ?? '—')?></td><td class="num"><?=e($d['published_at'] ? date('d/m/Y',strtotime($d['published_at'])) : '—')?></td><td class="num"><?=e(format_size((int)$d['file_size']))?></td><td class="actions"><a class="row-btn" title="Ouvrir" target="_blank" rel="noopener" href="<?=e(url('download.php?id='.(int)$d['id'].'&inline=1'))?>"><?=ai('eye')?></a></td></tr>
<?php endforeach; if(!$recent): ?><tr><td class="empty" colspan="6">Aucun contenu pour le moment.</td></tr><?php endif; ?>
</tbody></table></div>
<?php include __DIR__.'/../includes/admin_footer.php'; ?>
