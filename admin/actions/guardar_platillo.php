<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/db.php';
require_once dirname(__DIR__, 2) . '/includes/upload.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/admin/index.php'); exit;
}

$modo        = $_POST['modo']         ?? '';
$id          = (int)($_POST['id']     ?? 0);
$nombre      = trim($_POST['nombre']  ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$precio      = (float)($_POST['precio'] ?? 0);
$cat_id      = (int)($_POST['categoria_id'] ?? 0);
$disponible  = isset($_POST['disponible'])  ? 1 : 0;
$destacado   = isset($_POST['destacado'])   ? 1 : 0;

// Validaciones básicas
if (!$nombre || !$precio || !$cat_id) {
    $_SESSION['flash_msg']  = 'Faltan campos obligatorios.';
    $_SESSION['flash_type'] = 'error';
    header('Location: ' . BASE_URL . '/admin/index.php'); exit;
}

$pdo    = DB::get();
$imagen = null;

// Procesar imagen si se subió
if (!empty($_FILES['imagen']['name'])) {
    // Obtener imagen anterior si es edición
    $old_imagen = null;
    if ($modo === 'editar' && $id) {
        $stmt = $pdo->prepare('SELECT imagen FROM platillos WHERE id = ? AND seccion = "c"');
        $stmt->execute([$id]);
        $old_imagen = $stmt->fetchColumn() ?: null;
    }
    try {
        $imagen = upload_image($_FILES['imagen'], $old_imagen);
    } catch (RuntimeException $e) {
        $_SESSION['flash_msg']  = 'Error al subir imagen: ' . $e->getMessage();
        $_SESSION['flash_type'] = 'error';
        header('Location: ' . BASE_URL . '/admin/index.php'); exit;
    }
}

try {
    if ($modo === 'nuevo') {
        $sql = 'INSERT INTO platillos (categoria_id, nombre, descripcion, precio, imagen, disponible, destacado)
                VALUES (?, ?, ?, ?, ?, ?, ?)';
        $pdo->prepare($sql)->execute([$cat_id, $nombre, $descripcion, $precio, $imagen, $disponible, $destacado]);
        $_SESSION['flash_msg']  = "Platillo «{$nombre}» creado correctamente.";
    } elseif ($modo === 'editar' && $id) {
        if ($imagen) {
            $sql = 'UPDATE platillos SET categoria_id=?, nombre=?, descripcion=?, precio=?, imagen=?, disponible=?, destacado=? WHERE id=? AND seccion = "c"';
            $pdo->prepare($sql)->execute([$cat_id, $nombre, $descripcion, $precio, $imagen, $disponible, $destacado, $id]);
        } else {
            $sql = 'UPDATE platillos SET categoria_id=?, nombre=?, descripcion=?, precio=?, disponible=?, destacado=? WHERE id=? AND seccion = "c"';
            $pdo->prepare($sql)->execute([$cat_id, $nombre, $descripcion, $precio, $disponible, $destacado, $id]);
        }
        $_SESSION['flash_msg']  = "Platillo «{$nombre}» actualizado.";
    }
    $_SESSION['flash_type'] = 'success';
} catch (PDOException $e) {
    $_SESSION['flash_msg']  = 'Error de base de datos: ' . $e->getMessage();
    $_SESSION['flash_type'] = 'error';
}

header('Location: ' . BASE_URL . '/admin/index.php'); exit;
