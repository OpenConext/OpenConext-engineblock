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
    set the used IdP (OpenConext\EngineBlock\Metadata\Entity\IdentityProvider)
    set the originating SP (OpenConext\EngineBlock\Metadata\Entity\ServiceProvider)
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

### ValidateAuthnContextClassRef
Block any incoming IdP assertion with a blacklisted `AuthnContextClassRef`.
This is used to prevent an IdP to impersonate 'our' AuthnContextClassRef values.

Configured with:
 - `stepup.authn_context_class_ref_blacklist_regex`

Uses:
- EngineBlock_Saml2_ResponseAnnotationDecorator

### ValidateMfaAuthnContextClassRef
Check that an IdP response actually contains the requested AuthnContextClassRef, if Engineblock was configured to do so in the AuthnRequest to the IdP. Can also check the Microsoft ADFS specific attribute for this information. Blocks the incoming assertion if the requested AuthnConextClassRef was not reported back in the assertion.

Uses:
- EngineBlock_Saml2_ResponseAnnotationDecorator
- responseAttributes

### NormalizeAttributes 
Convert all OID attributes to URN and remove the OID variant

Depends on:
- EngineBlock_Attributes_Normalizer

Uses:
- responseAttributes

Modifies:
- responseAttributes

### FilterReservedMemberOfValues
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
- OpenConext\EngineBlock\Metadata\Entity\IdentityProvider
- OpenConext\EngineBlock\Metadata\Entity\ServiceProvider
- collabPersonId
- responseAttributes

Modifies:
- responseAttributes
- EngineBlock_Saml2_ResponseAnnotationDecorator

### VerifyShibMdScopingAllowsSchacHomeOrganisation
log a warning if received `schacHomeOrganization` is not allowed by the configured shibmd:scopes for the used IdP.
Log a notice otherwise.

If however the eb.block_user_on_violation feature toggle is set, it will throw an exception which will prevent access.

Uses:
- EngineBlock_Saml2_ResponseAnnotationDecorator
- OpenConext\EngineBlock\Metadata\Entity\IdentityProvider

### VerifyShibMdScopingAllowsEduPersonPrincipalName
log a warning if received `eduPersonPrincipalName` is not allowed by configured shibmd:scopes for the used IdP. Log a
notice otherwise.

If however the eb.block_user_on_violation feature toggle is set, it will throw an exception which will prevent access.

Uses:
- EngineBlock_Saml2_ResponseAnnotationDecorator
- OpenConext\EngineBlock\Metadata\Entity\IdentityProvider

### ValidateAllowedConnection
validate if the used IdP is allowed by the metadata configuration of the SP the user comes from.

Depends on:
- MetadataRepository

Uses:
- OpenConext\EngineBlock\Metadata\Entity\IdentityProvider
- OpenConext\EngineBlock\Metadata\Entity\ServiceProvider

### ValidateRequiredAttributes
validate that all required attributes are present in the received response

Depends on:
- AttributeValidator

Uses:
- OpenConext\EngineBlock\Metadata\Entity\IdentityProvider

Modifies:
- responseAttributes

### AddGuestStatus
Add the 'urn:collab:org:surf.nl' value to the isMemberOf attribute in case a user is considered a 'full member' of the
SURFfederation based on user and configuration

Depends On:
- Configuration

Uses:
- OpenConext\EngineBlock\Metadata\Entity\IdentityProvider

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
- OpenConext\EngineBlock\Metadata\Entity\ServiceProvider
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
- OpenConext\EngineBlock\Metadata\Entity\IdentityProvider
- OpenConext\EngineBlock\Metadata\Entity\ServiceProvider
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
- OpenConext\EngineBlock\Metadata\Entity\ServiceProvider
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

### AttributeReleaseAs

If in the ARP any attribute has a "release_as" setting that specifies another name
for this attribute, the attribute values will be released under that name and not
the original, official attribute name.

### RunAttributeManipulations (for SP)
Run the configured Attribute Manipulations for the current Service Provider. Attribute Manipulations are code that is
executed that allows the modification of response attribute values.

Depends On:
- EngineBlock_SamlHelper
- EngineBlock_Attributes_Manipulator_ServiceRegistry
- MetadataRepository

Uses:
- OpenConext\EngineBlock\Metadata\Entity\IdentityProvider
- OpenConext\EngineBlock\Metadata\Entity\ServiceProvider
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
- OpenConext\EngineBlock\Metadata\Entity\IdentityProvider
- OpenConext\EngineBlock\Metadata\Entity\ServiceProvider
- collabPersonId
- responseAttributes

Modifies:
- responseAttributes
- EngineBlock_Saml2_ResponseAnnotationDecorator

### ApplyTrustedProxyBehaviour

Iff there's a trusted proxy involved in the authentication, add a custom attribute
`internal-collabPersonId` for consumption by the trusted proxy.

Uses:
- collabPersonId
- OpenConext\EngineBlock\Metadata\Entity\ServiceProvider

Modifies:
- responseAttributes

### AddIdentityAttributes
Sets the NameID and/or the eduPersonTargetedId (EPTI) on the Response.

By default it will use the NameIDFormat defined for the SP in its metadata to
determine whether to generate a persistent or transient ID, or release the
unspecified/collabPersonId plainly. Also the 'use_as_nameid' flag in the ARP
is inspected, if set the first value of the attribute with that flag will be
used as the NameID, with format=unspecified.

Depends on:
- EngineBlock_Saml2_NameIdResolver
- EngineBlock_SamlHelper

Uses:
- EngineBlock_Saml2_ResponseAnnotationDecorator
- EngineBlock_Saml2_AuthnRequestAnnotationDecorator
- OpenConext\EngineBlock\Metadata\Entity\ServiceProvider
- collabPersonId

Modifies:
- EngineBlock_Saml2_ResponseAnnotationDecorator
- responseAttributes

### DenormalizeAttributes
If possible, convert all attributes with an urn:mace to their OID format (if known) and add these to the response. Only
runs if not explicitly disabled through the ServiceProvider Configuration.

Depends on:
- EngineBlock_Attributes_Normalizer

Uses:
responseAttributes
OpenConext\EngineBlock\Metadata\Entity\ServiceProvider

Modifies:
- responseAttributes

### LogLogin
Logs the login to the login log.

Depends on:
- OpenConext\EngineBlockBridge\Logger\AuthenticationLoggerAdapter
- EngineBlock_SamlHelper
- OpenConext\EngineBlock\Metadata\MetadataRepository\MetadataRepositoryInterface

Uses:
- collabPersonId
- responseAttributes
- OpenConext\EngineBlock\Metadata\Entity\ServiceProvider
- OpenConext\EngineBlock\Metadata\Entity\IdentityProvider
- EngineBlock_Saml2_AuthnRequestAnnotationDecorator





[input]: https://github.com/OpenConext/OpenConext-engineblock/tree/master/library/EngineBlock/Corto/Filter/Input.php
[output]: https://github.com/OpenConext/OpenConext-engineblock/tree/master/library/EngineBlock/Corto/Filter/Output.php
[folder]: https://github.com/OpenConext/OpenConext-engineblock/tree/master/library/EngineBlock/Corto/Filter/Command
[ps]: https://github.com/OpenConext/OpenConext-engineblock/blob/master/library/EngineBlock/Corto/ProxyServer.php
[fOAA]: https://github.com/OpenConext/OpenConext-engineblock/blob/master/library/EngineBlock/Corto/ProxyServer.php#L749
[fIAA]: https://github.com/OpenConext/OpenConext-engineblock/blob/master/library/EngineBlock/Corto/ProxyServer.php#L736
[cAF]: https://github.com/OpenConext/OpenConext-engineblock/blob/83591aeef5f79418a899aa57357a2d820adbb82c/library/EngineBlock/Corto/ProxyServer.php#L762
[pdp-repo]: https://github.com/OpenConext/OpenConext-pdp#miscellaneous
