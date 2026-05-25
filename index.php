<?php
// ============================================================
// RÉGAL — Menú público
// ============================================================
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/upload.php';

$pdo = DB::get();

// Categorías activas que tienen al menos 1 platillo disponible
$cats = $pdo->query(
  "SELECT c.* FROM categorias c
   INNER JOIN platillos p ON p.categoria_id = c.id AND p.disponible = 1
   WHERE c.activo = 1 AND c.seccion = 'c'
   GROUP BY c.id
   ORDER BY c.orden, c.id"
)->fetchAll();

// Platillos disponibles indexados por categoría
$platillos = [];
if ($cats) {
  $ids   = implode(',', array_column($cats, 'id'));
  $rows  = $pdo->query(
    "SELECT * FROM platillos WHERE disponible = 1 AND categoria_id IN ($ids) AND seccion = 'c' ORDER BY orden, id" 
  )->fetchAll();
  foreach ($rows as $p) {
    $platillos[$p['categoria_id']][] = $p;
  }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="theme-color" content="#000000">
  <title>Menú — RÉGAL Coffee + Lounge</title>
  <meta name="description" content="Descubre el menú de RÉGAL Coffee + Lounge. Bebidas, desayunos y postres de calidad.">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
  <link rel="icon" href="<?= BASE_URL ?>/assets/images/monito01.png" type="image/png">
</head>
<body>

<!-- ── NAVBAR ────────────────────────────────────────────── -->
<!-- <header class="navbar">
  <a class="navbar__logo" href="<?= BASE_URL ?>">
    <img src="<?= BASE_URL ?>/assets/images/regalDorado.png" alt="RÉGAL Coffee + Lounge">
  </a>
  
 <span class="navbar__tagline">Menú</span>
</header> -->

<!-- ── HERO ──────────────────────────────────────────────── -->
<section class="hero">
  <div class="hero__content-box"> <div class="hero__logo-tint">
      <img src="<?= BASE_URL ?>/assets/images/regalDorado.png" alt="Régal Coffee + Lounge" class="hero__logo">
    </div>
    
    <h1 class="hero__title">Nuestra Carta</h1>
    <p class="hero__desc">Ingredientes seleccionados, preparaciones cuidadosas y una experiencia hecha para disfrutarse.</p>
    <div class="hero__divider"></div>
  </div> </section>

<?php if ($cats): ?>

<!-- ── FILTROS ───────────────────────────────────────────── -->
<nav class="filtros" aria-label="Filtrar por categoría">
  <div class="filtros__inner">
    <button class="filtros__btn activo" data-cat="todos">Todos</button>
    <?php foreach ($cats as $c): ?>
      <button class="filtros__btn" data-cat="<?= $c['id'] ?>">
        <?= htmlspecialchars($c['nombre']) ?>
      </button>
    <?php endforeach; ?>
  </div>
</nav>

<!-- ── MENÚ ──────────────────────────────────────────────── -->
<main class="menu-section" id="menu">
  <?php foreach ($cats as $c): ?>
    <?php $plats = $platillos[$c['id']] ?? []; if (!$plats) continue; ?>

    <section class="categoria-bloque" data-cat="<?= $c['id'] ?>" id="cat-<?= $c['id'] ?>">
      <div class="categoria__header">
        <h2 class="categoria__nombre"><?= htmlspecialchars($c['nombre']) ?></h2>
        <div class="categoria__linea"></div>
      </div>

      <div class="platillos-grid">
        <?php foreach ($plats as $p): ?>
          <article class="card">

            <!-- Imagen -->
            <div class="card__img-wrap">
              <?php if ($p['imagen'] && file_exists(UPLOAD_DIR . $p['imagen'])): ?>
                <img class="card__img"
                     src="<?= imagen_url($p['imagen']) ?>"
                     alt="<?= htmlspecialchars($p['nombre']) ?>"
                     loading="lazy">
              <?php else: ?>
                <div class="card__img-placeholder">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round">
  <path d="M17 8h1a4 4 0 1 1 0 8h-1"></path>
  <path d="M3 8h14v9a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4Z"></path>
  <line x1="6" y1="2" x2="6" y2="4"></line>
  <line x1="10" y1="2" x2="10" y2="4"></line>
  <line x1="14" y1="2" x2="14" y2="4"></line>
</svg>
                </div>
              <?php endif; ?>

              <?php if ($p['destacado']): ?>
                <span class="card__badge">Destacado</span>
              <?php endif; ?>
            </div>

            <!-- Body -->
            <div class="card__body">
              <h3 class="card__nombre"><?= htmlspecialchars($p['nombre']) ?></h3>
              <?php if ($p['descripcion']): ?>
                <p class="card__desc"><?= htmlspecialchars($p['descripcion']) ?></p>
              <?php endif; ?>
              <div class="card__footer">
                <span class="card__precio"><?= number_format($p['precio'], 2) ?></span>
              </div>
            </div>

          </article>
        <?php endforeach; ?>
      </div>
    </section>

  <?php endforeach; ?>
</main>

<?php else: ?>
<main class="menu-section">
  <div class="empty-state">
    <div class="empty-state__icon">☕</div>
    <p class="empty-state__text">El menú está siendo actualizado. ¡Vuelve pronto!</p>
  </div>
</main>
<?php endif; ?>

<!-- ── FOOTER ─────────────────────────────────────────────── -->
<footer>
  <ul class="social-links">
    <li><a href="https://www.instagram.com/regal_coffeelounge" target="_blank" rel="noopener noreferrer">Instagram</a></li>
    <li><a href="https://www.facebook.com/regalcoffelounge" target="_blank" rel="noopener noreferrer">Facebook</a></li>
    <li><a href="https://wa.me/525540507317" target="_blank" rel="noopener noreferrer">WhatsApp</a></li>
    <li><a href="mailto:contacto@regal.mx">Contacto</a></li>
  </ul>
  <div class="copyright">© 2026 Regal. Todos los derechos reservados.</div>
  
  <div class="powered-by-container">
    <div class="powered-by-text">Powered By</div>
    <img src="<?= BASE_URL ?>/assets/images/KAI_NG.png" alt="KAI Experience" class="kai-logo">
  </div>
</footer>

<script src="<?= BASE_URL ?>/assets/js/menu.js"></script>
</body>
</html>
