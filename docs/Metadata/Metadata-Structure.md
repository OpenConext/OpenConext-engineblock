# Metadata

Below an overview of the Metadata structure which will be used as internal representation of metadata.
Where relevant, additional information is listed.

## IdentityProvider

```
IdentityProvider
├── Entity                                      > Entity Descriptor
│   ├── EntityId
│   └── EntityType                              > saml20-sp|saml20-idp
├── IdentityProviderSamlConfiguration           > Saml Specific Configuration
│   ├── EntitySamlConfiguration
│   │   ├── NameIdFormat                        > preferred NameID format
│   │   ├── NameIdFormatList                    > allowed NameID formats - used in Metadata
│   │   │   └── NameIdFormat[]
│   │   ├── CertificateList
│   │   │   └── Certificate[]
│   │   ├── SingleLogoutService                 > represented as Endpoint(Binding, location(, responselocation))
│   │   ├── ResponseProcessingService           > represented as Endpoint(Binding, location(, responselocation))
│   │   ├── ContactPersonList                   > Used in Metadata
│   │   │   └── ContactPerson[]
│   │   │       ├── ContactType
│   │   │       ├── EmailAddressList
│   │   │       │   └── EmailAddress[]
│   │   │       ├── TelephoneNumberList
│   │   │       │   └── TelephoneNumber[]
│   │   │       ├── GivenName (optional)
│   │   │       ├── Surname (optional)
│   │   │       └── Company (optional)
│   │   └── Organization                        > Used in Metadata
│   │       ├── OrganizationNameList
│   │       │   └── OrganizationName[]          > (name, language)
│   │       ├── OrganizationDisplayNameList
│   │       │   └── OrganizationDisplayName[]   > (displayname, language)
│   │       └── OrganizationUrlList
│   │           └── OrganizationUrl[]           > (url, language)
│   ├── SingleSignOnServices
│   │   └── Endpoint[]                          > (Binding, location(, responselocation)) 
│   └── ShibbolethMetadataScopeList
│       └── ShibbolethMetadataScope[]           > (scope, isRegexp)
├── IdentityProviderConfiguration               > EngineBlock specific configuration for an IdentityProvider, used during login
│   ├── EntityConfiguration
│   │   ├── AttributeManipulationCode           > 
│   │   ├── WorkflowState                       > testaccepted|prodaccepted | required, no default
│   │   ├── requiresAdditionalLogging           > bool | default: false
│   │   ├── disableScoping                      > bool | default: false
│   │   └── requiresSignedRequests              > bool | default: false
│   ├── ServiceProvidersWithoutConsent          > Represented as EntitySet(Entity[])
│   │   └── Entity[]                            > (EntityId, EntityType)
│   └── GuestQualifier                          > (All|Some|None) | not req., default: All
└── IdentityProviderAttributes                  > Specific attributes for an IdentityProvider | all optional
    ├── EntityAttributes
    │   ├── LocalizedServiceName
    │   │   └── LocalizedName[]                 > (name, locale)
    │   ├── LocalizedDescription
    │   │   └── LocalizedText[]                 > (text, locale)
    │   └── Logo                                > (url, width, height)
    ├── isHidden                                > bool, whether or not IdP shows up in metadata and WAYF | default: false
    ├── enabledInWayf                           > bool, whether or not IdP shows up in WAYF (lower precedence than isHidden) | default: true
    └── Keywords
        └── LocalizedKeywords[]                 > (locale, string[])
```

## ServiceProvider

```
ServiceProvider
├── Entity                                      > Entity Descriptor
│   ├── EntityId
│   └── EntityType                              > saml20-sp|saml20-idp
├── ServiceProviderSamlConfiguration            > Saml Specific Configuration
│   ├── EntitySamlConfiguration
│   │   ├── NameIdFormat                        > preferred NameID format
│   │   ├── NameIdFormatList                    > allowed NameID formats - used in Metadata
│   │   │   └── NameIdFormat[]
│   │   ├── CertificateList
│   │   │   └── Certificate[]
│   │   ├── SingleLogoutService                 > represented as Endpoint(Binding, location(, responselocation))
│   │   ├── ResponseProcessingService           > represented as Endpoint(Binding, location(, responselocation))
│   │   ├── ContactPersonList                   > Used in Metadata
│   │   │   └── ContactPerson[]
│   │   │       ├── ContactType
│   │   │       ├── EmailAddressList
│   │   │       │   └── EmailAddress[]
│   │   │       ├── TelephoneNumberList
│   │   │       │   └── TelephoneNumber[]
│   │   │       ├── GivenName (optional)
│   │   │       ├── Surname (optional)
│   │   │       └── Company (optional)
│   │   └── Organization                        > Used in Metadata
│   │       ├── OrganizationNameList
│   │       │   └── OrganizationName[]          > (name, language)
│   │       ├── OrganizationDisplayNameList
│   │       │   └── OrganizationDisplayName[]   > (displayname, language)
│   │       └── OrganizationUrlList
│   │           └── OrganizationUrl             > (url, language)
│   ├── AssertionConsumerServices
│   │   └── IndexedEndpoint[]                   > (Endpoint(Binding, location(, responselocation), index, isDefault) 
├── ServiceProviderConfiguration                > EngineBlock specific configuration for a ServiceProvider, used during login
│   ├── displayUnconnectedIdpsInWayf            > bool | default: false
│   ├── isTrustedProxy                          > bool | default: false
│   ├── isTransparentIssuer                     > bool | default: false
│   ├── requiresConsent                         > bool | default: true
│   ├── denormalizationShouldBeSkipped          > bool | default: false
│   ├── requiresPolicyEnforcementDecision       > bool | default: false
│   └── requiresAttributeAggregation            > bool | default: false
└── ServiceProviderAttributes                   > Specific attributes for a ServiceProvider | all optional
    ├── EntityAttributes
    │   ├── LocalizedServiceName
    │   │   └── LocalizedName[]                 > (name, locale)
    │   ├── LocalizedDescription
    │   │   └── LocalizedText[]                 > (text, locale)
    │   └── Logo                                > (url, width, height)
    ├── TermsOfServiceUrl                       > represented as LocalizedUri(uri, locale)
    └── LocalizedSupportUrl
        └── LocalizedUri[]                      > (uri, locale)
```
