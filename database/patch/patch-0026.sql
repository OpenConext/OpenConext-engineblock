-- add attribute_aggregation_required column to sso_provider_roles
ALTER TABLE sso_provider_roles ADD attribute_aggregation_required TINYINT(1) DEFAULT NULL;
