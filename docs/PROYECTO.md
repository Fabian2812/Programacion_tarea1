# Explicación del proyecto

## Qué hace la aplicación

Este proyecto es un sitio personal con dos partes:

1. **Sitio público:** presenta nombre, foto, descripción breve y larga, intereses, habilidades, experiencia, atestados, galería, blog y contacto.
2. **Panel privado:** permite cambiar el perfil y crear, editar o eliminar atestados, fotografías y publicaciones. Las entradas del blog se pueden guardar como borrador o publicar.

El sitio público lee la información de MySQL cada vez que se visita. Por eso los cambios guardados en el panel aparecen sin editar el código.

## Diseño

La paleta usa `#A9CEE9` como azul hielo y `#1764a0` como acento. Las tarjetas tienen fondo blanco translúcido, borde claro, radio de 28 px, desenfoque y sombra suave, según el ejemplo entregado. La navegación superior recuerda una barra de macOS y la navegación inferior en móvil recuerda el dock de iPhone. Los títulos usan **Syne**, una tipografía geométrica poco convencional y limpia; el texto usa **DM Sans**. Ambas se cargan desde Google Fonts, con alternativas del sistema si no hay Internet.

## Base de datos

El archivo `database/schema.sql` crea la base **`tarea#1`** (el símbolo `#` obliga a usar comillas invertidas en SQL) y cinco tablas:

| Tabla | Guarda |
| --- | --- |
| `admins` | Correo y hash de la contraseña del administrador |
| `profile` | Una ficha personal, con identificador 1 |
| `certificates` | Título, institución, año, descripción e imagen |
| `gallery` | Título, descripción e imagen de cada fotografía |
| `posts` | Título, resumen, contenido, imagen y estado de publicación |

El SQL incluye una ficha personal provisional. No incluye atestados, fotos ni publicaciones inventadas.

## Archivos y funciones

### Sitio público

- `home.php`: consulta las tablas y dibuja todas las secciones públicas.
- `post.php`: muestra una publicación por su identificador; solo permite leer las publicadas.
- `assets/style.css`: diseño adaptable a escritorio y móvil.
- `assets/app.js`: marca en la navegación la sección visible.

### Panel

- `admin/setup.php`: crea el primer administrador; después redirige al inicio de sesión.
- `admin/login.php`: comprueba la contraseña con `password_verify`, limita intentos por sesión y abre la sesión privada.
- `admin/logout.php`: cierra la sesión mediante una solicitud POST protegida.
- `admin/index.php`: formularios y listas de contenido.
- `admin/action.php`: valida y guarda o elimina datos con consultas preparadas.

### Funciones compartidas en `includes/app.php`

- `load_env()`: lee únicamente pares `CLAVE=valor` del `.env` local.
- `db()`: abre una conexión PDO a MySQL y reutiliza esa conexión durante la solicitud.
- `e()`: escapa texto antes de insertarlo en HTML; evita que contenido guardado se ejecute como código.
- `profile()` y `records()`: consultan el perfil y las listas de contenido. `records()` solo acepta tablas y órdenes definidos en el código.
- `site_url()` y `asset()`: construyen enlaces a la app, incluso dentro de una subcarpeta de `htdocs`. `asset()` solo acepta nombres de imágenes generados por el sistema.
- `redirect()`: redirige después de guardar un formulario.
- `start_session()`, `csrf_token()`, `verify_csrf()` y `require_admin()`: gestionan la sesión privada y protegen formularios contra solicitudes falsas.
- `flash()` y `take_flash()`: muestran una confirmación o un error después de guardar.
- `field()`, `required_field()` y `web_url()`: validan texto y enlaces recibidos del formulario.
- `uploaded_image()`: valida que el archivo sea una imagen JPEG, PNG o WebP real, de hasta 5 MB; lo guarda con un nombre aleatorio.
- `delete_image()`: borra una imagen reemplazada o un registro eliminado.
- `format_date()`: muestra fechas en formato día/mes/año.

## Flujo de uso

1. Importar el SQL y configurar `.env`.
2. Crear el primer administrador en `/admin/setup.php`.
3. Completar el perfil y subir una foto.
4. Registrar atestados, fotografías y publicaciones.
5. Visitar el sitio público y revisar el resultado.

## Seguridad y publicación

- Las consultas que reciben datos del usuario usan parámetros PDO.
- La salida HTML se escapa con `htmlspecialchars`.
- Las contraseñas del panel se guardan como hash; el valor original no se almacena.
- Los formularios privados usan tokens CSRF.
- Las imágenes tienen tipo, tamaño y nombre controlados.
- `.env` y las imágenes personales no se incluyen en Git. `.env.example` muestra las variables necesarias sin contraseña.
- Las sesiones se guardan en `storage/sessions/`, protegido del acceso web; esto evita depender de permisos de la carpeta temporal de XAMPP.
- `server.php` protege archivos privados al usar `php -S`; `.htaccess` hace lo mismo en Apache.

El proyecto no necesita bibliotecas instaladas *dentro* de MySQL. MySQL almacena los datos, PHP usa PDO para conectarse y el navegador carga HTML, CSS y JavaScript.

## Datos personales pendientes

Para que el contenido sea verdaderamente personal falta reemplazar los datos provisionales con: nombre completo; foto de perfil; presentación y biografía; intereses, habilidades y experiencia; correo y, si se desean, teléfono y redes; imágenes y datos de los atestados; fotografías de galería; y al menos una entrada de blog si se quiere mostrar el componente de blog con contenido real. Todo se puede cargar desde el panel.
