-- Add url_en and url_nl columns to sso_provider_roles table
ALTER TABLE sso_provider_roles
ADD COLUMN support_url_en VARCHAR(255) DEFAULT NULL,
ADD COLUMN support_url_nl VARCHAR(255) DEFAULT NULL;
