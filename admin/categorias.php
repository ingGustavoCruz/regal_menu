<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_login();

$pdo = DB::get();

// Flash
$msg      = $_SESSION['flash_msg']  ?? '';
$msg_type = $_SESSION['flash_type'] ?? 'success';
unset($_SESSION['flash_msg'], $_SESSION['flash_type']);

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    $id     = (int)($_POST['id'] ?? 0);
    $nombre = trim($_POST['nombre'] ?? '');
    $slug   = preg_replace('/[^a-z0-9]+/','-', strtolower(iconv('UTF-8','ASCII//TRANSLIT',$nombre)));
    $orden  = (int)($_POST['orden'] ?? 0);

    try {
        if ($accion === 'nueva' && $nombre) {
            $pdo->prepare('INSERT INTO categorias (nombre, slug, orden) VALUES (?, ?, ?)')
                ->execute([$nombre, $slug, $orden]);
            $_SESSION['flash_msg']  = "Categoría «{$nombre}» creada.";
        } elseif ($accion === 'editar' && $id && $nombre) {
            $pdo->prepare('UPDATE categorias SET nombre=?, slug=?, orden=? WHERE id=?')
                ->execute([$nombre, $slug, $orden, $id]);
            $_SESSION['flash_msg']  = "Categoría «{$nombre}» actualizada.";
        } elseif ($accion === 'toggle' && $id) {
            $activo = (int)($_POST['activo'] ?? 0);
            $pdo->prepare('UPDATE categorias SET activo=? WHERE id=?')->execute([$activo, $id]);
            $_SESSION['flash_msg']  = $activo ? 'Categoría activada.' : 'Categoría desactivada.';
        } elseif ($accion === 'eliminar' && $id) {
            $tiene = $pdo->prepare('SELECT COUNT(*) FROM platillos WHERE categoria_id=?');
            $tiene->execute([$id]);
            if ($tiene->fetchColumn() > 0) {
                $_SESSION['flash_msg']  = 'No se puede eliminar: la categoría tiene platillos asignados.';
                $_SESSION['flash_type'] = 'error';
            } else {
                $pdo->prepare('DELETE FROM categorias WHERE id=?')->execute([$id]);
                $_SESSION['flash_msg']  = 'Categoría eliminada.';
            }
        }
        $_SESSION['flash_type'] = $_SESSION['flash_type'] ?? 'success';
    } catch (PDOException $e) {
        $_SESSION['flash_msg']  = 'Error: ' . $e->getMessage();
        $_SESSION['flash_type'] = 'error';
    }
    header('Location: ' . BASE_URL . '/admin/categorias.php'); exit;
}

$categorias = $pdo->query(
    "SELECT c.*, (SELECT COUNT(*) FROM platillos p WHERE p.categoria_id=c.id) AS total_platillos
     FROM categorias c ORDER BY c.orden, c.id"
)->fetchAll();

require __DIR__ . '/partials/header.php';
?>

<?php if ($msg): ?>
  <div class="alert alert--<?= $msg_type ?>" data-auto-dismiss><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<div style="display:grid;gap:1.5rem;grid-template-columns:1fr 340px;align-items:start" class="cat-layout">

  <!-- Lista de categorías -->
  <div class="table-wrap">
    <div class="table-toolbar">
      <span class="table-toolbar__title">Categorías del menú</span>
    </div>
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Nombre</th>
          <th>Slug</th>
          <th>Platillos</th>
          <th>Estado</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($categorias as $c): ?>
        <tr>
          <td style="color:#8d7b72;font-size:0.8rem"><?= $c['orden'] ?: '—' ?></td>
          <td style="font-weight:500"><?= htmlspecialchars($c['nombre']) ?></td>
          <td style="font-family:monospace;font-size:0.78rem;color:#8d7b72"><?= htmlspecialchars($c['slug']) ?></td>
          <td><?= $c['total_platillos'] ?></td>
          <td>
            <?php if ($c['activo']): ?>
              <span class="badge badge--on">● Activa</span>
            <?php else: ?>
              <span class="badge badge--off">● Inactiva</span>
            <?php endif; ?>
          </td>
          <td>
            <div class="td-actions">
              <!-- Editar -->
              <button class="btn btn--outline btn--sm"
                onclick="abrirEditarCat({id: '<?= $c['id'] ?>', nombre: <?= htmlspecialchars(json_encode($c['nombre']), ENT_QUOTES, 'UTF-8') ?>, orden: '<?= $c['orden'] ?>'})">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                Editar
              </button>
              <!-- Toggle -->
              <form method="POST">
                <input type="hidden" name="accion" value="toggle">
                <input type="hidden" name="id" value="<?= $c['id'] ?>">
                <input type="hidden" name="activo" value="<?= $c['activo'] ? 0 : 1 ?>">
                <button class="btn btn--outline btn--sm" type="submit">
                  <?php if ($c['activo']): ?>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>
                  <?php else: ?>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                  <?php endif; ?>
                </button>
              </form>
              <!-- Eliminar -->
              <?php if ($c['total_platillos'] == 0): ?>
              <form method="POST" onsubmit="return confirm('¿Eliminar esta categoría?')">
                <input type="hidden" name="accion" value="eliminar">
                <input type="hidden" name="id" value="<?= $c['id'] ?>">
                <button class="btn btn--danger btn--sm btn--icon" type="submit">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/></svg>
                </button>
              </form>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Panel lateral: Nueva categoría -->
  <div class="table-wrap" style="padding:1.25rem">
    <h2 class="table-toolbar__title" style="margin-bottom:1.25rem">Nueva categoría</h2>
    <form method="POST">
      <input type="hidden" name="accion" value="nueva">
      <div class="form-group">
        <label class="form-label" for="c_nombre">Nombre *</label>
        <input class="form-control" type="text" id="c_nombre" name="nombre" required maxlength="80" placeholder="Ej: Bebidas Calientes">
      </div>
      <div class="form-group">
        <label class="form-label" for="c_orden">Orden de aparición</label>
        <input class="form-control" type="number" id="c_orden" name="orden" value="0" min="0" max="99">
        <p class="form-hint">Número menor = aparece primero</p>
      </div>
      <button class="btn btn--primary" style="width:100%;justify-content:center" type="submit">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Crear categoría
      </button>
    </form>
  </div>

</div>

<!-- Modal editar categoría -->
<div class="modal-backdrop" id="modalEditarCat">
  <div class="modal" style="max-width:420px">
    <div class="modal__header">
      <h2 class="modal__title">Editar categoría</h2>
      <button class="btn-close" data-modal-close>&times;</button>
    </div>
    <form method="POST">
      <input type="hidden" name="accion" value="editar">
      <input type="hidden" name="id" id="ec_id">
      <div class="modal__body">
        <div class="form-group">
          <label class="form-label" for="ec_nombre">Nombre *</label>
          <input class="form-control" type="text" id="ec_nombre" name="nombre" required maxlength="80">
        </div>
        <div class="form-group">
          <label class="form-label" for="ec_orden">Orden</label>
          <input class="form-control" type="number" id="ec_orden" name="orden" min="0" max="99">
        </div>
      </div>
      <div class="modal__footer">
        <button class="btn btn--outline" type="button" data-modal-close>Cancelar</button>
        <button class="btn btn--primary" type="submit">Guardar</button>
      </div>
    </form>
  </div>
</div>

<style>
@media(max-width:900px){.cat-layout{grid-template-columns:1fr}}
</style>
<script>
function abrirEditarCat(data) {
  document.getElementById('ec_id').value     = data.id;
  document.getElementById('ec_nombre').value = data.nombre;
  document.getElementById('ec_orden').value  = data.orden;
  document.getElementById('modalEditarCat').classList.add('abierto');
}
</script>

<?php require __DIR__ . '/partials/footer.php'; ?>
