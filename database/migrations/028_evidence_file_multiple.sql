-- Change evidence_file column to TEXT to support JSON array of multiple file paths
ALTER TABLE financial_movements 
  MODIFY COLUMN evidence_file TEXT DEFAULT NULL;
