# ✅ Lista de Verificación de Pruebas

## 📋 Instrucciones
Marcar cada elemento cuando se haya probado y funcione correctamente.

---

## 1️⃣ Gestión de Residentes

### 1.1 Ver Lista de Residentes
- [ ] Navegar a `/residents`
- [ ] Verificar que se muestra la lista de residentes
- [ ] Verificar que cada residente tiene columna de "Acciones"
- [ ] Verificar que hay 3 íconos: Ver (ojo), Suspender/Activar (usuario), Eliminar (basura)

### 1.2 Suspender Residente
- [ ] Click en ícono de suspender (usuario con slash)
- [ ] Aparece confirmación con nombre del residente
- [ ] Confirmar acción
- [ ] Verificar mensaje de éxito
- [ ] Verificar que el estado cambió a "inactive" (rojo)
- [ ] Verificar que el ícono cambió a activar (check)

### 1.3 Activar Residente
- [ ] Click en ícono de activar (usuario con check)
- [ ] Aparece confirmación
- [ ] Confirmar acción
- [ ] Verificar mensaje de éxito
- [ ] Verificar que el estado cambió a "active" (verde)

### 1.4 Eliminar Residente
- [ ] Click en ícono de eliminar (basura)
- [ ] Aparece confirmación con advertencia
- [ ] Confirmar acción
- [ ] Verificar mensaje de éxito
- [ ] Verificar que el residente ya no aparece en la lista

### 1.5 Ver Detalles
- [ ] Click en ícono de ver (ojo)
- [ ] Redirige a `/residents/viewDetails/{id}`
- [ ] Se muestran detalles completos del residente

---

## 2️⃣ Búsqueda Global

### 2.1 Buscar Residente
- [ ] En la barra superior, usar el campo de búsqueda
- [ ] Escribir nombre de residente (mínimo 2 caracteres)
- [ ] Aparece dropdown con resultados
- [ ] Los residentes aparecen en sección "Residentes"
- [ ] Click en un resultado
- [ ] Redirige correctamente a `/residents/viewDetails/{id}`
- [ ] NO redirige a `/users`

### 2.2 Buscar por Email
- [ ] Buscar por email de residente
- [ ] Verificar que encuentra el residente correcto
- [ ] Click redirige correctamente

### 2.3 Buscar por Teléfono
- [ ] Buscar por número de teléfono
- [ ] Verificar que encuentra el residente correcto
- [ ] Click redirige correctamente

### 2.4 Buscar Usuarios No Residentes
- [ ] Buscar admin o guardia por nombre
- [ ] Aparecen en sección "Usuarios"
- [ ] Click redirige a `/users/view/{id}`

---

## 3️⃣ Portal del Residente

### 3.1 Menú Lateral
- [ ] Iniciar sesión como residente
- [ ] Verificar que el menú lateral muestra:
  - [ ] "Mis Pagos" (ícono tarjeta)
  - [ ] "Generar Accesos" (ícono QR)
  - [ ] "Mis Accesos" (ícono lista)

### 3.2 Mis Pagos
- [ ] Click en "Mis Pagos"
- [ ] Redirige a `/residents/myPayments`
- [ ] Se muestra lista de pagos del residente
- [ ] Se muestra resumen de adeudos
- [ ] Se pueden ver pagos pendientes, pagados y vencidos

### 3.3 Generar Accesos
- [ ] Click en "Generar Accesos"
- [ ] Redirige a `/residents/generateAccess`
- [ ] Formulario permite seleccionar:
  - [ ] Tipo de pase (uso único, temporal, permanente)
  - [ ] Fechas válidas desde/hasta
  - [ ] Usos máximos
  - [ ] Notas
- [ ] Llenar formulario y generar
- [ ] Verificar mensaje de éxito
- [ ] Redirige a "Mis Accesos"

### 3.4 Mis Accesos
- [ ] Click en "Mis Accesos"
- [ ] Redirige a `/residents/myAccesses`
- [ ] Se muestran todos los pases generados
- [ ] Cada pase muestra:
  - [ ] Badge de estado (activo, expirado, usado, cancelado)
  - [ ] Badge de tipo de pase
  - [ ] Código QR
  - [ ] Fechas de validez
  - [ ] Contador de usos
  - [ ] Notas
- [ ] Para pases activos, hay botón "Cancelar Pase"

### 3.5 Cancelar Pase
- [ ] Click en "Cancelar Pase" de un pase activo
- [ ] Aparece confirmación
- [ ] Confirmar
- [ ] Verificar mensaje de éxito
- [ ] El pase ahora muestra estado "cancelled"
- [ ] Ya no hay botón de cancelar

### 3.6 Realizar Pago con PayPal
- [ ] Desde "Mis Pagos", click en un pago pendiente
- [ ] Redirige a `/residents/makePayment/{id}`
- [ ] Se muestra:
  - [ ] Detalles del pago (propiedad, período, vencimiento)
  - [ ] Monto total destacado
  - [ ] Botón de PayPal (si está configurado)
  - [ ] Otras opciones de pago
- [ ] Si PayPal está habilitado:
  - [ ] Click en botón PayPal
  - [ ] Abre ventana de PayPal
  - [ ] Completar pago de prueba
  - [ ] Confirmar que procesa correctamente

---

## 4️⃣ Reset de Contraseña

### 4.1 Solicitar Reset
- [ ] Cerrar sesión
- [ ] En login, click "¿Olvidaste tu contraseña?"
- [ ] Redirige a `/auth/forgotPassword`
- [ ] Ingresar email válido
- [ ] Submit
- [ ] Verificar que aparece mensaje: "Se ha enviado un enlace..."
- [ ] **IMPORTANTE**: Verificar que NO se muestra el enlace en pantalla
- [ ] Revisar bandeja de entrada del email

### 4.2 Recibir Email
- [ ] Email recibido con asunto "Recuperación de Contraseña"
- [ ] Email contiene:
  - [ ] Nombre del destinatario
  - [ ] Botón "Restablecer Contraseña"
  - [ ] Enlace alternativo copiable
  - [ ] Aviso de expiración (1 hora)
- [ ] Email tiene buen formato HTML

### 4.3 Usar Enlace
- [ ] Click en botón o enlace del email
- [ ] Redirige a `/auth/resetPassword?token=...`
- [ ] Formulario para nueva contraseña
- [ ] Ingresar nueva contraseña dos veces
- [ ] Submit
- [ ] Verificar mensaje de éxito
- [ ] Redirige a login

### 4.4 Iniciar Sesión con Nueva Contraseña
- [ ] En login, usar nueva contraseña
- [ ] Login exitoso

---

## 5️⃣ Soporte Técnico Público

### 5.1 Acceso Sin Autenticación
- [ ] Cerrar sesión (o usar ventana incógnito)
- [ ] Navegar a `/support`
- [ ] Página carga sin pedir login
- [ ] Se muestra información de soporte

### 5.2 Información Visible
- [ ] Título y descripción
- [ ] Tarjeta de Email con link mailto
- [ ] Tarjeta de Teléfono (si configurado)
- [ ] Tarjeta de Horario de atención
- [ ] Sección de FAQs

### 5.3 FAQs Interactivas
- [ ] Click en pregunta 1
- [ ] Se expande mostrando respuesta
- [ ] Ícono cambia a chevron-up
- [ ] Click nuevamente colapsa la respuesta
- [ ] Probar con todas las preguntas

### 5.4 Enlace de Login
- [ ] En header hay enlace "Iniciar Sesión"
- [ ] Click redirige a `/auth/login`

---

## 6️⃣ Recordatorios de Pago

### 6.1 Preparar Datos de Prueba
```sql
-- Crear un pago que vence mañana
INSERT INTO maintenance_fees (property_id, period, amount, due_date, status)
VALUES (1, '2024-12', 1500.00, DATE_ADD(CURDATE(), INTERVAL 1 DAY), 'pending');
```

### 6.2 Ejecutar Script Manualmente
```bash
php cron/send_payment_reminders.php
```

### 6.3 Verificar Ejecución
- [ ] Script ejecuta sin errores
- [ ] Se muestra: "Recordatorios generados"
- [ ] Se muestra: "Encontrados X recordatorios"
- [ ] Se muestra: "Enviando recordatorio a ..."
- [ ] Se muestra: "✓ Recordatorio enviado exitosamente"
- [ ] Se muestra: "Proceso completado. Enviados: X"

### 6.4 Verificar Email Recibido
- [ ] Email recibido con asunto "Recordatorio de Pago"
- [ ] Email contiene:
  - [ ] Nombre del residente
  - [ ] Propiedad
  - [ ] Período
  - [ ] Fecha de vencimiento
  - [ ] Monto destacado
  - [ ] Opciones de pago
  - [ ] Botón "Ver mis Pagos"
- [ ] Email tiene buen formato HTML

### 6.5 Verificar Base de Datos
```sql
-- Ver recordatorio registrado
SELECT * FROM payment_reminders WHERE sent = 1 ORDER BY sent_at DESC LIMIT 1;
```
- [ ] Registro existe
- [ ] status = 'sent'
- [ ] sent = 1
- [ ] sent_at tiene timestamp correcto

### 6.6 Cron Job Programado
```bash
crontab -l
```
- [ ] Existe entrada para payment_reminders.php
- [ ] Configurado para ejecutar a las 8:00 AM
- [ ] Redirige output a log

---

## 7️⃣ Configuraciones

### 7.1 Email
- [ ] Login como SuperAdmin
- [ ] Ir a Configuración > Email
- [ ] Verificar campos configurados:
  - Host: janetzy.shop
  - Puerto: 465
  - Usuario: hola@janetzy.shop
  - Email From: hola@janetzy.shop
- [ ] Enviar email de prueba
- [ ] Email recibido correctamente

### 7.2 PayPal
- [ ] Ir a Configuración > Pagos
- [ ] Campos disponibles para:
  - [ ] Habilitar PayPal
  - [ ] Modo (sandbox/live)
  - [ ] Client ID
  - [ ] Secret
- [ ] Guardar configuración

### 7.3 Soporte
- [ ] Ir a Configuración > Soporte
- [ ] Campos disponibles para:
  - [ ] Email de soporte
  - [ ] Teléfono
  - [ ] Horario
  - [ ] URL pública
- [ ] Se muestra enlace a la vista pública
- [ ] Click en enlace abre `/support` en nueva pestaña

### 7.4 Optimización
- [ ] Ir a Configuración > Optimización
- [ ] Se muestran opciones:
  - [ ] Caché habilitado
  - [ ] TTL de caché
  - [ ] Query cache
  - [ ] Registros por página
  - [ ] Optimización de imágenes
  - [ ] Lazy loading
- [ ] Se muestran estadísticas del sistema:
  - [ ] Tamaño de BD
  - [ ] Total usuarios
  - [ ] Total residentes
  - [ ] Total visitas
- [ ] Botón "Ejecutar Optimización"
- [ ] Click ejecuta y muestra mensaje de éxito

---

## 8️⃣ Base de Datos

### 8.1 Verificar Tablas
```sql
SHOW TABLES LIKE 'resident_access_passes';
SHOW TABLES LIKE 'payment_reminders';
```
- [ ] Ambas tablas existen

### 8.2 Verificar Procedimiento
```sql
SHOW PROCEDURE STATUS WHERE Name = 'generate_payment_reminders';
```
- [ ] Procedimiento existe

### 8.3 Verificar Vista
```sql
SELECT * FROM property_debt_summary LIMIT 5;
```
- [ ] Vista existe y retorna datos

### 8.4 Verificar Índices
```sql
SHOW INDEX FROM residents;
SHOW INDEX FROM maintenance_fees;
```
- [ ] Índices adicionales existen

### 8.5 Verificar Configuraciones
```sql
SELECT * FROM system_settings WHERE setting_key LIKE 'paypal_%';
SELECT * FROM system_settings WHERE setting_key LIKE 'support_%';
SELECT * FROM system_settings WHERE setting_key LIKE 'cache_%';
```
- [ ] Todas las configuraciones existen

---

## 9️⃣ Regresión - Funcionalidad Existente

### 9.1 Login
- [ ] Login de superadmin funciona
- [ ] Login de admin funciona
- [ ] Login de residente funciona
- [ ] Login de guardia funciona
- [ ] Login con credenciales incorrectas falla apropiadamente

### 9.2 Dashboard
- [ ] Dashboard carga correctamente
- [ ] Estadísticas se muestran
- [ ] Gráficas cargan (si aplica)
- [ ] Enlaces rápidos funcionan

### 9.3 Otros Módulos
- [ ] Amenidades funciona
- [ ] Mantenimiento funciona
- [ ] Seguridad funciona
- [ ] Vehículos funciona
- [ ] Financiero funciona

---

## 🎯 Resumen de Resultados

**Total de pruebas**: _____  
**Pruebas exitosas**: _____  
**Pruebas fallidas**: _____  
**Errores críticos**: _____

### Notas Adicionales
```
[Espacio para notas del testing]




```

### Firma y Fecha
- **Probado por**: _______________
- **Fecha**: _______________
- **Versión**: 1.1.0
- **Ambiente**: [ ] Desarrollo [ ] Staging [ ] Producción

---

## 📝 Plantilla de Reporte de Error

Si encuentras un error, documentarlo así:

**Error #**: ___  
**Módulo**: _______________  
**Descripción**: _______________  
**Pasos para reproducir**:
1. 
2. 
3. 

**Resultado esperado**: _______________  
**Resultado actual**: _______________  
**Severidad**: [ ] Crítico [ ] Alto [ ] Medio [ ] Bajo  
**Screenshots**: (adjuntar si es posible)
