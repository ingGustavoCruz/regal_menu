-- ============================================================
--  RÉGAL Coffee + Lounge — Base de Datos
--  Motor: MySQL 5.7+ / MariaDB 10+
-- ============================================================

CREATE DATABASE IF NOT EXISTS regal_menu_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE regal_menu_db;

CREATE TABLE IF NOT EXISTS categorias (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre      VARCHAR(80)  NOT NULL,
  descripcion VARCHAR(255) DEFAULT NULL,
  icono       VARCHAR(60)  DEFAULT NULL,
  orden       TINYINT UNSIGNED DEFAULT 0,
  activa      TINYINT(1)   NOT NULL DEFAULT 1,
  created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS platillos (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  categoria_id  INT UNSIGNED NOT NULL,
  nombre        VARCHAR(120) NOT NULL,
  descripcion   TEXT         DEFAULT NULL,
  precio        DECIMAL(8,2) NOT NULL,
  imagen        VARCHAR(255) DEFAULT NULL,
  destacado     TINYINT(1)   NOT NULL DEFAULT 0,
  activo        TINYINT(1)   NOT NULL DEFAULT 1,
  orden         SMALLINT UNSIGNED DEFAULT 0,
  created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_platillo_categoria
    FOREIGN KEY (categoria_id) REFERENCES categorias(id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  INDEX idx_categoria (categoria_id),
  INDEX idx_activo    (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS admin_users (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username   VARCHAR(60)  NOT NULL UNIQUE,
  password   VARCHAR(255) NOT NULL,
  nombre     VARCHAR(120) DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO categorias (nombre, descripcion, icono, orden) VALUES
  ('Bebidas Calientes', 'Cafés, tés y más',           '☕', 1),
  ('Bebidas Frías',     'Frappes, smoothies y aguas', '🧊', 2),
  ('Desayunos',         'Para comenzar el día',       '🍳', 3),
  ('Postres',           'Algo dulce para terminar',   '🍰', 4),
  ('Snacks',            'Bocadillos ligeros',          '🥐', 5);

INSERT INTO platillos (categoria_id, nombre, descripcion, precio, destacado) VALUES
  (1, 'Café Americano',      'Espresso doble con agua caliente, intenso y limpio.',           55.00, 0),
  (1, 'Latte de Vainilla',   'Espresso con leche vaporizada y toque de vainilla artesanal.',  75.00, 1),
  (1, 'Cappuccino',          'Espresso, leche y espuma en proporciones perfectas.',            70.00, 0),
  (1, 'Matcha Latte',        'Té matcha ceremonial con leche de avena.',                       80.00, 1),
  (2, 'Frappé de Caramelo',  'Base de espresso con caramelo, hielo y crema batida.',           95.00, 1),
  (2, 'Cold Brew',           '20 horas de extracción en frío, suave y concentrado.',           85.00, 0),
  (2, 'Agua de Jamaica',     'Tradicional, sin azúcar añadida, refrescante.',                  45.00, 0),
  (3, 'Avocado Toast',       'Pan artesanal, aguacate, jitomate cherry y semillas.',          110.00, 1),
  (3, 'Granola Bowl',        'Granola casera, fruta de temporada y yogurt griego.',            95.00, 0),
  (3, 'Omelette Régal',      'Huevo, queso manchego, espinacas y jitomate deshidratado.',    125.00, 0),
  (4, 'Cheesecake NY',       'Clásico horneado con base de galleta y frutos rojos.',           90.00, 1),
  (4, 'Brownie Caliente',    'Brownie de chocolate intenso con helado de vainilla.',            85.00, 0),
  (5, 'Croissant Mantequilla','Hojaldrado, dorado y crujiente. Elaboración propia.',           55.00, 0),
  (5, 'Muffin de Arándano',  'Muffin esponjoso con arándanos frescos y crumble.',              60.00, 0);

-- Password: regal2024 (bcrypt hash — cámbialo después de instalar)
INSERT INTO admin_users (username, password, nombre) VALUES
  ('admin', '$2y$12$Q8pPb4v6YxKpL2oNw3fQmeeZ8K3vVjQJ9F7cGhNrBBo2I5yZCDASq', 'Administrador Régal');
