# 📋 Guía de Despliegue - Actualización del Sistema

## 🎯 Resumen de Cambios

Esta actualización incluye mejoras significativas al sistema ERP Residencial:

1. **Gestión de Residentes Mejorada**: Acciones de suspender, activar y eliminar residentes
2. **Búsqueda Global Corregida**: Los resultados ahora dirigen correctamente a los detalles del residente
3. **Portal del Residente**: Nuevos módulos para generar accesos y realizar pagos
4. **Sistema de Recordatorios**: Emails automáticos un día antes del vencimiento de pago
5. **Soporte Técnico Público**: Página de soporte accesible sin autenticación
6. **Email SMTP Funcional**: Configuración completa de envío de correos
7. **Optimizaciones de Base de Datos**: Índices y procedimientos para mejor rendimiento

---

## 🗄️ 1. Actualización de Base de Datos

### Backup Previo (IMPORTANTE)
```bash
# Hacer backup de la base de datos antes de cualquier cambio
mysqldump -u usuario -p nombre_base_datos > backup_$(date +%Y%m%d_%H%M%S).sql
```

### Ejecutar Migración
```bash
# Ejecutar el script de migración
mysql -u usuario -p nombre_base_datos < database/migrations/006_system_enhancements.sql
```

### Verificar Aplicación
```sql
-- Verificar que la migración se aplicó correctamente
SELECT * FROM system_settings WHERE setting_key = 'migration_006_applied';

-- Verificar nuevas tablas
SHOW TABLES LIKE 'resident_access_passes';
SHOW TABLES LIKE 'payment_reminders';

-- Verificar procedimiento almacenado
SHOW PROCEDURE STATUS WHERE Name = 'generate_payment_reminders';
```

---

## 📧 2. Configuración de Email SMTP

### Opción A: Desde la Interfaz Web (Recomendado)
1. Iniciar sesión como SuperAdmin
2. Ir a **Configuración > Email**
3. Configurar:
   - **Host SMTP**: `janetzy.shop`
   - **Puerto SMTP**: `465`
   - **Usuario**: `hola@janetzy.shop`
   - **Contraseña**: `Danjohn007`
   - **Email From**: `hola@janetzy.shop`
4. Guardar cambios
5. Enviar email de prueba para verificar

### Opción B: Directo en Base de Datos
```sql
-- Configurar email (usar solo si la interfaz no está disponible)
UPDATE system_settings SET setting_value = 'janetzy.shop' WHERE setting_key = 'email_host';
UPDATE system_settings SET setting_value = '465' WHERE setting_key = 'email_port';
UPDATE system_settings SET setting_value = 'hola@janetzy.shop' WHERE setting_key = 'email_user';
UPDATE system_settings SET setting_value = 'hola@janetzy.shop' WHERE setting_key = 'email_from';
UPDATE system_settings SET setting_value = 'Danjohn007' WHERE setting_key = 'email_password';
```

### Verificar Configuración
```bash
# Probar envío de email desde PHP
php -r "mail('test@example.com', 'Test', 'Test message');"
```

---

## 💳 3. Configuración de PayPal (Opcional)

### Desde la Interfaz Web
1. Iniciar sesión como SuperAdmin
2. Ir a **Configuración > Pagos**
3. Configurar:
   - **Habilitar PayPal**: Sí
   - **Modo**: `sandbox` (desarrollo) o `live` (producción)
   - **Client ID**: Tu PayPal Client ID
   - **Secret**: Tu PayPal Secret Key
4. Guardar cambios

### Obtener Credenciales de PayPal
1. Ir a [PayPal Developer](https://developer.paypal.com/)
2. Crear una aplicación (o usar una existente)
3. Copiar Client ID y Secret
4. Para producción, crear aplicación en modo Live

---

## ⏰ 4. Configuración de Recordatorios Automáticos

### Configurar Cron Job
```bash
# Editar crontab
crontab -e

# Agregar línea para ejecutar diariamente a las 8:00 AM
0 8 * * * /usr/bin/php /var/www/html/IDresidencial/cron/send_payment_reminders.php >> /var/log/payment_reminders.log 2>&1
```

### Dar Permisos de Ejecución
```bash
chmod +x /var/www/html/IDresidencial/cron/send_payment_reminders.php
```

### Crear Directorio de Logs
```bash
sudo mkdir -p /var/log
sudo touch /var/log/payment_reminders.log
sudo chown www-data:www-data /var/log/payment_reminders.log
```

### Probar Manualmente
```bash
# Ejecutar el script manualmente para probar
php /var/www/html/IDresidencial/cron/send_payment_reminders.php

# Ver resultados
tail -f /var/log/payment_reminders.log
```

### Alternativa: Usar Systemd Timer (Linux moderno)
```bash
# Crear archivo de servicio
sudo nano /etc/systemd/system/payment-reminders.service
```

Contenido del archivo:
```ini
[Unit]
Description=Payment Reminders Service
After=network.target

[Service]
Type=oneshot
User=www-data
ExecStart=/usr/bin/php /var/www/html/IDresidencial/cron/send_payment_reminders.php
StandardOutput=append:/var/log/payment_reminders.log
StandardError=append:/var/log/payment_reminders.log
```

Crear timer:
```bash
sudo nano /etc/systemd/system/payment-reminders.timer
```

Contenido:
```ini
[Unit]
Description=Daily Payment Reminders
Requires=payment-reminders.service

[Timer]
OnCalendar=daily
OnCalendar=*-*-* 08:00:00
Persistent=true

[Install]
WantedBy=timers.target
```

Activar:
```bash
sudo systemctl daemon-reload
sudo systemctl enable payment-reminders.timer
sudo systemctl start payment-reminders.timer
sudo systemctl status payment-reminders.timer
```

---

## 🔧 5. Configuración de Optimización

### Desde la Interfaz Web
1. Iniciar sesión como SuperAdmin
2. Ir a **Configuración > Optimización**
3. Configurar según necesidades:
   - **Caché habilitado**: Sí
   - **TTL de caché**: 3600 segundos (1 hora)
   - **Registros por página**: 50
   - **Optimización de imágenes**: Sí
   - **Lazy loading**: Sí
4. Guardar y ejecutar optimización

### Optimización Manual de Base de Datos
```sql
-- Optimizar tablas principales
OPTIMIZE TABLE users;
OPTIMIZE TABLE residents;
OPTIMIZE TABLE properties;
OPTIMIZE TABLE maintenance_fees;
OPTIMIZE TABLE access_logs;
OPTIMIZE TABLE audit_logs;

-- Analizar tablas para estadísticas
ANALYZE TABLE users;
ANALYZE TABLE residents;
ANALYZE TABLE properties;
ANALYZE TABLE maintenance_fees;
```

---

## 🛠️ 6. Configuración de Soporte Técnico

### Configurar desde la Interfaz
1. Iniciar sesión como SuperAdmin
2. Ir a **Configuración > Soporte**
3. Configurar:
   - **Email de Soporte**: `soporte@janetzy.shop`
   - **Teléfono**: (opcional)
   - **Horario**: `Lunes a Viernes 9:00 - 18:00`
   - **URL Pública**: `https://janetzy.shop/residencial/14/support`
4. Guardar cambios

### Verificar Acceso Público
```bash
# Probar URL pública (sin autenticación)
curl https://janetzy.shop/residencial/14/support
```

---

## ✅ 7. Verificación Post-Despliegue

### Checklist de Verificación

- [ ] **Base de Datos**
  - [ ] Migración aplicada correctamente
  - [ ] Tablas nuevas creadas
  - [ ] Procedimientos almacenados funcionando
  - [ ] Índices aplicados

- [ ] **Email**
  - [ ] Configuración SMTP guardada
  - [ ] Email de prueba enviado exitosamente
  - [ ] Reset de contraseña funciona y NO muestra enlace público
  
- [ ] **Residentes**
  - [ ] Botones de suspender/eliminar visibles en `/residents`
  - [ ] Acciones funcionan correctamente
  - [ ] Búsqueda global redirige correctamente
  
- [ ] **Portal del Residente**
  - [ ] Menú lateral muestra "Mis Pagos", "Generar Accesos", "Mis Accesos"
  - [ ] Vista de generar accesos funciona
  - [ ] Vista de mis accesos muestra códigos QR
  - [ ] Vista de realizar pago muestra botón PayPal (si está configurado)
  
- [ ] **Recordatorios de Pago**
  - [ ] Cron job configurado y activo
  - [ ] Script se ejecuta sin errores
  - [ ] Logs generados correctamente
  
- [ ] **Soporte**
  - [ ] Página pública accesible en `/support`
  - [ ] Información de contacto visible
  - [ ] FAQs desplegables funcionan

### Pruebas Funcionales

#### 1. Probar Suspensión de Residente
```
1. Ir a /residents
2. Hacer clic en ícono de suspender (usuario pausado)
3. Confirmar acción
4. Verificar que el estado cambió a "inactive"
5. Intentar iniciar sesión con ese usuario (debe fallar)
```

#### 2. Probar Reset de Contraseña
```
1. Ir a /auth/forgotPassword
2. Ingresar un email válido
3. Verificar que NO se muestra enlace en pantalla
4. Verificar que se muestra mensaje de "Email enviado"
5. Revisar bandeja de entrada del email
6. Seguir enlace en el email
7. Cambiar contraseña exitosamente
```

#### 3. Probar Generar Acceso (como Residente)
```
1. Iniciar sesión como residente
2. Ir a "Generar Accesos" en el menú
3. Llenar formulario
4. Generar pase
5. Verificar en "Mis Accesos" que aparece
6. Verificar que se muestra código QR
```

#### 4. Probar Búsqueda Global
```
1. Iniciar sesión como admin
2. Usar buscador global en la barra superior
3. Buscar por nombre de residente
4. Hacer clic en resultado
5. Verificar que redirige a /residents/viewDetails/{id}
```

---

## 🚨 8. Solución de Problemas

### Email no se envía

**Problema**: Los emails no llegan
**Soluciones**:
1. Verificar configuración SMTP en base de datos
2. Verificar que el puerto 465 esté abierto:
   ```bash
   telnet janetzy.shop 465
   ```
3. Revisar logs de PHP:
   ```bash
   tail -f /var/log/php_errors.log
   ```
4. Verificar que no haya firewall bloqueando
5. Probar con otro puerto (587 con STARTTLS)

### Cron Job no ejecuta

**Problema**: Los recordatorios no se envían
**Soluciones**:
1. Verificar que cron está activo:
   ```bash
   systemctl status cron
   ```
2. Ver logs de cron:
   ```bash
   grep CRON /var/log/syslog
   ```
3. Ejecutar manualmente el script y ver errores
4. Verificar permisos de ejecución del script
5. Verificar ruta de PHP en crontab

### Error al suspender/eliminar residente

**Problema**: Error al ejecutar acciones
**Soluciones**:
1. Verificar que existen foreign keys correctas
2. Ver logs de PHP para detalles del error
3. Verificar permisos del usuario de base de datos

### PayPal no carga

**Problema**: Botón de PayPal no aparece
**Soluciones**:
1. Verificar que `paypal_enabled` esté en '1'
2. Verificar Client ID en configuración
3. Abrir consola del navegador para ver errores JavaScript
4. Verificar conexión a internet

---

## 📊 9. Monitoreo y Mantenimiento

### Logs a Revisar Regularmente

```bash
# Logs de recordatorios de pago
tail -f /var/log/payment_reminders.log

# Logs de PHP
tail -f /var/log/php_errors.log

# Logs de Apache/Nginx
tail -f /var/log/apache2/error.log
tail -f /var/log/nginx/error.log

# Logs del sistema
tail -f /var/log/syslog
```

### Queries de Monitoreo

```sql
-- Ver recordatorios pendientes
SELECT COUNT(*) as pending_reminders
FROM payment_reminders
WHERE status = 'pending' AND reminder_date <= CURDATE();

-- Ver adeudos totales
SELECT SUM(total_debt) as total_debt, COUNT(*) as properties_with_debt
FROM property_debt_summary
WHERE total_debt > 0;

-- Ver pases de acceso activos
SELECT COUNT(*) as active_passes
FROM resident_access_passes
WHERE status = 'active';

-- Ver últimos emails enviados
SELECT *
FROM payment_reminders
WHERE status = 'sent'
ORDER BY sent_at DESC
LIMIT 10;
```

### Tareas de Mantenimiento Periódicas

**Diario**: Verificar que cron job ejecutó correctamente
**Semanal**: Revisar logs de errores
**Mensual**: 
- Optimizar tablas de base de datos
- Revisar espacio en disco
- Limpiar logs antiguos
- Backup de base de datos

```bash
# Limpiar logs antiguos (mantener últimos 30 días)
find /var/log -name "*.log" -mtime +30 -delete
```

---

## 📞 10. Soporte

Para cualquier problema o duda:

- **Email**: soporte@janetzy.shop
- **Documentación**: `/support` (público)
- **Código fuente**: GitHub repository

---

## 📝 Notas Adicionales

### Seguridad
- Cambiar contraseñas predeterminadas
- Mantener sistema actualizado
- Revisar logs regularmente
- Hacer backups frecuentes

### Performance
- Monitorear uso de recursos
- Optimizar queries lentas
- Considerar CDN para assets estáticos
- Habilitar compresión gzip

### Escalabilidad
- Considerar separar base de datos en servidor dedicado
- Implementar caché Redis/Memcached
- Usar queue para emails (Laravel Queue, RabbitMQ, etc.)
- Load balancer para múltiples instancias

---

**Fecha de actualización**: 24 de Noviembre, 2024  
**Versión**: 1.1.0  
**Migración**: 006_system_enhancements
