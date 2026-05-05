<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_login();

$pdo = DB::get();

// Stats
$total     = $pdo->query("SELECT COUNT(*) FROM platillos")->fetchColumn();
$activos   = $pdo->query("SELECT COUNT(*) FROM platillos WHERE disponible=1")->fetchColumn();
$pausados  = $total - $activos;
$total_cat = $pdo->query("SELECT COUNT(*) FROM categorias WHERE activo=1")->fetchColumn();

// Mensajes de sesión
$msg      = $_SESSION['flash_msg']  ?? '';
$msg_type = $_SESSION['flash_type'] ?? 'success';
unset($_SESSION['flash_msg'], $_SESSION['flash_type']);

// Categorías para el select
$categorias = $pdo->query("SELECT id, nombre FROM categorias WHERE activo=1 ORDER BY orden,id")->fetchAll();

// Platillos con join a categoría
$platillos = $pdo->query(
  "SELECT p.*, c.nombre AS cat_nombre
   FROM platillos p
   JOIN categorias c ON c.id = p.categoria_id
   ORDER BY c.orden, p.orden, p.id"
)->fetchAll();

require __DIR__ . '/partials/header.php';
?>

<!-- Stats -->
<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-card__icon ic-verde">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
    </div>
    <div>
      <div class="stat-card__num"><?= $total ?></div>
      <div class="stat-card__label">Platillos totales</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-card__icon ic-verde">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 13l4 4L19 7"/></svg>
    </div>
    <div>
      <div class="stat-card__num"><?= $activos ?></div>
      <div class="stat-card__label">Activos en menú</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-card__icon ic-warm">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    </div>
    <div>
      <div class="stat-card__num"><?= $pausados ?></div>
      <div class="stat-card__label">Pausados</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-card__icon ic-topo">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 7h18M3 12h18M3 17h18"/></svg>
    </div>
    <div>
      <div class="stat-card__num"><?= $total_cat ?></div>
      <div class="stat-card__label">Categorías</div>
    </div>
  </div>
</div>

<?php if ($msg): ?>
  <div class="alert alert--<?= $msg_type ?>" data-auto-dismiss><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<!-- Tabla de platillos -->
<div class="table-wrap">
  <div class="table-toolbar">
    <span class="table-toolbar__title">Platillos</span>
    <div style="display:flex;gap:0.75rem;flex-wrap:wrap;align-items:center">
      <!-- Filtro categoría -->
      <select id="catFilter" class="form-control" style="width:auto;padding:0.45rem 0.75rem">
        <option value="">Todas las categorías</option>
        <?php foreach ($categorias as $c): ?>
          <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
        <?php endforeach; ?>
      </select>
      <!-- Búsqueda -->
      <div class="search-wrap">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input class="search-input" id="tablaSearch" type="search" placeholder="Buscar…">
      </div>
      <!-- Nuevo platillo -->
      <button class="btn btn--primary" data-modal-open="modalNuevo">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Nuevo platillo
      </button>
    </div>
  </div>

  <div style="overflow-x:auto">
    <table>
      <thead>
        <tr>
          <th>Foto</th>
          <th>Nombre</th>
          <th>Categoría</th>
          <th>Precio</th>
          <th>Estado</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($platillos as $p): ?>
          <tr data-cat="<?= $p['categoria_id'] ?>">
            <td>
              <?php if ($p['imagen'] && file_exists(UPLOAD_DIR . $p['imagen'])): ?>
                <img class="td-img" src="<?= BASE_URL ?>/assets/images/uploads/<?= htmlspecialchars($p['imagen']) ?>" alt="">
              <?php else: ?>
                <div class="td-img" style="background:#f0ece8;border-radius:6px;display:flex;align-items:center;justify-content:center;color:#c0b8b0;font-size:1.2rem">☕</div>
              <?php endif; ?>
            </td>
            <td>
              <div class="td-nombre"><?= htmlspecialchars($p['nombre']) ?></div>
              <div class="td-desc"><?= htmlspecialchars($p['descripcion'] ?? '') ?></div>
            </td>
            <td style="white-space:nowrap"><?= htmlspecialchars($p['cat_nombre']) ?></td>
            <td><span class="td-precio">$<?= number_format($p['precio'], 2) ?></span></td>
            <td>
              <?php if ($p['disponible']): ?>
                <span class="badge badge--on">● Activo</span>
              <?php else: ?>
                <span class="badge badge--off">● Pausado</span>
              <?php endif; ?>
            </td>
            <td>
              <div class="td-actions">
                <button class="btn btn--outline btn--sm"
                  onclick="abrirEditar({
                    id:'<?= $p['id'] ?>',
                    nombre:<?= htmlspecialchars(json_encode($p['nombre']), ENT_QUOTES, 'UTF-8') ?>,
                    descripcion:<?= htmlspecialchars(json_encode($p['descripcion']), ENT_QUOTES, 'UTF-8') ?>,
                    precio:'<?= $p['precio'] ?>',
                    categoria_id:'<?= $p['categoria_id'] ?>',
                    disponible:'<?= $p['disponible'] ?>',
                    destacado:'<?= $p['destacado'] ?>',
                    imagen:<?= htmlspecialchars(json_encode($p['imagen']), ENT_QUOTES, 'UTF-8') ?>,
                    imagen_url:<?= htmlspecialchars(json_encode($p['imagen'] ? BASE_URL . '/assets/images/uploads/' . $p['imagen'] : ''), ENT_QUOTES, 'UTF-8') ?>
                  })">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                  Editar
                </button>
                <!-- Toggle disponible -->
                <form method="POST" action="actions/toggle_platillo.php">
                  <input type="hidden" name="id" value="<?= $p['id'] ?>">
                  <input type="hidden" name="disponible" value="<?= $p['disponible'] ? 0 : 1 ?>">
                  <button class="btn btn--outline btn--sm" type="submit" title="<?= $p['disponible'] ? 'Pausar' : 'Activar' ?>">
                    <?php if ($p['disponible']): ?>
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>
                    <?php else: ?>
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                    <?php endif; ?>
                  </button>
                </form>
                <!-- Eliminar -->
                <form method="POST" action="actions/delete_platillo.php" onsubmit="confirmarEliminar(this);return false">
                  <input type="hidden" name="id" value="<?= $p['id'] ?>">
                  <button class="btn btn--danger btn--sm btn--icon" type="submit" title="Eliminar">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>
                  </button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$platillos): ?>
          <tr><td colspan="6" style="text-align:center;padding:3rem;color:#8d7b72">No hay platillos registrados aún.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/partials/modal_nuevo.php'; ?>
<?php require __DIR__ . '/partials/modal_editar.php'; ?>
<?php require __DIR__ . '/partials/footer.php'; ?>
