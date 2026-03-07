-- Your existing SQL code up to this point remains unchanged

SET @tableName = 'users';
SET @columnName = 'is_vigilance_committee';
SET @columnDefinition = 'TINYINT(1) DEFAULT 0';

SELECT COUNT(*) INTO @columnExists 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_NAME = @tableName AND COLUMN_NAME = @columnName;

IF @columnExists = 0 THEN
    SET @alterSQL = CONCAT('ALTER TABLE ', @tableName, ' ADD COLUMN ', @columnName, ' ', @columnDefinition, ' AFTER last_login;');
    PREPARE stmt FROM @alterSQL;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END IF;

-- Your existing SQL code after this point remains unchanged