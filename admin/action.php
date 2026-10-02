<?php
declare(strict_types=1);
require __DIR__ . '/../includes/app.php';
require_admin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Método no permitido.'); }
verify_csrf();

$action = (string) ($_POST['action'] ?? '');
$tab = in_array($action, ['save_certificate', 'delete_certificate'], true) ? 'atestados' :
    (in_array($action, ['save_gallery', 'delete_gallery'], true) ? 'galeria' :
    (in_array($action, ['save_post', 'delete_post'], true) ? 'blog' : 'perfil'));

function existing_row(string $table, int $id): array
{
    if (!in_array($table, ['certificates', 'gallery', 'posts'], true)) {
        throw new RuntimeException('Contenido no válido.');
    }
    $statement = db()->prepare("SELECT * FROM {$table} WHERE id = ?");
    $statement->execute([$id]);
    return $statement->fetch() ?: throw new RuntimeException('El elemento ya no existe.');
}

try {
    if ($action === 'save_profile') {
        $data = [
            'full_name' => required_field('full_name', 160),
            'tagline' => required_field('tagline', 220),
            'short_bio' => required_field('short_bio', 2000),
            'about_text' => required_field('about_text', 10000),
            'interests' => required_field('interests', 5000),
            'skills' => required_field('skills', 5000),
            'experience_text' => required_field('experience_text', 10000),
            'email' => required_field('email', 190),
            'phone' => field('phone', 60),
            'social_github' => web_url('social_github'),
            'social_linkedin' => web_url('social_linkedin'),
            'social_instagram' => web_url('social_instagram'),
        ];
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) { throw new RuntimeException('Escribe un correo de contacto válido.'); }
        $old = profile();
        $data['photo_path'] = uploaded_image('photo', $old['photo_path'] ?? null);
        $columns = array_keys($data);
        $sets = implode(', ', array_map(fn (string $column): string => "{$column} = ?", $columns));
        $statement = db()->prepare("UPDATE profile SET {$sets} WHERE id = 1");
        $statement->execute(array_values($data));
        if ($data['photo_path'] !== ($old['photo_path'] ?? null)) { delete_image($old['photo_path'] ?? null); }
        flash('success', 'Perfil actualizado.');
    } elseif ($action === 'save_certificate') {
        $id = (int) ($_POST['id'] ?? 0);
        $old = $id ? existing_row('certificates', $id) : null;
        $title = required_field('title', 180);
        $institution = required_field('institution', 180);
        $year = filter_var($_POST['year'] ?? null, FILTER_VALIDATE_INT);
        if (!$year || $year < 1900 || $year > (int) date('Y') + 1) { throw new RuntimeException('Escribe un año válido.'); }
        $description = required_field('description', 5000);
        $image = uploaded_image('image', $old['image_path'] ?? null, true);
        if ($old) {
            $statement = db()->prepare('UPDATE certificates SET title = ?, institution = ?, year = ?, description = ?, image_path = ? WHERE id = ?');
            $statement->execute([$title, $institution, $year, $description, $image, $id]);
            if ($image !== $old['image_path']) { delete_image($old['image_path']); }
        } else {
            $statement = db()->prepare('INSERT INTO certificates (title, institution, year, description, image_path) VALUES (?, ?, ?, ?, ?)');
            $statement->execute([$title, $institution, $year, $description, $image]);
        }
        flash('success', 'Atestado guardado.');
    } elseif ($action === 'save_gallery') {
        $id = (int) ($_POST['id'] ?? 0);
        $old = $id ? existing_row('gallery', $id) : null;
        $title = required_field('title', 180);
        $description = field('description', 5000);
        $image = uploaded_image('image', $old['image_path'] ?? null, true);
        if ($old) {
            $statement = db()->prepare('UPDATE gallery SET title = ?, description = ?, image_path = ? WHERE id = ?');
            $statement->execute([$title, $description, $image, $id]);
            if ($image !== $old['image_path']) { delete_image($old['image_path']); }
        } else {
            $statement = db()->prepare('INSERT INTO gallery (title, description, image_path) VALUES (?, ?, ?)');
            $statement->execute([$title, $description, $image]);
        }
        flash('success', 'Fotografía guardada.');
    } elseif ($action === 'save_post') {
        $id = (int) ($_POST['id'] ?? 0);
        $old = $id ? existing_row('posts', $id) : null;
        $title = required_field('title', 180);
        $excerpt = required_field('excerpt', 350);
        $body = required_field('body', 50000);
        $published = isset($_POST['is_published']) ? 1 : 0;
        $image = uploaded_image('image', $old['image_path'] ?? null);
        if ($old) {
            $statement = db()->prepare('UPDATE posts SET title = ?, excerpt = ?, body = ?, image_path = ?, is_published = ? WHERE id = ?');
            $statement->execute([$title, $excerpt, $body, $image, $published, $id]);
            if ($image !== $old['image_path']) { delete_image($old['image_path']); }
        } else {
            $statement = db()->prepare('INSERT INTO posts (title, excerpt, body, image_path, is_published) VALUES (?, ?, ?, ?, ?)');
            $statement->execute([$title, $excerpt, $body, $image, $published]);
        }
        flash('success', 'Publicación guardada.');
    } elseif (in_array($action, ['delete_certificate', 'delete_gallery', 'delete_post'], true)) {
        $table = ['delete_certificate' => 'certificates', 'delete_gallery' => 'gallery', 'delete_post' => 'posts'][$action];
        $id = (int) ($_POST['id'] ?? 0);
        $old = existing_row($table, $id);
        $statement = db()->prepare("DELETE FROM {$table} WHERE id = ?");
        $statement->execute([$id]);
        delete_image($old['image_path'] ?? null);
        flash('success', 'Elemento eliminado.');
    } else {
        throw new RuntimeException('Acción no válida.');
    }
} catch (Throwable $error) {
    $message = $error instanceof PDOException ? 'No se pudo guardar en MySQL. Comprueba la base de datos.' : $error->getMessage();
    flash('error', $message);
}
redirect('admin/index.php?tab=' . $tab);
