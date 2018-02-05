<?php

namespace OpenConext\EngineBlock\Metadata\Entity\Assembler;

use OpenConext\EngineBlock\Metadata\Logo;
use OpenConext\EngineBlock\Metadata\Organization;
use OpenConext\EngineBlock\Metadata\Service;
use OpenConext\EngineBlock\Metadata\ShibMdScope;
use OpenConext\EngineBlock\Metadata\ContactPerson;
use OpenConext\EngineBlock\Metadata\Entity\AbstractRole;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Metadata\IndexedService;
use OpenConext\EngineBlock\Metadata\Utils;
use OpenConext\EngineBlock\Metadata\X509\X509CertificateFactory;
use OpenConext\EngineBlock\Metadata\X509\X509CertificateLazyProxy;
use ReflectionClass;
use RuntimeException;

/**
 * Class JanusRestV1Assembler
 * @package OpenConext\EngineBlock\Metadata\Entity\Translator
 * @SuppressWarnings(PMD)
 */
class JanusRestV1Assembler
{
    /**
     * @param string $entityId
     * @param array $metadata
     * @return IdentityProvider|ServiceProvider
     * @throws \RuntimeException
     */
    public function assemble($entityId, array $metadata)
    {
        $arguments = array('entityId' => $entityId);

        if (isset($metadata['AssertionConsumerService:0:Location'])) {
            $arguments += $this->assembleAbstractRoleArguments($metadata);
            $arguments += $this->assembleServiceProviderArguments($metadata);

            return Utils::instantiate('OpenConext\EngineBlock\Metadata\Entity\ServiceProvider', $arguments);
        }

        if (isset($metadata['SingleSignOnService:0:Location'])) {
            $arguments += $this->assembleAbstractRoleArguments($metadata);
            $arguments += $this->assembleIdentityProviderArguments($metadata);

            return Utils::instantiate('OpenConext\EngineBlock\Metadata\Entity\IdentityProvider', $arguments);
        }

        // @todo log warning
        return null;
    }

    // @codingStandardsIgnoreStart

    /**
     * @param array $metadata
     * @return AbstractRole
     */
    private function assembleAbstractRoleArguments(array $metadata)
    {
        $arguments = array();
        if (isset($metadata['name:en']))        { $arguments['nameEn'] = $metadata['name:en']; }
        if (isset($metadata['name:nl']))        { $arguments['nameNl'] = $metadata['name:nl']; }
        if (isset($metadata['description:en'])) { $arguments['descriptionEn'] = $metadata['description:en']; }
        if (isset($metadata['description:nl'])) { $arguments['descriptionNl'] = $metadata['description:nl']; }
        if (isset($metadata['displayName:en'])) { $arguments['displayNameEn'] = $metadata['displayName:en']; }
        if (isset($metadata['displayName:nl'])) { $arguments['displayNameNl'] = $metadata['displayName:nl']; }
        if (isset($metadata['keywords:en']))    { $arguments['keywordsEn'] = $metadata['keywords:en']; }
        if (isset($metadata['keywords:nl']))    { $arguments['keywordsNl'] = $metadata['keywords:nl']; }

        if (isset($metadata['coin:publish_in_edugain'])) { $arguments['publishInEdugain'] = (bool) $metadata['coin:publish_in_edugain']; }
        $publishDate = Utils::ifsetor($metadata, 'coin:publish_in_edugain_date');
        if ($publishDate) {
            $arguments['publishInEduGainDate']   = date_create()->setTimestamp(strtotime($publishDate));
        }
        if (isset($metadata['coin:disable_scoping']))    { $arguments['disableScoping'] = (bool) $metadata['coin:disable_scoping']; }
        if (isset($metadata['coin:additional_logging'])) { $arguments['additionalLogging'] = (bool) $metadata['coin:additional_logging']; }

        if (isset($metadata['coin:signature_method'])) { $arguments['signatureMethod'] = $metadata['coin:signature_method']; }
        if (isset($metadata['redirect.sign'])) { $arguments['requestsMustBeSigned'] = (bool) $metadata['redirect.sign']; }
        if (isset($metadata['NameIDFormat']))  { $arguments['nameIdFormat'] = $metadata['NameIDFormat']; }
        if (isset($metadata['workflowState'])) { $arguments['workflowState'] = $metadata['workflowState']; }

        $arguments['logo']                   = $this->assembleLogo($metadata);
        $arguments['organizationEn']         = $this->assembleOrganizationEn($metadata);
        $arguments['organizationNl']         = $this->assembleOrganizationNl($metadata);
        $arguments['certificates']           = $this->assembleCertificates($metadata);
        $arguments['singleLogoutService']    = $this->assembleSloServices($metadata);

        $supportedNameIdFormats = $this->assembleNameIdFormats($metadata);
        if ($supportedNameIdFormats) $arguments['supportedNameIdFormats'] = $supportedNameIdFormats;
        $arguments['contactPersons']         = $this->assembleContactPersons($metadata);

        return $arguments;
    }

    /**
     * @param array $metadata
     * @param ServiceProvider $entity
     * @return ServiceProvider
     */
    public function assembleServiceProviderArguments(array $metadata)
    {
        $arguments = array();
        if (isset($metadata['coin:transparant_issuer']))                    { $arguments['isTransparentIssuer']             = (bool) $metadata['coin:transparant_issuer']; }
        if (isset($metadata['coin:trusted_proxy']))                         { $arguments['isTrustedProxy']                  = (bool) $metadata['coin:trusted_proxy']; }
        if (isset($metadata['coin:display_unconnected_idps_wayf']))         { $arguments['displayUnconnectedIdpsWayf']      = (bool) $metadata['coin:display_unconnected_idps_wayf']; }
        if (isset($metadata['coin:no_consent_required']))                   { $arguments['isConsentRequired']               = !(bool) $metadata['coin:no_consent_required']; }
        if (isset($metadata['coin:eula']))                                  { $arguments['termsOfServiceUrl']               = $metadata['coin:eula']; }
        if (isset($metadata['coin:do_not_add_attribute_aliases']))          { $arguments['skipDenormalization']             = (bool) $metadata['coin:do_not_add_attribute_aliases']; }
        if (isset($metadata['coin:policy_enforcement_decision_required']))  { $arguments['policyEnforcementDecisionRequired'] = (bool) $metadata['coin:policy_enforcement_decision_required']; }
        if (isset($metadata['coin:attribute_aggregation_required']))        { $arguments['attributeAggregationRequired']    = (bool) $metadata['coin:attribute_aggregation_required']; }

        if (isset($metadata['url:en']))    { $arguments['supportUrlEn'] = $metadata['url:en']; }
        if (isset($metadata['url:nl']))    { $arguments['supportUrlNl'] = $metadata['url:nl']; }

        $arguments['assertionConsumerServices'] = $this->assembleIndexedServices($metadata, 'AssertionConsumerService');

        return $arguments;
    }

    /**
     * @param array $metadata
     * @return IdentityProvider
     */
    public function assembleIdentityProviderArguments(array $metadata)
    {
        $arguments = array();
        $arguments['singleSignOnServices'] = $this->assembleIndexedServices($metadata, 'SingleSignOnService');
        if (isset($metadata['coin:schachomeorganization'])) {
            $arguments['schacHomeOrganization'] = $metadata['coin:schachomeorganization'];
        }
        $arguments['hidden']  = (bool) Utils::ifsetor($metadata, 'coin:hidden');

        if (isset($metadata['coin:guest_qualifier'])) {
            if (in_array($metadata['coin:guest_qualifier'], IdentityProvider::$GUEST_QUALIFIERS)) {
                $arguments['guestQualifier'] = $metadata['coin:guest_qualifier'];
            }
        }

        $arguments['shibMdScopes'] = $this->assembleShibMdScopes($metadata);
        $arguments['spsEntityIdsWithoutConsent'] = $this->assembleSpEntityIdsWithoutConsent($metadata);

        return $arguments;
    }

    // @codingStandardsIgnoreEnd

    /**
     * @param array $metadata
     * @return array
     */
    private function assembleCertificates(array $metadata)
    {
        $certificateFactory = new X509CertificateFactory();
        $certificates = array();

        // Try the primary certificate.
        $certData = Utils::ifsetor($metadata, 'certData');
        if (!$certData) {
            return $certificates;
        }

        $certificates[] = new X509CertificateLazyProxy($certificateFactory, $certData);

        // If we have a primary we may have a secondary.
        $certData2 = Utils::ifsetor($metadata, 'certData2');
        if (!$certData2) {
            return $certificates;
        }

        $certificates[] = new X509CertificateLazyProxy($certificateFactory, $certData2);

        // If we have a secondary we may have a tertiary.
        $certData3 = Utils::ifsetor($metadata, 'certData3');
        if (!$certData3) {
            return $certificates;
        }

        $certificates[] = new X509CertificateLazyProxy($certificateFactory, $certData3);

        return $certificates;
    }

    /**
     * @param array $metadata
     * @param $type
     * @return array
     * @throws \RuntimeException
     */
    private function assembleIndexedServices(array $metadata, $type)
    {
        $services = array();
        for ($i = 0; $i < 10; $i++) {
            $bindingKey = $type . ":$i:Binding";
            $bindingValue = Utils::ifsetor($metadata, $bindingKey);

            $locationKey = $type . ":$i:Location";
            $locationValue = Utils::ifsetor($metadata, $locationKey);

            if (!$bindingValue && !$locationValue) {
                continue;
            }

            if (!$bindingValue && $locationValue) {
                // @todo warn
                continue;
            }

            if ($bindingValue && !$locationValue) {
                // @todo warn
                continue;
            }

            $services[$i] = new IndexedService($locationValue, $bindingValue, $i);
        }
        return $services;
    }

    /**
     * @param array $metadata
     * @return null|Logo
     */
    private function assembleLogo(array $metadata)
    {
        $url = Utils::ifsetor($metadata, 'logo:0:url');
        if (!$url) {
            return null;
        }

        $logo = new Logo($url);
        $logo->width  = Utils::ifsetor($metadata, 'logo:0:width');
        $logo->height = Utils::ifsetor($metadata, 'logo:0:height');
        return $logo;
    }

    // @codingStandardsIgnoreStart

    /**
     * @param array $metadata
     * @return null|Organization
     */
    private function assembleOrganizationNl(array $metadata)
    {
        $organizationNameNl         = Utils::ifsetor($metadata, 'OrganizationName:nl'        , '');
        $organizationDisplayNameNl  = Utils::ifsetor($metadata, 'OrganizationDisplayName:nl' , '');
        $organizationUrlNl          = Utils::ifsetor($metadata, 'OrganizationURL:nl'         , '');

        if (!$organizationNameNl || !$organizationDisplayNameNl || !$organizationUrlNl) {
            return null;
        }

        return new Organization($organizationNameNl, $organizationDisplayNameNl, $organizationUrlNl);
    }

    /**
     * @param array $metadata
     * @return null|Organization
     */
    private function assembleOrganizationEn(array $metadata)
    {
        $organizationNameEn         = Utils::ifsetor($metadata, 'OrganizationName:en'        , false);
        $organizationDisplayNameEn  = Utils::ifsetor($metadata, 'OrganizationDisplayName:en' , false);
        $organizationUrlEn          = Utils::ifsetor($metadata, 'OrganizationURL:en'         , false);

        if (!$organizationNameEn || !$organizationDisplayNameEn || !$organizationUrlEn) {
            return null;
        }

        return new Organization($organizationNameEn, $organizationDisplayNameEn, $organizationUrlEn);
    }

    // @codingStandardsIgnoreEnd

    /**
     * @param array $metadata
     * @param array $defaults
     * @return array|null
     */
    private function assembleNameIdFormats(array $metadata)
    {
        $nameIdFormats = array_filter(array(
            Utils::ifsetor($metadata, 'NameIDFormats:0'),
            Utils::ifsetor($metadata, 'NameIDFormats:1'),
            Utils::ifsetor($metadata, 'NameIDFormats:2'),
        ));
        if (empty($nameIdFormats)) {
            return null;
        }

        return $nameIdFormats;
    }

    /**
     * @param array $metadata
     * @return null|Service
     */
    private function assembleSloServices(array $metadata)
    {
        $sloBinding  = Utils::ifsetor($metadata, 'SingleLogoutService_Binding');
        $sloLocation = Utils::ifsetor($metadata, 'SingleLogoutService_Location');

        if (!$sloBinding || !$sloLocation) {
            return null;
        }

        return new Service($sloLocation, $sloBinding);
    }

    /**
     * @param array $metadata
     * @return array
     */
    private function assembleShibMdScopes(array $metadata)
    {
        $scopes = array();
        for ($i = 0; $i < 10; $i++) {
            $allowedKey = "shibmd:scope:$i:allowed";
            $allowedValue = Utils::ifsetor($metadata, $allowedKey);

            $regexpKey = "shibmd:scope:$i:regexp";
            $regexpValue = Utils::ifsetor($metadata, $regexpKey);

            if (!$allowedValue) {
                continue;
            }

            $scope = new ShibMdScope();
            $scope->allowed = $allowedValue;
            $scope->regexp = $regexpValue;
            $scopes[] = $scope;
        }
        return $scopes;
    }

    /**
     * @param array $metadata
     * @return array
     */
    private function assembleContactPersons(array $metadata)
    {
        $contactPersons = array();
        for ($i = 0; $i < 3; $i++) {
            $contactTypeKey = "contacts:$i:contactType";
            $contactType = Utils::ifsetor($metadata, $contactTypeKey);
            if ($contactType) {
                $contactPerson = new ContactPerson($contactType);
                $contactPerson->emailAddress = Utils::ifsetor($metadata, "contacts:$i:emailAddress", '');
                $contactPerson->telephoneNumber = Utils::ifsetor($metadata, "contacts:$i:telephoneNumber", '');
                $contactPerson->givenName    = Utils::ifsetor($metadata, "contacts:$i:givenName", '');
                $contactPerson->surName      = Utils::ifsetor($metadata, "contacts:$i:surName", '');
                $contactPersons[] = $contactPerson;
            }
        }
        return $contactPersons;
    }

    /**
     * @param array $metadata
     * @return array
     */
    private function assembleSpEntityIdsWithoutConsent(array $metadata)
    {
        $i = 0;
        $spsEntityIdsWithoutConsent = array();
        /** @noinspection PhpAssignmentInConditionInspection */
        while ($disableConsentEntityId = Utils::ifsetor($metadata, 'disableConsent:' . $i++)) {
            $spsEntityIdsWithoutConsent[] = $disableConsentEntityId;
        }

        return $spsEntityIdsWithoutConsent;
    }
}
