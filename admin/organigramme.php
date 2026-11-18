<?php
require_once __DIR__.'/../config/config.php'; require_once __DIR__.'/../includes/functions.php'; require_login(); verify_csrf();
$error = ''; $success = '';
function org_delete_photos(PDO $pdo, int $id): void {   // supprime les photos du poste et de ses descendants
    $st = $pdo->prepare("SELECT id, photo FROM org_members WHERE parent_id=?"); $st->execute([$id]);
    foreach ($st->fetchAll() as $c) org_delete_photos($pdo, (int)$c['id']);
    $p = $pdo->prepare("SELECT photo FROM org_members WHERE id=?"); $p->execute([$id]); delete_public_img($p->fetchColumn() ?: null);
}
function org_descendants(array $all, int $id): array { $out = [$id]; foreach ($all as $m) if ((int)$m['parent_id'] === $id) $out = array_merge($out, org_descendants($all, (int)$m['id'])); return $out; }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    org_delete_photos($pdo, (int)$_POST['delete_id']);
    $pdo->prepare("DELETE FROM org_members WHERE id=?")->execute([(int)$_POST['delete_id']]);
    header('Location: organigramme.php?ok=deleted'); exit;
}
$all = $pdo->query("SELECT * FROM org_members ORDER BY sort_order, id")->fetchAll();
$editId = (int)($_GET['edit'] ?? 0); $edit = null;
foreach ($all as $m) if ((int)$m['id'] === $editId) $edit = $m;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['delete_id'])) {
    $id = (int)($_POST['id'] ?? 0); $name = trim($_POST['name'] ?? ''); $title = trim($_POST['title'] ?? '');
    $parent = (int)($_POST['parent_id'] ?? 0) ?: null; $order = (int)($_POST['sort_order'] ?? 0);
    if ($title === '') $error = 'La fonction / le poste est obligatoire.';
    elseif ($id && $parent && in_array($parent, org_descendants($all, $id), true)) $error = 'Un poste ne peut pas dépendre de lui-même ou de l’un de ses subordonnés.';
    else {
        $photo = null; $f = $_FILES['photo'] ?? null;
        if ($f && $f['error'] !== UPLOAD_ERR_NO_FILE) { $r = save_image_upload($f, 3); if (is_array($r)) $error = $r[0]; else $photo = $r; }
        if (!$error) {
            if ($id) {
                if ($photo) { $o = $pdo->prepare("SELECT photo FROM org_members WHERE id=?"); $o->execute([$id]); delete_public_img($o->fetchColumn() ?: null); $pdo->prepare("UPDATE org_members SET photo=? WHERE id=?")->execute([$photo, $id]); }
                $pdo->prepare("UPDATE org_members SET name=?, title=?, parent_id=?, sort_order=? WHERE id=?")->execute([$name, $title, $parent, $order, $id]);
            } else {
                $pdo->prepare("INSERT INTO org_members(name,title,parent_id,sort_order,photo) VALUES(?,?,?,?,?)")->execute([$name, $title, $parent, $order, $photo]);
            }
            header('Location: organigramme.php?ok=saved'); exit;
        }
    }
}
if (($_GET['ok'] ?? '') === 'saved') $success = 'Organigramme mis à jour.';
if (($_GET['ok'] ?? '') === 'deleted') $success = 'Poste supprimé (avec ses subordonnés).';
// Liste hiérarchique avec niveaux
$byParent = []; foreach ($all as $m) $byParent[(int)($m['parent_id'] ?? 0)][] = $m;
$flat = []; $walk = function (int $p, int $lv) use (&$walk, &$flat, $byParent) { foreach ($byParent[$p] ?? [] as $m) { $m['lv'] = $lv; $flat[] = $m; $walk((int)$m['id'], $lv + 1); } }; $walk(0, 0);
$pageTitle = 'Organigramme';
include __DIR__.'/../includes/admin_header.php'; ?>
<div class="page-title"><h1>Organigramme</h1><p>Ajoutez les postes et choisissez de qui chacun dépend. Le poste sans supérieur est le sommet (Directeur).</p></div>
<?php if ($error): ?><p class="alert err"><?= e($error) ?></p><?php endif; ?><?php if ($success): ?><p class="alert ok"><?= e($success) ?></p><?php endif; ?>
<form class="panel" method="post" enctype="multipart/form-data"><h2><?= $edit ? 'Modifier le poste' : 'Nouveau poste' ?></h2>
 <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
 <div class="form-grid">
  <label>Fonction / service *<input name="title" required maxlength="190" value="<?= e($edit['title'] ?? '') ?>" placeholder="Sous-direction de ..."></label>
  <label>Nom de la personne <small>(facultatif)</small><input name="name" maxlength="150" value="<?= e($edit['name'] ?? '') ?>"></label>
  <label>Dépend de
   <select name="parent_id"><option value="0">— Aucun (sommet) —</option>
    <?php foreach ($flat as $m): ?><option value="<?= (int)$m['id'] ?>"<?= (int)($edit['parent_id'] ?? 0) === (int)$m['id'] ? ' selected' : '' ?>><?= str_repeat('— ', $m['lv']) . e($m['title']) ?></option><?php endforeach; ?></select></label>
  <label>Ordre d’affichage<input type="number" name="sort_order" value="<?= (int)($edit['sort_order'] ?? 0) ?>"></label>
  <label class="full">Photo <small>(facultatif, 3 Mo max)</small><input type="file" name="photo" accept="image/jpeg,image/png,image/webp"></label>
  <div style="display:flex;gap:10px"><button class="btn-g"><?= ai('check') ?><?= $edit ? 'Enregistrer' : 'Ajouter' ?></button><?php if ($edit): ?><a class="btn-o" href="organigramme.php">Annuler</a><?php endif; ?></div>
 </div>
</form>
<div class="table-wrap"><table class="table"><thead><tr><th>Poste</th><th>Nom</th><th>Ordre</th><th class="actions">Actions</th></tr></thead><tbody>
<?php foreach ($flat as $m): ?><tr><td><span class="t-title indent" style="--lv:<?= (int)$m['lv'] ?>"><?php if ($m['photo']): ?><img class="thumb round" src="<?= e(public_img_url($m['photo'])) ?>" alt=""><?php else: ?><span class="chip"><?= $m['lv'] === 0 ? 'TOP' : 'SRV' ?></span><?php endif; ?><?= e($m['title']) ?></span></td><td><?= e($m['name'] ?: '—') ?></td><td class="num"><?= (int)$m['sort_order'] ?></td>
<td class="actions"><a class="row-btn" title="Modifier" href="?edit=<?= (int)$m['id'] ?>"><?= ai('edit') ?></a>
<form method="post" class="inline"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="delete_id" value="<?= (int)$m['id'] ?>"><button class="row-btn del" title="Supprimer" type="submit" data-confirm="Supprimer ce poste et tous ses subordonnés ?"><?= ai('trash') ?></button></form></td></tr>
<?php endforeach; if (!$flat): ?><tr><td class="empty" colspan="4">Aucun poste. Ajoutez d’abord le Directeur (sans « Dépend de »).</td></tr><?php endif; ?>
</tbody></table></div>
<?php include __DIR__.'/../includes/admin_footer.php'; ?>
