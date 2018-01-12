# Metadata Marshalling

## NameIdFormat
- Current dev metadata of https://engine.vm.openconext.org/authentication/idp/metadata does not contain NameIdFormat 
- Prod metadata (4 cases): "urn:oasis:names:tc:SAML:2.0:nameid-format:unspecified", value object supports only SAML:1.1 unspecified

## Email addresses
- Prod metadata (47 cases): contact person email address contains "mailto:", value object does not support it
    - Add to Value Object
- Prod metadata (? cases): contact person email address contains empty string, value object does not support it
    - How should we handle this?
- Prod metadata (2 cases): contact person email address contains name instead of email, value object does not support it
    - This should be fixed by clients

## Logo
- Prod metadata (2 cases), can be empty, value object does not support it

## Binding
- Prod metadata (100+ cases): "urn:oasis:names:tc:SAML:2.0:bindings:PAOS", value object does not support it
- Prod metadata (1 case): "urn:oasis:names:tc:SAML:2.0:bindings:URI", value object does not support it
- Prod metadata (2 cases): contains empty binding (in assertion consumer services and single logout service); supported? defaul?

## Allowed NameIdFormats
- Current dev metadata of Shibboleth does not have the `NameIdFormats` attribute. 
    - NameIdFormats = opt. (empty list)

## SingleLogoutService
- Current dev metadata does not contain single logout service, should the attribute be optional?
    - It might
- Represented in JSON as an array, only one endpoint as an object? 
    - Only 1

## ShibMdScope
- Current dev metadata does not contain shib md scopes, should they be optional?
- Are there multiple shibmd scopes possible?

## SingleSignOnService
- Represented in json as an array, only one endpoint as an object? 
    - Endpoints (multiple)

## Attribute Manipulation Code
- Non empty string, but required? What should the value be if non given?

## AssertionConsumerServices
- How to determine 'isDefault' in indexedEndpoints?
- Can there be multiple AssertionConsumerServices endpoints without an index defined?
- Can there be AssertionConsumerServices endpoints that have an index next to those that don't?

## Endpoint (ACS, SLS, SSOS)
- Can 'location' be empty in an endpoint?
- Is 'ResponseLocation' ever used?

## ServiceProviderConfiguration
- denormalizationShouldBeSkipped: what metadata key is used
- requiresAttributeAggregation: what metdata key is used

## Logo
- Width and height are optional in the metadata, value object does not support this

## Disabled in WAYF
- How is this represented in JSON?
    - coin:publish_in_edugain?

## SupportUrl (url)
- Can turn up as empty string in the metadata; value object does not support this

## ContactPerson values (surName, givenName, email adress in email address list, ...)
- Can turn up as empty string in the metadata; value object does not support this

## Description
- Can turn up as empty string in the metadata; value object does not support this

## ShibMdScope scope
- Can turn up as empty string in the metadata; svalue object does not support this
