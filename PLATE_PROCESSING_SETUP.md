# Configuración de Procesamiento Automático de Placas

## 📁 Estructura de Carpetas

```
/home2/janetzy/
├── placas/
│   └── IP CAMERA/
│       └── 01/                    ← Imágenes llegan aquí por FTP
│           ├── ABC123_20251124_143015.jpg
│           └── DEF456_20251124_143020.jpg
├── public_html/
│   ├── placas/                    ← Imágenes procesadas aquí
│   │   ├── ABC123_20251124143015.jpg
│   │   └── DEF456_20251124143020.jpg
│   ├── logs/                      ← Logs del procesamiento
│   │   └── plate_processing.log
│   └── cron/
│       └── process_plate_images.php  ← Script principal
```

## ⚙️ Instalación

### Paso 1: Subir el script
1. Sube el archivo `process_plate_images.php` a: `/home2/janetzy/public_html/cron/`
2. Dale permisos de ejecución: `chmod 755 process_plate_images.php`

### Paso 2: Configurar credenciales de BD
Edita el archivo `process_plate_images.php` y modifica:

```php
define('DB_USER', 'janetzy_admin'); // Tu usuario de BD
define('DB_PASS', 'tu_password_aqui'); // Tu contraseña de BD
```

### Paso 3: Probar manualmente
Ejecuta desde terminal SSH:
```bash
cd /home2/janetzy/public_html/cron
php process_plate_images.php
```

Deberías ver la salida en pantalla y en `/home2/janetzy/public_html/logs/plate_processing.log`

### Paso 4: Configurar Cron Job en cPanel

1. **Accede a cPanel → Cron Jobs**

2. **Agregar nuevo Cron Job:**
   - **Intervalo común:** Cada 5 minutos
   - **Comando:**
     ```bash
     /usr/bin/php /home2/janetzy/public_html/cron/process_plate_images.php
     ```

3. **Otras opciones de intervalo:**

   **Cada 1 minuto** (tiempo real):
   ```
   * * * * * /usr/bin/php /home2/janetzy/public_html/cron/process_plate_images.php
   ```

   **Cada 5 minutos** (recomendado):
   ```
   */5 * * * * /usr/bin/php /home2/janetzy/public_html/cron/process_plate_images.php
   ```

   **Cada 10 minutos**:
   ```
   */10 * * * * /usr/bin/php /home2/janetzy/public_html/cron/process_plate_images.php
   ```

   **Solo en horario laboral** (8am-8pm, cada 2 minutos):
   ```
   */2 8-20 * * * /usr/bin/php /home2/janetzy/public_html/cron/process_plate_images.php
   ```

## 🔍 Cómo Funciona

### 1. Detección de Imágenes
- El script busca imágenes (.jpg, .jpeg, .png) en `/home2/janetzy/placas/IP CAMERA/01`
- Soporta múltiples formatos de nombre:
  - `ABC123_20251124_143015.jpg`
  - `ABC-123-D_20251124143015.jpg`
  - `Snapshot_1_20251124143015_ABC123.jpg`

### 2. Extracción de Información
- Extrae la **placa** del nombre del archivo
- Extrae la **fecha/hora** de captura
- Si no puede extraer, usa la fecha actual

### 3. Verificación en Base de Datos
- Busca si la placa existe en la tabla `vehicles`
- Si existe: `is_match = 1` (autorizada)
- Si NO existe: `is_match = 0` (no autorizada)

### 4. Registro en BD
- Inserta registro en `detected_plates` con:
  - Placa detectada
  - Estado de coincidencia
  - Ruta de la imagen
  - Información del vehículo (si aplica)

### 5. Registro de Acceso
- Si la placa está autorizada:
  - Crea registro en `access_logs`
  - Tipo: `vehicle`
  - Método: `plate_recognition`
  - Registra entrada automática

### 6. Limpieza
- Mueve imagen a carpeta pública
- Elimina imagen original del FTP
- Evita duplicados (verifica últimas 2 horas)

## 📊 Consultas Útiles

### Ver últimas placas detectadas
```sql
SELECT 
    plate_text,
    captured_at,
    is_match,
    status,
    JSON_EXTRACT(payload_json, '$.image_path') as image_path
FROM detected_plates 
ORDER BY captured_at DESC 
LIMIT 20;
```

### Ver placas no autorizadas
```sql
SELECT * FROM detected_plates 
WHERE is_match = 0 
AND status = 'new'
ORDER BY captured_at DESC;
```

### Ver accesos automáticos
```sql
SELECT * FROM access_logs 
WHERE access_method = 'plate_recognition'
ORDER BY timestamp DESC 
LIMIT 20;
```

## 📝 Revisar Logs

Ver log completo:
```bash
tail -f /home2/janetzy/public_html/logs/plate_processing.log
```

Ver últimas 50 líneas:
```bash
tail -n 50 /home2/janetzy/public_html/logs/plate_processing.log
```

Buscar errores:
```bash
grep ERROR /home2/janetzy/public_html/logs/plate_processing.log
```

## 🚨 Solución de Problemas

### Problema: No se procesan imágenes
**Solución:**
1. Verificar permisos de carpetas:
   ```bash
   chmod 755 /home2/janetzy/placas/IP\ CAMERA/01
   chmod 755 /home2/janetzy/public_html/placas
   ```

2. Verificar que existen imágenes:
   ```bash
   ls -la /home2/janetzy/placas/IP\ CAMERA/01/
   ```

### Problema: Error de conexión a BD
**Solución:**
- Verificar credenciales en `process_plate_images.php`
- Probar conexión desde terminal:
  ```bash
  mysql -u janetzy_admin -p janetzy_residencial
  ```

### Problema: Cron no se ejecuta
**Solución:**
1. Verificar en cPanel → Cron Jobs que esté activo
2. Verificar email de cPanel (errores se envían ahí)
3. Probar comando manualmente desde SSH

### Problema: Formato de placa no reconocido
**Solución:**
- Agregar nuevo patrón en función `extractPlateInfo()`
- O renombrar imágenes desde la cámara HikVision

## 📸 Configuración de Cámara HikVision

Para que las imágenes lleguen con el formato correcto:

1. **Acceder a configuración de cámara**
2. **Storage → FTP**
   - Habilitar FTP
   - Server: IP de tu servidor
   - Port: 21
   - User: usuario FTP
   - Directory: `/placas/IP CAMERA/01`

3. **Event → Smart Event → Vehicle Detection**
   - Habilitar detección de placas
   - Snapshot: Enable
   - Formato de nombre: `%plate%_%date%_%time%`

## 📧 Notificaciones (Opcional)

Para recibir notificaciones por email de placas no autorizadas, agrega al final del script:

```php
if (!$isMatch) {
    mail(
        'admin@residencial.com',
        'Alerta: Placa no autorizada',
        "Se detectó la placa {$plateInfo['plate']} que NO está registrada.",
        'From: sistema@residencial.com'
    );
}
```

## 🔐 Seguridad

- ✅ El script valida existencia de directorios
- ✅ Evita duplicados (últimas 2 horas)
- ✅ Registra todo en logs
- ✅ Elimina archivos procesados
- ✅ Usa prepared statements (previene SQL injection)

## 📈 Mantenimiento

### Limpiar imágenes antiguas (cada mes)
```bash
find /home2/janetzy/public_html/placas -name "*.jpg" -mtime +90 -delete
```

### Limpiar logs antiguos
```bash
find /home2/janetzy/public_html/logs -name "*.log" -mtime +30 -delete
```

### Agregar al cron mensual en cPanel:
```bash
0 0 1 * * find /home2/janetzy/public_html/placas -name "*.jpg" -mtime +90 -delete
```
