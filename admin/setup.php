<?php
declare(strict_types=1);
require __DIR__ . '/../includes/app.php';
try {
    $exists = (int) db()->query('SELECT COUNT(*) FROM admins')->fetchColumn() > 0;
} catch (Throwable $error) {
    http_response_code(503);
    require __DIR__ . '/../includes/database-error.php';
    exit;
}
if ($exists) { redirect('admin/login.php'); }
$errorMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    try {
        $email = required_field('email', 190);
        $password = (string) ($_POST['password'] ?? '');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { throw new RuntimeException('Escribe un correo válido.'); }
        if (strlen($password) < 10) { throw new RuntimeException('La contraseña debe tener al menos 10 caracteres.'); }
        if ((int) db()->query('SELECT COUNT(*) FROM admins')->fetchColumn() > 0) { redirect('admin/login.php'); }
        $statement = db()->prepare('INSERT INTO admins (email, password_hash) VALUES (?, ?)');
        $statement->execute([$email, password_hash($password, PASSWORD_DEFAULT)]);
        flash('success', 'Administrador creado. Ya puedes iniciar sesión.');
        redirect('admin/login.php');
    } catch (Throwable $error) { $errorMessage = $error->getMessage(); }
}
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Crear administrador</title><link rel="stylesheet" href="<?= e(site_url('assets/style.css')) ?>"></head><body class="admin-page auth-page"><main class="glass auth-card"><span class="brand-mark">✳</span><h1>Primera configuración</h1><p>Crea el acceso privado para administrar tu sitio. Se desactivará automáticamente después de crear la primera cuenta.</p><?php if ($errorMessage): ?><div class="flash error"><?= e($errorMessage) ?></div><?php endif; ?><form method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><div class="form-field"><label for="email">Correo de administrador</label><input id="email" name="email" type="email" maxlength="190" required autocomplete="email"></div><div class="form-field"><label for="password">Contraseña de administrador</label><input id="password" name="password" type="password" minlength="10" required autocomplete="new-password"><small>Mínimo 10 caracteres. Puede ser distinta de la contraseña de MySQL.</small></div><button class="button primary" type="submit">Crear cuenta ↗</button></form><a class="back-link" href="<?= e(site_url()) ?>">← Ver sitio</a></main></body></html>
