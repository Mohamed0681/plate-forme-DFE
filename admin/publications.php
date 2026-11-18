<?php
require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/../includes/functions.php';
require_once __DIR__.'/../includes/media.php';
require_login();
// post_max_size dépassé : PHP vide $_POST, on affiche un message clair au lieu d'une erreur CSRF
$overflow = $_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST) && empty($_FILES) && (int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 0;
if (!$overflow) verify_csrf();

$types = media_types();
$error = ''; $success = '';
$dir = __DIR__.'/../uploads/documents/';

// Suppression (POST + CSRF)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = (int)$_POST['delete_id'];
    $st = $pdo->prepare("SELECT file_name, cover_image FROM publications WHERE id=?"); $st->execute([$id]); $f = $st->fetch();
    if ($f) {
        $path = $dir.basename($f['file_name']);
        if (is_file($path)) unlink($path);
        delete_public_img($f['cover_image'] ?? null);
        $pdo->prepare("DELETE FROM publications WHERE id=?")->execute([$id]);
    }
    header('Location: publications.php?ok=deleted'); exit;
}

// Changer / ajouter la couverture d'un contenu existant
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cover_id'])) {
    $id = (int)$_POST['cover_id']; $r = save_image_upload($_FILES['cover'] ?? ['error' => UPLOAD_ERR_NO_FILE]);
    if (is_array($r)) $error = $r[0];
    else {
        $o = $pdo->prepare("SELECT cover_image FROM publications WHERE id=?"); $o->execute([$id]); delete_public_img($o->fetchColumn() ?: null);
        $pdo->prepare("UPDATE publications SET cover_image=? WHERE id=?")->execute([$r, $id]);
        header('Location: publications.php?ok=cover'); exit;
    }
}

// Ajout (plusieurs fichiers possibles)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['delete_id']) && !isset($_POST['cover_id']) || $overflow) {
    $title = trim($_POST['title'] ?? '');
    $desc  = trim($_POST['description'] ?? '');
    $cat   = (int)($_POST['category_id'] ?? 0);
    $type  = $_POST['media_type'] ?? '';

    // Cas où le serveur a rejeté toute la requête (post_max_size dépassé)
    if ($overflow) {
        $error = 'Envoi trop volumineux pour le serveur : augmentez post_max_size et upload_max_filesize dans php.ini.';
    } elseif (!isset($types[$type])) {
        $error = 'Choisissez un type de contenu.';
    } else {
        $files = [];
        $up = $_FILES['files'] ?? null;
        if ($up && is_array($up['name'])) {
            foreach ($up['name'] as $i => $n) {
                if ($up['error'][$i] === UPLOAD_ERR_NO_FILE) continue;
                $files[] = ['name'=>$n,'tmp_name'=>$up['tmp_name'][$i],'size'=>$up['size'][$i],'error'=>$up['error'][$i]];
            }
        }
        if (!$files) $error = 'Sélectionnez au moins un fichier.';
        elseif ($title === '' && count($files) === 1) $error = 'Le titre est obligatoire.';
        else {
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            $added = 0; $errors = []; $coverName = null;
            $cv = $_FILES['cover'] ?? null;
            if ($cv && $cv['error'] !== UPLOAD_ERR_NO_FILE) { $r = save_image_upload($cv); if (is_array($r)) $errors[] = 'Couverture : '.$r[0]; else $coverName = $r; }
            foreach ($files as $file) {
                $res = validate_upload($file, $type);
                if (is_string($res)) { $errors[] = basename($file['name']).' : '.$res; continue; }
                [$ext, $mime] = $res;
                $stored = bin2hex(random_bytes(16)).'.'.$ext;
                if (!move_uploaded_file($file['tmp_name'], $dir.$stored)) { $errors[] = basename($file['name']).' : enregistrement impossible.'; continue; }
                $t = (count($files) === 1) ? $title : ($title !== '' ? $title.' – ' : '').pathinfo($file['name'], PATHINFO_FILENAME);
                $st = $pdo->prepare("INSERT INTO publications(title,description,category_id,media_type,extension,mime_type,file_size,file_name,original_name,published_at,cover_image) VALUES(?,?,?,?,?,?,?,?,?,?,?)");
                $st->execute([mb_substr($t,0,220), $desc, $cat ?: null, $type, $ext, $mime, (int)$file['size'], $stored, mb_substr(basename($file['name']),0,255), date('Y-m-d'), $coverName]);
                $added++;
            }
            if ($added) $success = $added.' fichier(s) ajouté(s).';
            if ($errors) $error = implode(' | ', $errors);
        }
    }
}
if (($_GET['ok'] ?? '') === 'deleted') $success = 'Contenu supprimé.';
if (($_GET['ok'] ?? '') === 'cover') $success = 'Couverture mise à jour.';

$cats  = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
$items = $pdo->query("SELECT p.*, c.name category FROM publications p LEFT JOIN categories c ON c.id=p.category_id ORDER BY p.id DESC")->fetchAll();
$pageTitle = 'Publications';
include __DIR__.'/../includes/admin_header.php'; ?>
<div class="page-title"><h1>Publications</h1><p>Ajoutez des publications PDF avec leur image de couverture, ainsi que des vidéos et des images.</p></div>
<?php if ($error): ?><p class="alert err"><?= e($error) ?></p><?php endif; ?>
<?php if ($success): ?><p class="alert ok"><?= e($success) ?></p><?php endif; ?>
<form class="panel" method="post" enctype="multipart/form-data" id="upload-form"><h2>Ajouter un contenu</h2>
 <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
 <div class="form-grid">
  <label>Type de contenu
   <select name="media_type" id="media-type" required>
    <?php foreach ($types as $k => $t): ?><option value="<?= e($k) ?>" data-accept="<?= e(media_accept($k)) ?>" data-max="<?= (int)$t['max_mb'] ?>"><?= e($t['label']) ?> (<?= e(strtoupper(implode(', ', array_keys($t['ext'])))) ?>)</option><?php endforeach; ?>
   </select></label>
  <label>Catégorie
   <select name="category_id"><option value="0">Sans catégorie</option><?php foreach ($cats as $c): ?><option value="<?= (int)$c['id'] ?>"><?= e($c['name']) ?></option><?php endforeach; ?></select></label>
  <label class="full">Titre <small>(si plusieurs fichiers : préfixe du titre)</small><input name="title" maxlength="200"></label>
  <label class="full">Description<textarea name="description" rows="2"></textarea></label>
  <label class="full">Image de couverture <small>(JPG, PNG ou WEBP, 5 Mo max ; format portrait recommandé, ex. 600×800)</small><input type="file" name="cover" accept="image/jpeg,image/png,image/webp"></label>
  <label class="full">Fichier(s) <small id="max-hint"></small><input type="file" name="files[]" id="files" multiple required></label>
  <progress id="progress" class="full" value="0" max="100" hidden></progress>
  <button class="btn-g"><?= ai('upload') ?>Ajouter</button>
 </div>
</form>
<div class="table-wrap"><table class="table"><thead><tr><th>Titre</th><th>Type</th><th>Catégorie</th><th>Date</th><th>Taille</th><th class="actions">Actions</th></tr></thead><tbody>
<?php foreach ($items as $d): $t = $types[$d['media_type']] ?? $types['document']; ?>
<tr><td><span class="t-title"><?php if (!empty($d['cover_image'])): ?><img class="thumb" src="<?= e(public_img_url($d['cover_image'])) ?>" alt=""><?php endif; ?><span class="chip <?= e(['video'=>'vid','image'=>'img','livre'=>'liv'][$d['media_type']] ?? ($d['extension']==='pdf'?'pdf':'')) ?>"><?= e(strtoupper($d['extension'])) ?></span><?= e($d['title']) ?></span></td><td><?= e($t['label']) ?></td><td><?= e($d['category'] ?? '—') ?></td><td class="num"><?= e($d['published_at'] ? date('d/m/Y', strtotime($d['published_at'])) : '—') ?></td><td class="num"><?= e(format_size((int)$d['file_size'])) ?></td>
<td class="actions"><form method="post" enctype="multipart/form-data" class="inline"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="cover_id" value="<?= (int)$d['id'] ?>"><label class="row-btn" title="Changer la couverture"><?= ai('image') ?><input type="file" name="cover" accept="image/jpeg,image/png,image/webp" onchange="this.form.submit()"></label></form><a class="row-btn" title="Ouvrir" target="_blank" rel="noopener" href="<?= e(url('download.php?id='.(int)$d['id'].'&inline=1')) ?>"><?= ai('eye') ?></a><a class="row-btn" title="Télécharger" href="<?= e(url('download.php?id='.(int)$d['id'])) ?>"><?= ai('download') ?></a>
<form method="post" class="inline"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="delete_id" value="<?= (int)$d['id'] ?>"><button class="row-btn del" title="Supprimer" type="submit" data-confirm="Supprimer ce contenu ?"><?= ai('trash') ?></button></form></td></tr>
<?php endforeach; if (!$items): ?><tr><td class="empty" colspan="6">Aucun contenu pour le moment.</td></tr><?php endif; ?>
</tbody></table></div>
<script>
(function () {
  var sel = document.getElementById('media-type'), input = document.getElementById('files'), hint = document.getElementById('max-hint');
  function sync() { var o = sel.options[sel.selectedIndex]; input.accept = o.dataset.accept; hint.textContent = '(' + o.dataset.max + ' Mo maximum par fichier)'; }
  sel.addEventListener('change', sync); sync();
  // Barre de progression pour les gros fichiers (vidéos)
  var form = document.getElementById('upload-form'), bar = document.getElementById('progress');
  form.addEventListener('submit', function (ev) {
    if (!window.XMLHttpRequest || !window.FormData) return;
    ev.preventDefault();
    var xhr = new XMLHttpRequest(); bar.hidden = false;
    xhr.upload.onprogress = function (e) { if (e.lengthComputable) bar.value = Math.round(e.loaded / e.total * 100); };
    xhr.onload = function () { document.open(); document.write(xhr.responseText); document.close(); };
    xhr.onerror = function () { alert('Échec du téléversement.'); bar.hidden = true; };
    xhr.open('POST', form.action || location.href); xhr.send(new FormData(form));
  });
})();
</script>
<?php include __DIR__.'/../includes/admin_footer.php'; ?>
