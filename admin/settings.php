<?php
require_once __DIR__.'/../config/config.php'; require_once __DIR__.'/../includes/functions.php'; require_login(); verify_csrf();
$error = ''; $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lat = str_replace(',', '.', trim($_POST['map_lat'] ?? '')); $lng = str_replace(',', '.', trim($_POST['map_lng'] ?? ''));
    if (($lat !== '' && !is_numeric($lat)) || ($lng !== '' && !is_numeric($lng)) || abs((float)$lat) > 90 || abs((float)$lng) > 180) $error = 'Latitude / longitude invalides (ex. 11.5880 et 43.1450).';
    else {
        foreach (['address', 'phone', 'email', 'hours', 'about'] as $k) save_setting($k, trim($_POST[$k] ?? ''));
        save_setting('map_lat', $lat); save_setting('map_lng', $lng);
        header('Location: settings.php?ok=1'); exit;
    }
}
if (isset($_GET['ok'])) $success = 'Coordonnées enregistrées.';
$S = settings(); $lat = $S['map_lat'] ?? '11.5880'; $lng = $S['map_lng'] ?? '43.1450'; $pageTitle = 'Adresse & carte';
include __DIR__.'/../includes/admin_header.php'; ?>
<div class="page-title"><h1>Adresse &amp; carte</h1><p>Coordonnées affichées sur la page Contact et dans le pied de page.</p></div>
<?php if ($error): ?><p class="alert err"><?= e($error) ?></p><?php endif; ?><?php if ($success): ?><p class="alert ok"><?= e($success) ?></p><?php endif; ?>
<form class="panel" method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
 <div class="form-grid">
  <label class="full">Adresse<input name="address" maxlength="255" value="<?= e($S['address'] ?? '') ?>"></label>
  <label>Téléphone<input name="phone" maxlength="60" value="<?= e($S['phone'] ?? '') ?>"></label>
  <label>E-mail<input type="email" name="email" maxlength="190" value="<?= e($S['email'] ?? '') ?>"></label>
  <label class="full">Horaires<input name="hours" maxlength="190" value="<?= e($S['hours'] ?? '') ?>"></label>
  <label class="full">Phrase de présentation (pied de page)<input name="about" maxlength="255" value="<?= e($S['about'] ?? '') ?>" placeholder="Mobilisation, suivi et gestion des financements extérieurs."></label>
  <label>Latitude<input name="map_lat" value="<?= e($lat) ?>" placeholder="11.5880"></label>
  <label>Longitude<input name="map_lng" value="<?= e($lng) ?>" placeholder="43.1450"></label>
  <p class="hint full">Pour obtenir les coordonnées exactes : ouvrez Google Maps, faites un clic droit sur l’emplacement de la Direction, puis cliquez sur les deux nombres affichés pour les copier (latitude, longitude).</p>
  <button class="btn-g"><?= ai('check') ?>Enregistrer</button>
 </div>
</form>
<h2 style="margin:0 0 12px">Aperçu de la carte</h2>
<div class="table-wrap"><iframe title="Carte" style="display:block;width:100%;height:340px;border:0" src="<?= e(osm_embed($lat, $lng)) ?>"></iframe></div>
<?php include __DIR__.'/../includes/admin_footer.php'; ?>
