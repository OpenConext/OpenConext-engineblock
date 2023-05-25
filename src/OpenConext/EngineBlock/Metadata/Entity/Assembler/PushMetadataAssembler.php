<?php

/**
 * Copyright 2010 SURFnet B.V.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace OpenConext\EngineBlock\Metadata\Entity\Assembler;

use DateTime;
use OpenConext\EngineBlock\Metadata\AttributeReleasePolicy;
use OpenConext\EngineBlock\Metadata\ConsentSettings;
use OpenConext\EngineBlock\Metadata\ContactPerson;
use OpenConext\EngineBlock\Metadata\EmptyMduiElement;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Metadata\Factory\MduiPushAssemblerFactory;
use OpenConext\EngineBlock\Metadata\IndexedService;
use OpenConext\EngineBlock\Metadata\Logo;
use OpenConext\EngineBlock\Metadata\Mdui;
use OpenConext\EngineBlock\Metadata\MduiElement;
use OpenConext\EngineBlock\Metadata\MfaEntityCollection;
use OpenConext\EngineBlock\Metadata\MultilingualElement;
use OpenConext\EngineBlock\Metadata\MultilingualValue;
use OpenConext\EngineBlock\Metadata\Organization;
use OpenConext\EngineBlock\Metadata\Service;
use OpenConext\EngineBlock\Metadata\ShibMdScope;
use OpenConext\EngineBlock\Metadata\StepupConnections;
use OpenConext\EngineBlock\Metadata\Utils;
use OpenConext\EngineBlock\Metadata\X509\X509CertificateFactory;
use OpenConext\EngineBlock\Metadata\X509\X509CertificateLazyProxy;
use OpenConext\EngineBlock\Validator\ValidatorInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use stdClass;
use function array_key_exists;

/**
 * @SuppressWarnings(PMD)
 */
class PushMetadataAssembler implements MetadataAssemblerInterface
{
    /**
     * @var ValidatorInterface
     */
    private $allowedAcsLocationsValidator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Maximum length that will fit in certain string type fields.
     */
    private const FIELDS_MAX_LENGTH = 255;

    public function __construct(ValidatorInterface $allowedAcsLocations, LoggerInterface $logger)
    {
        $this->allowedAcsLocationsValidator = $allowedAcsLocations;
        $this->logger = $logger;
    }

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

        if (count($roles) === 0) {
            throw new RuntimeException('Received 0 connections, refusing to process');
        }

        return $roles;
    }

    /**
     * Shorten possibly lengthy fields that need to fit into a varchar(255).
     * If they are longer than this, we likely do not use the contents for
     * anything useful so it's OK to just chop it off. Otherwise the assembler
     * will get errors when using MySQL with strict mode enabled.
     */
    private function limitValueLength(string $value): string
    {
        if (strlen($value) < self::FIELDS_MAX_LENGTH) {
            return $value;
        }
        $this->logger->info(sprintf("Push Metadata Assembler: truncating too long value: '%s'", $value));
        return mb_strcut($value, 0, self::FIELDS_MAX_LENGTH);
    }

    /**
     * @param stdClass $connection
     * @return IdentityProvider|ServiceProvider
     */
    private function assembleConnection(stdClass $connection)
    {
        if ($connection->type === 'saml20-sp') {
            return $this->assembleSp($connection);
        }

        if ($connection->type === 'saml20-idp') {
            return $this->assembleIdp($connection);
        }

        throw new RuntimeException(
            sprintf('Unrecognized type: "%s" "%s"', $connection->type, var_export($connection, true))
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
        $properties += $this->setPathFromObjectBool(
            array(
                $connection,
                'metadata:coin:transparant_issuer'
            ),
            'isTransparentIssuer'
        );
        $properties += $this->setPathFromObjectBool(
            array(
                $connection,
                'metadata:coin:trusted_proxy'
            ),
            'isTrustedProxy'
        );
        $properties += $this->setPathFromObjectBool(
            array(
                $connection,
                'metadata:coin:display_unconnected_idps_wayf'
            ),
            'displayUnconnectedIdpsWayf'
        );

        $properties += $this->assembleIsConsentRequired($connection);

        $properties += $this->setPathFromObjectString(
            array(
                $connection,
                'metadata:coin:eula'
            ),
            'termsOfServiceUrl'
        );
        $properties += $this->setPathFromObjectBool(
            array(
                $connection,
                'metadata:coin:do_not_add_attribute_aliases'
            ),
            'skipDenormalization'
        );
        $properties += $this->setPathFromObjectBool(
            array(
                $connection,
                'metadata:coin:policy_enforcement_decision_required'
            ),
            'policyEnforcementDecisionRequired'
        );

        $properties += $this->setPathFromObjectBool(
            array(
                $connection,
                'metadata:coin:requesterid_required'
            ),
            'requesteridRequired'
        );

        $properties += $this->setPathFromObjectBool(
            array(
                $connection,
                'metadata:coin:sign_response'
            ),
            'signResponse'
        );
        $properties += $this->setPathFromObjectString(
            array(
                $connection,
                'metadata:coin:stepup:requireloa'
            ),
            'stepupRequireLoa'
        );
        $properties += $this->setPathFromObjectBool(
            array(
                $connection,
                'metadata:coin:stepup:allow_no_token'
            ),
            'stepupAllowNoToken'
        );
        $properties += $this->setPathFromObjectBool(
            array(
                $connection,
                'metadata:coin:stepup:forceauthn'
            ),
            'stepupForceAuthn'
        );
        return Utils::instantiate(
            ServiceProvider::class,
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
        $properties += $this->setPathFromObjectString(array($connection, 'metadata:coin:guest_qualifier'), 'guestQualifier');
        $properties += $this->setPathFromObjectString(array($connection, 'metadata:coin:schachomeorganization'), 'schacHomeOrganization');
        $properties += $this->assembleConsentSettings($connection);
        $properties += $this->setPathFromObjectBool(array($connection, 'metadata:coin:hidden'), 'hidden');
        $properties += $this->assembleShibMdScopes($connection);

        $properties += $this->assembleStepupConnections($connection);
        $properties += $this->assembleMfaEntities($connection);

        return Utils::instantiate(
            IdentityProvider::class,
            $properties
        );
    }

    private function assembleCommon(stdClass $connection)
    {
        $properties = array();

        $properties += $this->setPathFromObjectString(array($connection, 'name'), 'entityId');
        $properties += $this->setPathFromObjectString(array($connection, 'metadata:name:nl'), 'nameNl');
        $properties += $this->setPathFromObjectString(array($connection, 'metadata:name:en'), 'nameEn');
        $properties += $this->setPathFromObjectString(array($connection, 'metadata:name:pt'), 'namePt');
        $properties += $this->setPathFromObjectString(array($connection, 'metadata:displayName:nl'), 'displayNameNl');
        $properties += $this->setPathFromObjectString(array($connection, 'metadata:displayName:en'), 'displayNameEn');
        $properties += $this->setPathFromObjectString(array($connection, 'metadata:displayName:pt'), 'displayNamePt');
        $properties += $this->setPathFromObjectString(array($connection, 'metadata:description:nl'), 'descriptionNl', true);
        $properties += $this->setPathFromObjectString(array($connection, 'metadata:description:en'), 'descriptionEn', true);
        $properties += $this->setPathFromObjectString(array($connection, 'metadata:description:pt'), 'descriptionPt', true);
        $properties += $this->assembleLogo($connection);
        $properties += $this->assembleOrganization($connection, 'nl');
        $properties += $this->assembleOrganization($connection, 'en');
        $properties += $this->assembleOrganization($connection, 'pt');
        $properties += $this->setPathFromObjectString(array($connection, 'metadata:keywords:en'), 'keywordsEn', true);
        $properties += $this->setPathFromObjectString(array($connection, 'metadata:keywords:nl'), 'keywordsNl', true);
        $properties += $this->setPathFromObjectString(array($connection, 'metadata:keywords:pt'), 'keywordsPt', true);

        $properties += $this->assembleCertificates($connection);
        $properties += $this->setPathFromObjectString(array($connection, 'state'), 'workflowState');
        $properties += $this->assembleContactPersons($connection);
        $properties += $this->setPathFromObjectString(array($connection, 'metadata:NameIDFormat'), 'nameIdFormat');
        $properties += $this->setPathFromObjectArray(array($connection, 'metadata:NameIDFormats'), 'supportedNameIdFormats');
        $properties += $this->assembleSingleLogoutServices($connection);
        $properties += $this->setPathFromObjectBool(array($connection, 'metadata:coin:disable_scoping'), 'disableScoping');

        $properties += $this->setPathFromObjectBool(array($connection, 'metadata:coin:additional_logging'), 'additionalLogging');
        $properties += $this->setPathFromObjectString(array($connection, 'metadata:coin:signature_method'), 'signatureMethod');
        $properties += $this->setPathFromObjectBool(array($connection, 'metadata:redirect:sign'), 'requestsMustBeSigned');
        $properties += $this->setPathFromObjectString(array($connection, 'manipulation_code'), 'manipulation');
        $properties += $this->setPathFromObjectString(array($connection, 'metadata:url:en'), 'supportUrlEn');
        $properties += $this->setPathFromObjectString(array($connection, 'metadata:url:nl'), 'supportUrlNl');
        $properties += $this->setPathFromObjectString(array($connection, 'metadata:url:pt'), 'supportUrlPt');

        $properties['mdui'] = MduiPushAssemblerFactory::buildFrom($properties, $connection);

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
        $name = null;
        $displayName = null;
        $url = null;
        if (isset($connection->metadata->OrganizationName) && isset($connection->metadata->OrganizationName->$langCode)) {
            $name = $connection->metadata->OrganizationName->$langCode;
        }
        if (isset($connection->metadata->OrganizationDisplayName) && isset($connection->metadata->OrganizationDisplayName->$langCode)) {
            $displayName = $connection->metadata->OrganizationDisplayName->$langCode;
        }
        if (isset($connection->metadata->OrganizationURL) && isset($connection->metadata->OrganizationURL->$langCode)) {
            $url = $connection->metadata->OrganizationURL->$langCode;
        }
        return array('organization' . ucfirst($langCode) => new Organization($name, $displayName, $url));
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

    private function setPathFromObjectString(array $from, string $to, bool $limitlength = false): array
    {
        $reference = $this->getValueFromPath($from);
        if (is_null($reference)) {
            return array($to => null);
        }
        if ($limitlength) {
            $reference = $this->limitValueLength($reference);
        }
        return array($to => (string)$reference);
    }

    private function setPathFromObjectArray(array $from, string $to): array
    {
        $reference = $this->getValueFromPath($from);
        if (!array($reference)) {
            return array();
        }
        return array($to => $reference);
    }

    private function setPathFromObjectBool(array $from, string $to): array
    {
        $reference = $this->getValueFromPath($from);
        if (is_null($reference)) {
            return array($to => null);
        }
        return array($to => (bool)$reference);
    }

    private function getValueFromPath(array $from)
    {
        $pathParts = explode(':', $from[1]);

        $reference = $from[0];
        while ($pathPart = array_shift($pathParts)) {
            if (!isset($reference->$pathPart)) {
                return null;
            }

            $reference = $reference->$pathPart;
        }

        return $reference;
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

    private function assembleConsentSettings(stdClass $connection)
    {
        if (empty($connection->disable_consent_connections)) {
            return array();
        }

        return array(
            'consentSettings' => new ConsentSettings(
                (array)$connection->disable_consent_connections
            ),
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

    private function assembleStepupConnections(stdClass $connection)
    {
        if (empty($connection->stepup_connections)) {
            return array();
        }

        $connections = [];
        foreach ($connection->stepup_connections as $sp) {
            $connections[(string)$sp->name] = (string)$sp->level;
        }

        return array(
            'stepupConnections' => new StepupConnections(
                $connections
            ),
        );
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

            // Only allow ACS locations with a verified URI scheme
            if (!$this->allowedAcsLocationsValidator->validate($assertionConsumerServiceMetadata->Location)) {
                throw new RuntimeException('The acs metadata contained an invalid location uri scheme');
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

    private function assembleMfaEntities(stdClass $connection): array
    {
        if (!isset($connection->mfa_entities)) {
            return [];
        }

        $entities = [];
        foreach ($connection->mfa_entities as $sp) {
            $entities[] = [
                'name' => (string)$sp->name,
                'level' => (string)$sp->level,
            ];
        }

        return [
            'mfaEntities' => MfaEntityCollection::fromMetadataPush($entities)
        ];
    }
}
