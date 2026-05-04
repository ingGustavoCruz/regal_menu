<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

function session_start_safe(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params(['lifetime'=>SESSION_LIFETIME,'path'=>'/','secure'=>false,'httponly'=>true,'samesite'=>'Strict']);
        session_start();
    }
}

function is_logged_in(): bool {
    session_start_safe();
    if (empty($_SESSION['admin_id']) || empty($_SESSION['admin_last_activity'])) return false;
    if (time() - $_SESSION['admin_last_activity'] > SESSION_LIFETIME) { session_unset(); session_destroy(); return false; }
    $_SESSION['admin_last_activity'] = time();
    return true;
}

function require_login(): void {
    if (!is_logged_in()) { header('Location: ' . BASE_URL . '/admin/login.php'); exit; }
}

function login(string $usuario, string $password): bool {
    session_start_safe();
    $stmt = DB::get()->prepare('SELECT id, usuario, password, nombre FROM admins WHERE usuario = ? LIMIT 1');
    $stmt->execute([$usuario]);
    $admin = $stmt->fetch();
    if ($admin && password_verify($password, $admin['password'])) {
        session_regenerate_id(true);
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_usuario'] = $admin['usuario'];
        $_SESSION['admin_nombre'] = $admin['nombre'];
        $_SESSION['admin_last_activity'] = time();
        return true;
    }
    return false;
}

function logout(): void {
    session_start_safe(); session_unset(); session_destroy();
    header('Location: ' . BASE_URL . '/admin/login.php'); exit;
}
