<?php
function e(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function url(string $path = ''): string {
    return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
}
function is_logged_in(): bool {
    return isset($_SESSION['user_id']);
}
function require_login(): void {
    if (!is_logged_in()) {
        header('Location: ' . url('admin/login.php'));
        exit;
    }
}
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function verify_csrf(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf'] ?? '';
        if (!hash_equals($_SESSION['csrf'] ?? '', $token)) {
            http_response_code(403); exit('Requête invalide. Rechargez la page.');
        }
    }
}
function slugify(string $text): string {
    $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
    $text = strtolower(trim((string)$text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim((string)$text, '-') ?: 'article';
}

/** Médias du hero : images du diaporama (assets/img/hero) et vidéo de fond (assets/video/hero.mp4 ou .webm). */
function hero_media(): array {
    $root = dirname(__DIR__);
    $images = [];
    foreach (glob($root . '/assets/img/hero/*.{jpg,jpeg,png,webp}', GLOB_BRACE) ?: [] as $f) {
        $images[] = url('assets/img/hero/' . basename($f));
    }
    sort($images);
    $video = null;
    foreach (['mp4' => 'video/mp4', 'webm' => 'video/webm'] as $ext => $mime) {
        if (is_file($root . '/assets/video/hero.' . $ext)) { $video = ['src' => url('assets/video/hero.' . $ext), 'type' => $mime]; break; }
    }
    return ['images' => $images, 'video' => $video];
}

/* ---------- Réglages du site (table settings) ---------- */
function settings(): array {
    static $s = null; global $pdo;
    if ($s === null) {
        $s = [];
        try { foreach ($pdo->query("SELECT k, v FROM settings")->fetchAll() as $r) $s[$r['k']] = (string)$r['v']; } catch (Throwable $e) {}
    }
    return $s;
}
function setting(string $k, string $default = ''): string {
    $v = trim(settings()[$k] ?? '');
    return $v !== '' ? $v : $default;
}
function save_setting(string $k, string $v): void {
    global $pdo;
    $pdo->prepare("INSERT INTO settings(k, v) VALUES(?, ?) ON DUPLICATE KEY UPDATE v = VALUES(v)")->execute([$k, $v]);
}

/* ---------- Images publiques (couvertures, photos) : uploads/public/ ---------- */
function public_dir(): string { return dirname(__DIR__) . '/uploads/public/'; }
function public_img_url(?string $f): string { return $f ? url('uploads/public/' . rawurlencode($f)) : ''; }
/** Enregistre une image téléversée. Retourne le nom de fichier, ou un tableau [message] en cas d'erreur. */
function save_image_upload(array $file, int $maxMb = 5): string|array {
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) return ['Erreur de téléversement de l’image (code ' . (int)($file['error'] ?? -1) . ').'];
    if ($file['size'] > $maxMb * 1048576) return ["Image trop volumineuse ($maxMb Mo maximum)."];
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mime] ?? null;
    if (!$ext) return ['Format d’image non accepté (JPG, PNG ou WEBP).'];
    if (!is_dir(public_dir())) mkdir(public_dir(), 0755, true);
    $name = bin2hex(random_bytes(12)) . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], public_dir() . $name)) return ['Enregistrement de l’image impossible (droits du dossier uploads/public ?).'];
    return $name;
}
function delete_public_img(?string $f): void {
    if ($f && is_file(public_dir() . basename($f))) @unlink(public_dir() . basename($f));
}
function initials(string $name): string {
    $w = preg_split('/\s+/', trim($name)); $o = '';
    foreach (array_slice($w, 0, 2) as $x) $o .= mb_strtoupper(mb_substr($x, 0, 1));
    return $o;
}
/** Carte OpenStreetMap (sans clé API) centrée sur lat/lng. */
function osm_embed(string $lat, string $lng): string {
    $la = (float)$lat; $ln = (float)$lng; $d = 0.006;
    return 'https://www.openstreetmap.org/export/embed.html?bbox=' . ($ln - $d * 1.6) . '%2C' . ($la - $d) . '%2C' . ($ln + $d * 1.6) . '%2C' . ($la + $d) . '&layer=mapnik&marker=' . $la . '%2C' . $ln;
}

/**
 * Convertit une taille en octets en Ko, Mo ou Go.
 */
function format_size(int $bytes): string {
    if ($bytes >= 1073741824) {
        return round($bytes / 1073741824, 2) . ' Go';
    }

    if ($bytes >= 1048576) {
        return round($bytes / 1048576, 2) . ' Mo';
    }

    if ($bytes >= 1024) {
        return round($bytes / 1024, 2) . ' Ko';
    }

    return $bytes . ' octets';
}
