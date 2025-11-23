# Database Migrations

This directory contains SQL migration files for the IDresidencial database.

## How to Apply Migrations

Execute the migration files in order:

```bash
# For MySQL command line (ajusta el nombre de usuario y base de datos)
mysql -u your_username -p your_database_name < 001_system_improvements.sql

# Ejemplo con configuración por defecto:
mysql -u janetzy_residencial -p janetzy_residencial < 001_system_improvements.sql

# O simplemente:
mysql -u root -p erp_residencial < 001_system_improvements.sql

# Or via phpMyAdmin
# 1. Select your database (erp_residencial or janetzy_residencial)
# 2. Go to SQL tab
# 3. Copy and paste the content of the migration file
# 4. Execute
```

## Latest Migration: 001_system_improvements.sql (2024-11-23)

This comprehensive migration includes all the latest system improvements:

### New Features:
1. **Módulo de Fraccionamientos** - Sistema de gestión de subdivisiones/fraccionamientos
2. **Validaciones Pendientes** - Sistema de registro público con validación de email y aprobación de admin
3. **Historial de Pagos** - Tracking completo de pagos de residentes
4. **Adeudos Acumulados** - Gestión de balances y deudas
5. **Recordatorios de Pago** - Sistema automático de recordatorios
6. **Verificación de Email** - Tokens y validación de correos electrónicos
7. **Optimización del Sistema** - Configuraciones de rendimiento
8. **Vistas SQL** - Vista de dashboard para residentes

### Tables Created:
- `subdivisions` - Fraccionamientos
- `pending_validations` - Validaciones pendientes
- `resident_payment_history` - Historial de pagos
- `resident_balances` - Adeudos
- `payment_reminders` - Recordatorios
- `system_optimization` - Configuración de optimización
- `email_verifications` - Verificación de emails

### Tables Modified:
- `properties` - Agregado `subdivision_id`
- `residents` - Agregado `subdivision_id`
- `users` - Agregado `subdivision_id`, `email_verified`, `email_verified_at`
- `vehicles` - Agregado `subdivision_id`
- `maintenance_fees` - Agregado `reminder_sent`, `payment_confirmation`

### Performance Improvements:
- Nuevos índices en `access_logs`, `visits`, `reservations`, `maintenance_reports`
- Vista optimizada para dashboard de residentes

## Previous Migration Files

### 001_add_audit_logs.sql
Creates the `audit_logs` table for tracking system activities:
- User actions (login, create, update, delete)
- IP addresses and user agents
- Detailed descriptions of activities
- Links to affected records

### 002_add_devices_tables.sql
Creates tables for access control devices (HikVision, Shelly)

### 003_password_reset_and_fixes.sql
Password reset functionality and system fixes

## Notes

- ⚠️ **IMPORTANT**: Backup your database before running migrations in production
- Migrations should be run in numerical order
- Each migration is idempotent (can be run multiple times safely)
- The latest migration (001_system_improvements.sql) is comprehensive and includes all required changes
- Test in development environment first
