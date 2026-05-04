<?php
// ============================================================
// RÉGAL — Configuración de conexión a la base de datos
// ============================================================
// Copia este archivo y renómbralo config.php si quieres
// mantener credenciales fuera del repo con .gitignore

define('DB_HOST', 'localhost');
define('DB_NAME', 'regal_menu_db');
define('DB_USER', 'root');        // Cambia en producción
define('DB_PASS', '');            // Cambia en producción
define('DB_CHARSET', 'utf8mb4');

// Zona horaria
date_default_timezone_set('America/Mexico_City');

// Ruta base del proyecto (ajusta si usas un subdirectorio)
define('BASE_PATH', dirname(__DIR__));
define('BASE_URL',  'http://localhost/regal_menu');   // sin slash al final

// Directorio de uploads
define('UPLOAD_DIR', BASE_PATH . '/assets/images/uploads/');
define('UPLOAD_URL', BASE_URL  . '/assets/images/uploads/');

// Tamaño máximo de imagen (en bytes) — 3 MB
define('MAX_UPLOAD_SIZE', 3 * 1024 * 1024);
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'webp']);

// Duración de la sesión admin (segundos) — 2 horas
define('SESSION_LIFETIME', 7200);
