# Guía de Migración v1.1.0 - Mejoras del Sistema

## Fecha: 24 de Noviembre, 2024

Esta guía documenta las actualizaciones implementadas en el sistema para mejorar la funcionalidad y rendimiento.

## Cambios Implementados

### 1. Módulo de Residentes
- **Agregado**: Ícono de edición en la columna de acciones del listado de residentes
- **Agregado**: Funcionalidad de edición de residentes con vista dedicada
- **Ruta**: `/residents/edit/{id}`

### 2. Gestión de Usuarios
- **Agregado**: Filtro por rol (SuperAdmin, Administrador, Guardia, Residente)
- **Agregado**: Filtro por estado (Activo, Inactivo, Bloqueado, Pendiente)
- **Agregado**: Búsqueda por nombre, teléfono o correo electrónico

### 3. Configuración del Sistema (SuperAdmin)
- **Agregado**: Acceso directo a "Soporte Técnico" en el índice de configuración
- **Agregado**: Acceso directo a "Auto-Optimización" en el índice de configuración
- **Ruta Soporte**: `/settings/support`
- **Ruta Optimización**: `/settings/optimization`

### 4. Módulo de Auditoría
- **Agregado**: Submenú de "Auto-Optimización" bajo el menú de Auditoría
- **Funcionalidad**: Configuración de caché, optimización de consultas, y gestión de rendimiento
- **Ruta**: `/audit/optimization`

### 5. Calendario Global de Amenidades
- **Agregado**: Calendario global para reservaciones de amenidades
- **Características**:
  - Vista detallada para SuperAdmin/Administrador con información completa
  - Vista simplificada para Residentes con restricción de una reservación por día
  - Integración con FullCalendar.js
  - Accesible desde menú lateral (Amenidades > Calendario)
  - Acceso rápido desde Dashboard
- **Ruta**: `/amenities/calendar`

### 6. Corrección de Acceso para Residentes
- **Corregido**: Módulos de residentes que redirigían incorrectamente al Dashboard
- **Módulos afectados**:
  - Mis Pagos: `/residents/myPayments`
  - Generar Accesos: `/residents/generateAccess`
  - Mis Accesos: `/residents/myAccesses`
- **Solución**: Actualizado el control de acceso en ResidentsController

## Instrucciones de Migración

### Prerrequisitos
- Acceso a la base de datos MySQL
- Permisos de superadministrador
- Backup actual de la base de datos

### Paso 1: Backup de la Base de Datos
```bash
mysqldump -u janetzy_residencial -p janetzy_residencial > backup_antes_migracion_$(date +%Y%m%d).sql
```

### Paso 2: Ejecutar Script de Migración
```bash
mysql -u janetzy_residencial -p janetzy_residencial < database/migrations/migration_2024_11_24_system_enhancements.sql
```

O desde MySQL:
```sql
SOURCE /ruta/completa/al/IDresidencial/database/migrations/migration_2024_11_24_system_enhancements.sql;
```

### Paso 3: Verificar Tablas Creadas
```sql
USE erp_residencial;

-- Verificar tabla de pases de acceso
DESCRIBE resident_access_passes;

-- Verificar tabla de movimientos financieros
DESCRIBE financial_movements;

-- Verificar tabla de tipos de movimientos
DESCRIBE financial_movement_types;

-- Verificar tabla de logs de auditoría
DESCRIBE audit_logs;
```

### Paso 4: Verificar Configuraciones
```sql
-- Verificar configuraciones de optimización
SELECT * FROM system_settings WHERE setting_key LIKE 'cache_%' OR setting_key LIKE '%_optimization';

-- Verificar configuraciones de soporte
SELECT * FROM system_settings WHERE setting_key LIKE 'support_%';
```

### Paso 5: Verificar Vistas
```sql
-- Verificar vista de residentes activos
SELECT COUNT(*) FROM v_active_residents;

-- Verificar vista de calendario de reservaciones
SELECT COUNT(*) FROM v_reservation_calendar;
```

## Cambios en la Base de Datos

### Nuevas Tablas
1. **resident_access_passes** - Pases de acceso generados por residentes
2. **financial_movements** - Movimientos financieros del sistema
3. **financial_movement_types** - Tipos de movimientos financieros
4. **audit_logs** - Registro de auditoría del sistema (si no existía)

### Modificaciones a Tablas Existentes
- **residents.status**: Agregado valor 'deleted' para soft delete
- **users.status**: Agregado valor 'deleted' para soft delete

### Nuevos Índices
- users: idx_first_name, idx_last_name, idx_phone
- residents: idx_status
- properties: idx_status
- amenities: idx_status
- reservations: idx_payment_status, idx_reservation_date_status

### Nuevas Vistas
1. **v_active_residents** - Vista de residentes activos con información de propiedad
2. **v_reservation_calendar** - Vista de calendario de reservaciones

### Nuevas Configuraciones del Sistema
- cache_enabled
- cache_ttl
- query_cache_enabled
- max_records_per_page
- image_optimization
- lazy_loading
- minify_assets
- session_timeout
- support_email
- support_phone
- support_hours
- support_url

## Pruebas Post-Migración

### 1. Probar Edición de Residentes
- Navegar a `/residents`
- Hacer clic en el ícono de edición (✏️)
- Modificar información y guardar
- Verificar que los cambios se guardaron correctamente

### 2. Probar Filtros de Usuarios
- Navegar a `/users`
- Probar filtro por rol
- Probar filtro por estado
- Probar búsqueda por nombre, email, teléfono

### 3. Probar Calendario de Amenidades
- Navegar a `/amenities/calendar`
- Verificar que se muestran las reservaciones existentes
- Hacer clic en una reservación para ver detalles
- Como residente, intentar hacer una segunda reservación el mismo día

### 4. Probar Acceso de Residentes
- Iniciar sesión como residente
- Navegar a "Mis Pagos"
- Navegar a "Generar Accesos"
- Navegar a "Mis Accesos"
- Verificar que no redirigen al Dashboard

### 5. Probar Auto-Optimización
- Como SuperAdmin, navegar a `/audit/optimization`
- Revisar estadísticas del sistema
- Modificar configuraciones de caché
- Ejecutar optimización inmediata
- Verificar que las tablas se optimizaron

## Rollback (En caso de problemas)

Si se presentan problemas después de la migración:

```bash
# Restaurar backup
mysql -u janetzy_residencial -p janetzy_residencial < backup_antes_migracion_YYYYMMDD.sql
```

## Notas Adicionales

### Seguridad
- Las nuevas funcionalidades respetan los roles y permisos existentes
- Se agregaron validaciones adicionales en los controladores
- Los logs de auditoría registran todas las acciones críticas

### Rendimiento
- Los nuevos índices mejoran significativamente las consultas
- La configuración de caché puede reducir la carga del servidor
- Se recomienda ejecutar OPTIMIZE TABLE semanalmente

### Compatibilidad
- Compatible con PHP 7.4+
- Compatible con MySQL 5.7+
- Requiere navegadores modernos para el calendario (Chrome, Firefox, Safari, Edge)

## Soporte

Para cualquier problema o pregunta relacionada con la migración:
- Email: soporte@residencial.com
- Teléfono: +52 442 123 4567
- Horario: Lunes a Viernes 9:00 AM - 6:00 PM

## Registro de Cambios

| Fecha | Versión | Cambios |
|-------|---------|---------|
| 2024-11-24 | 1.1.0 | Implementación de mejoras del sistema |

---

**Autor**: Sistema de Gestión Residencial  
**Última actualización**: 24 de Noviembre, 2024
