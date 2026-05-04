<!-- ── MODAL EDITAR PLATILLO ─────────────────────────────── -->
<div class="modal-backdrop" id="modalEditar">
  <div class="modal">
    <div class="modal__header">
      <h2 class="modal__title">Editar platillo</h2>
      <button class="btn-close" data-modal-close aria-label="Cerrar">&times;</button>
    </div>
    <form method="POST" action="<?= BASE_URL ?>/admin/actions/guardar_platillo.php" enctype="multipart/form-data">
      <input type="hidden" name="modo" value="editar">
      <input type="hidden" name="id" id="e_id">
      <div class="modal__body">

        <div class="form-row">
          <div class="form-group">
            <label class="form-label" for="e_nombre">Nombre *</label>
            <input class="form-control" type="text" id="e_nombre" name="nombre" required maxlength="120">
          </div>
          <div class="form-group">
            <label class="form-label" for="e_precio">Precio (MXN) *</label>
            <input class="form-control" type="number" id="e_precio" name="precio" required min="0" step="0.01">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="e_categoria">Categoría *</label>
          <select class="form-control" id="e_categoria" name="categoria_id" required>
            <option value="">— Selecciona —</option>
            <?php foreach ($categorias as $c): ?>
              <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label" for="e_desc">Descripción</label>
          <textarea class="form-control" id="e_desc" name="descripcion" maxlength="400" rows="3"></textarea>
        </div>

        <div class="form-group">
          <label class="form-label">Foto del platillo</label>
          <div class="upload-area" data-preview="editPreview">
            <input type="file" name="imagen" accept="image/jpeg,image/png,image/webp">
            <div class="upload-area__icon">📷</div>
            <div class="upload-area__text">Reemplazar imagen<br><small>Deja vacío para conservar la actual</small></div>
          </div>
          <div class="upload-preview" id="editPreview" style="display:none"></div>
        </div>

        <div style="display:flex;gap:2rem;flex-wrap:wrap">
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label">Estado</label>
            <label class="toggle-wrap">
              <label class="toggle">
                <input type="checkbox" id="e_disponible" name="disponible" value="1">
                <span class="toggle__slider"></span>
              </label>
              Visible en el menú
            </label>
          </div>
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label">Destacado</label>
            <label class="toggle-wrap">
              <label class="toggle">
                <input type="checkbox" id="e_destacado" name="destacado" value="1">
                <span class="toggle__slider"></span>
              </label>
              Marcar como destacado
            </label>
          </div>
        </div>

      </div>
      <div class="modal__footer">
        <button class="btn btn--outline" type="button" data-modal-close>Cancelar</button>
        <button class="btn btn--primary" type="submit">Guardar cambios</button>
      </div>
    </form>
  </div>
</div>

<script>
// Sobrescribimos abrirEditar para manejar los IDs de edición
function abrirEditar(data) {
  const modal = document.getElementById('modalEditar');
  if (!modal) return;

  modal.querySelector('[name="id"]').value        = data.id;
  modal.querySelector('[name="nombre"]').value    = data.nombre;
  modal.querySelector('[name="descripcion"]').value = data.descripcion || '';
  modal.querySelector('[name="precio"]').value    = data.precio;
  modal.querySelector('[name="categoria_id"]').value = data.categoria_id;

  const dispChk = modal.querySelector('[name="disponible"]');
  const destChk = modal.querySelector('[name="destacado"]');
  dispChk.checked = parseInt(data.disponible) === 1;
  destChk.checked = parseInt(data.destacado)  === 1;

  const prevWrap = document.getElementById('editPreview');
  if (prevWrap) {
    if (data.imagen_url) {
      prevWrap.innerHTML = `<img src="${data.imagen_url}" alt="Imagen actual" style="max-height:120px;border-radius:8px;">`;
      prevWrap.style.display = 'inline-block';
    } else {
      prevWrap.innerHTML = '';
      prevWrap.style.display = 'none';
    }
  }
  modal.classList.add('abierto');
}
</script>
