# Mi sitio personal administrable

Portafolio personal y blog básico hecho con **PHP, MySQL, HTML, CSS y JavaScript**. El contenido público se administra desde `/admin/`. El diseño usa azul hielo, tarjetas de vidrio y una navegación inspirada en iPhone y macOS.

## Requisitos

- PHP 8.1 o superior con `pdo_mysql`, `fileinfo` y sesiones habilitadas.
- MySQL 8.0 o MariaDB reciente.
- Un servidor web con PHP (Apache, XAMPP, Laragon o el servidor integrado de PHP).

No hay bibliotecas de terceros que descargar ni instalar en MySQL. PDO y Fileinfo son extensiones de PHP; `database/schema.sql` es el archivo que se importa en MySQL.

## Instalación local

1. Importa [`database/schema.sql`](database/schema.sql) en SQLyog, MySQL Workbench o desde consola. El script crea la base **`tarea#1`** y sus tablas. En SQLyog, abre el archivo SQL y ejecútalo con la conexión `root@localhost` de la captura.
2. Copia `.env.example` a `.env` y configura usuario y contraseña de MySQL. El archivo `.env` ya puede estar creado en este entorno; Git lo ignora.
3. Inicia el servidor en la carpeta del proyecto: `php -S localhost:8000 server.php` (en este equipo también puedes usar `C:\xampp\php\php.exe -S localhost:8000 server.php`), o copia el proyecto a `htdocs` de XAMPP.
4. Visita `http://localhost:8000/admin/setup.php` y crea la contraseña del administrador. Este paso solo funciona cuando todavía no existe un administrador.
5. Entra en `/admin/login.php` para completar perfil, atestados, galería y publicaciones.

Si usas Apache, activa `mod_rewrite` para que las reglas de `.htaccess` protejan archivos privados. Para despliegues con otro servidor, configura reglas equivalentes para impedir acceso web a `.env`, `.git/`, `database/`, `includes/`, `docs/` y `storage/` salvo `storage/uploads/`.

## Uso

- **Inicio y acerca de mí:** el panel permite editar nombre, presentación, biografía, intereses, habilidades, experiencia y fotografía.
- **Atestados:** crear, editar y eliminar título, institución, año, descripción e imagen.
- **Galería:** subir fotos con título y descripción; eliminar las que ya no se quieran mostrar.
- **Blog:** publicar, editar, despublicar y eliminar entradas.
- **Contacto:** editar correo, teléfono y enlaces de redes sociales desde el perfil.

Las imágenes se guardan en `storage/uploads/`; las demás secciones se guardan en MySQL. La carga acepta JPEG, PNG y WebP hasta 5 MB.

## Estructura

| Ruta | Propósito |
| --- | --- |
| `home.php`, `post.php` | Sitio público y lectura de publicaciones |
| `admin/` | Instalación inicial, acceso y gestión de contenido |
| `includes/` | Configuración, conexión, autenticación y utilidades |
| `assets/` | Estilos e interacción visual |
| `database/schema.sql` | Base de datos `tarea#1` |
| `storage/uploads/` | Imágenes subidas |
| `docs/PROYECTO.md` | Explicación funcional y técnica |
| `server.php` | Enrutador y protección para el servidor integrado de PHP |

## Antes de publicar en GitHub

El sitio incluye textos provisionales editables. Para completarlo con datos reales se necesitan nombre, fotografías, biografía, intereses, habilidades, experiencia, contacto y detalles de cada atestado. **No publiques `.env` ni credenciales.** En GitHub Pages no funcionará porque requiere PHP y MySQL; el repositorio público sirve para entregar el código, mientras la aplicación necesita un alojamiento compatible.

## Comprobaciones rápidas

Después de importar el SQL, prueba el inicio, la creación de administrador, el acceso, un cambio de perfil, una subida de imagen, una publicación y su visualización pública. Si aparece un error de conexión, comprueba `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASSWORD` y la extensión `pdo_mysql`.
