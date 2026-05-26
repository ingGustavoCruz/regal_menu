<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/db.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/admin/index.php'); exit;
}

$id         = (int)($_POST['id']         ?? 0);
$disponible = (int)($_POST['disponible'] ?? 0);

if ($id) {
    // Quitamos el filtro AND seccion = "c"
    $stmt = DB::get()->prepare('UPDATE platillos SET disponible = ? WHERE id = ?');
    $stmt->execute([$disponible ? 1 : 0, $id]);
    $_SESSION['flash_msg']  = $disponible ? 'Platillo activado en el menú.' : 'Platillo pausado del menú.';
    $_SESSION['flash_type'] = 'success';
}

header('Location: ' . BASE_URL . '/admin/index.php'); exit;