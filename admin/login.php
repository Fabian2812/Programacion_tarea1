<?php
declare(strict_types=1);
require __DIR__ . '/../includes/app.php';
start_session();
if (!empty($_SESSION['admin_id'])) { redirect('admin/index.php'); }
try {
    $hasAdmin = (int) db()->query('SELECT COUNT(*) FROM admins')->fetchColumn() > 0;
} catch (Throwable $error) {
    http_response_code(503);
    require __DIR__ . '/../includes/database-error.php';
    exit;
}
if (!$hasAdmin) { redirect('admin/setup.php'); }
$errorMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $attempts = (int) ($_SESSION['login_attempts'] ?? 0);
    $lastAttempt = (int) ($_SESSION['last_login_attempt'] ?? 0);
    if ($attempts >= 5 && time() - $lastAttempt < 300) {
        $errorMessage = 'Demasiados intentos. Espera cinco minutos.';
    } else {
        if (time() - $lastAttempt >= 300) { $_SESSION['login_attempts'] = 0; }
        $statement = db()->prepare('SELECT id, password_hash FROM admins WHERE email = ?');
        $statement->execute([trim((string) ($_POST['email'] ?? ''))]);
        $admin = $statement->fetch();
        if ($admin && password_verify((string) ($_POST['password'] ?? ''), $admin['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = (int) $admin['id'];
            $_SESSION['login_attempts'] = 0;
            redirect('admin/index.php');
        }
        $_SESSION['login_attempts'] = (int) ($_SESSION['login_attempts'] ?? 0) + 1;
        $_SESSION['last_login_attempt'] = time();
        $errorMessage = 'Correo o contraseña incorrectos.';
    }
}
$notice = take_flash();
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Iniciar sesión · Administración</title><link rel="stylesheet" href="<?= e(site_url('assets/style.css')) ?>"></head><body class="admin-page auth-page"><main class="glass auth-card"><span class="brand-mark">✳</span><h1>Bienvenido de vuelta</h1><p>Accede para actualizar tu espacio personal.</p><?php if ($notice): ?><div class="flash"><?= e($notice['message']) ?></div><?php endif; ?><?php if ($errorMessage): ?><div class="flash error"><?= e($errorMessage) ?></div><?php endif; ?><form method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><div class="form-field"><label for="email">Correo</label><input id="email" name="email" type="email" required autocomplete="username"></div><div class="form-field"><label for="password">Contraseña</label><input id="password" name="password" type="password" required autocomplete="current-password"></div><button class="button primary" type="submit">Entrar al panel ↗</button></form><a class="back-link" href="<?= e(site_url()) ?>">← Volver al sitio</a></main></body></html>
