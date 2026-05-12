<?php
$current = basename($_SERVER['PHP_SELF']);
$titles  = ['index.php'=>'Platillos','categorias.php'=>'Categorías'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $titles[$current] ?? 'Admin' ?> — RÉGAL</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css">
  <link rel="icon" href="<?= BASE_URL ?>/assets/images/monito01.png" type="image/png">
</head>
<body>
<aside class="sidebar" id="sidebar">
  <div class="sidebar__brand">
    <img class="login-card__logo" src="<?= BASE_URL ?>/assets/images/regalDorado.png" alt="RÉGAL">
    <div class="sidebar__label">Panel de administración</div>
  </div>
  <nav class="sidebar__nav">
    <a href="<?= BASE_URL ?>/admin/index.php" class="<?= $current==='index.php'?'activo':'' ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
      Platillos
    </a>
    <a href="<?= BASE_URL ?>/admin/categorias.php" class="<?= $current==='categorias.php'?'activo':'' ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 7h18M3 12h18M3 17h18"/></svg>
      Categorías
    </a>
    <a href="<?= BASE_URL ?>" target="_blank">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
      Ver menú Regalianos
    </a>
    <a href="<?= BASE_URL ?>/admin/regalianos.php" class="<?= $current==='regalianos.php'?'activo':'' ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
        <circle cx="9" cy="7" r="4"></circle>
        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
      </svg>
      Regalianos
    </a>
  </nav>
  <div class="sidebar__footer">
    <div class="sidebar__user">
      <strong><?= htmlspecialchars($_SESSION['admin_nombre'] ?? 'Admin') ?></strong>
      <?= htmlspecialchars($_SESSION['admin_usuario'] ?? '') ?>
    </div>
    <a class="btn-logout" href="<?= BASE_URL ?>/admin/logout.php">Cerrar sesión</a>
  </div>
</aside>

<div class="admin-main">
  <div class="admin-topbar">
    <button class="sidebar-toggle" id="sidebarToggle" aria-label="Menú">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
    </button>
    <h1 class="admin-topbar__title"><?= $titles[$current] ?? 'Panel' ?></h1>
    <div></div>
  </div>
  <div class="admin-content">
