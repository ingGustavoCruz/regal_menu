-- ============================================================
-- RÉGAL Coffee + Lounge — Esquema de Base de Datos
-- Motor: MySQL 5.7+ / MariaDB 10.3+
-- ============================================================

CREATE DATABASE IF NOT EXISTS regal_menu_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE regal_menu_db;

-- ------------------------------------------------------------
-- Tabla: categorias
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS categorias (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre      VARCHAR(80)  NOT NULL,
  slug        VARCHAR(80)  NOT NULL UNIQUE,
  descripcion VARCHAR(255) DEFAULT NULL,
  orden       TINYINT UNSIGNED DEFAULT 0,
  activo      TINYINT(1)   NOT NULL DEFAULT 1,
  created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabla: platillos
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS platillos (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  categoria_id    INT UNSIGNED NOT NULL,
  nombre          VARCHAR(120) NOT NULL,
  descripcion     TEXT         DEFAULT NULL,
  precio          DECIMAL(8,2) NOT NULL,
  imagen          VARCHAR(255) DEFAULT NULL,   -- ruta relativa desde /assets/images/uploads/
  disponible      TINYINT(1)   NOT NULL DEFAULT 1,  -- 0 = pausado, 1 = visible
  destacado       TINYINT(1)   NOT NULL DEFAULT 0,
  orden           SMALLINT UNSIGNED DEFAULT 0,
  created_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_platillo_categoria
    FOREIGN KEY (categoria_id) REFERENCES categorias(id)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabla: admins  (sesiones simples para el panel)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS admins (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario    VARCHAR(60)  NOT NULL UNIQUE,
  password   VARCHAR(255) NOT NULL,   -- bcrypt hash
  nombre     VARCHAR(120) DEFAULT NULL,
  created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Datos de ejemplo
-- ------------------------------------------------------------
INSERT INTO categorias (nombre, slug, orden) VALUES
  ('Bebidas Calientes',  'bebidas-calientes',  1),
  ('Bebidas Frías',      'bebidas-frias',       2),
  ('Desayunos',          'desayunos',           3),
  ('Pasteles & Postres', 'pasteles-postres',    4),
  ('Snacks',             'snacks',              5);

INSERT INTO platillos (categoria_id, nombre, descripcion, precio, disponible, destacado) VALUES
  (1, 'Espresso Clásico',    'Extracción perfecta de nuestro blend artesanal, intenso y equilibrado.',   55.00, 1, 0),
  (1, 'Latte de Temporada',  'Espresso con leche vaporizada y nuestro sirope especial de la semana.',    75.00, 1, 1),
  (1, 'Americano',           'Espresso con agua caliente. Limpio, directo, sin pretensiones.',            60.00, 1, 0),
  (1, 'Capuchino',           'Proporción clásica de espresso, leche y espuma cremosa.',                  70.00, 1, 0),
  (2, 'Cold Brew',           '18 horas de extracción en frío. Suave, oscuro y sin acidez.',              80.00, 1, 1),
  (2, 'Frappé Caramelo',     'Cold brew, leche, caramelo y crema batida. El favorito de la casa.',       90.00, 1, 0),
  (2, 'Matcha Latte Frío',   'Matcha ceremonial japonés con leche de avena. Suave y reconfortante.',     85.00, 1, 0),
  (3, 'Tostada Régal',       'Pan artesanal tostado, aguacate, jitomate cherry y aceite de oliva.',     115.00, 1, 1),
  (3, 'Huevos Benedictinos', 'Muffin inglés, jamón de pavo, huevos pochados y salsa holandesa.',        155.00, 1, 0),
  (3, 'Bowl de Granola',     'Granola artesanal, yogur griego, frutos rojos frescos y miel de abeja.',  110.00, 1, 0),
  (4, 'Croissant de Mantequilla', 'Elaborado con mantequilla francesa, hojaldrado y crujiente.',         65.00, 1, 0),
  (4, 'Tarta de Limón',      'Base de pasta sablée, crema de limón y merengue italiano.',               85.00, 1, 1),
  (4, 'Brownie Intenso',     'Chocolate 70%, nueces tostadas. Servido tibio con helado de vainilla.',   95.00, 1, 0),
  (5, 'Crostini de Queso',   'Baguette tostado, queso brie, nuez y reducción de miel.',                 85.00, 1, 0),
  (5, 'Hummus & Pita',       'Hummus casero con aceite de oliva y paprika ahumada.',                    80.00, 0, 0);

-- Admin por defecto: usuario=admin, password=Regal2025
-- (hash bcrypt de "Regal2025")
INSERT INTO admins (usuario, password, nombre) VALUES
  ('admin', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador Régal');
