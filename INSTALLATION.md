# 📘 Guía de Instalación - ERP Residencial

## Requisitos Previos

### Software Requerido
- **PHP**: 8.0 o superior
- **MySQL**: 5.7 o superior (o MariaDB 10.3+)
- **Apache**: 2.4+ con mod_rewrite habilitado
- **Extensiones PHP**:
  - pdo
  - pdo_mysql
  - session
  - gd
  - json
  - mbstring

### Verificar Requisitos

```bash
# Verificar versión de PHP
php -v

# Verificar extensiones PHP
php -m | grep -E 'pdo|mysql|gd|session|json'

# Verificar Apache mod_rewrite
apache2ctl -M | grep rewrite
```

## Instalación Paso a Paso

### 1. Descargar el Proyecto

```bash
# Clonar repositorio
git clone https://github.com/danjohn007/IDresidencial.git

# O descargar ZIP y extraer
cd IDresidencial
```

### 2. Configurar Base de Datos

#### Opción A: Línea de comandos

```bash
# Crear base de datos e importar schema
mysql -u root -p

# En el prompt de MySQL:
CREATE DATABASE erp_residencial CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;

# Importar el schema
mysql -u root -p erp_residencial < database/schema.sql
```

#### Opción B: phpMyAdmin

1. Acceder a phpMyAdmin (http://localhost/phpmyadmin)
2. Crear nueva base de datos: `erp_residencial`
3. Seleccionar cotejamiento: `utf8mb4_unicode_ci`
4. Ir a la pestaña "Importar"
5. Seleccionar el archivo `database/schema.sql`
6. Clic en "Continuar"

### 3. Configurar Credenciales

Editar el archivo `config/config.php`:

```php
// Configuración de la base de datos
define('DB_HOST', 'localhost');           // Tu host de MySQL
define('DB_NAME', 'erp_residencial');     // Nombre de la BD
define('DB_USER', 'tu_usuario');          // Tu usuario MySQL
define('DB_PASS', 'tu_contraseña');       // Tu contraseña MySQL
```

### 4. Configurar Permisos

```bash
# Dar permisos de escritura a carpetas de uploads
chmod -R 755 public/uploads
chmod -R 755 public/uploads/qr
chmod -R 755 public/uploads/documents
chmod -R 755 public/uploads/incidents
chmod -R 755 public/uploads/photos

# Verificar propietario (reemplaza www-data con tu usuario de Apache)
chown -R www-data:www-data public/uploads
```

### 5. Configurar Apache

#### Opción A: Carpeta htdocs (XAMPP/WAMP)

```bash
# Copiar proyecto a htdocs
cp -r IDresidencial /path/to/xampp/htdocs/

# Acceder vía:
# http://localhost/IDresidencial
```

#### Opción B: Virtual Host (Recomendado)

1. Crear archivo de configuración:

```bash
sudo nano /etc/apache2/sites-available/residencial.conf
```

2. Agregar configuración:

```apache
<VirtualHost *:80>
    ServerName residencial.local
    DocumentRoot /var/www/IDresidencial
    
    <Directory /var/www/IDresidencial>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/residencial-error.log
    CustomLog ${APACHE_LOG_DIR}/residencial-access.log combined
</VirtualHost>
```

3. Habilitar sitio y mod_rewrite:

```bash
sudo a2ensite residencial.conf
sudo a2enmod rewrite
sudo systemctl restart apache2
```

4. Agregar al archivo hosts:

```bash
sudo nano /etc/hosts

# Agregar línea:
127.0.0.1    residencial.local
```

5. Acceder vía: http://residencial.local

### 6. Verificar Instalación

Visitar: `http://tu-servidor/test_connection.php`

Este archivo verifica:
- ✅ Conexión a base de datos
- ✅ URL base detectada correctamente
- ✅ Directorios creados
- ✅ Extensiones PHP instaladas
- ✅ Permisos de escritura

### 7. Primer Acceso

**URL de Login**: `http://tu-servidor/auth/login`

**Credenciales por defecto**:

| Usuario | Contraseña | Rol |
|---------|-----------|-----|
| admin | password | Superadmin |
| guardia1 | password | Guardia |
| residente1 | password | Residente |

**⚠️ IMPORTANTE**: Cambiar estas contraseñas inmediatamente en producción.

## Solución de Problemas Comunes

### Error: "Could not connect to database"

**Solución**:
1. Verificar credenciales en `config/config.php`
2. Verificar que MySQL esté corriendo:
   ```bash
   sudo systemctl status mysql
   ```
3. Verificar que la base de datos existe:
   ```sql
   SHOW DATABASES LIKE 'erp_residencial';
   ```

### Error 404 en todas las páginas

**Solución**:
1. Verificar mod_rewrite habilitado:
   ```bash
   sudo a2enmod rewrite
   sudo systemctl restart apache2
   ```
2. Verificar que existe `.htaccess` en raíz y en `public/`
3. Verificar `AllowOverride All` en configuración de Apache

### No se muestran imágenes o CSS

**Solución**:
1. Verificar URL base en `config/config.php`
2. Verificar permisos de carpeta `public/`
3. Verificar que DocumentRoot apunte correctamente

### Error: "Call to undefined function imagecreatetruecolor"

**Solución**:
```bash
# Ubuntu/Debian
sudo apt-get install php-gd
sudo systemctl restart apache2

# CentOS/RHEL
sudo yum install php-gd
sudo systemctl restart httpd
```

### Sesión no persiste / Login no funciona

**Solución**:
1. Verificar permisos de carpeta de sesiones:
   ```bash
   sudo chmod 1733 /var/lib/php/sessions
   ```
2. Verificar configuración de sesiones en `php.ini`
3. Limpiar cookies del navegador

## Configuración de Producción

### 1. Seguridad

**Cambiar en `config/config.php`**:

```php
// Deshabilitar display de errores
error_reporting(0);
ini_set('display_errors', 0);

// Cambiar contraseñas por defecto
// Usar contraseñas fuertes de al menos 12 caracteres
```

### 2. Optimización

```bash
# Habilitar OPcache
sudo nano /etc/php/8.0/apache2/php.ini

# Agregar/modificar:
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=10000
```

### 3. SSL/HTTPS

```bash
# Instalar Certbot para Let's Encrypt
sudo apt-get install certbot python3-certbot-apache

# Obtener certificado
sudo certbot --apache -d tudominio.com

# Renovación automática ya está configurada
```

### 4. Backup Automático

```bash
# Crear script de backup
sudo nano /usr/local/bin/backup-residencial.sh

#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u root -p[PASSWORD] erp_residencial > /backups/db_$DATE.sql
tar -czf /backups/files_$DATE.tar.gz /var/www/IDresidencial/public/uploads

# Hacerlo ejecutable
sudo chmod +x /usr/local/bin/backup-residencial.sh

# Agregar a crontab (backup diario a las 2 AM)
crontab -e
0 2 * * * /usr/local/bin/backup-residencial.sh
```

## Actualización del Sistema

```bash
# 1. Backup de base de datos
mysqldump -u root -p erp_residencial > backup_$(date +%Y%m%d).sql

# 2. Backup de uploads
tar -czf uploads_backup.tar.gz public/uploads/

# 3. Descargar nueva versión
git pull origin main

# 4. Ejecutar migraciones si existen
mysql -u root -p erp_residencial < database/migrations/nueva_version.sql

# 5. Limpiar caché del navegador
```

## Contacto y Soporte

Para soporte técnico:
- **Email**: contacto@residencial.com
- **Teléfono**: +52 442 123 4567
- **GitHub Issues**: https://github.com/danjohn007/IDresidencial/issues

## Licencia

Este proyecto es de código abierto bajo licencia MIT.

---

**¡Instalación completada! Disfruta del sistema ERP Residencial.** 🏘️
