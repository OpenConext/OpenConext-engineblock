-- Add consent_type column to consent table
ALTER TABLE consent
ADD COLUMN consent_type VARCHAR(20)
DEFAULT 'explicit';
