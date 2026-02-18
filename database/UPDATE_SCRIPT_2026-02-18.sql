-- SQL script to update indexes in MySQL

-- Dropping existing indexes safely
DROP INDEX IF EXISTS index_name ON table_name;

-- Adding new index
ALTER TABLE table_name ADD INDEX index_name (column_name);
-- Repeat the above for other indexes as necessary
