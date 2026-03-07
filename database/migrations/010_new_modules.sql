-- Add is_unforeseen and evidence_file to financial_movements
ALTER TABLE financial_movements 
  ADD COLUMN IF NOT EXISTS is_unforeseen TINYINT(1) DEFAULT 0 AFTER notes,
  ADD COLUMN IF NOT EXISTS evidence_file VARCHAR(255) DEFAULT NULL AFTER is_unforeseen;

-- Add is_vigilance_committee to users
ALTER TABLE users 
  ADD COLUMN IF NOT EXISTS is_vigilance_committee TINYINT(1) DEFAULT 0 AFTER last_login;

-- Create uploads directory marker (handled in PHP)
