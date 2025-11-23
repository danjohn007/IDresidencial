# 🚀 Guía de Migración - Nuevas Funcionalidades

Esta guía describe cómo aplicar las nuevas funcionalidades al sistema ERP Residencial.

## 📋 Resumen de Cambios

### Nuevos Módulos Implementados

1. **💰 Módulo Financiero** - Gestión completa de ingresos y egresos
2. **🎫 Módulo de Membresías** - Sistema de membresías para residentes
3. **📊 Módulo de Reportes** - Reportes detallados del sistema
4. **🔍 Sistema de Auditoría** - Registro de acciones del sistema

### Mejoras Realizadas

- ✅ Botón "Nuevo Dispositivo Hikvision" agregado al módulo de dispositivos
- ✅ Formulario de usuario actualizado (teléfono 10 dígitos, sin campo usuario, campo casa para residentes)
- ✅ Los residentes pueden generar accesos y realizar pagos (ya estaba implementado)

## 🗄️ Paso 1: Actualizar la Base de Datos

### Ejecutar el Script de Migración

El archivo `database/migrations/001_add_new_features.sql` contiene todas las actualizaciones necesarias.

**Opción 1: Desde línea de comandos**
```bash
mysql -u tu_usuario -p erp_residencial < database/migrations/001_add_new_features.sql
```

**Opción 2: Desde phpMyAdmin**
1. Accede a phpMyAdmin
2. Selecciona la base de datos `erp_residencial`
3. Ve a la pestaña "SQL"
4. Copia y pega el contenido de `database/migrations/001_add_new_features.sql`
5. Haz clic en "Continuar"

### ¿Qué hace el script de migración?

#### Nuevas Tablas Creadas

1. **audit_logs** - Registra todas las acciones importantes del sistema
   - Rastrea usuario, acción, descripción, IP, user agent
   - Permite auditoría completa del sistema

2. **financial_movement_types** - Catálogo de tipos de movimientos
   - 12 tipos predefinidos (ingresos y egresos)
   - Clasificación: ingreso, egreso, ambos
   - Totalmente extensible

3. **financial_movements** - Movimientos financieros
   - Registro de ingresos y egresos
   - Vinculación con propiedades y residentes
   - Integración con otros módulos (pagos, reservaciones, penalizaciones)

4. **membership_plans** - Planes de membresía
   - 3 planes predefinidos: Básico ($500), Premium ($1000), VIP ($1500)
   - Beneficios en formato JSON
   - Completamente personalizables

5. **memberships** - Membresías activas
   - Vinculación residente-plan
   - Control de fechas de vigencia
   - Estados: activo, suspendido, cancelado, expirado

6. **membership_payments** - Pagos de membresías
   - Control de pagos mensuales
   - Integración con módulo financiero
   - Estados de pago

#### Modificaciones a Tablas Existentes

- **users**: Se agregó campo `house_number` para residentes

#### Migración de Datos Existentes

El script migra automáticamente:
- ✅ Cuotas de mantenimiento pagadas → Movimientos financieros
- ✅ Reservaciones pagadas → Movimientos financieros
- ✅ Penalizaciones pagadas → Movimientos financieros

**Importante**: Los datos existentes NO se eliminan, solo se replican en el nuevo sistema.

## 📁 Paso 2: Verificar Archivos del Código

Los siguientes archivos ya están incluidos en este commit:

### Controladores Nuevos
- `app/controllers/FinancialController.php`
- `app/controllers/MembershipsController.php`
- `app/controllers/ReportsController.php`

### Modelos Nuevos
- `app/models/Financial.php`
- `app/models/Membership.php`

### Vistas Nuevas

**Módulo Financiero:**
- `app/views/financial/index.php` - Dashboard con gráficas
- `app/views/financial/create.php` - Crear movimiento
- `app/views/financial/view.php` - Ver detalle

**Módulo Membresías:**
- `app/views/memberships/index.php` - Listado
- `app/views/memberships/create.php` - Crear membresía
- `app/views/memberships/plans.php` - Ver planes

**Módulo Reportes:**
- `app/views/reports/index.php` - Dashboard de reportes

### Archivos Modificados
- `app/controllers/UsersController.php` - Auto-generación de username
- `app/models/User.php` - Campo house_number
- `app/views/users/create.php` - Formulario actualizado
- `app/views/layouts/sidebar.php` - Nuevos menús
- `app/views/devices/index.php` - Botón Hikvision

## 🎨 Paso 3: Verificar Permisos

Asegúrate de que el servidor web tenga permisos de escritura en:
```bash
chmod -R 755 public/uploads
```

## 🧪 Paso 4: Probar las Nuevas Funcionalidades

### 1. Módulo Financiero
1. Inicia sesión como **admin** o **superadmin**
2. Ve a **Módulo Financiero** en el menú lateral
3. Verás las gráficas de los últimos 12 meses
4. Crea un nuevo movimiento de prueba:
   - Click en "Nuevo Movimiento"
   - Selecciona tipo (ingreso/egreso)
   - Completa el formulario
   - Guarda

### 2. Módulo de Membresías
1. Ve a **Membresías** en el menú
2. Click en "Ver Planes" para ver los 3 planes predefinidos
3. Crea una membresía de prueba:
   - Click en "Nueva Membresía"
   - Selecciona un residente
   - Selecciona un plan
   - Completa el formulario
   - Guarda

### 3. Sistema de Auditoría
1. Ve a **Auditoría** en el menú (solo superadmin)
2. Deberías ver registros de las acciones que realizaste
3. Los logs se generan automáticamente

### 4. Módulo de Reportes
1. Ve a **Reportes** en el menú
2. Explora los diferentes tipos de reportes:
   - Reporte Financiero
   - Reporte de Accesos
   - Reporte de Mantenimiento
   - Reporte de Residentes
   - Reporte de Membresías

### 5. Dispositivos Hikvision
1. Ve a **Dispositivos**
2. Verás el botón morado "Nuevo Dispositivo Hikvision"
3. Click para agregar un dispositivo

### 6. Formulario de Usuario
1. Ve a **Usuarios** > "Nuevo Usuario"
2. Verifica los cambios:
   - ✅ No hay campo "Usuario"
   - ✅ Campo "Teléfono/WhatsApp" con límite de 10 dígitos
   - ✅ Al seleccionar rol "Residente", aparece campo "Número de Casa"

### 7. Funcionalidad de Residentes
1. Inicia sesión como **residente** (residente1 / password)
2. Ve a **Control de Accesos**
3. Podrás generar pases de visita
4. Los residentes también pueden ver sus pagos en el módulo correspondiente

## 📊 Características del Módulo Financiero

### Dashboard Principal
- 📈 Gráfica de movimientos por mes (líneas)
- 📊 Gráfica de movimientos por tipo (barras)
- 💵 Total de ingresos del período
- 💸 Total de egresos del período
- 💰 Balance general
- 📅 Filtros por fecha (default: últimos 12 meses)

### Tipos de Movimiento Predefinidos

**Ingresos:**
- Cuota de Mantenimiento
- Reservación de Amenidades
- Penalización
- Membresía Mensual
- Otros Ingresos

**Egresos:**
- Mantenimiento General
- Servicios Públicos
- Personal (nómina)
- Proveedores
- Reparaciones
- Seguridad
- Otros Egresos

### Integración Automática
El módulo financiero se integra automáticamente con:
- ✅ Cuotas de mantenimiento (cuando se marcan como pagadas)
- ✅ Reservaciones de amenidades (cuando se pagan)
- ✅ Penalizaciones (cuando se pagan)
- ✅ Membresías (pagos mensuales)

## 🎫 Características del Módulo de Membresías

### Planes Incluidos

**Plan Básico - $500/mes**
- Acceso a alberca
- Acceso a gimnasio
- 2 reservaciones mensuales

**Plan Premium - $1000/mes**
- Acceso a alberca
- Acceso a gimnasio
- Reservaciones ilimitadas
- Descuento 10% en eventos
- Invitados sin costo

**Plan VIP - $1500/mes**
- Acceso a alberca
- Acceso a gimnasio
- Reservaciones prioritarias
- Descuento 20% en eventos
- Invitados sin costo
- Acceso a áreas exclusivas

### Gestión de Membresías
- Asignación de plan a residente
- Control de fechas de vigencia
- Día de pago mensual configurable
- Estados: activo, suspendido, cancelado, expirado
- Historial de pagos

## 📊 Módulo de Reportes

Reportes disponibles para administradores:

1. **Reporte Financiero**
   - Ingresos vs Egresos
   - Movimientos por tipo
   - Balance del período

2. **Reporte de Accesos**
   - Visitas por día
   - Tipo de acceso
   - Estadísticas de seguridad

3. **Reporte de Mantenimiento**
   - Incidencias por categoría
   - Tiempo de resolución
   - Estados de reportes

4. **Reporte de Residentes**
   - Ocupación de propiedades
   - Propietarios vs Inquilinos
   - Estadísticas generales

5. **Reporte de Membresías**
   - Membresías activas
   - Ingresos por plan
   - Distribución de planes

## 🔐 Roles y Permisos

### Módulo Financiero
- ✅ Superadmin: Acceso completo
- ✅ Administrador: Acceso completo
- ❌ Guardia: Sin acceso
- ❌ Residente: Sin acceso

### Módulo de Membresías
- ✅ Superadmin: Acceso completo
- ✅ Administrador: Acceso completo
- ❌ Guardia: Sin acceso
- ❌ Residente: Sin acceso

### Módulo de Reportes
- ✅ Superadmin: Acceso completo
- ✅ Administrador: Acceso completo
- ❌ Guardia: Sin acceso
- ❌ Residente: Sin acceso

### Sistema de Auditoría
- ✅ Superadmin: Acceso completo
- ❌ Todos los demás: Sin acceso

## 🔧 Solución de Problemas

### Error: "Table doesn't exist"
**Solución:** Ejecuta el script de migración `database/migrations/001_add_new_features.sql`

### Error: "Column 'house_number' not found"
**Solución:** El script de migración no se ejecutó correctamente. Ejecuta manualmente:
```sql
ALTER TABLE users ADD COLUMN house_number VARCHAR(20) AFTER phone;
```

### No aparecen los nuevos menús
**Solución:** 
1. Limpia el caché del navegador
2. Verifica que el archivo `app/views/layouts/sidebar.php` esté actualizado
3. Cierra sesión y vuelve a iniciar sesión

### Las gráficas no se muestran
**Solución:** 
1. Verifica tu conexión a internet (se usa Chart.js desde CDN)
2. Revisa la consola del navegador para errores de JavaScript

### Error al crear usuario sin username
**Solución:**
1. Verifica que el archivo `app/controllers/UsersController.php` esté actualizado
2. El username se genera automáticamente del email

## 📝 Mantenimiento

### Limpiar logs antiguos
Los logs de auditoría crecerán con el tiempo. Para limpiar logs antiguos:
```sql
DELETE FROM audit_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 6 MONTH);
```

O usa el método en el controller:
```
/audit/cleanup
```

### Agregar nuevos tipos de movimiento
```sql
INSERT INTO financial_movement_types (name, description, category) 
VALUES ('Nuevo Tipo', 'Descripción', 'ingreso');
```

### Crear nuevos planes de membresía
```sql
INSERT INTO membership_plans (name, description, monthly_cost, benefits) 
VALUES (
    'Plan Custom', 
    'Descripción del plan', 
    2000.00, 
    '["Beneficio 1", "Beneficio 2", "Beneficio 3"]'
);
```

## 📞 Soporte

Si encuentras algún problema durante la migración:
1. Revisa los logs del servidor en `/var/log/apache2/error.log`
2. Verifica los permisos de archivos y carpetas
3. Asegúrate de que todas las extensiones PHP necesarias estén instaladas
4. Consulta la documentación en README.md

## ✅ Checklist de Migración

- [ ] Ejecutar script de migración SQL
- [ ] Verificar que todas las tablas se crearon correctamente
- [ ] Confirmar que los datos existentes se migraron
- [ ] Probar inicio de sesión con diferentes roles
- [ ] Verificar acceso a los nuevos módulos
- [ ] Crear un movimiento financiero de prueba
- [ ] Crear una membresía de prueba
- [ ] Verificar que las gráficas se muestran correctamente
- [ ] Probar el formulario de nuevo usuario
- [ ] Verificar el botón de Hikvision en dispositivos
- [ ] Revisar que los residentes pueden generar accesos
- [ ] Limpiar datos de prueba si es necesario

## 🎉 ¡Listo!

Una vez completados todos los pasos, el sistema estará actualizado con todas las nuevas funcionalidades:

✅ Módulo Financiero con gráficas
✅ Sistema de Membresías
✅ Módulo de Reportes
✅ Sistema de Auditoría
✅ Botón Hikvision
✅ Formulario de usuario mejorado
✅ Funcionalidad de residentes verificada

---

**Versión:** 2.0
**Fecha:** 2025-11-23
**Autor:** Sistema ERP Residencial
