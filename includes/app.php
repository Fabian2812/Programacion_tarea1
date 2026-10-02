<?php
declare(strict_types=1);

const ROOT_PATH = __DIR__ . '/..';

// Lee un .env sencillo sin requerir Composer; no interpreta comandos ni variables.
function load_env(): void
{
    $path = ROOT_PATH . '/.env';
    if (!is_readable($path)) {
        return;
    }
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        if (preg_match('/^[A-Z][A-Z0-9_]*$/', $key) && getenv($key) === false) {
            putenv($key . '=' . trim($value, " \t\n\r\0\x0B\"'"));
        }
    }
}

load_env();

function db(): PDO
{
    static $connection = null;
    if ($connection instanceof PDO) {
        return $connection;
    }
    $host = getenv('DB_HOST') ?: '127.0.0.1';
    $port = getenv('DB_PORT') ?: '3306';
    $name = getenv('DB_NAME') ?: 'tarea#1';
    $user = getenv('DB_USER') ?: 'root';
    $password = getenv('DB_PASSWORD') ?: '';
    $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
    $connection = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    return $connection;
}

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function profile(): array
{
    return db()->query('SELECT * FROM profile WHERE id = 1')->fetch() ?: [];
}

function records(string $table, string $order = 'id DESC'): array
{
    if (!in_array($table, ['certificates', 'gallery', 'posts'], true)) {
        throw new InvalidArgumentException('Tabla no permitida.');
    }
    $allowedOrders = ['id DESC', 'year DESC, id DESC', 'created_at DESC'];
    if (!in_array($order, $allowedOrders, true)) {
        throw new InvalidArgumentException('Orden no permitido.');
    }
    return db()->query("SELECT * FROM {$table} ORDER BY {$order}")->fetchAll();
}

function asset(?string $path): string
{
    if (!$path || !preg_match('~^storage/uploads/[a-f0-9]{32}\.(jpg|png|webp)$~', $path)) {
        return '';
    }
    return site_url($path);
}

function site_url(string $path = ''): string
{
    $script = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
    $folder = str_replace('\\', '/', dirname($script));
    if (str_ends_with($folder, '/admin')) {
        $folder = str_replace('\\', '/', dirname($folder));
    }
    $base = $folder === '/' || $folder === '.' ? '' : rtrim($folder, '/');
    return $base . '/' . ltrim($path, '/');
}

function redirect(string $path): never
{
    header('Location: ' . site_url($path), true, 303);
    exit;
}

function start_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    $directory = ROOT_PATH . '/storage/sessions';
    if (!is_dir($directory) && !mkdir($directory, 0700, true)) {
        throw new RuntimeException('No se pudo crear la carpeta de sesiones.');
    }
    session_save_path($directory);
    session_set_cookie_params([
        'httponly' => true,
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
        'path' => '/',
    ]);
    session_start();
}

function csrf_token(): string
{
    start_session();
    return $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
}

function verify_csrf(): void
{
    start_session();
    if (!hash_equals(csrf_token(), (string) ($_POST['csrf'] ?? ''))) {
        http_response_code(419);
        exit('La sesión del formulario expiró. Vuelve atrás, recarga la página e inténtalo otra vez.');
    }
}

function require_admin(): void
{
    start_session();
    if (empty($_SESSION['admin_id'])) {
        redirect('admin/login.php');
    }
}

function flash(string $type, string $message): void
{
    start_session();
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function take_flash(): ?array
{
    start_session();
    $message = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $message;
}

function field(string $name, int $limit = 10000): string
{
    $value = trim((string) ($_POST[$name] ?? ''));
    if (strlen($value) > $limit) {
        throw new RuntimeException("El campo {$name} es demasiado largo.");
    }
    return $value;
}

function required_field(string $name, int $limit): string
{
    $value = field($name, $limit);
    if ($value === '') {
        throw new RuntimeException("Completa el campo {$name}.");
    }
    return $value;
}

function web_url(string $name): string
{
    $value = field($name, 255);
    if ($value !== '' && (!filter_var($value, FILTER_VALIDATE_URL) || !in_array(strtolower((string) parse_url($value, PHP_URL_SCHEME)), ['http', 'https'], true))) {
        throw new RuntimeException("El enlace {$name} debe empezar con https:// o http://.");
    }
    return $value;
}

// Comprueba MIME real, tamaño y origen; el nombre final es aleatorio.
function uploaded_image(string $input, ?string $oldPath = null, bool $required = false): ?string
{
    $file = $_FILES[$input] ?? null;
    if (!$file || $file['error'] === UPLOAD_ERR_NO_FILE) {
        if ($required && !$oldPath) {
            throw new RuntimeException('Selecciona una imagen.');
        }
        return $oldPath;
    }
    if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > 5 * 1024 * 1024 || $file['size'] <= 0) {
        throw new RuntimeException('La imagen debe pesar menos de 5 MB.');
    }
    if (!is_uploaded_file($file['tmp_name'])) {
        throw new RuntimeException('La imagen no es una carga válida.');
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $types = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($types[$mime]) || @getimagesize($file['tmp_name']) === false) {
        throw new RuntimeException('Solo se aceptan imágenes JPEG, PNG o WebP.');
    }
    $name = bin2hex(random_bytes(16)) . '.' . $types[$mime];
    $directory = ROOT_PATH . '/storage/uploads';
    if (!is_dir($directory) && !mkdir($directory, 0755, true)) {
        throw new RuntimeException('No se pudo crear la carpeta de imágenes.');
    }
    if (!move_uploaded_file($file['tmp_name'], $directory . '/' . $name)) {
        throw new RuntimeException('No se pudo guardar la imagen.');
    }
    return 'storage/uploads/' . $name;
}

function delete_image(?string $path): void
{
    if (!asset($path)) {
        return;
    }
    $absolute = ROOT_PATH . '/' . $path;
    if (is_file($absolute)) {
        unlink($absolute);
    }
}

function format_date(string $date): string
{
    return date('d/m/Y', strtotime($date));
}
