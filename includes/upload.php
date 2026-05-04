<?php
// ============================================================
// RÉGAL — Helper de subida de imágenes
// ============================================================
require_once __DIR__ . '/config.php';

/**
 * Procesa y guarda una imagen subida por el admin.
 * Devuelve el nombre del archivo guardado o lanza una excepción.
 */
function upload_image(array $file, ?string $old_file = null): string {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Error al recibir el archivo.');
    }
    if ($file['size'] > MAX_UPLOAD_SIZE) {
        throw new RuntimeException('La imagen supera el tamaño máximo de 3 MB.');
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_EXTENSIONS, true)) {
        throw new RuntimeException('Formato no permitido. Usa JPG, PNG o WEBP.');
    }

    // Verificar que sea imagen real
    $info = @getimagesize($file['tmp_name']);
    if ($info === false) {
        throw new RuntimeException('El archivo no es una imagen válida.');
    }

    // Nombre único
    $filename = uniqid('regal_', true) . '.' . $ext;
    $dest     = UPLOAD_DIR . $filename;

    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        throw new RuntimeException('No se pudo guardar la imagen en el servidor.');
    }

    // Borrar imagen anterior si existe
    if ($old_file && file_exists(UPLOAD_DIR . $old_file)) {
        @unlink(UPLOAD_DIR . $old_file);
    }

    return $filename;
}

/**
 * Devuelve la URL completa de la imagen de un platillo.
 */
function imagen_url(?string $filename): string {
    if ($filename && file_exists(UPLOAD_DIR . $filename)) {
        return UPLOAD_URL . $filename;
    }
    return BASE_URL . '/assets/images/placeholder.svg';
}
