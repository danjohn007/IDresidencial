ALTER TABLE users 
MODIFY role ENUM('superadmin','administrador','guardia','residente','proveedor') 
COLLATE utf8mb4_unicode_ci 
NOT NULL;