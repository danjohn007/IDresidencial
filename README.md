# 🏘️ ERP Residencial Online

Sistema completo de gestión para fraccionamientos y residenciales desarrollado con tecnologías open source.

## 📋 Características Principales

### Módulos Implementados

1. **Control de Accesos**
   - Registro de visitas con QR, identificación y placas
   - Generación de pases de visita con tiempo limitado
   - Control de accesos vehiculares y peatonales
   - Bitácora completa de entradas y salidas

2. **Administración de Predios**
   - Gestión de residentes (propietarios, inquilinos, familia)
   - Asociación de usuarios con viviendas
   - Sistema de carga de documentos
   - Generación automática de cuotas de mantenimiento
   - Integración de pagos en línea
   - Sistema de comunicados y notificaciones

3. **Casa Club**
   - Reservación de amenidades (salón, alberca, asadores, canchas)
   - Control de horarios, aforo y disponibilidad
   - Sistema de penalizaciones por incumplimiento
   - Bloqueo temporal por violaciones

4. **Mantenimiento**
   - Reportes de incidencias con categorías
   - Carga de fotos/videos
   - Asignación de tareas a personal
   - Seguimiento de avances
   - Notificaciones de cambios de estatus

5. **Seguridad**
   - Monitoreo en tiempo real
   - Registro de rondines
   - Sistema de alertas automáticas

6. **Dashboard Administrativo**
   - Estadísticas en tiempo real
   - Gráficas interactivas
   - Filtros por fecha, zona y sección

7. **Consola de Guardia**
   - Vista de visitas programadas
   - Registro manual de accesos
   - Sistema de alertas

8. **Sistema de Configuración**
   - Personalización de nombre y logo
   - Configuración de correos
   - Horarios de atención
   - Personalización de colores
   - Integración con PayPal
   - API para QR masivos

## 🛠️ Tecnologías Utilizadas

- **Backend:** PHP 8+ (sin framework)
- **Base de Datos:** MySQL 5.7+
- **Frontend:** HTML5, CSS3, JavaScript
- **Estilos:** Tailwind CSS
- **Gráficas:** Chart.js
- **Calendario:** FullCalendar.js
- **Iconos:** Font Awesome
- **Arquitectura:** MVC (Model-View-Controller)

## 📦 Requisitos del Sistema

- PHP 8.0 o superior
- MySQL 5.7 o superior
- Apache 2.4+ con mod_rewrite habilitado
- Extensiones PHP requeridas:
  - PDO
  - pdo_mysql
  - session
  - gd (para QR y procesamiento de imágenes)
  - json

## 🚀 Instalación

### 1. Clonar el repositorio

```bash
git clone https://github.com/danjohn007/IDresidencial.git
cd IDresidencial
```

### 2. Configurar la base de datos

Crea la base de datos y ejecuta el script SQL:

```bash
mysql -u root -p < database/schema.sql
```

O manualmente:
1. Accede a phpMyAdmin o tu cliente MySQL
2. Crea una nueva base de datos llamada `erp_residencial`
3. Importa el archivo `database/schema.sql`

### 3. Configurar credenciales

Edita el archivo `config/config.php` y ajusta las credenciales de tu base de datos:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'erp_residencial');
define('DB_USER', 'tu_usuario');
define('DB_PASS', 'tu_contraseña');
```

### 4. Configurar permisos

Da permisos de escritura a las carpetas de uploads:

```bash
chmod -R 755 public/uploads
```

### 5. Configurar Apache

Asegúrate de que Apache tenga habilitado el módulo `mod_rewrite`:

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

Crea un Virtual Host o simplemente copia el proyecto a tu carpeta `htdocs` o `www`.

**Ejemplo de Virtual Host:**

```apache
<VirtualHost *:80>
    ServerName residencial.local
    DocumentRoot /ruta/al/proyecto/IDresidencial
    
    <Directory /ruta/al/proyecto/IDresidencial>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/residencial-error.log
    CustomLog ${APACHE_LOG_DIR}/residencial-access.log combined
</VirtualHost>
```

### 6. Probar la instalación

Visita: `http://tu-servidor/test_connection.php`

Este archivo verificará:
- ✅ Conexión a la base de datos
- ✅ URL base detectada correctamente
- ✅ Directorios necesarios
- ✅ Extensiones PHP requeridas

## 🔐 Credenciales por Defecto

El sistema incluye usuarios de ejemplo con los siguientes accesos:

| Usuario | Contraseña | Rol |
|---------|-----------|-----|
| admin | password | Superadmin |
| guardia1 | password | Guardia |
| residente1 | password | Residente |
| residente2 | password | Residente |
| residente3 | password | Residente |

**⚠️ IMPORTANTE:** Cambia estas contraseñas en producción.

## 📱 Roles y Permisos

### Superadmin
- Acceso completo a todos los módulos
- Configuración del sistema
- Gestión de usuarios
- Reportes y estadísticas

### Administrador
- Gestión de residentes y propiedades
- Control de pagos y mantenimiento
- Comunicados y notificaciones
- Visualización de estadísticas

### Guardia
- Control de accesos
- Registro de visitas
- Bitácora de entradas/salidas
- Consola de guardia

### Residente
- Generar pases de visita
- Reservar amenidades
- Reportar incidencias
- Ver estado de cuenta

## 🌐 URLs del Sistema

El sistema utiliza URLs amigables:

- `/` - Redirecciona al login o dashboard
- `/auth/login` - Página de inicio de sesión
- `/auth/logout` - Cerrar sesión
- `/dashboard` - Panel principal
- `/access` - Control de accesos
- `/residents` - Gestión de residentes
- `/amenities` - Amenidades y reservaciones
- `/maintenance` - Reportes de mantenimiento
- `/security` - Seguridad y alertas
- `/guard` - Consola de guardia
- `/settings` - Configuración del sistema

## 📁 Estructura del Proyecto

```
IDresidencial/
├── app/
│   ├── controllers/      # Controladores MVC
│   ├── models/          # Modelos de datos
│   ├── views/           # Vistas (HTML/PHP)
│   └── core/            # Clases del núcleo (Router, Controller)
├── config/
│   ├── config.php       # Configuración principal
│   └── database.php     # Configuración de BD
├── database/
│   └── schema.sql       # Esquema de base de datos
├── public/
│   ├── css/            # Estilos personalizados
│   ├── js/             # JavaScript personalizado
│   ├── img/            # Imágenes
│   ├── uploads/        # Archivos subidos
│   ├── .htaccess       # Reescritura de URLs
│   └── index.php       # Punto de entrada
├── .htaccess           # Redirección a public/
├── test_connection.php # Archivo de prueba
└── README.md           # Este archivo
```

## 🎨 Personalización

### Cambiar colores del tema

Edita `config/config.php`:

```php
define('THEME_COLORS', [
    'primary' => 'blue',    // Color principal
    'secondary' => 'gray',  // Color secundario
    'accent' => 'green',    // Color de acento
    'danger' => 'red'       // Color de peligro
]);
```

### Cambiar nombre del sitio

```php
define('SITE_NAME', 'Tu Residencial');
define('SITE_EMAIL', 'contacto@turesidencial.com');
define('SITE_PHONE', '+52 442 XXX XXXX');
```

## 🔧 Configuración Avanzada

### Pagos con PayPal

```php
define('PAYPAL_MODE', 'sandbox'); // o 'live'
define('PAYPAL_CLIENT_ID', 'tu_client_id');
define('PAYPAL_SECRET', 'tu_secret');
```

### SMTP para correos

```php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'tu_email@gmail.com');
define('SMTP_PASS', 'tu_contraseña');
```

## 📊 Base de Datos

La base de datos incluye datos de ejemplo del estado de Querétaro:
- Propiedades en diferentes secciones
- Residentes registrados
- Vehículos asociados
- Amenidades configuradas
- Cuotas de mantenimiento

## 🐛 Solución de Problemas

### Error: "Could not connect to database"
- Verifica las credenciales en `config/config.php`
- Asegúrate de que MySQL esté corriendo
- Verifica que la base de datos existe

### Error 404 en las URLs
- Verifica que `mod_rewrite` esté habilitado en Apache
- Comprueba que los archivos `.htaccess` existen
- Revisa los permisos de los archivos

### No se muestran imágenes
- Verifica permisos de la carpeta `public/uploads`
- Comprueba que la extensión GD esté instalada

## 📝 Desarrollo

### Crear un nuevo módulo

1. Crea el controlador en `app/controllers/`
2. Crea el modelo en `app/models/`
3. Crea las vistas en `app/views/`
4. Añade las rutas en la navegación

### Convenciones de código

- Nombres de clases en PascalCase
- Nombres de métodos en camelCase
- Nombres de archivos igual que la clase
- Un archivo por clase

## 🤝 Contribuir

Las contribuciones son bienvenidas. Por favor:
1. Haz fork del proyecto
2. Crea una rama para tu feature
3. Realiza tus cambios
4. Envía un pull request

## 📄 Licencia

Este proyecto es de código abierto y está disponible bajo la licencia MIT.

## 👨‍💻 Autor

Desarrollado para la gestión eficiente de fraccionamientos residenciales.

## 📞 Soporte

Para soporte técnico o consultas:
- Email: contacto@residencial.com
- Teléfono: +52 442 123 4567

---

**¡Gracias por usar ERP Residencial Online!** 🏘️
