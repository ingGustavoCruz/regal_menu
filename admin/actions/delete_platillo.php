<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/db.php';
require_once dirname(__DIR__, 2) . '/includes/upload.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/admin/index.php'); exit;
}

$id  = (int)($_POST['id'] ?? 0);
$pdo = DB::get();

if ($id) {
    // Borrar imagen del disco (Quitamos el filtro AND seccion = "c")
    $stmt = $pdo->prepare('SELECT imagen FROM platillos WHERE id = ?');
    $stmt->execute([$id]);
    $imagen = $stmt->fetchColumn();
    if ($imagen && file_exists(UPLOAD_DIR . $imagen)) {
        @unlink(UPLOAD_DIR . $imagen);
    }
    
    // Quitamos el filtro AND seccion = "c" de la eliminación
    $pdo->prepare('DELETE FROM platillos WHERE id = ?')->execute([$id]);
    $_SESSION['flash_msg']  = 'Platillo eliminado correctamente.';
    $_SESSION['flash_type'] = 'success';
}

header('Location: ' . BASE_URL . '/admin/index.php'); exit;