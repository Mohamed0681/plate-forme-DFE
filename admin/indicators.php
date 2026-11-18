<?php require_once __DIR__.'/../config/config.php'; require_once __DIR__.'/../includes/functions.php'; require_login(); verify_csrf(); if(isset($_GET['delete'])){$pdo->prepare("DELETE FROM indicators WHERE id=?")->execute([(int)$_GET['delete']]);header('Location: indicators.php');exit;} if($_SERVER['REQUEST_METHOD']==='POST'){$st=$pdo->prepare("INSERT INTO indicators(label,value,unit,period,source) VALUES(?,?,?,?,?)");$st->execute([trim($_POST['label']),trim($_POST['value']),trim($_POST['unit']),trim($_POST['period']),trim($_POST['source'])]);header('Location: indicators.php');exit;} $items=$pdo->query("SELECT * FROM indicators ORDER BY id DESC")->fetchAll();$pageTitle='Indicateurs';include __DIR__.'/../includes/admin_header.php'; ?>
<div class="page-title"><h1>Indicateurs économiques</h1><p>Chiffres clés affichés sur la page d’accueil.</p></div>
<form class="panel" method="post"><h2>Nouvel indicateur</h2><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><div class="form-grid">
 <label>Nom de l’indicateur<input name="label" required></label><label>Valeur<input name="value" required></label>
 <label>Unité<input name="unit" placeholder="% / milliards FDJ"></label><label>Période<input name="period" placeholder="2025"></label>
 <label class="full">Source<input name="source"></label><button class="btn-g"><?=ai('plus')?>Ajouter</button></div></form>
<div class="table-wrap"><table class="table"><thead><tr><th>Indicateur</th><th>Valeur</th><th>Période</th><th>Source</th><th class="actions">Actions</th></tr></thead><tbody>
<?php foreach($items as $i): ?><tr><td><strong><?=e($i['label'])?></strong></td><td><?=e($i['value'])?> <span class="muted"><?=e($i['unit'])?></span></td><td class="num"><?=e($i['period'])?></td><td class="muted"><?=e($i['source'])?></td><td class="actions"><a class="row-btn del" title="Supprimer" href="?delete=<?=(int)$i['id']?>" data-confirm="Supprimer cet indicateur ?"><?=ai('trash')?></a></td></tr><?php endforeach; ?>
<?php if(!$items): ?><tr><td class="empty" colspan="5">Aucun indicateur.</td></tr><?php endif; ?>
</tbody></table></div>
<?php include __DIR__.'/../includes/admin_footer.php'; ?>
