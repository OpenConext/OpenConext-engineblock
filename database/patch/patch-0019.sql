--
-- Create table for EngineBlock metadata (pushed from Janus)
--

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
  certificates                        LONGTEXT           NOT NULL
  COMMENT '(DC2Type:array)',
  workflow_state                      VARCHAR(255)       NOT NULL,
  contact_persons                     LONGTEXT           NOT NULL
  COMMENT '(DC2Type:array)',
  name_id_format                      VARCHAR(255)       NOT NULL,
  name_id_formats                     LONGTEXT           NOT NULL
  COMMENT '(DC2Type:array)',
  publish_in_edu_gain_date            DATE               NOT NULL,
  disable_scoping                     TINYINT(1)         NOT NULL,
  additional_logging                  TINYINT(1)         NOT NULL,
  requests_must_be_signed             TINYINT(1)         NOT NULL,
  response_processing_service_binding VARCHAR(255)       NOT NULL,
  type                                VARCHAR(255)       NOT NULL,
  PRIMARY KEY (id)
)
  DEFAULT CHARACTER SET utf8
  COLLATE utf8_unicode_ci
  ENGINE = InnoDB;
