# Notas de Despliegue

## Error "Class AuditController not found"

Si después de actualizar el código sigues viendo este error en tu servidor, sigue estos pasos:

### 1. Asegúrate de haber subido todos los archivos

Verifica que todos estos archivos estén actualizados en tu servidor:
- `public/index.php` (ahora carga AuditController globalmente)
- `app/controllers/AuthController.php`
- `app/controllers/ProfileController.php`
- `app/controllers/FinancialController.php`
- `app/controllers/ResidentsController.php`
- `app/controllers/AmenitiesController.php`
- `app/controllers/SubdivisionsController.php`
- `app/controllers/AuditController.php`

### 2. Limpia la caché de PHP (OPcache)

Si tu servidor tiene OPcache habilitado, los archivos PHP antiguos pueden estar en caché. Opciones para limpiarlo:

**Opción A: Reiniciar PHP-FPM o Apache**
```bash
# Para PHP-FPM
sudo systemctl restart php-fpm

# Para Apache con mod_php
sudo systemctl restart apache2
# o
sudo systemctl restart httpd
```

**Opción B: Usar función de PHP**
Crea un archivo temporal `clear_cache.php` en la raíz de tu servidor:
```php
<?php
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OPcache limpiado exitosamente";
} else {
    echo "OPcache no está habilitado";
}
?>
```

Accede a este archivo desde el navegador: `http://tu-dominio.com/clear_cache.php`

Luego elimínalo por seguridad.

**Opción C: Desde cPanel o panel de control**
- Busca la opción "PHP Configuration" o "Select PHP Version"
- Busca el botón "Reset OPcache" o similar

### 3. Verifica permisos de archivos

```bash
chmod 644 public/index.php
chmod 644 app/controllers/*.php
```

### 4. Verifica la configuración de rutas

Asegúrate de que `APP_PATH` esté correctamente definido en `config/config.php`:
```php
define('APP_PATH', ROOT_PATH . '/app');
```

### 5. Modo de depuración

Si el error persiste, activa el modo de depuración temporalmente en `config/config.php`:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

Y verifica el error exacto que se muestra.

## Solución Implementada

En el commit más reciente, se agregó la carga de `AuditController.php` en `public/index.php`, lo que asegura que esté disponible globalmente para todos los controladores. Esto es una doble protección además de los `require_once` individuales en cada controlador.

## Verificación

Para verificar que todo está correcto en tu servidor, crea un archivo temporal `test_audit.php`:

```php
<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once APP_PATH . '/core/Controller.php';
require_once APP_PATH . '/controllers/AuditController.php';

if (class_exists('AuditController')) {
    echo "✓ AuditController está cargado correctamente";
} else {
    echo "✗ AuditController NO está cargado";
}
?>
```

Accede a este archivo desde el navegador y verifica el resultado. Luego elimínalo por seguridad.

## Soporte

Si después de seguir estos pasos el error persiste:
1. Verifica que estás usando PHP 8.0 o superior
2. Revisa los logs de error de PHP (generalmente en `/var/log/apache2/error.log` o similar)
3. Asegúrate de que no hay restricciones de `open_basedir` que impidan cargar archivos
