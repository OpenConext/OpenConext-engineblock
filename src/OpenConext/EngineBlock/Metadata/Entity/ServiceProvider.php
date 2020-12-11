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

namespace OpenConext\EngineBlock\Metadata\Entity;

use OpenConext\EngineBlock\Metadata\AttributeReleasePolicy;
use OpenConext\EngineBlock\Metadata\Coins;
use OpenConext\EngineBlock\Metadata\ContactPerson;
use OpenConext\EngineBlock\Metadata\Factory\ServiceProviderEntityInterface;
use OpenConext\EngineBlock\Metadata\Logo;
use OpenConext\EngineBlock\Metadata\MetadataRepository\Visitor\VisitorInterface;
use OpenConext\EngineBlock\Metadata\Organization;
use OpenConext\EngineBlock\Metadata\RequestedAttribute;
use OpenConext\EngineBlock\Metadata\IndexedService;
use OpenConext\EngineBlock\Metadata\Service;
use OpenConext\EngineBlock\Metadata\X509\X509Certificate;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use SAML2\Constants;
use Doctrine\ORM\Mapping as ORM;

/**
 * @package OpenConext\EngineBlock\Metadata\Entity
 * @ORM\Entity
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 *
 * WARNING: Please don't use this entity directly but use the dedicated factory instead.
 * @see \OpenConext\EngineBlock\Factory\Factory\ServiceProviderFactory
 */
class ServiceProvider extends AbstractRole
{
    /**
     * @var null|AttributeReleasePolicy
     *
     * @ORM\Column(name="attribute_release_policy", type="array")
     */
    public $attributeReleasePolicy;

    /**
     * @var IndexedService[]
     *
     * @ORM\Column(name="assertion_consumer_services", type="array")
     */
    public $assertionConsumerServices;

    /**
     * @var string[]
     *
     * @ORM\Column(name="allowed_idp_entity_ids", type="array")
     */
    public $allowedIdpEntityIds;

    /**
     * @var bool
     *
     * @ORM\Column(name="allow_all", type="boolean")
     */
    public $allowAll;

    /**
     * @var null|RequestedAttribute[]
     *
     * @ORM\Column(name="requested_attributes", type="array")
     */
    public $requestedAttributes;

    /**
     * @var null|string
     *
     * @ORM\Column(name="support_url_en", type="string", nullable=true)
     */
    public $supportUrlEn;

    /**
     * @var null|string
     *
     * @ORM\Column(name="support_url_nl", type="string", nullable=true)
     */
    public $supportUrlNl;

    /**
     * @var null|string
     *
     * @ORM\Column(name="support_url_pt", type="string", nullable=true)
     */
    public $supportUrlPt;

    /**
     * WARNING: Please don't use this entity directly but use the dedicated factory instead.
     * @see \OpenConext\EngineBlock\Factory\Factory\ServiceProviderFactory
     *
     * @param string $entityId
     * @param Organization $organizationEn
     * @param Organization $organizationNl
     * @param Organization $organizationPt
     * @param Service $singleLogoutService
     * @param bool $additionalLogging
     * @param X509Certificate[] $certificates
     * @param ContactPerson[] $contactPersons
     * @param string $descriptionEn
     * @param string $descriptionNl
     * @param string $descriptionPt
     * @param bool $disableScoping
     * @param string $displayNameEn
     * @param string $displayNameNl
     * @param string $displayNamePt
     * @param string $keywordsEn
     * @param string $keywordsNl
     * @param string $keywordsPt
     * @param Logo $logo
     * @param string $nameEn
     * @param string $nameNl
     * @param string $namePt
     * @param null $nameIdFormat
     * @param array $supportedNameIdFormats
     * @param bool $requestsMustBeSigned
     * @param string $signatureMethod
     * @param string $workflowState
     * @param array $allowedIdpEntityIds
     * @param bool $allowAll
     * @param array $assertionConsumerServices
     * @param bool $displayUnconnectedIdpsWayf
     * @param null $termsOfServiceUrl
     * @param bool $isConsentRequired
     * @param bool $isTransparentIssuer
     * @param bool $isTrustedProxy
     * @param null $requestedAttributes
     * @param bool $skipDenormalization
     * @param bool $policyEnforcementDecisionRequired
     * @param bool $requesteridRequired
     * @param bool $signResponse
     * @param string $manipulation
     * @param AttributeReleasePolicy $attributeReleasePolicy
     * @param string|null $supportUrlEn
     * @param string|null $supportUrlNl
     * @param string|null $supportUrlPt
     * @param bool|null $stepupAllowNoToken
     * @param bool|null $stepupRequireLoa
     */
    public function __construct(
        $entityId,
        Organization $organizationEn = null,
        Organization $organizationNl = null,
        Organization $organizationPt = null,
        Service $singleLogoutService = null,
        $additionalLogging = false,
        array $certificates = array(),
        array $contactPersons = array(),
        $descriptionEn = '',
        $descriptionNl = '',
        $descriptionPt = '',
        $disableScoping = false,
        $displayNameEn = '',
        $displayNameNl = '',
        $displayNamePt = '',
        $keywordsEn = '',
        $keywordsNl = '',
        $keywordsPt = '',
        Logo $logo = null,
        $nameEn = '',
        $nameNl = '',
        $namePt = '',
        $nameIdFormat = null,
        $supportedNameIdFormats = array(
            Constants::NAMEID_TRANSIENT,
            Constants::NAMEID_PERSISTENT,
        ),
        $requestsMustBeSigned = false,
        $signatureMethod = XMLSecurityKey::RSA_SHA256,
        $workflowState = self::WORKFLOW_STATE_DEFAULT,
        array $allowedIdpEntityIds = array(),
        $allowAll = false,
        array $assertionConsumerServices = array(),
        $displayUnconnectedIdpsWayf = false,
        $termsOfServiceUrl = null,
        $isConsentRequired = true,
        $isTransparentIssuer = false,
        $isTrustedProxy = false,
        $requestedAttributes = null,
        $skipDenormalization = false,
        $policyEnforcementDecisionRequired = false,
        $requesteridRequired = false,
        $signResponse = false,
        $manipulation = '',
        AttributeReleasePolicy $attributeReleasePolicy = null,
        $supportUrlEn = null,
        $supportUrlNl = null,
        $supportUrlPt = null,
        $stepupAllowNoToken = null,
        $stepupRequireLoa = null
    ) {
        parent::__construct(
            $entityId,
            $organizationEn,
            $organizationNl,
            $organizationPt,
            $singleLogoutService,
            $certificates,
            $contactPersons,
            $descriptionEn,
            $descriptionNl,
            $descriptionPt,
            $displayNameEn,
            $displayNameNl,
            $displayNamePt,
            $keywordsEn,
            $keywordsNl,
            $keywordsPt,
            $logo,
            $nameEn,
            $nameNl,
            $namePt,
            $nameIdFormat,
            $supportedNameIdFormats,
            $requestsMustBeSigned,
            $workflowState,
            $manipulation
        );

        $this->attributeReleasePolicy = $attributeReleasePolicy;
        $this->allowedIdpEntityIds = $allowedIdpEntityIds;
        $this->allowAll = $allowAll;
        $this->assertionConsumerServices = $assertionConsumerServices;
        $this->requestedAttributes = $requestedAttributes;
        $this->supportUrlEn = $supportUrlEn;
        $this->supportUrlNl = $supportUrlNl;
        $this->supportUrlPt = $supportUrlPt;

        $this->coins = Coins::createForServiceProvider(
            $isConsentRequired,
            $isTransparentIssuer,
            $isTrustedProxy,
            $displayUnconnectedIdpsWayf,
            $termsOfServiceUrl,
            $skipDenormalization,
            $policyEnforcementDecisionRequired,
            $requesteridRequired,
            $signResponse,
            $stepupAllowNoToken,
            $stepupRequireLoa,
            $disableScoping,
            $additionalLogging,
            $signatureMethod
        );
    }

    /**
     * This is a factory method to convert the immutable ServiceProviderEntityInterface to the legacy domain entity.
     *
     * @param ServiceProviderEntityInterface $serviceProvider
     * @return ServiceProvider
     */
    public static function fromServiceProviderEntity(ServiceProviderEntityInterface $serviceProvider): ServiceProvider
    {
        $entity = new self($serviceProvider->getEntityId());
        $entity->id = $serviceProvider->getId();
        $entity->entityId = $serviceProvider->getEntityId();
        $entity->nameNl = $serviceProvider->getName('nl');
        $entity->nameEn = $serviceProvider->getName('en');
        $entity->namePt = $serviceProvider->getName('pt');
        $entity->descriptionNl = $serviceProvider->getDescription('nl');
        $entity->descriptionEn = $serviceProvider->getDescription('en');
        $entity->descriptionPt = $serviceProvider->getDescription('pt');
        $entity->displayNameNl = $serviceProvider->getDisplayName('nl');
        $entity->displayNameEn = $serviceProvider->getDisplayName('en');
        $entity->displayNamePt = $serviceProvider->getDisplayName('pt');
        $entity->logo = $serviceProvider->getLogo();
        $entity->organizationNl = $serviceProvider->getOrganization('nl');
        $entity->organizationEn = $serviceProvider->getOrganization('en');
        $entity->organizationPt = $serviceProvider->getOrganization('pt');
        $entity->keywordsNl = $serviceProvider->getKeywords('nl');
        $entity->keywordsEn = $serviceProvider->getKeywords('en');
        $entity->keywordsPt = $serviceProvider->getKeywords('pt');
        $entity->certificates = $serviceProvider->getCertificates();
        $entity->workflowState = $serviceProvider->getWorkflowState();
        $entity->contactPersons = $serviceProvider->getContactPersons();
        $entity->nameIdFormat = $serviceProvider->getNameIdFormat();
        $entity->supportedNameIdFormats = $serviceProvider->getSupportedNameIdFormats();
        $entity->singleLogoutService = $serviceProvider->getSingleLogoutService();
        $entity->requestsMustBeSigned = $serviceProvider->isRequestsMustBeSigned();
        $entity->manipulation = $serviceProvider->getManipulation();
        $entity->coins = $serviceProvider->getCoins();
        $entity->attributeReleasePolicy = $serviceProvider->getAttributeReleasePolicy();
        $entity->assertionConsumerServices = $serviceProvider->getAssertionConsumerServices();
        $entity->allowedIdpEntityIds = $serviceProvider->getAllowedIdpEntityIds();
        $entity->allowAll = $serviceProvider->isAllowAll();
        $entity->requestedAttributes = $serviceProvider->getRequestedAttributes();
        $entity->supportUrlNl = $serviceProvider->getSupportUrl('nl');
        $entity->supportUrlEn = $serviceProvider->getSupportUrl('en');
        $entity->supportUrlPt = $serviceProvider->getSupportUrl('pt');

        return $entity;
    }

    /**
     * {@inheritdoc}
     */
    public function accept(VisitorInterface $visitor)
    {
        $visitor->visitServiceProvider($this);
    }

    /**
     * @return null|AttributeReleasePolicy
     */
    public function getAttributeReleasePolicy()
    {
        return $this->attributeReleasePolicy;
    }

    /**
     * @param string $idpEntityId
     * @return bool
     */
    public function isAllowed($idpEntityId)
    {
        return $this->allowAll || in_array($idpEntityId, $this->allowedIdpEntityIds);
    }

    /**
     * @param string $preferredLocale
     * @return string
     */
    public function getDisplayName($preferredLocale = '')
    {
        $spName = '';
        if ($preferredLocale === 'nl') {
            $spName = $this->displayNameNl;
            if (empty($spName)) {
                $spName = $this->nameNl;
            }
        } elseif ($preferredLocale === 'en') {
            $spName = $this->displayNameEn;
            if (empty($spName)) {
                $spName = $this->nameEn;
            }
        } elseif ($preferredLocale === 'pt') {
            $spName = $this->displayNamePt;
            if (empty($spName)) {
                $spName = $this->namePt;
            }
        }

        if (empty($spName)) {
            $spName = $this->entityId;
        }
        return $spName;
    }

    public function getOrganizationName(string $preferredLocale = ''): string
    {
        $orgName = '';

        if ($preferredLocale === 'nl') {
            $orgName = $this->organizationNl->displayName;
            if (empty($orgName)) {
                $orgName = $this->organizationNl->name;
            }
        } elseif ($preferredLocale === 'pt') {
            $orgName = $this->organizationPt->displayName;
            if (empty($orgName)) {
                $orgName = $this->organizationPt->name;
            }
        }

        if ($preferredLocale === 'en' || empty($orgName)) {
            $orgName = $this->organizationEn->displayName;
            if (empty($orgName)) {
                $orgName = $this->organizationEn->name;
            }
        }

        if (empty($orgName)) {
            $orgName = '';
        }

        return $orgName;
    }

    /**
     * @return bool
     */
    public function isAttributeAggregationRequired()
    {
        if (is_null($this->attributeReleasePolicy)) {
            return false;
        }

        $rules = $this->attributeReleasePolicy->getRulesWithSourceSpecification();

        return count($rules) > 0;
    }
}
