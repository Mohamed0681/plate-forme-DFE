<?php
/**
 * Médiathèque : types de contenus autorisés, tailles maximales et utilitaires.
 * Pour autoriser une extension, l'ajouter dans la liste du type voulu.
 */
function media_types(): array {
    return [
        'document' => [
            'label' => 'Document', 'plural' => 'Documents', 'icon' => 'DOC', 'max_mb' => 50,
            'ext' => [
                'pdf'  => ['application/pdf'],
                'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip', 'application/octet-stream'],
                'doc'  => ['application/msword', 'application/x-ole-storage', 'application/CDFV2', 'application/octet-stream'],
                'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/zip', 'application/octet-stream'],
                'pptx' => ['application/vnd.openxmlformats-officedocument.presentationml.presentation', 'application/zip', 'application/octet-stream'],
                'txt'  => ['text/plain'],
            ],
        ],
        'livre' => [
            'label' => 'Livre', 'plural' => 'Livres', 'icon' => 'LIV', 'max_mb' => 100,
            'ext' => [
                'pdf'  => ['application/pdf'],
                'epub' => ['application/epub+zip', 'application/zip', 'application/octet-stream'],
            ],
        ],
        'video' => [
            'label' => 'Vidéo', 'plural' => 'Vidéos', 'icon' => 'VID', 'max_mb' => 500,
            'ext' => [
                'mp4'  => ['video/mp4', 'application/octet-stream'],
                'webm' => ['video/webm', 'application/octet-stream'],
                'ogv'  => ['video/ogg', 'application/ogg'],
            ],
        ],
        'image' => [
            'label' => 'Image', 'plural' => 'Images', 'icon' => 'IMG', 'max_mb' => 10,
            'ext' => [
                'jpg'  => ['image/jpeg'],
                'jpeg' => ['image/jpeg'],
                'png'  => ['image/png'],
                'webp' => ['image/webp'],
                'gif'  => ['image/gif'],
            ],
        ],
    ];
}
function media_mime_for_ext(string $ext): string {
    static $map = [
        'pdf' => 'application/pdf', 'epub' => 'application/epub+zip', 'txt' => 'text/plain; charset=utf-8',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'mp4' => 'video/mp4', 'webm' => 'video/webm', 'ogv' => 'video/ogg',
        'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp', 'gif' => 'image/gif',
    ];
    return $map[$ext] ?? 'application/octet-stream';
}
function media_accept(string $type): string {
    return '.' . implode(',.', array_keys(media_types()[$type]['ext']));
}

function upload_error_message(int $code): string {
    return match ($code) {
        UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Fichier plus grand que la limite PHP (upload_max_filesize / post_max_size dans php.ini).',
        UPLOAD_ERR_PARTIAL  => 'Téléversement interrompu. Réessayez.',
        UPLOAD_ERR_NO_FILE  => 'Aucun fichier sélectionné.',
        default             => 'Erreur de téléversement (code ' . $code . ').',
    };
}
/** Valide un fichier téléversé. Retourne [extension, mime] ou une chaîne d'erreur. */
function validate_upload(array $file, string $type): array|string {
    $types = media_types();
    if (!isset($types[$type])) return 'Type de contenu invalide.';
    if ($file['error'] !== UPLOAD_ERR_OK) return upload_error_message((int)$file['error']);
    $cfg = $types[$type];
    if ($file['size'] > $cfg['max_mb'] * 1048576) return 'Fichier trop volumineux (' . $cfg['max_mb'] . ' Mo maximum pour ce type).';
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!isset($cfg['ext'][$ext])) return 'Extension non autorisée. Formats acceptés : ' . strtoupper(implode(', ', array_keys($cfg['ext']))) . '.';
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!in_array($mime, $cfg['ext'][$ext], true)) return 'Le contenu du fichier ne correspond pas à son extension (' . $mime . ').';
    return [$ext, media_mime_for_ext($ext)];
}
