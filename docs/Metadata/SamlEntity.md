# SamlEntity

Note: this document is best viewed in raw format.

This document was used to identify, structure and "translate" the current inflexible metadata representation to the new
 structure metadata representation. This links the existing database fields to the new ValueObjects.
 
This document is to be considered deprecated, but for now is kept as it may be informative whilst the new metadata
 structure has not been rolled out yet.

## Entity Data Definition (EntityAttributes)

- [X] id INT(11) PRIMARY KEY NOT NULL,                              **id**
- [X]                                                               **SamlEntityUuid**
- [X] entity_id VARCHAR(255) NOT NULL,                              **Entity\EntityId**
- [X] type VARCHAR(255) NOT NULL,                                   **Entity\EntityType**

## SAML compliance

### SP (ServiceProviderSamlConfiguration)
- [X] assertion_consumer_services LONGTEXT COMMENT '(DC2Type:array)',   _set<>_
- [X] ^- AssertionConsumerServices  
  Represented as: `assertion_consumer_services: a:1:{i:0;O:55:"OpenConext\Component\EngineBlockMetadata\IndexedService":4:{s:12:"serviceIndex";i:0;s:9:"isDefault";N;s:7:"binding";s:46:"urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST";s:8:"location";s:45:"https://nyenrode.shop.canon-bs.nl/extern/saml";}}`

### [X] IdP (IdentityProviderSamlConfiguration)
- [X] shib_md_scopes LONGTEXT COMMENT '(DC2Type:array)',                _list<string>_ **keep, already implemented**
- [X] single_sign_on_services LONGTEXT COMMENT '(DC2Type:array)',       _list<>_
- [X] ^- SingleSignOnServices  
  Represented as: `a:2:{i:0;O:48:"OpenConext\Component\EngineBlockMetadata\Service":2:{s:7:"binding";s:50:"urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect";s:8:"location";s:37:"https://teamwerk.fed.vumc.nl/adfs/ls/";}i:1;O:48:"OpenConext\Component\EngineBlockMetadata\Service":2:{s:7:"binding";s:46:"urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST";s:8:"location";s:37:"https://teamwerk.fed.vumc.nl/adfs/ls/";}}`

### [X] BOTH (EntitySamlConfiguration)
- [X] certificates LONGTEXT NOT NULL COMMENT '(DC2Type:array)',         _list<string>_  **keep <SAML2 certs?>** <- custom classes with toSaml2Cert
- [X] single_logout_service LONGTEXT COMMENT '(DC2Type:object)',        _Endpoint_
- [X] response_processing_service_binding VARCHAR(255),                 _Endpoint_ (optional, seems to be used interal only?
- [X] name_id_format VARCHAR(255),                                      _NameIdFormat_        **keep <preferred NameID format>**  
- [X] name_id_formats LONGTEXT NOT NULL COMMENT '(DC2Type:array)',      _NameIdFormatList_  **keep <allowed NameID formats>**
- [X] + ContactPersonList (below)
- [X] + Organization (below) - optional field, may not exist

#### Organization

- [X] organization_nl_name LONGTEXT COMMENT '(DC2Type:object)',     **keep <is organization, if FQ>**
- [X] organization_en_name LONGTEXT COMMENT '(DC2Type:object)',     **keep <is organization, if FQ>**
- [X] display_name_nl VARCHAR(255) NOT NULL,                        **keep @DEPRECATE <used in WAYF> -> OrganisationDisplayName should be used instead**
- [X] display_name_en VARCHAR(255) NOT NULL,                        **keep @DEPRECATE <used in WAYF> -> OrganisationDisplayName should be used instead**

#### ContactPersonList

- [X] contact_persons LONGTEXT NOT NULL COMMENT '(DC2Type:array)',

---

## Service Attribute Configuration (IdP/Sp)

Mostly service attributes used for view (UI) related affairs 

### IdP (IdentityProviderAttributes) (IdentityProviderVisibilitySettings)
- [X] keywords_nl VARCHAR(255) NOT NULL,                            _string_    **keep <used for metadata AND WAYF>**
- [X] keywords_en VARCHAR(255) NOT NULL,                            _string_    **keep <used for metadata AND WAYF>**
- [X] hidden TINYINT(1),                                            **keep**     *****^ Add to***
- [X] enabled_in_wayf TINYINT(1),                                   **keep**

### SP (ServiceProviderAttributes)
- [X] terms_of_service_url VARCHAR(255),                            _string_    **keep <used for CONSENT and consent API>**
- [X] support_url_en VARCHAR(255),                                  _string_    **keep <used for consent API>**
    - LocalizedUri
- [X] support_url_nl VARCHAR(255),                                  _string_    **keep <used for consent API>**
    - LocalizedUri
- [X] ^- both captured in LocalizedSupportUrls

### BOTH (EntityAttributes)
- [X] description_nl VARCHAR(255) NOT NULL,                         _string_    **keep <used for metadata>**
- [X] description_en VARCHAR(255) NOT NULL,                         _string_    **keep <used for metadata>**
- [X] logo LONGTEXT NOT NULL COMMENT '(DC2Type:object)',            **keep**
- [X] name_nl VARCHAR(255) NOT NULL,                                **keep <shown i.s.o. organizationDisplayName: "dienst">**
    - LocalizedName
- [X] name_en VARCHAR(255) NOT NULL,                                **keep <shown i.s.o. organizationDisplayName: "dienst">**
    - LocalizedName
- [X] ^- both captured in LocalizedServiceName

---

## Connections < To create

- [X] allowed_idp_entity_ids LONGTEXT COMMENT '(DC2Type:array)',    **keep _EntitySet in ServiceProvider_**

## Custom Configuration (EngineBlock)

### SP (ServiceProviderConfiguration)
- [X] display_unconnected_idps_wayf TINYINT(1),                     **keep**
- [X] is_trusted_proxy TINYINT(1),                                  **keep**
- [X] is_transparent_issuer TINYINT(1),                             **keep**
- [X] is_consent_required TINYINT(1),                               **keep**
- [X] skip_denormalization TINYINT(1),                              **keep**
- [X] policy_enforcement_decision_required TINYINT(1),              **keep**
- [X] attribute_aggregation_required TINYINT(1)                     **keep**
- [X] attribute_release_policy LONGTEXT COMMENT '(DC2Type:array)',  **keep**

### [X] IdP (IdentityProviderConfiguration) < TO CREATE

- [X] sps_entity_ids_without_consent LONGTEXT COMMENT '(DC2Type:array)',    **keep _EntitySet_**
- [X] guest_qualifier VARCHAR(255),

### Both < (Entity Configuration)
- [X] additional_logging TINYINT(1) NOT NULL,                       **keep**
- [X] disable_scoping TINYINT(1) NOT NULL,                          **keep**
- [X] requests_must_be_signed TINYINT(1) NOT NULL,                  **keep**
- [X] manipulation LONGTEXT NOT NULL,                               **keep**    ******^ add to*****
- [X] workflow_state VARCHAR(255) NOT NULL,                         **keep**

## Deprecated Fields

- [-] implicit_vo_id VARCHAR(255),                                  **@DEPRECATED vo auth stuff [REMOVE]**
- [-] publish_in_edugain TINYINT(1) NOT NULL,                       **@DEPRECATED edugain metadata stuff [REMOVE]**
- [-] publish_in_edu_gain_date DATE,                                **@DEPRECATED edugain metadata stuff [REMOVE]**
- [-] schac_home_organization VARCHAR(255),                         **@DEPRECATED replaced with shibmd scope [REMOVE]** 
- [-] requested_attributes LONGTEXT COMMENT '(DC2Type:array)',      **@DEPRECATED edugain metadata stuff [REMOVE]**
