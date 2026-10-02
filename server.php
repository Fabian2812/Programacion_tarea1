<?php
declare(strict_types=1);

// Protege archivos privados al usar el servidor integrado de PHP.
$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
$segments = explode('/', ltrim($path, '/'));
$private = ['includes', 'database', 'docs', '.git', '.agents', '.codex', '.aws'];
if (str_contains($path, "\0") || str_contains($path, '..') || preg_match('~(^|/)\.~', $path)
    || in_array($segments[0], $private, true)
    || ($segments[0] === 'storage' && ($segments[1] ?? '') !== 'uploads')
    || ($segments[0] === 'storage' && preg_match('~\.(php|phtml|phar|php[0-9]*)$~i', $path))) {
    http_response_code(403);
    exit('Acceso denegado.');
}
if ($path !== '/' && is_file(__DIR__ . $path)) {
    return false;
}
if ($path !== '/' && is_dir(__DIR__ . $path) && is_file(__DIR__ . $path . '/index.php')) {
    require __DIR__ . $path . '/index.php';
    return true;
}
if ($path === '/' || $path === '') {
    require __DIR__ . '/home.php';
    return true;
}
http_response_code(404);
exit('Página no encontrada.');
