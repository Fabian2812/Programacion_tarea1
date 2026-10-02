<?php
declare(strict_types=1);
require __DIR__ . '/../includes/app.php';
require_admin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Método no permitido.'); }
verify_csrf();
$_SESSION = [];
session_regenerate_id(true);
redirect('admin/login.php');
