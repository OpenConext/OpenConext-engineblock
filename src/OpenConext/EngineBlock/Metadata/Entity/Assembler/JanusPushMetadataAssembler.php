<?php

namespace OpenConext\EngineBlock\Metadata\Entity\Assembler;

use DateTime;
use OpenConext\EngineBlock\Metadata\AttributeReleasePolicy;
use OpenConext\EngineBlock\Metadata\ContactPerson;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Metadata\IndexedService;
use OpenConext\EngineBlock\Metadata\Logo;
use OpenConext\EngineBlock\Metadata\Organization;
use OpenConext\EngineBlock\Metadata\Service;
use OpenConext\EngineBlock\Metadata\ShibMdScope;
use OpenConext\EngineBlock\Metadata\Utils;
use OpenConext\EngineBlock\Metadata\X509\X509CertificateFactory;
use OpenConext\EngineBlock\Metadata\X509\X509CertificateLazyProxy;
use RuntimeException;
use stdClass;

/**
 * Class JanusPushMetadataAssembler
 * @package OpenConext\EngineBlock\Metadata\Entity\Assembler
 * @SuppressWarnings(PMD)
 */
class JanusPushMetadataAssembler
{
    public function assemble($connections)
    {
        $roles = array();
        $allIdpEntityIds = array();
        $spAllowedEntityIds = array();
        $idpAllowedEntityIds = array();

        foreach ($connections as $connection) {
            $role = $this->assembleConnection($connection);

            if ($role instanceof ServiceProvider) {
                if (isset($connection->allowed_connections)) {
                    $spAllowedEntityIds[$role->entityId] = array_map(
                        function ($allowedConnection) {
                            return $allowedConnection->name;
                        },
                        $connection->allowed_connections
                    );
                }

                if ($connection->allow_all_entities) {
                    $spAllowedEntityIds[$role->entityId] = true;
                }
            }

            if ($role instanceof IdentityProvider) {
                $allIdpEntityIds[] = $role->entityId;

                if (isset($connection->allowed_connections)) {
                    $idpAllowedEntityIds[$role->entityId] = array_map(
                        function ($allowedConnection) {
                            return $allowedConnection->name;
                        },
                        $connection->allowed_connections
                    );
                }

                if ($connection->allow_all_entities) {
                    $idpAllowedEntityIds[$role->entityId] = true;
                }
            }

            $roles[] = $role;
        }

        // For all service providers
        foreach ($roles as $role) {
            if (!$role instanceof ServiceProvider) {
                continue;
            }

            // Get the IdPs that are allowed for this SP.
            $allowedIdpEntityIds = $spAllowedEntityIds[$role->entityId];
            if ($allowedIdpEntityIds === true) {
                $allowedIdpEntityIds = $allIdpEntityIds;
            }

            // Strip out the IdPs that disallow the SP
            foreach ($idpAllowedEntityIds as $idpEntityId => $allowedSpEntityIds) {
                if ($allowedSpEntityIds === true) {
                    continue;
                }

                if (in_array($role->entityId, $allowedSpEntityIds)) {
                    continue;
                }

                $index = array_search($idpEntityId, $allowedIdpEntityIds);

                if ($index === false) {
                    continue;
                }


                unset($allowedIdpEntityIds[$index]);
            }

            if ($allowedIdpEntityIds === $allIdpEntityIds) {
                // If a blacklist was configured, and no IDPs were explicitly
                // blacklisted, then don't keep track of all entity IDs, but
                // remember that all IDPs are allowed.
                $role->allowAll = true;
            } else {
                $role->allowedIdpEntityIds = $allowedIdpEntityIds;
            }
        }
        return $roles;
    }

    /**
     * @param stdClass $connection
     * @return IdentityProvider|ServiceProvider
     */
    public function assembleConnection(stdClass $connection)
    {
        if ($connection->type === 'saml20-sp') {
            return $this->assembleSp($connection);
        }

        if ($connection->type === 'saml20-idp') {
            return $this->assembleIdp($connection);
        }

        throw new RuntimeException(
            "Unrecognized type: '{$connection->type}'"
            . var_export($connection, true)
        );
    }

    /**
     * @param stdClass $connection
     * @return ServiceProvider
     */
    private function assembleSp(stdClass $connection)
    {
        $properties = $this->assembleCommon($connection);

        $properties += $this->assembleAttributeReleasePolicy($connection);
        $properties += $this->assembleAssertionConsumerServices($connection);
        $properties += $this->setPathFromObject(
            array(
                $connection,
                'metadata:coin:transparant_issuer'
            ),
            'isTransparentIssuer'
        );
        $properties += $this->setPathFromObject(
            array(
                $connection,
                'metadata:coin:trusted_proxy'
            ),
            'isTrustedProxy'
        );
        $properties += $this->setPathFromObject(
            array(
                $connection,
                'metadata:coin:display_unconnected_idps_wayf'
            ),
            'displayUnconnectedIdpsWayf'
        );

        $properties += $this->assembleIsConsentRequired($connection);

        $properties += $this->setPathFromObject(
            array(
                $connection,
                'metadata:coin:eula'
            ),
            'termsOfServiceUrl'
        );
        $properties += $this->setPathFromObject(
            array(
                $connection,
                'metadata:coin:do_not_add_attribute_aliases'
            ),
            'skipDenormalization'
        );
        $properties += $this->setPathFromObject(
            array(
                $connection,
                'metadata:coin:policy_enforcement_decision_required'
            ),
            'policyEnforcementDecisionRequired'
        );
        $properties += $this->setPathFromObject(
            array(
                $connection,
                'metadata:coin:attribute_aggregation_required'
            ),
            'attributeAggregationRequired'
        );

        return Utils::instantiate(
            'OpenConext\EngineBlock\Metadata\Entity\ServiceProvider',
            $properties
        );
    }

    /**
     * @param stdClass $connection
     * @return IdentityProvider
     */
    private function assembleIdp(stdClass $connection)
    {
        $properties = $this->assembleCommon($connection);

        $properties += $this->assembleSingleSignOnServices($connection);
        $properties += $this->setPathFromObject(array($connection, 'metadata:coin:guest_qualifier'), 'guestQualifier');
        $properties += $this->setPathFromObject(array($connection, 'metadata:coin:schachomeorganization'), 'schacHomeOrganization');
        $properties += $this->assembleSpEntityIdsWithoutConsent($connection);
        $properties += $this->setPathFromObject(array($connection, 'metadata:coin:hidden'), 'hidden');
        $properties += $this->assembleShibMdScopes($connection);

        return Utils::instantiate(
            'OpenConext\EngineBlock\Metadata\Entity\IdentityProvider',
            $properties
        );
    }

    private function assembleCommon(stdClass $connection)
    {
        $properties = array();

        $properties += $this->setPathFromObject(array($connection, 'name'), 'entityId');
        $properties += $this->setPathFromObject(array($connection, 'metadata:name:nl'), 'nameNl');
        $properties += $this->setPathFromObject(array($connection, 'metadata:name:en'), 'nameEn');
        $properties += $this->setPathFromObject(array($connection, 'metadata:displayName:nl'), 'displayNameNl');
        $properties += $this->setPathFromObject(array($connection, 'metadata:displayName:en'), 'displayNameEn');
        $properties += $this->setPathFromObject(array($connection, 'metadata:description:nl'), 'descriptionNl');
        $properties += $this->setPathFromObject(array($connection, 'metadata:description:en'), 'descriptionEn');
        $properties += $this->assembleLogo($connection);
        $properties += $this->assembleOrganization($connection, 'nl');
        $properties += $this->assembleOrganization($connection, 'en');
        $properties += $this->setPathFromObject(array($connection, 'metadata:keywords:en'), 'keywordsEn');
        $properties += $this->setPathFromObject(array($connection, 'metadata:keywords:nl'), 'keywordsNl');
        $properties += $this->setPathFromObject(array($connection, 'metadata:coin:publish_in_edugain'), 'publishInEdugain');
        $properties += $this->assembleCertificates($connection);
        $properties += $this->setPathFromObject(array($connection, 'state'), 'workflowState');
        $properties += $this->assembleContactPersons($connection);
        $properties += $this->setPathFromObject(array($connection, 'metadata:NameIDFormat'), 'nameIdFormat');
        $properties += $this->setPathFromObject(array($connection, 'metadata:NameIDFormats'), 'supportedNameIdFormats');
        $properties += $this->assembleSingleLogoutServices($connection);
        $properties += $this->assemblePublishInEdugainDate($connection);
        $properties += $this->setPathFromObject(array($connection, 'metadata:coin:disable_scoping'), 'disableScoping');
        $properties += $this->setPathFromObject(array($connection, 'metadata:coin:additional_logging'), 'additionalLogging');
        $properties += $this->setPathFromObject(array($connection, 'metadata:coin:signature_method'), 'signatureMethod');
        $properties += $this->setPathFromObject(array($connection, 'metadata:redirect:sign'), 'requestsMustBeSigned');
        $properties += $this->setPathFromObject(array($connection, 'manipulation_code'), 'manipulation');

        return $properties;
    }

    private function assembleLogo(stdClass $connection)
    {
        if (empty($connection->metadata->logo[0]->url)) {
            return array();
        }

        $assembled = new Logo($connection->metadata->logo[0]->url);
        if (!empty($connection->metadata->logo[0]->width)) {
            $assembled->width = $connection->metadata->logo[0]->width;
        }
        if (!empty($connection->metadata->logo[0]->height)) {
            $assembled->height = $connection->metadata->logo[0]->height;
        }
        return array('logo' => $assembled);
    }

    private function assembleOrganization(stdClass $connection, $langCode)
    {
        if (empty($connection->metadata->OrganizationName->$langCode)) {
            return array();
        }

        if (empty($connection->metadata->OrganizationDisplayName->$langCode)) {
            return array();
        }

        if (empty($connection->metadata->OrganizationURL->$langCode)) {
            return array();
        }

        return array('organization' . ucfirst($langCode) => new Organization(
            $connection->metadata->OrganizationName->$langCode,
            $connection->metadata->OrganizationDisplayName->$langCode,
            $connection->metadata->OrganizationURL->$langCode
        ));
    }

    private function assemblePublishInEdugainDate(stdClass $connection)
    {
        if (empty($connection->coin->publish_in_edugain)) {
            return array();
        }

        return new DateTime($connection->coin->publish_in_edugain);
    }

    private function assembleCertificates(stdClass $connection)
    {
        $certificateFactory = new X509CertificateFactory();

        // Try the primary certificate.
        if (empty($connection->metadata->certData)) {
            return array();
        }

        $certificates = array();
        $certificates[] = new X509CertificateLazyProxy($certificateFactory, $connection->metadata->certData);

        // If we have a primary we may have a secondary.
        if (empty($connection->metadata->certData2)) {
            return array('certificates' => $certificates);
        }

        $certificates[] = new X509CertificateLazyProxy($certificateFactory, $connection->metadata->certData2);

        // If we have a secondary we may have a tertiary.
        if (empty($connection->metadata->certData3)) {
            return array('certificates' => $certificates);
        }

        $certificates[] = new X509CertificateLazyProxy($certificateFactory, $connection->metadata->certData3);

        return array('certificates' => $certificates);
    }

    private function assembleContactPersons($connection)
    {
        $contactPersons = array();
        for ($i = 0; $i < 3; $i++) {
            if (empty($connection->metadata->contacts[$i]->contactType)) {
                continue;
            }
            $contactMetadata = $connection->metadata->contacts[$i];
            $contactPerson = new ContactPerson($contactMetadata->contactType);
            if (!empty($contactMetadata->emailAddress)) {
                $contactPerson->emailAddress = $contactMetadata->emailAddress;
            }
            if (!empty($contactMetadata->telephoneNumber)) {
                $contactPerson->telephoneNumber = $contactMetadata->telephoneNumber;
            }
            if (!empty($contactMetadata->givenName)) {
                $contactPerson->givenName = $contactMetadata->givenName;
            }
            if (!empty($contactMetadata->surName)) {
                $contactPerson->surName = $contactMetadata->surName;
            }
            $contactPersons[] = $contactPerson;
        }
        return empty($contactPersons) ? array() : array('contactPersons' => $contactPersons);
    }

    private function assembleSingleLogoutServices($connection)
    {
        if (empty($connection->metadata->SingleLogoutService[0]->Location)) {
            return array();
        }
        $serviceMetadata = $connection->metadata->SingleLogoutService[0];
        return array('singleLogoutService' => new Service(
            $serviceMetadata->Location,
            $serviceMetadata->Binding
        ));
    }

    private function setPathFromObject($from, $to)
    {
        $pathParts = explode(':', $from[1]);

        $reference = $from[0];
        while ($pathPart = array_shift($pathParts)) {
            if (!isset($reference->$pathPart)) {
                return array();
            }

            $reference = $reference->$pathPart;
        }
        return array($to => $reference);
    }

    private function assembleSingleSignOnServices($connection)
    {
        if (empty($connection->metadata->SingleSignOnService)) {
            return array();
        }

        $services = array();
        foreach ($connection->metadata->SingleSignOnService as $singleSignOnServiceMetadata) {
            if (empty($singleSignOnServiceMetadata->Location)) {
                continue;
            }
            if (empty($singleSignOnServiceMetadata->Binding)) {
                continue;
            }

            $services[] = new Service($singleSignOnServiceMetadata->Location, $singleSignOnServiceMetadata->Binding);
        }
        return array('singleSignOnServices' => $services);
    }

    private function assembleSpEntityIdsWithoutConsent(stdClass $connection)
    {
        if (empty($connection->disable_consent_connections)) {
            return array();
        }

        $entityIds = array_map(
            function ($disableConsentConnection) {
                // Detect manage >=2.0.18. If a type property is present, it
                // could be set to something other than 'no_consent'.
                if (isset($disableConsentConnection->type) && $disableConsentConnection->type !== 'no_consent') {
                    return;
                }

                return $disableConsentConnection->name;
            },
            $connection->disable_consent_connections
        );

        return array(
            'spsEntityIdsWithoutConsent' => array_filter($entityIds),
        );
    }

    private function assembleShibMdScopes($connection)
    {
        if (empty($connection->metadata->shibmd->scope)) {
            return array();
        }

        $shibMdScopes = array();

        foreach ($connection->metadata->shibmd->scope as $scopeMetadata) {
            if (empty($scopeMetadata->allowed)) {
                continue;
            }

            $scope = new ShibMdScope();
            $scope->allowed = $scopeMetadata->allowed;
            if (!empty($scopeMetadata->regexp)) {
                $scope->regexp = $scopeMetadata->regexp;
            }
            $shibMdScopes[] = $scope;
        }

        return array('shibMdScopes' => $shibMdScopes);
    }

    private function assembleAssertionConsumerServices(stdClass $connection)
    {
        if (empty($connection->metadata->AssertionConsumerService)) {
            return array();
        }

        $services = array();
        $index = 0;
        foreach ($connection->metadata->AssertionConsumerService as $assertionConsumerServiceMetadata) {
            if (empty($assertionConsumerServiceMetadata->Location)) {
                continue;
            }
            if (empty($assertionConsumerServiceMetadata->Binding)) {
                continue;
            }

            if (!empty($assertionConsumerServiceMetadata->Index)) {
                $index = (int) $assertionConsumerServiceMetadata->Index;
            }

            $services[] = new IndexedService(
                $assertionConsumerServiceMetadata->Location,
                $assertionConsumerServiceMetadata->Binding,
                $index
            );

            $index += 1;
        }
        return array('assertionConsumerServices' => $services);
    }

    private function assembleIsConsentRequired(stdClass $connection)
    {
        if (empty($connection->metadata->coin->no_consent_required)) {
            return array();
        }

        return array( 'isConsentRequired' => !$connection->metadata->coin->no_consent_required );
    }

    private function assembleAttributeReleasePolicy(stdClass $connection)
    {
        if (empty($connection->arp_attributes)) {
            return array();
        }

        // EngineBlock expects objects in the metadata in many places so we
        // can't decode the metadata with assoc=true. ARP rules should always
        // be arrays so we explicitly cast the ARP rules to arrays here.
        foreach ($connection->arp_attributes as &$rules) {
            foreach ($rules as &$rule) {
                if (is_object($rule)) {
                    $rule = (array) $rule;
                }
            }
        }

        return array(
            'attributeReleasePolicy' => new AttributeReleasePolicy(
                (array) $connection->arp_attributes
            )
        );
    }
}
