<?php
// ============================================================
//  RÉGAL Admin — CRUD de Platillos
// ============================================================

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) session_start();
requireLogin();

$uploadDir = __DIR__ . '/../uploads/products/';
$msg = '';
$msgType = 'success';

// ── Acción POST ───────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';

  // CREAR / EDITAR
  if (in_array($action, ['crear', 'editar'])) {
    $id      = (int)($_POST['id'] ?? 0);
    $catId   = (int)($_POST['categoria_id'] ?? 0);
    $nombre  = trim($_POST['nombre'] ?? '');
    $desc    = trim($_POST['descripcion'] ?? '');
    $precio  = (float)($_POST['precio'] ?? 0);
    $dest    = isset($_POST['destacado']) ? 1 : 0;
    $activo  = isset($_POST['activo']) ? 1 : 0;
    $orden   = (int)($_POST['orden'] ?? 0);

    if (!$nombre || !$catId || $precio <= 0) {
      $msg = 'Completa nombre, categoría y precio.';
      $msgType = 'error';
    } else {
      // Imagen
      $imagenNueva = null;
      if (!empty($_FILES['imagen']['name'])) {
        $imagenNueva = subirImagen($_FILES['imagen'], $uploadDir);
        if (!$imagenNueva) {
          $msg = 'Error al subir imagen. Formatos válidos: JPG, PNG, WEBP (máx 5 MB).';
          $msgType = 'error';
        }
      }

      if (!$msg) {
        if ($action === 'crear') {
          $stmt = $mysqli->prepare(
            "INSERT INTO platillos (categoria_id,nombre,descripcion,precio,imagen,destacado,activo,orden)
             VALUES (?,?,?,?,?,?,?,?)"
          );
          $stmt->bind_param('issdsiis', $catId,$nombre,$desc,$precio,$imagenNueva,$dest,$activo,$orden);
          $stmt->execute();
          $stmt->close();
          $msg = "Platillo «{$nombre}» creado correctamente.";
        } else {
          // Obtener imagen anterior
          $prev = $mysqli->query("SELECT imagen FROM platillos WHERE id={$id} LIMIT 1")->fetch_assoc();
          $imagenFinal = $imagenNueva ?? $prev['imagen'];

          $stmt = $mysqli->prepare(
            "UPDATE platillos SET categoria_id=?,nombre=?,descripcion=?,precio=?,imagen=?,destacado=?,activo=?,orden=?
             WHERE id=?"
          );
          $stmt->bind_param('issdsiisi', $catId,$nombre,$desc,$precio,$imagenFinal,$dest,$activo,$orden,$id);
          $stmt->execute();
          $stmt->close();

          // Borra imagen vieja si se subió nueva
          if ($imagenNueva && $prev['imagen'] && $prev['imagen'] !== $imagenNueva) {
            @unlink($uploadDir . $prev['imagen']);
          }
          $msg = "Platillo «{$nombre}» actualizado.";
        }
      }
    }
  }

  // TOGGLE ACTIVO
  if ($action === 'toggle_activo') {
    $id = (int)$_POST['id'];
    $mysqli->query("UPDATE platillos SET activo = NOT activo WHERE id = {$id}");
    redirect('productos.php?msg=Estado+actualizado');
  }

  // ELIMINAR
  if ($action === 'eliminar') {
    $id = (int)$_POST['id'];
    $prev = $mysqli->query("SELECT imagen FROM platillos WHERE id={$id} LIMIT 1")->fetch_assoc();
    $mysqli->query("DELETE FROM platillos WHERE id = {$id}");
    if ($prev['imagen']) @unlink($uploadDir . $prev['imagen']);
    redirect('productos.php?msg=Platillo+eliminado');
  }
}

// Msg de redirect
if (!$msg && isset($_GET['msg'])) {
  $msg = urldecode($_GET['msg']);
}

// ── Datos ─────────────────────────────────────────────────
$cats = $mysqli->query(
  "SELECT * FROM categorias ORDER BY orden ASC, nombre ASC"
)->fetch_all(MYSQLI_ASSOC);

$platillos = $mysqli->query(
  "SELECT p.*, c.nombre AS cat_nombre FROM platillos p
   JOIN categorias c ON p.categoria_id = c.id
   ORDER BY c.orden ASC, p.orden ASC, p.nombre ASC"
)->fetch_all(MYSQLI_ASSOC);

// ¿Modo edición?
$editando = null;
if (isset($_GET['editar'])) {
  $eid = (int)$_GET['editar'];
  foreach ($platillos as $p) {
    if ($p['id'] == $eid) { $editando = $p; break; }
  }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Régal Admin — Platillos</title>
  <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
<div class="admin-layout">

  <!-- Sidebar -->
  <aside class="admin-sidebar">
    <div class="sidebar-logo">
      <img src="../assets/images/logo-blanco.png"
           onerror="this.style.display='none'; this.nextElementSibling.style.display='block'"
           alt="RÉGAL">
      <div style="display:none; color:#fff; font-family:'DM Serif Display',serif; font-size:1.4rem;">RÉGAL</div>
    </div>
    <nav class="sidebar-nav">
      <span class="nav-label">Principal</span>
      <a class="nav-link" href="index.php"><span class="icon">📊</span> Dashboard</a>
      <span class="nav-label">Contenido</span>
      <a class="nav-link active" href="productos.php"><span class="icon">🍽️</span> Platillos</a>
      <a class="nav-link" href="categorias.php"><span class="icon">🗂️</span> Categorías</a>
      <span class="nav-label">Sitio</span>
      <a class="nav-link" href="../index.php" target="_blank"><span class="icon">👁️</span> Ver menú</a>
    </nav>
    <div class="sidebar-footer">
      <a href="logout.php">🚪 Cerrar sesión</a>
    </div>
  </aside>

  <div class="admin-main">
    <header class="admin-topbar">
      <h1 class="topbar-title">
        <?= $editando ? '✏️ Editar: ' . e($editando['nombre']) : '🍽️ Platillos' ?>
      </h1>
      <span class="topbar-user">👤 <?= e($_SESSION['admin_nombre'] ?? 'Admin') ?></span>
    </header>

    <div class="admin-content">

      <?php if ($msg): ?>
        <div class="alert alert-<?= $msgType ?>" data-auto-dismiss><?= e($msg) ?></div>
      <?php endif; ?>

      <!-- ── Formulario Crear / Editar ──────────────────── -->
      <div class="card" id="form-platillo">
        <div class="card-header">
          <h2 class="card-title"><?= $editando ? 'Editar platillo' : 'Nuevo platillo' ?></h2>
          <?php if ($editando): ?>
            <a href="productos.php" class="btn btn-secondary btn-sm">✕ Cancelar edición</a>
          <?php endif; ?>
        </div>
        <div style="padding:20px;">
          <form method="POST" action="productos.php" enctype="multipart/form-data">
            <input type="hidden" name="action" value="<?= $editando ? 'editar' : 'crear' ?>">
            <?php if ($editando): ?>
              <input type="hidden" name="id" value="<?= $editando['id'] ?>">
            <?php endif; ?>

            <div class="form-grid">
              <div class="form-group">
                <label for="nombre">Nombre del platillo *</label>
                <input type="text" id="nombre" name="nombre" required
                       value="<?= e($editando['nombre'] ?? '') ?>">
              </div>
              <div class="form-group">
                <label for="categoria_id">Categoría *</label>
                <select id="categoria_id" name="categoria_id" required>
                  <option value="">— Selecciona —</option>
                  <?php foreach ($cats as $cat): ?>
                    <option value="<?= $cat['id'] ?>"
                      <?= ($editando && $editando['categoria_id'] == $cat['id']) ? 'selected' : '' ?>>
                      <?= e($cat['nombre']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="form-group full">
                <label for="descripcion">Descripción</label>
                <textarea id="descripcion" name="descripcion"><?= e($editando['descripcion'] ?? '') ?></textarea>
              </div>

              <div class="form-group">
                <label for="precio">Precio (MXN) *</label>
                <input type="number" id="precio" name="precio" step="0.50" min="0" required
                       value="<?= $editando['precio'] ?? '' ?>">
              </div>
              <div class="form-group">
                <label for="orden">Orden de aparición</label>
                <input type="number" id="orden" name="orden" min="0"
                       value="<?= $editando['orden'] ?? 0 ?>">
              </div>

              <!-- Imagen -->
              <div class="form-group full">
                <label>Imagen del platillo (JPG/PNG/WEBP, máx 5 MB)</label>
                <div style="display:flex; align-items:center; gap:16px; flex-wrap:wrap;">
                  <div class="img-preview-wrap" id="img-preview-wrap">
                    <?php if ($editando && $editando['imagen'] && file_exists(__DIR__ . '/../uploads/products/' . $editando['imagen'])): ?>
                      <img src="../uploads/products/<?= e($editando['imagen']) ?>" alt="">
                    <?php else: ?>
                      📷
                    <?php endif; ?>
                  </div>
                  <input type="file" name="imagen" accept="image/jpeg,image/png,image/webp"
                         data-preview="img-preview-wrap">
                </div>
              </div>

              <!-- Toggles -->
              <div class="form-group">
                <label>Opciones</label>
                <div style="display:flex; flex-direction:column; gap:10px; margin-top:4px;">
                  <label class="form-check">
                    <input type="checkbox" name="activo" value="1"
                           <?= (!$editando || $editando['activo']) ? 'checked' : '' ?>>
                    Visible en el menú
                  </label>
                  <label class="form-check">
                    <input type="checkbox" name="destacado" value="1"
                           <?= ($editando && $editando['destacado']) ? 'checked' : '' ?>>
                    Marcar como destacado ⭐
                  </label>
                </div>
              </div>
            </div>

            <div class="form-actions">
              <button type="submit" class="btn btn-primary">
                <?= $editando ? '💾 Guardar cambios' : '➕ Crear platillo' ?>
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- ── Tabla de platillos ──────────────────────────── -->
      <div class="card">
        <div class="card-header">
          <h2 class="card-title">Todos los platillos (<?= count($platillos) ?>)</h2>
        </div>
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>Img</th>
                <th>Nombre</th>
                <th>Categoría</th>
                <th>Precio</th>
                <th>Estado</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($platillos as $p): ?>
              <tr>
                <td class="td-img">
                  <?php if ($p['imagen'] && file_exists(__DIR__ . '/../uploads/products/' . $p['imagen'])): ?>
                    <img src="../uploads/products/<?= e($p['imagen']) ?>" alt="">
                  <?php else: ?>
                    <div class="no-img">🍽️</div>
                  <?php endif; ?>
                </td>
                <td>
                  <strong><?= e($p['nombre']) ?></strong>
                  <?php if ($p['destacado']): ?><br><small style="color:#00534b;">⭐ Destacado</small><?php endif; ?>
                </td>
                <td><?= e($p['cat_nombre']) ?></td>
                <td><?= formatPrecio((float)$p['precio']) ?></td>
                <td>
                  <?php if ($p['activo']): ?>
                    <span class="badge badge-active">● Activo</span>
                  <?php else: ?>
                    <span class="badge badge-paused">⏸ Pausado</span>
                  <?php endif; ?>
                </td>
                <td>
                  <div style="display:flex; gap:6px; flex-wrap:wrap;">
                    <a href="productos.php?editar=<?= $p['id'] ?>#form-platillo"
                       class="btn btn-secondary btn-sm">✏️ Editar</a>

                    <form method="POST" action="productos.php" style="display:inline;">
                      <input type="hidden" name="action" value="toggle_activo">
                      <input type="hidden" name="id" value="<?= $p['id'] ?>">
                      <button type="submit" class="btn btn-warning btn-sm">
                        <?= $p['activo'] ? '⏸ Pausar' : '▶ Activar' ?>
                      </button>
                    </form>

                    <form method="POST" action="productos.php" style="display:inline;">
                      <input type="hidden" name="action" value="eliminar">
                      <input type="hidden" name="id" value="<?= $p['id'] ?>">
                      <button type="submit" class="btn btn-danger btn-sm"
                              data-confirm="¿Eliminar «<?= e($p['nombre']) ?>»? Esta acción no se puede deshacer.">
                        🗑 Eliminar
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </div>
</div>

<script src="../assets/js/admin.js"></script>
</body>
</html>
