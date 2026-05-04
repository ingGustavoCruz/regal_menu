// RÉGAL — Lógica del menú público
document.addEventListener('DOMContentLoaded', () => {

  const btns = document.querySelectorAll('.filtros__btn');
  const bloques = document.querySelectorAll('.categoria-bloque');

  btns.forEach(btn => {
    btn.addEventListener('click', () => {
      btns.forEach(b => b.classList.remove('activo'));
      btn.classList.add('activo');

      const cat = btn.dataset.cat;

      bloques.forEach(bloque => {
        if (cat === 'todos' || bloque.dataset.cat === cat) {
          bloque.classList.remove('oculto');
        } else {
          bloque.classList.add('oculto');
        }
      });

      // Scroll suave al primer bloque visible
      const visible = document.querySelector('.categoria-bloque:not(.oculto)');
      if (visible) {
        const offset = 70 + 48; // header + filtros
        const top = visible.getBoundingClientRect().top + window.scrollY - offset - 16;
        window.scrollTo({ top, behavior: 'smooth' });
      }
    });
  });

});
