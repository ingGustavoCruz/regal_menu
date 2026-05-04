# RÉGAL Coffee + Lounge — Sitio Web

# RÉGAL Coffee + Lounge — Menú Digital

![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?style=flat&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-Database-4479A1?style=flat&logo=mysql&logoColor=white)
![HTML5](https://img.shields.io/badge/HTML5-Markup-E34F26?style=flat&logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-Style-1572B6?style=flat&logo=css3&logoColor=white)

Plataforma web desarrollada a la medida para RÉGAL Coffee + Lounge. Consiste en dos módulos principales:
1. **Menú Público:** Interfaz responsiva y minimalista con efecto de degradado metálico, filtros interactivos y navegación *sticky*.
2. **Panel Administrativo (Backoffice):** Sistema CMS seguro para crear, editar, ocultar y eliminar categorías y platillos, incluyendo gestión de imágenes y credenciales encriptadas.

## Características Principales

* **Front-end:**
    * Diseño "Mobile-First" altamente responsivo.
    * Filtrado asíncrono de categorías en el menú público.
    * Efectos visuales avanzados mediante CSS puro (`mix-blend-mode`, degradados, `position: sticky`).
    * Variables CSS para fácil personalización del tema (Dark Mode/Lounge).
* **Back-end:**
    * Arquitectura estructurada (separación de lógica, configuración y vistas).
    * Conexión a BD mediante PDO y Consultas Preparadas (mitigación SQL Injection).
    * Autenticación segura con encriptación de contraseñas (`PASSWORD_BCRYPT`).
    * Carga y gestión de imágenes optimizada.
    
## Instalación rápida en XAMPP/WAMP/Laragon

### 1. Copiar el proyecto

Copia la carpeta `regal/` dentro de `htdocs/` (XAMPP) o `www/` (WAMP/Laragon):

```
C:\xampp\htdocs\regal\
```

### 2. Crear la base de datos

1. Abre **phpMyAdmin** → `http://localhost/phpmyadmin`
2. Crea una base de datos llamada `regal_menu_db` (collation: `utf8mb4_unicode_ci`)
3. Importa el archivo `database/schema.sql`

### 3. Configurar la conexión

Abre `includes/config.php` y ajusta si es necesario:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'regal_menu_db');
define('DB_USER', 'root');
define('DB_PASS', '');           // En XAMPP por defecto está vacía
define('BASE_URL', 'http://localhost/regal');
```

### 4. Permisos de uploads

La carpeta `assets/images/uploads/` ya existe. En Windows no hace falta nada.
En Linux/Mac: `chmod 755 assets/images/uploads/`

### 5. Acceder al sitio

| URL                             | Descripción             |
| ------------------------------- | ----------------------- |
| `http://localhost/regal/`       | Menú público            |
| `http://localhost/regal/admin/` | Panel de administración |

### Credenciales por defecto

| Campo      | Valor       |
| ---------- | ----------- |
| Usuario    | `admin`     |
| Contraseña | `Regal2025` |

> ⚠️ **Cambia la contraseña** en producción via phpMyAdmin ejecutando:
>
> ```sql
> UPDATE admins SET password = '$2y$12$TU_NUEVO_HASH' WHERE usuario = 'admin';
> ```
>
> Genera el hash con: `php -r "echo password_hash('TuNuevaContra', PASSWORD_BCRYPT);"`

---

## Estructura de carpetas

```
regal/
├── assets/
│   ├── css/
│   │   ├── style.css          # Estilos menú público
│   │   └── admin.css          # Estilos panel admin
│   ├── js/
│   │   ├── menu.js            # Filtros menú público
│   │   └── admin.js           # Lógica panel admin
│   └── images/
│       ├── logo-blanco.png
│       ├── logo-negro.png
│       ├── placeholder.svg
│       └── uploads/           # Fotos de platillos (generado por el sistema)
├── admin/
│   ├── index.php              # Dashboard + tabla de platillos
│   ├── categorias.php         # Gestión de categorías
│   ├── login.php              # Login
│   ├── logout.php
│   ├── partials/
│   │   ├── header.php
│   │   ├── footer.php
│   │   ├── modal_nuevo.php
│   │   └── modal_editar.php
│   └── actions/
│       ├── guardar_platillo.php
│       ├── toggle_platillo.php
│       └── delete_platillo.php
├── database/
│   └── schema.sql             # Script SQL completo con datos de ejemplo
├── includes/
│   ├── config.php             # Configuración global
│   ├── db.php                 # Singleton PDO
│   ├── auth.php               # Autenticación admin
│   └── upload.php             # Helper de imágenes
├── index.php                  # Menú público
├── .htaccess                  # Seguridad Apache
└── README.md
```
