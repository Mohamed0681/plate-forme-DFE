<?php
/**
 * Diffusion des fichiers de la médiathèque.
 *   download.php?id=12            -> téléchargement
 *   download.php?id=12&inline=1   -> affichage (vidéo, image, PDF) avec support Range (lecture/avance rapide)
 */
require_once __DIR__.'/config/config.php';
require_once __DIR__.'/includes/media.php';
session_write_close(); // ne pas bloquer les autres requêtes pendant le streaming

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT file_name, original_name, extension, media_type FROM publications WHERE id=?");
$stmt->execute([$id]); $doc = $stmt->fetch();
if (!$doc) { http_response_code(404); exit('Document introuvable.'); }

$path = __DIR__.'/uploads/documents/'.basename($doc['file_name']);
if (!is_file($path)) { http_response_code(404); exit('Fichier indisponible.'); }

$mime   = media_mime_for_ext($doc['extension']);
$inlineOk = in_array($doc['extension'], ['pdf','mp4','webm','ogv','jpg','jpeg','png','webp','gif'], true);
$inline = !empty($_GET['inline']) && $inlineOk;
$size   = filesize($path);
$start  = 0; $end = $size - 1;

header('X-Content-Type-Options: nosniff');
header('Accept-Ranges: bytes');
header('Content-Type: '.$mime);
$name = str_replace(['"', "\r", "\n"], '', $doc['original_name']);
header('Content-Disposition: '.($inline ? 'inline' : 'attachment').'; filename="'.addcslashes(iconv('UTF-8','ASCII//TRANSLIT//IGNORE',$name),'"\\').'"; filename*=UTF-8\'\''.rawurlencode($name));

if (isset($_SERVER['HTTP_RANGE']) && preg_match('/bytes=(\d*)-(\d*)/', $_SERVER['HTTP_RANGE'], $m)) {
    if ($m[1] !== '') { $start = (int)$m[1]; if ($m[2] !== '') $end = min((int)$m[2], $size - 1); }
    elseif ($m[2] !== '') { $start = max(0, $size - (int)$m[2]); }
    if ($start > $end || $start >= $size) { http_response_code(416); header("Content-Range: bytes */$size"); exit; }
    http_response_code(206);
    header("Content-Range: bytes $start-$end/$size");
}
header('Content-Length: '.($end - $start + 1));

$fp = fopen($path, 'rb'); fseek($fp, $start);
$left = $end - $start + 1;
while ($left > 0 && !feof($fp) && !connection_aborted()) {
    $chunk = fread($fp, min(8192 * 16, $left));
    echo $chunk; $left -= strlen($chunk); flush();
}
fclose($fp); exit;
