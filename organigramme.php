<?php
require_once __DIR__.'/config/config.php'; require_once __DIR__.'/includes/functions.php';
$pageTitle = 'Organigramme';
$rows = $pdo->query("SELECT id, name, title, parent_id, photo FROM org_members ORDER BY sort_order, id")->fetchAll();
$kids = []; foreach ($rows as $r) $kids[(int)($r['parent_id'] ?? 0)][] = $r;
function org_render(array $kids, int $parent, bool $root = false): void {
    if (empty($kids[$parent])) return;
    echo $root ? '<ul class="org-tree">' : '<ul>';
    foreach ($kids[$parent] as $m) {
        echo '<li><div class="org-node' . ($root ? ' root' : '') . '">';
        $label = $m['name'] !== '' ? $m['name'] : $m['title'];
        echo '<span class="avatar">' . ($m['photo'] ? '<img src="' . e(public_img_url($m['photo'])) . '" alt="">' : e(initials($label))) . '</span>';
        echo '<strong>' . e($label) . '</strong>';
        if ($m['name'] !== '') echo '<span>' . e($m['title']) . '</span>';
        echo '</div>';
        org_render($kids, (int)$m['id']);
        echo '</li>';
    }
    echo '</ul>';
}
include __DIR__.'/includes/header.php';
?>
<section class="page-banner banner-organisation"><div class="container"><span class="eyebrow">STRUCTURE</span><h1>Organigramme</h1><p>L’organisation de la Direction du Financement Extérieur.</p></div></section>
<section class="section"><div class="container">
 <p class="org-hint">Faites défiler horizontalement pour voir tout l’organigramme.</p>
 <div class="org-scroll" data-reveal="zoom"><?php if ($rows) org_render($kids, 0, true); else echo '<p class="muted">Organigramme à renseigner depuis l’espace administrateur.</p>'; ?></div>
</div></section>
<?php include __DIR__.'/includes/footer.php'; ?>
