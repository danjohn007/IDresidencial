# Instrucciones para Aplicar la Migración

## Opción 1: Ejecutar desde el navegador

1. Accede a la siguiente URL desde tu navegador:
   ```
   https://residencial.digital/sistema/11/apply_service_request_migration.php
   ```

2. Verás un mensaje de confirmación cuando la migración se complete exitosamente.

## Opción 2: Ejecutar manualmente en MySQL

1. Accede a phpMyAdmin o tu cliente MySQL preferido

2. Selecciona la base de datos `residenc_residencial`

3. Ejecuta el siguiente SQL:

```sql
ALTER TABLE `provider_service_requests`
ADD COLUMN `image_path` VARCHAR(255) DEFAULT NULL COMMENT 'Optional image attachment for service request'
AFTER `notes`;
```

## Opción 3: Desde la terminal con PHP

Si tienes PHP en tu PATH, ejecuta:

```bash
cd d:\Musica\UAQ\1_impacto_digital\residencial\IDresidencial
php apply_service_request_migration.php
```

## Verificar que la migración fue exitosa

Ejecuta esta consulta en MySQL:

```sql
DESCRIBE provider_service_requests;
```

Deberías ver el campo `image_path` en la lista de columnas.

---

## Resumen de cambios implementados

### 1. ✅ Campo de imagen opcional agregado
- Los residentes ahora pueden adjuntar una imagen a sus solicitudes
- Formatos permitidos: JPG, PNG, WEBP
- Tamaño máximo: 5MB

### 2. ✅ Select de Categoría arreglado
- Cambiado de `<input type="text" list="datalist">` a `<select>`
- Ahora se puede cambiar la opción seleccionada sin problemas

### 3. ✅ Mejoras en el manejo de errores
- Mensajes de error más descriptivos del controlador
- Validación de tipo y tamaño de archivo
- Mejor logging de errores en el servidor

### 4. ⚠️ Nota sobre el error "Unexpected token '<'"
Este error indica que el servidor está devolviendo HTML en lugar de JSON. 
Posibles causas:
- El campo `image_path` no existe en la tabla (se soluciona aplicando la migración)
- Error PHP no capturado antes del `header('Content-Type: application/json')`
- Problema con la sesión

**Despues de aplicar la migración, el error debería desaparecer.**
