<div class="modal-backdrop" id="modalNuevo">
  <div class="modal">
    <div class="modal__header">
      <h2 class="modal__title">Nuevo platillo</h2>
      <button class="btn-close" data-modal-close aria-label="Cerrar">&times;</button>
    </div>
    <form method="POST" action="<?= BASE_URL ?>/admin/actions/guardar_platillo.php" enctype="multipart/form-data">
      <input type="hidden" name="modo" value="nuevo">
      <div class="modal__body">

        <div class="form-row">
          <div class="form-group">
            <label class="form-label" for="n_nombre">Nombre *</label>
            <input class="form-control" type="text" id="n_nombre" name="nombre" required maxlength="120">
          </div>
          <div class="form-group">
            <label class="form-label" for="n_precio">Precio (MXN) *</label>
            <input class="form-control" type="number" id="n_precio" name="precio" required min="0" step="0.01">
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label" for="n_seccion">Sección *</label>
            <select class="form-control" id="n_seccion" name="seccion" required>
              <option value="c">Cafetería</option>
              <option value="ch">Changarrito</option>
              <option value="co">Comedor Institucional</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label" for="n_dia_semana">Día (Solo Comedor) *</label>
            <select class="form-control" id="n_dia_semana" name="dia_semana" required>
              <option value="Todos">Todos los días</option>
              <option value="Lunes">Lunes</option>
              <option value="Martes">Martes</option>
              <option value="Miercoles">Miércoles</option>
              <option value="Jueves">Jueves</option>
              <option value="Viernes">Viernes</option>
            </select>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="n_categoria">Categoría *</label>
          <select class="form-control" id="n_categoria" name="categoria_id" required>
            <option value="">— Selecciona —</option>
            <?php foreach ($categorias as $c): ?>
              <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label" for="n_desc">Descripción</label>
          <textarea class="form-control" id="n_desc" name="descripcion" maxlength="400" rows="3"></textarea>
        </div>

        <div class="form-group">
          <label class="form-label">Foto del platillo</label>
          <div class="upload-area" data-preview="nuevoPrev">
            <input type="file" name="imagen" accept="image/jpeg,image/png,image/webp">
            <div class="upload-area__icon">📷</div>
            <div class="upload-area__text">Haz clic o arrastra una imagen aquí<br><small>JPG, PNG o WEBP · máx 3 MB</small></div>
          </div>
          <div class="upload-preview" id="nuevoPrev" style="display:none"></div>
        </div>

        <div style="display:flex;gap:2rem;flex-wrap:wrap">
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label">Estado</label>
            <label class="toggle-wrap">
              <label class="toggle">
                <input type="checkbox" name="disponible" value="1" checked>
                <span class="toggle__slider"></span>
              </label>
              Visible en el menú
            </label>
          </div>
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label">Destacado</label>
            <label class="toggle-wrap">
              <label class="toggle">
                <input type="checkbox" name="destacado" value="1">
                <span class="toggle__slider"></span>
              </label>
              Marcar como destacado
            </label>
          </div>
        </div>

      </div>
      <div class="modal__footer">
        <button class="btn btn--outline" type="button" data-modal-close>Cancelar</button>
        <button class="btn btn--primary" type="submit">Guardar platillo</button>
      </div>
    </form>
  </div>
</div>