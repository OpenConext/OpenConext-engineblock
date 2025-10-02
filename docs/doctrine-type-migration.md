# Doctrine Type Migration Tracking

Goal: Replace deprecated Doctrine types (ARRAY, OBJECT, json_array, simple_array) with supported custom JSON based Doctrine types as per historic `doctrine-types.diff` while upgrading to doctrine/dbal ^4.

## Legend
- Pending: Not yet implemented
- Implemented: Code changes in place (entity mapping + custom Type class + registration)
- Verified: Basic runtime / tests exercise the mapping successfully
- Data Migration Needed: Existing production data must be converted from legacy serialized PHP format to new JSON format

## Inventory

| Entity | Field / Column | Old Doctrine Type | New Doctrine Type / Strategy | Custom Type Class | Status | Notes |
|--------|----------------|-------------------|------------------------------|-------------------|--------|-------|
| AbstractRole | logo | OBJECT | engineblock_logo | LogoType | Implemented | |
| AbstractRole | organization_nl_name | OBJECT | engineblock_organization | OrganizationType | Implemented | |
| AbstractRole | organization_en_name | OBJECT | engineblock_organization | OrganizationType | Implemented | |
| AbstractRole | organization_pt_name | OBJECT | engineblock_organization | OrganizationType | Implemented | |
| AbstractRole | certificates | ARRAY (serialized PHP) | engineblock_certificate_array | CertificateArrayType | Implemented | Uses JSON array of cert data |
| AbstractRole | contact_persons | ARRAY | engineblock_contact_person_array | ContactPersonArrayType | Implemented | |
| AbstractRole | name_id_formats | ARRAY | JSON (native) | n/a | Implemented | Switched to native json column |
| AbstractRole | single_logout_service | OBJECT | engineblock_service | ServiceType | Implemented | |
| ServiceProvider | attribute_release_policy | ARRAY | engineblock_attribute_release_policy | AttributeReleasePolicyType | Implemented | |
| ServiceProvider | assertion_consumer_services | ARRAY | engineblock_indexed_service_array | IndexedServiceArrayType | Implemented | |
| ServiceProvider | allowed_idp_entity_ids | ARRAY | JSON (native) | n/a | Implemented | |
| ServiceProvider | requested_attributes | ARRAY | engineblock_requested_attribute_array | RequestedAttributeArrayType | Implemented | |
| IdentityProvider | single_sign_on_services | ARRAY | engineblock_service_array | ServiceArrayType | Implemented | |
| IdentityProvider | shib_md_scopes | ARRAY | engineblock_shib_md_scope_array | ShibMdScopeArrayType | Implemented | |

## Additional Custom Types (already present)
- engineblock_collab_person_id
- engineblock_collab_person_uuid
- engineblock_metadata_coins
- engineblock_metadata_mdui

## Tasks Checklist
1. Implement missing custom Type classes (serialize to JSON, validate VO instances). (Done)
2. Register new types in `config/packages/doctrine.yaml`. (Done)
3. Update entity attribute mappings to use new types. (Done)
4. Run unit test suite to validate no mapping errors. (In progress)
5. (Optional) Add migration or backward-compatible deserialization logic for legacy serialized data. (Deferred) – requires DB state inspection
6. Mark implemented items above and iterate until all Verified. (In progress)

## Data Migration Strategy (Draft)
For production rollout we will need a Doctrine migration that:
1. Reads rows from `sso_provider_roles_eb5`.
2. For each legacy column still containing serialized PHP (detected by prefix `a:` or `O:`), unserialize (allow-listed classes) and re-save entity forcing Doctrine to persist JSON representation.
3. Alternatively perform an in-place PHP script using the application's ORM bootstrap.

NOTE: A follow-up migration script still needs to be written once DB contents are reviewed. Current code assumes fresh schema or manual conversion.

---
Progress updates will be appended below.

## Progress Log
- [x] Initialized tracking file.
- [x] Implemented all custom Doctrine types and updated mappings.
- [ ] Executed full test suite (pending run / verification).
