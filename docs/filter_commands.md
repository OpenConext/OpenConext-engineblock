# EngineBlock Input and Output Command Chains

EngineBlock pre-processes incoming and outgoing SAML Responses using so-called Filters. These filters provide specific,
critical functionality, by invoking a sequence of Filter Commands. However, it is not easily discoverable what these 
Filters and Filter Commands exactly do and how they work. This document outlines how these Filters and Filter Commands 
work and what each filter command does.

The chains are:
- [`EngineBlock_Corto_Filter_Input`][input]
- [`EngineBlock_Corto_Filter_Output`][output]

The specific commands can be found in the [`library\EngineBlock\Corto\Filter\Command`][commands] folder.

## Input and Output Filters

These are called by [`ProxyServer`][ps], through [`filterOutputAssertionAttributes`][fOAA] and 
[`filterInputAssertionAttributes`][fIAA] calling [`callAttributeFilter`][cAF], which invokes the actual Filter Commands.

Each Filter then executes Filter Commands in a specified order for Input (between receiving Assertion from IdP and
Consent) and Output (after Consent, before sending Response to SP).  
What the filter does is:
```
Loop over given Filter Commands, for each Command:
    set the proxy server (EngineBlock_Corto_ProxyServer)
    set the used IdP (OpenConext\Component\EngineBlockMetadata\Entity\IdentityProvider)
    set the originating SP (OpenConext\Component\EngineBlockMetadata\Entity\ServiceProvider)
    set the SAMLRequest (EngineBlock_Saml2_AuthnRequestAnnotationDecorator)
    set the Response (EngineBlock_Saml2_ResponseAnnotationDecorator)
    set the responseAttributes (array of attributes from Assertion from the receieved Response)
    set the collabPersonId (either: string stored in session, string found in Response, string found in responseAttributes, string found in nameId response or null, in that order)
    execute the command
```
During the loop, the Response, responseAttributes and collabPersonId are retrieved from the previous command and are 
used by the commands that follows.

A command can also stop filtering by calling `$this->stopFiltering();`

There is also a feature toggle, `eb.run_all_manipulations_prior_to_consent`. When this toggle is enabled, all filter
commands run in the Input Filter and no Filter commands run in the Output Filter. This is an option that ensures that
the consent screen (if shown) shows the exact attributes that will be sent to the SP without any possible Output Filter
Commands changing attributes.

Below all filters are documented. They are listed in order of application. Each Filter command has a short description,
list dependencies that are not set (see pseudo code above) as "Depends on", lists all set dependencies as "Uses" and
lists which values may be modified by this Filter Command under "Modifies". Almost all Filter Commands make use of the
logger (and those that do not currently should) thus that is a dependency for all Filter Commands and not explicitly
listed. If a section (depends, uses, modifies) misses, that means that that is not applicable for that Filter Command.

## Input Filter Commands

Commands executed between receiving Assertion from IdP and Consent

### NormalizeAttributes 
Convert all OID attributes to URN and remove the OID variant

Depends on:
- EngineBlock_Attributes_Normalizer

Uses:
- responseAttributes

Modifies:
- responseAttributes

### FilterReserverMemberOfValues
Removes any attributes starting with `urn:collab:org` as these may only be set by EngineBlock

Uses:
- responseAttributes

Modifies:
- responseAttributes

### RunAttributeManipulations (for IdP)
run possible custom attribute manipulations

Depends on:
- EngineBlock_SamlHelper
- EngineBlock_Attributes_Manipulator_ServiceRegistry
- MetadataRepository

Uses:
- OpenConext\Component\EngineBlockMetadata\Entity\IdentityProvider
- OpenConext\Component\EngineBlockMetadata\Entity\ServiceProvider
- collabPersonId
- responseAttributes

Modifies:
- responseAttributes
- EngineBlock_Saml2_ResponseAnnotationDecorator

### VerifyShibMdScopingAllowsSchacHomeOrganisation
log a warning if received `schacHomeOrganization` is not allowed by the configured shibmd:scopes for the used IdP.
Log a notice otherwise.

Uses:
- EngineBlock_Saml2_ResponseAnnotationDecorator
- OpenConext\Component\EngineBlockMetadata\Entity\IdentityProvider

### VerifyShibMdScopingAllowsEduPersonPrincipalName
log a warning if received `eduPersonPrincipalName` is not allowed by configured shibmd:scopes for the used IdP. Log a
notice otherwise.

Uses:
- EngineBlock_Saml2_ResponseAnnotationDecorator
- OpenConext\Component\EngineBlockMetadata\Entity\IdentityProvider

### ValidateAllowedConnection
validate if the used IdP is allowed by the metadata configuration of the SP the user comes from.

Depends on:
- MetadataRepository

Uses:
- OpenConext\Component\EngineBlockMetadata\Entity\IdentityProvider
- OpenConext\Component\EngineBlockMetadata\Entity\ServiceProvider

### ValidateRequiredAttributes
validate that all required attributes are present in the received response

Depends on:
- AttributeValidator

Uses:
- OpenConext\Component\EngineBlockMetadata\Entity\IdentityProvider

Modifies:
- responseAttributes

### AddGuestStatus
Add the 'urn:collab:org:surf.nl' value to the isMemberOf attribute in case a user is considered a 'full member' of the
SURFfederation based on user and configuration

Depends On:
- Configuration

Uses:
- OpenConext\Component\EngineBlockMetadata\Entity\IdentityProvider

Modifies:
- responseAttributes

### ProvisionUser
If a new user is encountered, provision the user in the UserDirectory, generating a CollabPersonId for new users. If
an existing user is encountered, retrieve the stored CollabPersonId

Depends On:
- UserDirectory

Uses:
- EngineBlock_Saml2_ResponseAnnotationDecorator

Modifies:
- collabPersonId
- response

### AttributeAggregator
Makes a call to the external AttributeAggregator service to apply the configured aggregations to the received attributes
so that the aggregated attributes can be used in the Response to be sent.

Depends On:
- AttributeAggregation_Client

Uses:
- OpenConext\Component\EngineBlockMetadata\Entity\ServiceProvider
- collabPersonId
- responseAttributes

Modifies:
- responseAttributes

See: [Engineblock Attribute Aggregation](attribute_aggregation.md) for more information.

### EnforcePolicy
Makes a call to the external PolicyDecisionPoint service. This returns a response which details whether or not the 
current User is allowed access to the Service Provider. For more information see [the PDP repository README][pdp-repo]

Depends On:
- EngineBlock_SamlHelper
- OpenConext\EngineBlockBundle\Pdp\PdpClient (and associated classes)

Uses:
- collabPersonId
- OpenConext\Component\EngineBlockMetadata\Entity\IdentityProvider
- OpenConext\Component\EngineBlockMetadata\Entity\ServiceProvider
- responseAttributes

### AttributeReleasePolicy
Applies the Attribute Release Policy determining which attributes may be released. This must run before consent is given
since it modifies which attributes are released.

Depends on:
- MetadataRepository
- EngineBlock_Arp_AttributeReleasePolicyEnforcer
- EngineBlock_SamlHelper

Uses:
- responseAttributes
- OpenConext\Component\EngineBlockMetadata\Entity\ServiceProvider
- EngineBlock_Saml2_AuthnRequestAnnotationDecorator

Modifies:
- responseAttributes

## Output Filter Commands

Filter Commands executed after Consent has been given (or determined that is has already been given or is implicit),
before sending Response to SP:

### RejectProcessingMode
Stop the running of Filter Commands if EngineBlock is in processing mode. EngineBlock is deemed in processing mode when
handling an internal request (e.g. from a received Reponse to Consent).

Uses:
 - EngineBlock_Corto_ProxyServer

### AddCollabPersonIdAttribute
Sets the collabPersonId attribute on the Response Attributes

Uses:
- responseAttributes
- collabPersonId

Modifies:
- responseAttributes

### RunAttributeManipulations (for SP)
Run the configured Attribute Manipulations for the current Service Provider. Attribute Manipulations are code that is
executed that allows the modification of response attribute values.

Depends On:
- EngineBlock_SamlHelper
- EngineBlock_Attributes_Manipulator_ServiceRegistry
- MetadataRepository

Uses:
- OpenConext\Component\EngineBlockMetadata\Entity\IdentityProvider
- OpenConext\Component\EngineBlockMetadata\Entity\ServiceProvider
- collabPersonId
- responseAttributes

Modifies:
- responseAttributes
- EngineBlock_Saml2_ResponseAnnotationDecorator

### RunAttributeManipulations (for Requester SP)
Run the configured Attribute Manipulations for the Requester SP if the current SP is a trusted proxy. Attribute
Manipulations are code that is executed that allows the modification of response attribute values.

Depends On:
- EngineBlock_SamlHelper
- EngineBlock_Attributes_Manipulator_ServiceRegistry
- MetadataRepository

Uses:
- OpenConext\Component\EngineBlockMetadata\Entity\IdentityProvider
- OpenConext\Component\EngineBlockMetadata\Entity\ServiceProvider
- collabPersonId
- responseAttributes

Modifies:
- responseAttributes
- EngineBlock_Saml2_ResponseAnnotationDecorator

### SetNameId
Sets the NameID on the Response

Depends on:
- EngineBlock_Saml2_NameIdResolver

Uses:
- EngineBlock_Saml2_ResponseAnnotationDecorator
- EngineBlock_Saml2_AuthnRequestAnnotationDecorator
- OpenConext\Component\EngineBlockMetadata\Entity\ServiceProvider
- collabPersonId

Modifies:
- EngineBlock_Saml2_ResponseAnnotationDecorator

### AddEduPersonTargettedId
Sets the EduPersonTargetedId (EPTI) attribute on the responseAttributes

Depends on:
- EngineBlock_Saml2_NameIdResolver
- EngineBlock_SamlHelper

Uses:
- EngineBlock_Saml2_ResponseAnnotationDecorator
- EngineBlock_Saml2_AuthnRequestAnnotationDecorator
- OpenConext\Component\EngineBlockMetadata\Entity\ServiceProvider
- collabPersonId

Modifies:
- responseAttributes

### AttributeReleasePolicy
Applies the Attribute Release Policy determining which attributes may be released. This is the same Filter Command as
detailed in the Input Filter. This is ran again for the EPTI.

Depends on:
- MetadataRepository
- EngineBlock_Arp_AttributeReleasePolicyEnforcer
- EngineBlock_SamlHelper

Uses:
- responseAttributes
- OpenConext\Component\EngineBlockMetadata\Entity\ServiceProvider
- EngineBlock_Saml2_AuthnRequestAnnotationDecorator

Modifies:
- responseAttributes

### DenormalizeAttributes
If possible, convert all attributes with an urn:mace to their OID format (if known) and add these to the response. Only
runs if not explicitly disabled through the ServiceProvider Configuration.

Depends on:
- EngineBlock_Attributes_Normalizer

Uses:
responseAttributes
OpenConext\Component\EngineBlockMetadata\Entity\ServiceProvider

Modifies:
- responseAttributes

### LogLogin
Logs the login to the login log.

Depends on:
- OpenConext\EngineBlockBridge\Logger\AuthenticationLoggerAdapter
- EngineBlock_SamlHelper
- OpenConext\Component\EngineBlockMetadata\MetadataRepository\MetadataRepositoryInterface

Uses:
- collabPersonId
- responseAttributes
- OpenConext\Component\EngineBlockMetadata\Entity\ServiceProvider
- OpenConext\Component\EngineBlockMetadata\Entity\IdentityProvider
- EngineBlock_Saml2_AuthnRequestAnnotationDecorator

[input]: https://github.com/OpenConext/OpenConext-engineblock/tree/master/library/EngineBlock/Corto/Filter/Input.php
[output]: https://github.com/OpenConext/OpenConext-engineblock/tree/master/library/EngineBlock/Corto/Filter/Output.php
[folder]: https://github.com/OpenConext/OpenConext-engineblock/tree/master/library/EngineBlock/Corto/Filter/Command
[ps]: https://github.com/OpenConext/OpenConext-engineblock/blob/master/library/EngineBlock/Corto/ProxyServer.php
[fOAA]: https://github.com/OpenConext/OpenConext-engineblock/blob/master/library/EngineBlock/Corto/ProxyServer.php#L749
[fIAA]: https://github.com/OpenConext/OpenConext-engineblock/blob/master/library/EngineBlock/Corto/ProxyServer.php#L736
[cAF]: https://github.com/OpenConext/OpenConext-engineblock/blob/83591aeef5f79418a899aa57357a2d820adbb82c/library/EngineBlock/Corto/ProxyServer.php#L762
[pdp-repo]: https://github.com/OpenConext/OpenConext-pdp#miscellaneous
