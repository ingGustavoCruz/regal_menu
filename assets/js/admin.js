// RÉGAL Admin — JS principal
document.addEventListener('DOMContentLoaded', () => {

  // ── Sidebar mobile ──────────────────────────────────────
  const toggle = document.getElementById('sidebarToggle');
  const sidebar = document.getElementById('sidebar');
  if (toggle && sidebar) {
    toggle.addEventListener('click', () => sidebar.classList.toggle('open'));
    document.addEventListener('click', e => {
      if (!sidebar.contains(e.target) && !toggle.contains(e.target)) {
        sidebar.classList.remove('open');
      }
    });
  }

  // ── Modales ─────────────────────────────────────────────
  document.querySelectorAll('[data-modal-open]').forEach(btn => {
    btn.addEventListener('click', () => {
      const id = btn.dataset.modalOpen;
      document.getElementById(id)?.classList.add('abierto');
    });
  });

  document.querySelectorAll('[data-modal-close], .modal-backdrop').forEach(el => {
    el.addEventListener('click', function(e) {
      if (e.target === this || this.dataset.modalClose !== undefined) {
        document.querySelectorAll('.modal-backdrop').forEach(m => m.classList.remove('abierto'));
      }
    });
  });

  // Evita que el click dentro del modal cierre el backdrop
  document.querySelectorAll('.modal').forEach(m => {
    m.addEventListener('click', e => e.stopPropagation());
  });

  // ── Preview imagen upload ────────────────────────────────
  document.querySelectorAll('.upload-area input[type=file]').forEach(input => {
    input.addEventListener('change', function() {
      const file = this.files[0];
      if (!file) return;
      const reader = new FileReader();
      const previewId = this.closest('.upload-area')?.dataset.preview;
      reader.onload = e => {
        if (previewId) {
          const wrap = document.getElementById(previewId);
          if (wrap) {
            wrap.innerHTML = `<img src="${e.target.result}" alt="Preview">
              <button type="button" class="upload-preview__remove" onclick="clearPreview('${previewId}',this)">×</button>`;
            wrap.style.display = 'inline-block';
          }
        }
      };
      reader.readAsDataURL(file);
    });
  });

  // Drag & drop
  document.querySelectorAll('.upload-area').forEach(area => {
    area.addEventListener('dragover', e => { e.preventDefault(); area.classList.add('drag'); });
    area.addEventListener('dragleave', () => area.classList.remove('drag'));
    area.addEventListener('drop', e => { e.preventDefault(); area.classList.remove('drag'); });
  });

  // ── Búsqueda en tabla ────────────────────────────────────
  const searchInput = document.getElementById('tablaSearch');
  if (searchInput) {
    searchInput.addEventListener('input', () => {
      const q = searchInput.value.toLowerCase();
      document.querySelectorAll('tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
      });
    });
  }

  // ── Filtro de categoría en tabla (CON MEMORIA) ───────────
  const catFilter = document.getElementById('catFilter');
  if (catFilter) {
    // Función centralizada para aplicar el filtro visualmente
    const applyFilter = (val) => {
      document.querySelectorAll('tbody tr[data-cat]').forEach(row => {
        row.style.display = (!val || row.dataset.cat === val) ? '' : 'none';
      });
    };

    // 1. Al cargar la página, revisar si hay un filtro guardado
    const savedFilter = localStorage.getItem('admin_cat_filter');
    if (savedFilter) {
      catFilter.value = savedFilter;
      applyFilter(savedFilter);
    }

    // 2. Escuchar cambios, guardarlos en memoria y aplicar
    catFilter.addEventListener('change', () => {
      const val = catFilter.value;
      localStorage.setItem('admin_cat_filter', val);
      applyFilter(val);
    });
  }

  // ── Auto-dismiss alerts ──────────────────────────────────
  document.querySelectorAll('.alert[data-auto-dismiss]').forEach(alert => {
    setTimeout(() => alert.remove(), 4000);
  });

});

// ── Abrir modal de edición con datos ──────────────────────
function abrirEditar(data) {
  const modal = document.getElementById('modalEditar');
  if (!modal) return;
  Object.entries(data).forEach(([k, v]) => {
    const el = modal.querySelector(`[name="${k}"]`);
    if (!el) return;
    if (el.type === 'checkbox') { el.checked = !!parseInt(v); }
    else { el.value = v; }
  });
  // Preview imagen existente
  const prevWrap = document.getElementById('editPreview');
  if (prevWrap && data.imagen) {
    prevWrap.innerHTML = `<img src="${data.imagen_url}" alt="">`;
    prevWrap.style.display = 'inline-block';
  }
  modal.classList.add('abierto');
}

function clearPreview(id, btn) {
  const wrap = document.getElementById(id);
  if (wrap) { wrap.innerHTML = ''; wrap.style.display = 'none'; }
  // Limpiar input file
  const area = wrap?.closest?.('.form-group')?.querySelector('input[type=file]');
  if (area) area.value = '';
}

function confirmarEliminar(form) {
  if (confirm('¿Eliminar este platillo? Esta acción no se puede deshacer.')) form.submit();
}