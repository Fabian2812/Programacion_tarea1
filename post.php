<?php
declare(strict_types=1);
require __DIR__ . '/includes/app.php';
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) { http_response_code(404); exit('Publicación no encontrada.'); }
try {
    $statement = db()->prepare('SELECT * FROM posts WHERE id = ? AND is_published = 1');
    $statement->execute([$id]);
    $post = $statement->fetch();
    $person = profile();
} catch (Throwable $error) {
    http_response_code(503);
    require __DIR__ . '/includes/database-error.php';
    exit;
}
if (!$post) { http_response_code(404); exit('Publicación no encontrada.'); }
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="theme-color" content="#a9cee9"><title><?= e($post['title']) ?> · <?= e($person['full_name'] ?? '') ?></title><link rel="stylesheet" href="<?= e(site_url('assets/style.css')) ?>"></head>
<body class="article-page"><div class="ambient ambient-one"></div><header class="site-header glass"><a class="brand" href="<?= e(site_url()) ?>"><span class="brand-mark">✳</span><span><?= e($person['full_name'] ?? '') ?></span></a><a class="header-contact" href="<?= e(site_url('#blog')) ?>">Volver al blog <span>↗</span></a></header><main class="article-wrap"><a class="back-link" href="<?= e(site_url('#blog')) ?>">← Todas las publicaciones</a><article class="glass article-card"><span class="eyebrow numbered">BLOG / <?= e(format_date($post['created_at'])) ?></span><h1><?= e($post['title']) ?></h1><p class="article-excerpt"><?= e($post['excerpt']) ?></p><?php if (asset($post['image_path'])): ?><img class="article-image" src="<?= e(asset($post['image_path'])) ?>" alt=""><?php endif; ?><div class="article-body"><?= nl2br(e($post['body'])) ?></div></article></main><footer class="site-footer section-wrap"><span>✳ <?= e($person['full_name'] ?? '') ?></span><a href="<?= e(site_url()) ?>">Inicio</a></footer></body></html>
