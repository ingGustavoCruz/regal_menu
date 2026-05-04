<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';

if (is_logged_in()) {
    header('Location: ' . BASE_URL . '/admin/index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario  = trim($_POST['usuario'] ?? '');
    $password = trim($_POST['password'] ?? '');
    if (login($usuario, $password)) {
        header('Location: ' . BASE_URL . '/admin/index.php');
        exit;
    }
    $error = 'Credenciales incorrectas. Intenta de nuevo.';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin — RÉGAL</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css">
</head>
<body class="login-page">
  <div class="login-card">
    <img class="login-card__logo" src="<?= BASE_URL ?>/assets/images/logo-negro.png" alt="RÉGAL">
    <h1 class="login-card__title">Panel de Administración</h1>
    <p class="login-card__sub">Ingresa con tus credenciales para continuar</p>

    <?php if ($error): ?>
      <div class="alert alert--error" data-auto-dismiss><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
      <div class="form-group">
        <label class="form-label" for="usuario">Usuario</label>
        <input class="form-control" type="text" id="usuario" name="usuario" required autocomplete="username" autofocus>
      </div>
      <div class="form-group">
        <label class="form-label" for="password">Contraseña</label>
        <input class="form-control" type="password" id="password" name="password" required autocomplete="current-password">
      </div>
      <button class="btn btn--primary" style="width:100%;justify-content:center;padding:0.75rem" type="submit">
        Iniciar sesión
      </button>
    </form>
    <p style="text-align:center;margin-top:1.5rem">
      <a href="<?= BASE_URL ?>" style="font-size:0.82rem;color:#826f61;">← Ver menú público</a>
    </p>
  </div>
  <script src="<?= BASE_URL ?>/assets/js/admin.js"></script>
</body>
</html>
