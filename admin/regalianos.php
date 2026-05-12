<?php
// ============================================================
//  RÉGAL Admin — CRUD de Regalianos
// ============================================================

require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/db.php';

if (session_status() === PHP_SESSION_NONE) session_start();
require_login(); 

$pdo = DB::get();

// Mensajes de sesión
$msg      = $_SESSION['flash_msg']  ?? ($_GET['msg'] ?? '');
$msg_type = $_SESSION['flash_type'] ?? 'success';
unset($_SESSION['flash_msg'], $_SESSION['flash_type']);

// ── Datos (KPIs) ──────────────────────────────────────────
$total_regalianos = $pdo->query("SELECT COUNT(*) FROM regalianos")->fetchColumn();
$activos = $pdo->query("SELECT COUNT(*) FROM regalianos WHERE estado = 1")->fetchColumn();
$saldo_total = $pdo->query("SELECT SUM(saldo) FROM regalianos")->fetchColumn() ?? 0.00;

// ── Lista de Regalianos ───────────────────────────────────
$regalianos = $pdo->query("SELECT * FROM regalianos ORDER BY fecha_registro DESC")->fetchAll();

// ¿Modo edición?
$editando = null;
if (isset($_GET['editar'])) {
    $eid = (int)$_GET['editar'];
    foreach ($regalianos as $r) {
        if ($r['id'] == $eid) { $editando = $r; break; }
    }
}

// Inyectamos el script del QR antes del header para que esté disponible
$extra_head = '<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>';

require __DIR__ . '/partials/header.php';
?>

<header class="admin-topbar">
  <h1 class="admin-topbar__title">Comunidad Regalianos</h1>
</header>

<div class="admin-content">
    <?php if ($msg): ?>
      <div class="alert alert--<?= $msg_type ?>" data-auto-dismiss><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-card__icon ic-verde">👑</div>
        <div>
          <div class="stat-card__num"><?= $total_regalianos ?></div>
          <div class="stat-card__label">Regalianos totales</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-card__icon ic-verde">✅</div>
        <div>
          <div class="stat-card__num"><?= $activos ?></div>
          <div class="stat-card__label">Cuentas activas</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-card__icon ic-warm">💰</div>
        <div>
          <div class="stat-card__num">$<?= number_format($saldo_total, 2) ?></div>
          <div class="stat-card__label">Saldo retenido</div>
        </div>
      </div>
    </div>

    <div class="table-wrap">
      <div class="table-toolbar">
        <span class="table-toolbar__title">Directorio</span>
        <div style="display:flex;gap:0.75rem;align-items:center">
          <button class="btn btn--primary" type="button" onclick="abrirModalNuevo()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Nuevo Regaliano
          </button>
        </div>
      </div>
      <div style="overflow-x:auto">
        <table>
          <thead>
            <tr>
              <th>Cliente</th>
              <th>Contacto</th>
              <th>Nivel</th>
              <th>Saldo</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($regalianos as $r): ?>
            <tr>
              <td>
                <div class="td-nombre"><?= htmlspecialchars($r['nombre_completo']) ?></div>
                <div class="td-desc" style="color: var(--gold);">"<?= htmlspecialchars($r['alias']) ?>"</div>
              </td>
              <td>
                <div class="td-desc">📞 <?= htmlspecialchars($r['whatsapp'] ?? 'N/D') ?></div>
                <div class="td-desc">✉️ <?= htmlspecialchars($r['correo'] ?? 'N/D') ?></div>
              </td>
              <td>
                <strong><?= htmlspecialchars($r['nivel']) ?></strong><br>
                <small><?= $r['puntos'] ?> pts</small>
              </td>
              <td><span class="td-precio">$<?= number_format($r['saldo'], 2) ?></span></td>
              <td>
                <?php if ($r['estado']): ?>
                  <span class="badge badge--on">● Activo</span>
                <?php else: ?>
                  <span class="badge badge--off">● Pausado</span>
                <?php endif; ?>
              </td>
              <td>
                <div class="td-actions">
                  <button type="button" class="btn btn--outline btn--sm" onclick="mostrarQR('<?= $r['qr_token'] ?>', '<?= htmlspecialchars($r['alias']) ?>')">
                    📷 Ver QR
                  </button>
                  <a href="regalianos.php?editar=<?= $r['id'] ?>" class="btn btn--outline btn--sm">
                    ✏️ Editar
                  </a>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php if (!$regalianos): ?>
              <tr><td colspan="6" style="text-align:center;padding:3rem;color:#8d7b72">No hay Regalianos registrados aún.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
</div>

<div class="modal-backdrop <?= $editando ? 'abierto' : '' ?>" id="modalFormulario">
    <div class="modal">
        <div class="modal__header">
            <h2 class="modal__title"><?= $editando ? 'Editar Regaliano' : 'Nuevo Regaliano' ?></h2>
            <button class="btn-close" type="button" onclick="cerrarModalFormulario()">✕</button>
        </div>
        <div class="modal__body">
            <form method="POST" action="actions/guardar_regaliano.php">
                <input type="hidden" name="action" value="<?= $editando ? 'editar' : 'crear' ?>">
                <?php if ($editando): ?>
                    <input type="hidden" name="id" value="<?= $editando['id'] ?>">
                <?php endif; ?>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="nombre_completo">Nombre Completo *</label>
                        <input type="text" class="form-control" id="nombre_completo" name="nombre_completo" required value="<?= htmlspecialchars($editando['nombre_completo'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="alias">Alias (Para el vaso) *</label>
                        <input type="text" class="form-control" id="alias" name="alias" required value="<?= htmlspecialchars($editando['alias'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="correo">Correo Electrónico</label>
                        <input type="email" class="form-control" id="correo" name="correo" value="<?= htmlspecialchars($editando['correo'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="whatsapp">WhatsApp</label>
                        <input type="text" class="form-control" id="whatsapp" name="whatsapp" value="<?= htmlspecialchars($editando['whatsapp'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="nivel">Nivel</label>
                        <select class="form-control" id="nivel" name="nivel">
                            <option value="Bronce" <?= ($editando && $editando['nivel'] == 'Bronce') ? 'selected' : '' ?>>Bronce</option>
                            <option value="Plata" <?= ($editando && $editando['nivel'] == 'Plata') ? 'selected' : '' ?>>Plata</option>
                            <option value="Oro" <?= ($editando && $editando['nivel'] == 'Oro') ? 'selected' : '' ?>>Oro</option>
                            <option value="Diamante" <?= ($editando && $editando['nivel'] == 'Diamante') ? 'selected' : '' ?>>Diamante</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="saldo">Saldo (MXN)</label>
                        <input type="number" class="form-control" id="saldo" name="saldo" step="0.50" min="0" value="<?= $editando['saldo'] ?? '0.00' ?>">
                    </div>
                </div>

                <div class="modal__footer" style="margin-top: 1rem; border:none; padding:0;">
                    <button type="button" class="btn btn--outline" onclick="cerrarModalFormulario()">Cancelar</button>
                    <button type="submit" class="btn btn--primary">
                        <?= $editando ? 'Guardar cambios' : 'Crear Regaliano' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal-backdrop" id="qrModal">
    <div class="modal" style="max-width: 380px;">
        <div class="modal__header">
            <h2 class="modal__title" id="qrName"></h2>
            <button class="btn-close" type="button" onclick="document.getElementById('qrModal').classList.remove('abierto')">✕</button>
        </div>
        <div class="modal__body" style="display:flex; flex-direction:column; align-items:center; padding: 2rem;">
            <div id="qrcode" style="margin-bottom: 1.5rem; padding: 1rem; background: #fff; border-radius: 10px; display:inline-block;"></div>
            <p class="form-hint" style="font-weight: 600; font-size: 0.9rem;">Escanea este código en el Kiosko</p>
        </div>
        <div class="modal__footer">
            <button onclick="document.getElementById('qrModal').classList.remove('abierto')" class="btn btn--outline" style="width: 100%; justify-content: center;">Cerrar</button>
        </div>
    </div>
</div>

<script>
function mostrarQR(token, alias) {
    document.getElementById('qrName').innerText = "Acceso: " + alias;
    document.getElementById('qrcode').innerHTML = ""; 
    
    new QRCode(document.getElementById("qrcode"), {
        text: token,
        width: 200,
        height: 200,
        colorDark : "#000000",
        colorLight : "#ffffff",
        correctLevel : QRCode.CorrectLevel.H
    });
    
    document.getElementById('qrModal').classList.add('abierto');
}

function abrirModalNuevo() {
    // Si la URL actual está en modo edición, redireccionamos para limpiar y abrir nuevo
    if (window.location.search.includes('editar')) {
        window.location.href = 'regalianos.php';
    } else {
        document.getElementById('modalFormulario').classList.add('abierto');
    }
}

function cerrarModalFormulario() {
    // Si estábamos editando, limpiamos la URL, si no, solo ocultamos el modal
    if (window.location.search.includes('editar')) {
        window.location.href = 'regalianos.php';
    } else {
        document.getElementById('modalFormulario').classList.remove('abierto');
    }
}
</script>

<?php require __DIR__ . '/partials/footer.php'; ?>