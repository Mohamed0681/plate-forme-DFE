<?php
require_once __DIR__.'/../config/config.php'; require_once __DIR__.'/../includes/functions.php'; require_login(); verify_csrf();
if(isset($_GET['delete'])){$st=$pdo->prepare("DELETE FROM news WHERE id=?");$st->execute([(int)$_GET['delete']]);header('Location: news.php');exit;}
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){$title=trim($_POST['title']??'');$summary=trim($_POST['summary']??'');$content=trim($_POST['content']??'');$status=$_POST['status']==='published'?'published':'draft';if($title===''||$content==='')$error='Titre et contenu obligatoires.';else{$slug=slugify($title).'-'.bin2hex(random_bytes(3));$date=$status==='published'?date('Y-m-d H:i:s'):null;$st=$pdo->prepare("INSERT INTO news(title,slug,summary,content,status,published_at) VALUES(?,?,?,?,?,?)");$st->execute([$title,$slug,$summary,$content,$status,$date]);header('Location: news.php');exit;}}
$items=$pdo->query("SELECT id,title,status,created_at FROM news ORDER BY id DESC")->fetchAll();$pageTitle='Gestion des actualités';include __DIR__.'/../includes/admin_header.php'; ?>
<div class="page-title"><h1>Actualités</h1><p>Créer, publier ou supprimer des articles.</p></div>
<?php if($error): ?><p class="alert err"><?=e($error)?></p><?php endif; ?>
<form class="panel" method="post"><h2>Nouvel article</h2><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><div class="form-grid">
 <label class="full">Titre<input name="title" required maxlength="220"></label>
 <label class="full">Résumé<textarea name="summary" rows="2"></textarea></label>
 <label class="full">Contenu<textarea name="content" rows="7" required></textarea></label>
 <label>Statut<select name="status"><option value="draft">Brouillon</option><option value="published">Publié</option></select></label>
 <div></div><button class="btn-g"><?=ai('check')?>Enregistrer</button></div></form>
<div class="table-wrap"><table class="table"><thead><tr><th>Titre</th><th>Statut</th><th>Créé le</th><th class="actions">Actions</th></tr></thead><tbody>
<?php foreach($items as $n): ?><tr><td><span class="t-title"><span class="chip">ART</span><?=e($n['title'])?></span></td><td><span class="tag <?= $n['status']==='published'?'ok':'warn' ?>"><?= $n['status']==='published'?'Publié':'Brouillon' ?></span></td><td class="num"><?=e(date('d/m/Y',strtotime($n['created_at'])))?></td><td class="actions"><a class="row-btn del" title="Supprimer" href="?delete=<?=(int)$n['id']?>" data-confirm="Supprimer cet article ?"><?=ai('trash')?></a></td></tr><?php endforeach; ?>
<?php if(!$items): ?><tr><td class="empty" colspan="4">Aucun article.</td></tr><?php endif; ?>
</tbody></table></div>
<?php include __DIR__.'/../includes/admin_footer.php'; ?>
