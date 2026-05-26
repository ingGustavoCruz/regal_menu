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

// ATRAPAMOS LOS NUEVOS CAMPOS (Si por alguna razón vienen vacíos, por defecto serán de la cafetería)
$seccion     = $_POST['seccion']      ?? 'c';
$dia_semana  = $_POST['dia_semana']   ?? 'Todos';

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
    // Obtener imagen anterior si es edición (QUITAMOS EL FILTRO DE SECCION='C')
    $old_imagen = null;
    if ($modo === 'editar' && $id) {
        $stmt = $pdo->prepare('SELECT imagen FROM platillos WHERE id = ?');
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
        // AGREGAMOS SECCION Y DIA A LA INSERCIÓN
        $sql = 'INSERT INTO platillos (categoria_id, nombre, descripcion, precio, imagen, disponible, destacado, seccion, dia_semana)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)';
        $pdo->prepare($sql)->execute([$cat_id, $nombre, $descripcion, $precio, $imagen, $disponible, $destacado, $seccion, $dia_semana]);
        $_SESSION['flash_msg']  = "Platillo «{$nombre}» creado correctamente.";
    } elseif ($modo === 'editar' && $id) {
        // AGREGAMOS SECCION Y DIA A LA EDICIÓN (Y QUITAR EL FILTRO 'C')
        if ($imagen) {
            $sql = 'UPDATE platillos SET categoria_id=?, nombre=?, descripcion=?, precio=?, imagen=?, disponible=?, destacado=?, seccion=?, dia_semana=? WHERE id=?';
            $pdo->prepare($sql)->execute([$cat_id, $nombre, $descripcion, $precio, $imagen, $disponible, $destacado, $seccion, $dia_semana, $id]);
        } else {
            $sql = 'UPDATE platillos SET categoria_id=?, nombre=?, descripcion=?, precio=?, disponible=?, destacado=?, seccion=?, dia_semana=? WHERE id=?';
            $pdo->prepare($sql)->execute([$cat_id, $nombre, $descripcion, $precio, $disponible, $destacado, $seccion, $dia_semana, $id]);
        }
        $_SESSION['flash_msg']  = "Platillo «{$nombre}» actualizado.";
    }
    $_SESSION['flash_type'] = 'success';
} catch (PDOException $e) {
    $_SESSION['flash_msg']  = 'Error de base de datos: ' . $e->getMessage();
    $_SESSION['flash_type'] = 'error';
}

header('Location: ' . BASE_URL . '/admin/index.php'); exit;