<?php
require_once __DIR__.'/../config/config.php'; require_once __DIR__.'/../includes/functions.php'; require_login(); verify_csrf();
$error = ''; $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    save_setting('director_name', trim($_POST['director_name'] ?? ''));
    save_setting('director_title', trim($_POST['director_title'] ?? ''));
    save_setting('director_word', trim($_POST['director_word'] ?? ''));
    if (!empty($_POST['remove_photo'])) { delete_public_img(setting('director_photo')); save_setting('director_photo', ''); }
    $f = $_FILES['photo'] ?? null;
    if ($f && $f['error'] !== UPLOAD_ERR_NO_FILE) {
        $r = save_image_upload($f);
        if (is_array($r)) $error = $r[0];
        else { delete_public_img($_POST['old_photo'] ?? ''); save_setting('director_photo', $r); }
    }
    if (!$error) { header('Location: direction.php?ok=1'); exit; }
}
if (isset($_GET['ok'])) $success = 'Modifications enregistrées.';
$S = settings(); $pageTitle = 'Le Directeur';
include __DIR__.'/../includes/admin_header.php'; ?>
<div class="page-title"><h1>Le Directeur</h1><p>Informations du Directeur et mot affiché sur le site (page « Le Directeur » et accueil).</p></div>
<?php if ($error): ?><p class="alert err"><?= e($error) ?></p><?php endif; ?><?php if ($success): ?><p class="alert ok"><?= e($success) ?></p><?php endif; ?>
<form class="panel" method="post" enctype="multipart/form-data"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="old_photo" value="<?= e($S['director_photo'] ?? '') ?>">
 <div class="form-grid">
  <label>Nom complet<input name="director_name" maxlength="150" value="<?= e($S['director_name'] ?? '') ?>" placeholder="Prénom NOM"></label>
  <label>Fonction<input name="director_title" maxlength="190" value="<?= e($S['director_title'] ?? '') ?>"></label>
  <label class="full">Photo <small>(JPG, PNG ou WEBP, 5 Mo max ; portrait recommandé)</small>
   <?php if (!empty($S['director_photo'])): ?><img class="preview-img" src="<?= e(public_img_url($S['director_photo'])) ?>" alt=""><span class="hint"><label style="display:inline-flex;gap:6px;font-weight:400"><input type="checkbox" name="remove_photo" value="1" style="width:auto"> Supprimer la photo actuelle</label></span><?php endif; ?>
   <input type="file" name="photo" accept="image/jpeg,image/png,image/webp"></label>
  <label class="full">Mot du Directeur <small>(séparez les paragraphes par une ligne vide)</small><textarea name="director_word" rows="14"><?= e($S['director_word'] ?? '') ?></textarea></label>
  <button class="btn-g"><?= ai('check') ?>Enregistrer</button>
 </div>
</form>
<?php include __DIR__.'/../includes/admin_footer.php'; ?>
