-- Create indexes on sso_provider_roles for PUSH metadata
CREATE INDEX idx_sso_provider_roles_type ON sso_provider_roles (type);
CREATE INDEX idx_sso_provider_roles_entity_id ON sso_provider_roles (entity_id);
CREATE INDEX idx_sso_provider_roles_publish_in_edugain ON sso_provider_roles (publish_in_edugain);
CREATE UNIQUE INDEX idx_sso_provider_roles_entity_id_type ON sso_provider_roles (type, entity_id);
