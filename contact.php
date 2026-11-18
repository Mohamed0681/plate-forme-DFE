<?php
require_once __DIR__.'/config/config.php'; require_once __DIR__.'/includes/functions.php';
$pageTitle = 'Contact'; $sent = false; $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim($_POST['name'] ?? ''); $email = trim($_POST['email'] ?? ''); $subject = trim($_POST['subject'] ?? ''); $message = trim($_POST['message'] ?? '');
    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $subject === '' || $message === '') $error = 'Veuillez remplir correctement tous les champs.';
    else { $pdo->prepare("INSERT INTO messages(name,email,subject,message) VALUES(?,?,?,?)")->execute([$name, $email, $subject, $message]); $sent = true; }
}
$address = setting('address'); $lat = setting('map_lat', '11.5880'); $lng = setting('map_lng', '43.1450');
$icons = [
 'pin'   => '<path d="M12 21s7-6.2 7-12a7 7 0 0 0-14 0c0 5.8 7 12 7 12z"/><circle cx="12" cy="9" r="2.5"/>',
 'phone' => '<path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2z"/>',
 'mail'  => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/>',
 'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
];
$ico = fn($n) => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">' . $icons[$n] . '</svg>';
include __DIR__.'/includes/header.php';
?>
<section class="page-banner"><div class="container"><span class="eyebrow">ÉCHANGEONS</span><h1>Contact</h1><p>Retrouvez-nous ou envoyez-nous une demande d’information.</p></div></section>
<section class="section"><div class="container contact-layout">
 <div>
  <h2>Nos coordonnées</h2>
  <div class="contact-info">
   <div class="info-row"><span class="ic"><?= $ico('pin') ?></span><div><strong>Adresse</strong><span><?= e($address) ?></span></div></div>
   <?php if (setting('phone')): ?><div class="info-row"><span class="ic"><?= $ico('phone') ?></span><div><strong>Téléphone</strong><span><?= e(setting('phone')) ?></span></div></div><?php endif; ?>
   <?php if (setting('email')): ?><div class="info-row"><span class="ic"><?= $ico('mail') ?></span><div><strong>E-mail</strong><span><?= e(setting('email')) ?></span></div></div><?php endif; ?>
   <?php if (setting('hours')): ?><div class="info-row"><span class="ic"><?= $ico('clock') ?></span><div><strong>Horaires</strong><span><?= e(setting('hours')) ?></span></div></div><?php endif; ?>
  </div>
 </div>
 <form class="form-card" method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
  <?php if ($sent): ?><p class="success">Votre message a été enregistré.</p><?php endif; ?><?php if ($error): ?><p class="error"><?= e($error) ?></p><?php endif; ?>
  <label>Nom complet<input required name="name" maxlength="120"></label><label>Adresse e-mail<input required type="email" name="email" maxlength="190"></label>
  <label>Objet<input required name="subject" maxlength="200"></label><label>Message<textarea required name="message" rows="5"></textarea></label>
  <button class="btn">Envoyer le message</button></form>
</div></section>
<section class="section section-soft" style="padding-top:56px"><div class="container">
 <div class="section-heading"><div><span class="eyebrow">LOCALISATION</span><h2>Où nous trouver</h2></div></div>
 <div class="map-box" data-reveal="zoom">
  <iframe title="Carte de localisation" loading="lazy" referrerpolicy="no-referrer-when-downgrade" src="<?= e(osm_embed($lat, $lng)) ?>"></iframe>
  <div class="map-foot"><span><?= e($address) ?></span>
   <span><a class="text-link" target="_blank" rel="noopener" href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($lat . ',' . $lng) ?>">Ouvrir dans Google Maps →</a> &nbsp; <a class="text-link" target="_blank" rel="noopener" href="https://www.google.com/maps/dir/?api=1&destination=<?= urlencode($lat . ',' . $lng) ?>">Itinéraire →</a></span></div>
 </div>
</div></section>
<?php include __DIR__.'/includes/footer.php'; ?>
