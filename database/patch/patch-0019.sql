--
-- Create table for EngineBlock metadata (pushed from Janus)
--
DROP TABLE IF EXISTS sso_provider_roles;
CREATE TABLE sso_provider_roles (
  id                                  INT AUTO_INCREMENT NOT NULL,
  entity_id                           VARCHAR(255)       NOT NULL,
  name_nl                             VARCHAR(255)       NOT NULL,
  name_en                             VARCHAR(255)       NOT NULL,
  description_nl                      VARCHAR(255)       NOT NULL,
  description_en                      VARCHAR(255)       NOT NULL,
  display_name_nl                     VARCHAR(255)       NOT NULL,
  display_name_en                     VARCHAR(255)       NOT NULL,
  logo                                LONGTEXT           NOT NULL,
  organization_nl_name                VARCHAR(255)       NOT NULL,
  organization_en_name                VARCHAR(255)       NOT NULL,
  keywords_nl                         VARCHAR(255)       NOT NULL,
  keywords_en                         VARCHAR(255)       NOT NULL,
  publish_in_edugain                  TINYINT(1)         NOT NULL,
  certificates                        LONGTEXT           NOT NULL COMMENT '(DC2Type:array)',
  workflow_state                      VARCHAR(255)       NOT NULL,
  contact_persons                     LONGTEXT           NOT NULL COMMENT '(DC2Type:array)',
  name_id_format                      VARCHAR(255)       NOT NULL,
  name_id_formats                     LONGTEXT           NOT NULL COMMENT '(DC2Type:array)',
  publish_in_edu_gain_date            DATE               NOT NULL,
  disable_scoping                     TINYINT(1)         NOT NULL,
  additional_logging                  TINYINT(1)         NOT NULL,
  requests_must_be_signed             TINYINT(1)         NOT NULL,
  response_processing_service_binding VARCHAR(255)       NOT NULL,
  manipulation                        LONGTEXT           NOT NULL,
  type                                VARCHAR(255)       NOT NULL,
  attribute_release_policy            LONGTEXT     DEFAULT NULL COMMENT '(DC2Type:array)',
  assertion_consumer_services         LONGTEXT     DEFAULT NULL COMMENT '(DC2Type:array)',
  is_transparent_issuer               TINYINT(1)   DEFAULT NULL,
  is_trusted_proxy                    TINYINT(1)   DEFAULT NULL,
  implicit_vo_id                      VARCHAR(255) DEFAULT NULL,
  display_unconnected_idps_wayf       TINYINT(1)   DEFAULT NULL,
  is_consent_required                 TINYINT(1)   DEFAULT NULL,
  terms_of_service_url                VARCHAR(255) DEFAULT NULL,
  skip_denormalization                TINYINT(1)   DEFAULT NULL,
  allowed_idp_entity_ids              LONGTEXT     DEFAULT NULL COMMENT '(DC2Type:array)',
  requested_attributes                LONGTEXT     DEFAULT NULL COMMENT '(DC2Type:array)',
  enabled_in_wayf                     TINYINT(1)   DEFAULT NULL,
  single_sign_on_services             LONGTEXT     DEFAULT NULL COMMENT '(DC2Type:array)',
  guest_qualifier                     VARCHAR(255) DEFAULT NULL,
  schac_home_organization             VARCHAR(255) DEFAULT NULL,
  sps_entity_ids_without_consent      LONGTEXT     DEFAULT NULL COMMENT '(DC2Type:array)',
  hidden                              TINYINT(1)   DEFAULT NULL,
  shib_md_scopes                      LONGTEXT     DEFAULT NULL COMMENT '(DC2Type:array)',
  PRIMARY KEY (id)
)
  DEFAULT CHARACTER SET utf8
  COLLATE utf8_unicode_ci
  ENGINE = InnoDB;
