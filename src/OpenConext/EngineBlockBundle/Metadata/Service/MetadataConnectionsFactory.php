<?php

namespace OpenConext\EngineBlockBundle\Metadata\Service;

use Exception;
use OpenConext\EngineBlock\Metadata\Model\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Model\ServiceProvider;
use OpenConext\EngineBlock\Metadata\Value\AssertionConsumerServices;
use OpenConext\EngineBlock\Metadata\Value\AttributeManipulationCode;
use OpenConext\EngineBlock\Metadata\Value\Common\LocalizedText;
use OpenConext\EngineBlock\Metadata\Value\EntityAttributes;
use OpenConext\EngineBlock\Metadata\Value\EntityConfiguration;
use OpenConext\EngineBlock\Metadata\Value\EntitySamlConfiguration;
use OpenConext\EngineBlock\Metadata\Value\GuestQualifier;
use OpenConext\EngineBlock\Metadata\Value\IdentityProviderAttributes;
use OpenConext\EngineBlock\Metadata\Value\IdentityProviderConfiguration;
use OpenConext\EngineBlock\Metadata\Value\IdentityProviderSamlConfiguration;
use OpenConext\EngineBlock\Metadata\Value\Keywords;
use OpenConext\EngineBlock\Metadata\Value\LocalizedDescription;
use OpenConext\EngineBlock\Metadata\Value\LocalizedKeywords;
use OpenConext\EngineBlock\Metadata\Value\LocalizedServiceName;
use OpenConext\EngineBlock\Metadata\Value\LocalizedSupportUrl;
use OpenConext\EngineBlock\Metadata\Value\Logo;
use OpenConext\EngineBlock\Metadata\Value\ServiceProviderAttributes;
use OpenConext\EngineBlock\Metadata\Value\ServiceProviderConfiguration;
use OpenConext\EngineBlock\Metadata\Value\ServiceProviderSamlConfiguration;
use OpenConext\EngineBlock\Metadata\Value\SingleSignOnServices;
use OpenConext\EngineBlock\Metadata\Value\Url;
use OpenConext\EngineBlock\Metadata\Value\WorkflowState;
use OpenConext\EngineBlock\Metadata\Value\X509\Certificate;
use OpenConext\EngineBlock\Metadata\Value\X509\CertificateList;
use OpenConext\Value\Saml\Entity;
use OpenConext\Value\Saml\EntityId;
use OpenConext\Value\Saml\EntitySet;
use OpenConext\Value\Saml\EntityType;
use OpenConext\Value\Saml\Metadata\Common\Binding;
use OpenConext\Value\Saml\Metadata\Common\Endpoint;
use OpenConext\Value\Saml\Metadata\Common\IndexedEndpoint;
use OpenConext\Value\Saml\Metadata\Common\LocalizedName;
use OpenConext\Value\Saml\Metadata\Common\LocalizedUri;
use OpenConext\Value\Saml\Metadata\ContactPerson;
use OpenConext\Value\Saml\Metadata\ContactPerson\Company;
use OpenConext\Value\Saml\Metadata\ContactPerson\ContactType;
use OpenConext\Value\Saml\Metadata\ContactPerson\EmailAddress;
use OpenConext\Value\Saml\Metadata\ContactPerson\EmailAddressList;
use OpenConext\Value\Saml\Metadata\ContactPerson\GivenName;
use OpenConext\Value\Saml\Metadata\ContactPerson\Surname;
use OpenConext\Value\Saml\Metadata\ContactPerson\TelephoneNumber;
use OpenConext\Value\Saml\Metadata\ContactPerson\TelephoneNumberList;
use OpenConext\Value\Saml\Metadata\ContactPersonList;
use OpenConext\Value\Saml\Metadata\Organization;
use OpenConext\Value\Saml\Metadata\Organization\OrganizationDisplayName;
use OpenConext\Value\Saml\Metadata\Organization\OrganizationDisplayNameList;
use OpenConext\Value\Saml\Metadata\Organization\OrganizationName;
use OpenConext\Value\Saml\Metadata\Organization\OrganizationNameList;
use OpenConext\Value\Saml\Metadata\Organization\OrganizationUrl;
use OpenConext\Value\Saml\Metadata\Organization\OrganizationUrlList;
use OpenConext\Value\Saml\Metadata\ShibbolethMetadataScope;
use OpenConext\Value\Saml\Metadata\ShibbolethMetadataScopeList;
use OpenConext\Value\Saml\NameIdFormat;
use OpenConext\Value\Saml\NameIdFormatList;

/**
 * @SuppressWarnings(PHPMD) // we know it's complex...
 */
final class MetadataConnectionsFactory
{
    public static function createConnectionsFrom(array $connectionData)
    {
        $connections = [];

        foreach ($connectionData as $connection) {
            if (!isset($connection['type'])) {
                throw new Exception('Invalid JSON structure: connection is missing a type');
            }

            if ($connection['type'] === EntityType::TYPE_IDP) {
                $connections['identityProviders'][] = self::createIdentityProviderFrom($connection);
                continue;
            }

            if ($connection['type'] === EntityType::TYPE_SP) {
                $connections['serviceProviders'][] = self::createServiceProviderFrom($connection);
            }
        }

        return $connections;
    }

    private static function createIdentityProviderFrom($connection)
    {
        // EntitySamlConfiguration: NameID format
        if (!isset($connection['metadata']['NameIDFormat'])) {
            // What to do with non set NameIDFormat?
            $nameIdFormat = NameIdFormat::unspecified();
        } else {
            $nameIdFormat = new NameIdFormat($connection['metadata']['NameIDFormat']);
        }

        // EntitySamlConfiguration: Allowed NameID formats
        if (isset($connection['metadata']['NameIDFormats'])) {
            $allowedNameIdFormats = new NameIdFormatList(
                array_map(
                    function ($nameIdFormat) {
                        return new NameIdFormat($nameIdFormat);
                    },
                    $connection['metadata']['NameIDFormats']
                )
            );
        } else {
            $allowedNameIdFormats = new NameIdFormatList([]);
        }

        // EntitySamlConfiguration: Certificates
        $certificates = [];
        if (isset($connection['metadata']['certdata1'])) {
            $certificates[] = new Certificate($connection['metadata']['certdata1']);
        }
        if (isset($connection['metadata']['certdata2'])) {
            $certificates[] = new Certificate($connection['metadata']['certdata2']);
        }
        if (isset($connection['metadata']['certdata3'])) {
            $certificates[] = new Certificate($connection['metadata']['certdata3']);
        }
        $certificateList = new CertificateList($certificates);

        // EntitySamlConfiguration: Single Logout Service
        $singleLogoutService = null;
        if (isset($connection['metadata']['SingleLogoutService'])) {
            $singleLogoutResponseLocation = null;
            if (isset($connection['metadata']['SingleLogoutService']['ResponseLocation'])) {
                $singleLogoutResponseLocation = $connection['metadata']['SingleLogoutService']['ResponseLocation'];
            }

            $singleLogoutService = new Endpoint(
                new Binding($connection['metadata']['SingleLogoutService']['Binding']),
                $connection['metadata']['SingleLogoutService']['Location'],
                $singleLogoutResponseLocation
            );
        }

        // EntitySamlConfiguration: Response Processing Service
        $responseProcessingService = null;
        if (isset($connection['metadata']['ResponseProcessingService'])) {
            $responseProcessingResponseLocation = null;
            if (isset($connection['metadata']['ResponseProcessingService']['ResponseLocation'])) {
                $responseProcessingResponseLocation = $connection['metadata']['ResponseProcessingService']['ResponseLocation'];
            }

            $responseProcessingService = new Endpoint(
                new Binding($connection['metadata']['ResponseProcessingService']['Binding']),
                $connection['metadata']['ResponseProcessingService']['Location'],
                $responseProcessingResponseLocation
            );
        }

        // EntitySamlConfiguration: Contact persons
        $contactPersons = [];

        foreach ($connection['metadata']['contacts'] as $contactPerson) {
            $telephoneNumbers = [];
            if (isset($contactPerson['telephoneNumber'])) {
                $telephoneNumbers = [new TelephoneNumber($contactPerson['telephoneNumber'])];
            }
            $telephoneNumberList = new TelephoneNumberList($telephoneNumbers);

            $surname = null;
            if (isset($contactPerson['surName']) && trim($contactPerson['surName']) !== '') {
                $surname = new Surname($contactPerson['surName']);
            }

            $givenName = null;
            if (isset($contactPerson['givenName']) && trim($contactPerson['givenName']) !== '') {
                $givenName = new GivenName($contactPerson['givenName']);
            }

            $company = null;
            if (isset($contactPerson['company']) && trim($contactPerson['company']) !== '') {
                $company = new Company($contactPerson['company']);
            }

            $emailAddresses = [];
            // non empty string? optional? invalid?
            if (isset($contactPerson['emailAddress']) && trim($contactPerson['emailAddress']) !== ''
                && filter_var(trim($contactPerson['emailAddress']), FILTER_VALIDATE_EMAIL)
            ) {
                // strip mailto:
                $emailAddress = trim($contactPerson['emailAddress']);
                if (strpos($emailAddress, 'mailto:') !== false) {
                    $emailAddress = substr($emailAddress, 7);
                }

                $emailAddresses[] = new EmailAddress($emailAddress);
            }

            $contactPersons[] = new ContactPerson(
                new ContactType($contactPerson['contactType']),
                new EmailAddressList($emailAddresses),
                $telephoneNumberList,
                $givenName,
                $surname,
                $company
            );
        }

        $contactPersonList = new ContactPersonList($contactPersons);

        // EntitySamlConfiguration: Organization
        $organization = null;
        if (isset($connection['metadata']['OrganizationName'])
            && isset($connection['metadata']['OrganizationDisplayName'])
            && isset($connection['metadata']['OrganizationURL'])
        ) {
            $organizationNames = [];
            $organizationDisplayNames = [];
            $organizationUrls = [];

            if (isset($connection['metadata']['OrganizationName'])) {
                foreach ($connection['metadata']['OrganizationName'] as $language => $organizationName) {
                    $organizationNames[] = new OrganizationName($organizationName, $language);
                }
            }

            if (isset($connection['metadata']['OrganizationDisplayName'])) {
                foreach ($connection['metadata']['OrganizationDisplayName'] as $language => $organizationDisplayName) {
                    $organizationDisplayNames[] = new OrganizationDisplayName($organizationDisplayName, $language);
                }
            }

            if (isset($connection['metadata']['OrganizationURL'])) {
                foreach ($connection['metadata']['OrganizationURL'] as $language => $organizationUrl) {
                    $organizationUrls[] = new OrganizationUrl($organizationUrl, $language);
                }
            }

            $organization = new Organization(
                new OrganizationNameList($organizationNames),
                new OrganizationDisplayNameList($organizationDisplayNames),
                new OrganizationUrlList($organizationUrls)
            );
        }

        // Single Sign On Services
        $singleSignOnEndpoints = [];
        if (isset($connection['metadata']['SingleSignOnService'])) {
            foreach ($connection['metadata']['SingleSignOnService'] as $endpoint) {
                // Optional?
                $location = '(empty)';
                if (isset($endpoint['Location'])) {
                    $location = $endpoint['Location'];
                }

                $responseLocation = null;
                if (isset($endpoint['ResponseLocation'])) {
                    $responseLocation = $endpoint['ResponseLocation'];
                }

                $singleSignOnEndpoints[] = new Endpoint(
                    new Binding($endpoint['Binding']),
                    $location,
                    $responseLocation
                );
            }
        }

        // ShibMdScopes
        $shibMdScopes = [];

        if (isset($connection['metadata']['shibmd'])) {
            foreach ($connection['metadata']['shibmd'] as $shibMd) {

                // optional?
                $shibMdScope = '(empty)';
                if (isset($shibMd['scope'])) {
                    $shibMdScope = $shibMd['scope'];
                }

                $shibMdIsRegex = false;
                if (isset($shibMd['isRegexp'])) {
                    $shibMdIsRegex = $shibMd['isRegexp'];
                }

                $shibMdScopes[] = new ShibbolethMetadataScope($shibMdScope, $shibMdIsRegex);
            }
        }

        // AttributeManipulationCode
        $attributeManipulationCode = '//';
        if (isset($connection['metadata']['manipulation_code'])) {
            $attributeManipulationCode = $connection['metadata']['manipulation_code'];
        }

        // Requires additional logging
        $requiresAdditionalLogging = false;
        if (isset($connection['metadata']['coin']['additional_logging'])) {
            $requiresAdditionalLogging = $connection['metadata']['coin']['additional_logging'];
        }

        // Disable scoping
        $disableScoping = false;
        if (isset($connection['metadata']['coin']['disable_scoping'])) {
            $disableScoping = $connection['metadata']['coin']['disable_scoping'];
        }

        // Requires signed requests
        $requiresSignedRequests = false;
        if (isset($connection['metadata']['redirect']['sign'])) {
            $requiresSignedRequests = $connection['metadata']['redirect']['sign'];
        }

        // Service providers without consent
        $serviceProvidersWithoutConsent = [];
        if (isset($connection['metadata']['disable_consent_connections'])) {
            $serviceProvidersWithoutConsent = $connection['metadata']['disable_consent_connections'];
        }

        // Guest qualifier
        if (isset($connection['metadata']['coin']['guest_qualifier'])) {
            $guestQualifier = new GuestQualifier($connection['metadata']['coin']['guest_qualifier']);
        } else {
            $guestQualifier = GuestQualifier::all();
        }

        // Is Hidden
        $isHidden = false;
        if (isset($connection['metadata']['coin']['hidden'])) {
            $isHidden = isset($connection['metadata']['coin']['hidden']);
        }

        // Enabled in WAYF
        $isEnabledInWayf = true;
        if (isset($connection['metadata']['coin']['publish_in_edugain'])) {
            $isEnabledInWayf = isset($connection['metadata']['coin']['publish_in_edugain']);
        }

        // IdentityProviderAttributes: EntityAttributes: LocalizedServiceName
        $localizedNames = [];
        if (isset($connection['metadata']['name'])) {
            foreach ($connection['metadata']['name'] as $language => $name) {
                $localizedNames[] = new LocalizedName($name, $language);
            }
        }

        // IdentityProviderAttributes: EntityAttributes: LocalizedDescription
        $localizedTexts = [];
        // optional?
        if (isset($connection['metadata']['description'])) {
            foreach ($connection['metadata']['description'] as $language => $text) {
                // optional?
                if (!empty($text)) {
                    $localizedTexts[] = new LocalizedText($text, $language);
                }
            }
        }

        // IdentityProviderAttributes: Keywords
        $localizedKeywords = [];
        // optional?
        if (isset($connection['metadata']['keywords'])) {
            foreach ($connection['metadata']['keywords'] as $locale => $keywords) {
                $localizedKeywords[] = new LocalizedKeywords($locale, [$keywords]);
            }
        }
        $idpKeywords = new Keywords($localizedKeywords);

        return new IdentityProvider(
            new Entity(
                new EntityId($connection['name']),
                EntityType::IdP()
            ),
            new IdentityProviderSamlConfiguration(
                new EntitySamlConfiguration(
                    $nameIdFormat,
                    $allowedNameIdFormats,
                    $certificateList,
                    $contactPersonList,
                    $singleLogoutService,
                    $responseProcessingService,
                    $organization
                ),
                new SingleSignOnServices($singleSignOnEndpoints),
                new ShibbolethMetadataScopeList($shibMdScopes)
            ),
            new IdentityProviderConfiguration(
                new EntityConfiguration(
                    new AttributeManipulationCode($attributeManipulationCode),
                    new WorkflowState($connection['state']),
                    $requiresAdditionalLogging,
                    $disableScoping,
                    $requiresSignedRequests
                ),
                new EntitySet($serviceProvidersWithoutConsent),
                $guestQualifier
            ),
            new IdentityProviderAttributes(
                new EntityAttributes(
                    new LocalizedServiceName($localizedNames),
                    new LocalizedDescription($localizedTexts),
                    new Logo(
                        $connection['metadata']['logo'][0]['url'],
                        // width and height are optional, but not in the VO
                        (int)$connection['metadata']['logo'][0]['width'],
                        (int)$connection['metadata']['logo'][0]['height']
                    )
                ),
                $isHidden,
                $isEnabledInWayf,
                $idpKeywords
            )
        );
    }

    private static function createServiceProviderFrom($connection)
    {
        // EntitySamlConfiguration: NameID format
        if (!isset($connection['metadata']['NameIDFormat'])) {
            // Should this happen?
            $nameIdFormat = NameIdFormat::unspecified();
        } else {
            $nameIdFormat = new NameIdFormat($connection['metadata']['NameIDFormat']);
        }

        // EntitySamlConfiguration: Allowed NameID formats
        if (isset($connection['metadata']['NameIDFormats'])) {
            $allowedNameIdFormats = new NameIdFormatList(
                array_map(
                    function ($nameIdFormat) {
                        return new NameIdFormat($nameIdFormat);
                    },
                    $connection['metadata']['NameIDFormats']
                )
            );
        } else {
            $allowedNameIdFormats = new NameIdFormatList([]);
        }

        // EntitySamlConfiguration: Certificates
        $certificates = [];
        if (isset($connection['metadata']['certdata1'])) {
            $certificates[] = new Certificate($connection['metadata']['certdata1']);
        }
        if (isset($connection['metadata']['certdata2'])) {
            $certificates[] = new Certificate($connection['metadata']['certdata2']);
        }
        if (isset($connection['metadata']['certdata3'])) {
            $certificates[] = new Certificate($connection['metadata']['certdata3']);
        }
        $certificateList = new CertificateList($certificates);

        // EntitySamlConfiguration: Single Logout Service
        $singleLogoutService = null;
        if (isset($connection['metadata']['SingleLogoutService'])) {
            $singleLogoutResponseLocation = null;

            if (isset($connection['metadata']['SingleLogoutService']['ResponseLocation'])) {
                $singleLogoutResponseLocation = $connection['metadata']['SingleLogoutService']['ResponseLocation'];
            }

            // ! What to do here?
            if (!isset($connection['metadata']['SingleLogoutService']['Binding'])) {
                $connection['metadata']['SingleLogoutService']['Binding'] = Binding::HTTP_ARTIFACT;
            }

            // optional?
            if (!isset($connection['metadata']['SingleLogoutService']['Location'])) {
                $connection['metadata']['SingleLogoutService']['Location'] = '(empty)';
            }

            $singleLogoutService = new Endpoint(
                new Binding($connection['metadata']['SingleLogoutService']['Binding']),
                $connection['metadata']['SingleLogoutService']['Location'],
                $singleLogoutResponseLocation
            );
        }

        // EntitySamlConfiguration: Response Processing Service
        $responseProcessingService = null;
        if (isset($connection['metadata']['ResponseProcessingService'])) {
            $responseProcessingResponseLocation = null;

            if (isset($connection['metadata']['ResponseProcessingService']['ResponseLocation'])) {
                $responseProcessingResponseLocation = $connection['metadata']['ResponseProcessingService']['ResponseLocation'];
            }

            $responseProcessingService = new Endpoint(
                new Binding($connection['metadata']['ResponseProcessingService']['Binding']),
                $connection['metadata']['ResponseProcessingService']['Location'],
                $responseProcessingResponseLocation
            );
        }

        // EntitySamlConfiguration: Contact persons
        $contactPersons = [];

        if (isset($connection['metadata']['contacts'])) {
            foreach ($connection['metadata']['contacts'] as $contactPerson) {
                $telephoneNumbers = [];
                if (isset($contactPerson['telephoneNumber'])) {
                    $telephoneNumbers = [new TelephoneNumber($contactPerson['telephoneNumber'])];
                }
                $telephoneNumberList = new TelephoneNumberList($telephoneNumbers);

                $surname = null;
                if (isset($contactPerson['surName']) && trim($contactPerson['surName']) !== '') {
                    $surname = new Surname($contactPerson['surName']);
                }

                $givenName = null;
                if (isset($contactPerson['givenName']) && trim($contactPerson['givenName']) !== '') {
                    $givenName = new GivenName($contactPerson['givenName']);
                }

                $company = null;
                if (isset($contactPerson['company']) && trim($contactPerson['company']) !== '') {
                    $company = new Company($contactPerson['company']);
                }

                $emailAddresses = [];
                // non empty string? optional?
                if (isset($contactPerson['emailAddress']) && trim($contactPerson['emailAddress']) !== ''
                    && filter_var(trim($contactPerson['emailAddress']), FILTER_VALIDATE_EMAIL)
                ) {
                    $emailAddress = trim($contactPerson['emailAddress']);
                    if (strpos($emailAddress, 'mailto:') !== false) {
                        $emailAddress = substr($emailAddress, 7);
                    }

                    $emailAddresses[] = new EmailAddress($emailAddress);
                }

                $contactPersons[] = new ContactPerson(
                    new ContactType($contactPerson['contactType']),
                    new EmailAddressList($emailAddresses),
                    $telephoneNumberList,
                    $givenName,
                    $surname,
                    $company
                );
            }
        }

        $contactPersonList = new ContactPersonList($contactPersons);

        // EntitySamlConfiguration: Organization
        $organization = null;
        if (isset($connection['metadata']['OrganizationName'])
            && isset($connection['metadata']['OrganizationDisplayName'])
            && isset($connection['metadata']['OrganizationURL'])
        ) {

            $organizationNames        = [];
            $organizationDisplayNames = [];
            $organizationUrls         = [];
            if (isset($connection['metadata']['OrganizationName'])) {
                foreach ($connection['metadata']['OrganizationName'] as $language => $organizationName) {
                    $organizationNames[] = new OrganizationName($organizationName, $language);
                }
            }

            if (isset($connection['metadata']['OrganizationDisplayName'])) {
                foreach ($connection['metadata']['OrganizationDisplayName'] as $language => $organizationDisplayName) {
                    $organizationDisplayNames[] = new OrganizationDisplayName($organizationDisplayName, $language);
                }
            }

            if (isset($connection['metadata']['OrganizationURL'])) {
                foreach ($connection['metadata']['OrganizationURL'] as $language => $organizationUrl) {
                    $organizationUrls[] = new OrganizationUrl($organizationUrl, $language);
                }
            }

            $organization = new Organization(
                new OrganizationNameList($organizationNames),
                new OrganizationDisplayNameList($organizationDisplayNames),
                new OrganizationUrlList($organizationUrls)
            );
        }

        // AssertionConsumerServices: IndexedEndpoints
        $indexedEndpoints = [];
        foreach ($connection['metadata']['AssertionConsumerService'] as $key => $acs) {
            // ! What to do here?
            if (!isset($acs['Binding'])) {
                $acs['Binding'] = Binding::HTTP_ARTIFACT;
            }

            // Optional?
            $location = '(empty)';
            if (isset($acs['Location'])) {
                $location = $acs['Location'];
            }

            $responseLocation = null;
            if (isset($acs['ResponseLocation'])) {
                $responseLocation = $acs['ResponseLocation'];
            }

            $index = $key;
            if (isset($acs['index'])) {
                $index = $acs['index'];
            }


            $indexedEndpoints[] = new IndexedEndpoint(
                new Endpoint(
                    new Binding($acs['Binding']),
                    $location,
                    $responseLocation
                ),
                (int) $index
            );
        }

        // ServiceProviderConfiguration
        $displayUnconnectedIdpsInWayf = false;
        if (isset($connection['metadata']['coin']['display_unconnected_idps_wayf'])) {
            $displayUnconnectedIdpsInWayf = $connection['metadata']['coin']['display_unconnected_idps_wayf'];
        }
        $isTrustedProxy = false;
        if (isset($connection['metadata']['coin']['trusted_proxy'])) {
            $isTrustedProxy = $connection['metadata']['coin']['trusted_proxy'];
        }
        $isTransparentIssuer = false;
        if (isset($connection['metadata']['coin']['transparent_issuer'])) {
            $isTransparentIssuer = $connection['metadata']['coin']['transparent_issuer'];
        }
        $requiresConsent = true;
        if (isset($connection['metadata']['coin']['no_consent_required'])) {
            $requiresConsent = !$connection['metadata']['coin']['no_consent_required'];
        }
        $denormalizationShouldBeSkipped = false;
        if (isset($connection['metadata']['coin']['do_not_add_attribute_aliases'])) {
            $denormalizationShouldBeSkipped = $connection['metadata']['coin']['do_not_add_attribute_aliases'];
        }
        $requiresPolicyEnforcementDecision = false;
        if (isset($connection['metadata']['coin']['policy_enforcement_decision_required'])) {
            $requiresPolicyEnforcementDecision = $connection['metadata']['coin']['policy_enforcement_decision_required'];
        }
        $requiresAttributeAggregation = false;
        if (isset($connection['metadata']['coin']['attribute_aggregation_required'])) {
            $denormalizationShouldBeSkipped = $connection['metadata']['coin']['attribute_aggregation_required'];
        }

        // ServiceProviderAttributes: EntityAttributes: LocalizedServiceName
        $localizedNames = [];
        if (isset($connection['metadata']['name'])) {
            foreach ($connection['metadata']['name'] as $language => $name) {
                $localizedNames[] = new LocalizedName($name, $language);
            }
        }

        // ServiceProviderAttributes: EntityAttributes: LocalizedDescription
        $localizedTexts = [];
        if (isset($connection['metadata']['description'])) {
            foreach ($connection['metadata']['description'] as $language => $text) {
                // optional?
                if (!empty($text)) {
                    $localizedTexts[] = new LocalizedText($text, $language);
                }
            }
        }

        // logo
        $logo = new Logo('(empty)', 0, 0);
        if (isset($connection['metadata']['logo'][0]['url'])) {
            $logo = new Logo(
                $connection['metadata']['logo'][0]['url'],
                // width and height are optional, but not in the VO
                (int)$connection['metadata']['logo'][0]['width'],
                (int)$connection['metadata']['logo'][0]['height']
            );
        }

        // ServiceProviderAttributes
        if (isset($connection['metadata']['coin']['eula'])) {
            $termsOfServiceUrl = new Url($connection['metadata']['coin']['eula']);
        } else {
            // Optional?
            $termsOfServiceUrl = new Url('/');
        }

        $localizedUris = [];
        if (isset($connection['metadata']['url'])) {
            foreach ($connection['metadata']['url'] as $language => $uri) {
                // Optional?
                $localizedUris[] = empty($uri) ? new LocalizedUri('(empty)', $language) : new LocalizedUri(
                    $uri,
                    $language
                );
            }
        }
        $supportUrl = new LocalizedSupportUrl($localizedUris);

        return new ServiceProvider(
            new Entity(
                new EntityId($connection['name']),
                EntityType::SP()
            ),
            new ServiceProviderSamlConfiguration(
                new EntitySamlConfiguration(
                    $nameIdFormat,
                    $allowedNameIdFormats,
                    $certificateList,
                    $contactPersonList,
                    $singleLogoutService,
                    $responseProcessingService,
                    $organization
                ),
                new AssertionConsumerServices($indexedEndpoints)
            ),
            new ServiceProviderConfiguration(
                $displayUnconnectedIdpsInWayf,
                $isTrustedProxy,
                $isTransparentIssuer,
                $requiresConsent,
                $denormalizationShouldBeSkipped,
                $requiresPolicyEnforcementDecision,
                $requiresAttributeAggregation
            ),
            new ServiceProviderAttributes(
                new EntityAttributes(
                    new LocalizedServiceName($localizedNames),
                    new LocalizedDescription($localizedTexts),
                    $logo
                ),
                $termsOfServiceUrl,
                $supportUrl
            )
        );
    }
}
