ALTER TABLE sso_provider_roles ADD policy_enforcement_decision_required TINYINT(1) DEFAULT NULL AFTER shib_md_scopes;
