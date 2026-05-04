<?php
// ============================================================
//  RÉGAL — Funciones globales
// ============================================================

/**
 * Formatea precio en pesos mexicanos
 */
function formatPrecio(float $precio): string {
    return '$' . number_format($precio, 2, '.', ',');
}

/**
 * Devuelve la ruta pública de la imagen de un platillo
 * Si no tiene imagen, regresa un placeholder SVG inline
 */
function imagenUrl(?string $imagen): string {
    if ($imagen && file_exists(__DIR__ . '/../uploads/products/' . $imagen)) {
        return '../uploads/products/' . htmlspecialchars($imagen);
    }
    return '../assets/images/placeholder.svg';
}

function imagenUrlAdmin(?string $imagen): string {
    if ($imagen && file_exists(__DIR__ . '/../uploads/products/' . $imagen)) {
        return '../uploads/products/' . htmlspecialchars($imagen);
    }
    return '../assets/images/placeholder.svg';
}

/**
 * Escapa HTML
 */
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/**
 * Sube una imagen, valida tipo y tamaño, devuelve nombre o false
 */
function subirImagen(array $file, string $destDir): string|false {
    $allowed = ['image/jpeg', 'image/png', 'image/webp'];
    $maxSize = 5 * 1024 * 1024; // 5 MB

    if ($file['error'] !== UPLOAD_ERR_OK) return false;
    if (!in_array($file['type'], $allowed)) return false;
    if ($file['size'] > $maxSize) return false;

    $ext  = pathinfo($file['name'], PATHINFO_EXTENSION);
    $name = uniqid('prod_', true) . '.' . strtolower($ext);
    $dest = rtrim($destDir, '/') . '/' . $name;

    if (!move_uploaded_file($file['tmp_name'], $dest)) return false;
    return $name;
}

/**
 * Redirige y termina
 */
function redirect(string $url): never {
    header('Location: ' . $url);
    exit;
}
