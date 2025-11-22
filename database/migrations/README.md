# Database Migrations

This directory contains SQL migration files for the IDresidencial database.

## How to Apply Migrations

Execute the migration files in order:

```bash
# For MySQL command line
mysql -u username -p database_name < 001_add_audit_logs.sql

# Or via phpMyAdmin
# 1. Select the database
# 2. Go to SQL tab
# 3. Copy and paste the content of the migration file
# 4. Execute
```

## Migration Files

### 001_add_audit_logs.sql
Creates the `audit_logs` table for tracking system activities:
- User actions (login, create, update, delete)
- IP addresses and user agents
- Detailed descriptions of activities
- Links to affected records

**Required for**: Audit/Logs admin module

## Notes

- Migrations should be run in numerical order
- Each migration is idempotent (can be run multiple times safely)
- Backup your database before running migrations in production
