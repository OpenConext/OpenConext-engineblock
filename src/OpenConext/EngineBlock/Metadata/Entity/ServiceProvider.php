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
use OpenConext\EngineBlock\Metadata\Logo;
use OpenConext\EngineBlock\Metadata\MetadataRepository\Visitor\VisitorInterface;
use OpenConext\EngineBlock\Metadata\Organization;
use OpenConext\EngineBlock\Metadata\RequestedAttribute;
use OpenConext\EngineBlock\Metadata\IndexedService;
use OpenConext\EngineBlock\Metadata\Service;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use SAML2\Constants;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class ServiceProvider
 * @package OpenConext\EngineBlock\Metadata\Entity
 * @ORM\Entity
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
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
     * @param string $entityId
     * @param Organization $organizationEn
     * @param Organization $organizationNl
     * @param Service $singleLogoutService
     * @param bool $additionalLogging
     * @param array $certificates
     * @param array $contactPersons
     * @param string $descriptionEn
     * @param string $descriptionNl
     * @param bool $disableScoping
     * @param string $displayNameEn
     * @param string $displayNameNl
     * @param string $keywordsEn
     * @param string $keywordsNl
     * @param Logo $logo
     * @param string $nameEn
     * @param string $nameNl
     * @param null $nameIdFormat
     * @param array $supportedNameIdFormats
     * @param null $publishInEduGainDate
     * @param bool $publishInEdugain
     * @param bool $requestsMustBeSigned
     * @param string $signatureMethod
     * @param Service $responseProcessingService
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
     * #param bool $signResponse
     * @param bool $signResponse
     * @param string $manipulation
     * @param AttributeReleasePolicy $attributeReleasePolicy
     * @param string|null $supportUrlEn
     * @param string|null $supportUrlNl
     * @param bool|null $stepupAllowNoToken
     * @param bool|null $stepupRequireLoa
     */
    public function __construct(
        $entityId,
        Organization $organizationEn = null,
        Organization $organizationNl = null,
        Service $singleLogoutService = null,
        $additionalLogging = false,
        array $certificates = array(),
        array $contactPersons = array(),
        $descriptionEn = '',
        $descriptionNl = '',
        $disableScoping = false,
        $displayNameEn = '',
        $displayNameNl = '',
        $keywordsEn = '',
        $keywordsNl = '',
        Logo $logo = null,
        $nameEn = '',
        $nameNl = '',
        $nameIdFormat = null,
        $supportedNameIdFormats = array(
            Constants::NAMEID_TRANSIENT,
            Constants::NAMEID_PERSISTENT,
        ),
        $publishInEduGainDate = null,
        $publishInEdugain = false,
        $requestsMustBeSigned = false,
        $signatureMethod = XMLSecurityKey::RSA_SHA256,
        Service $responseProcessingService = null,
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
        $stepupAllowNoToken = null,
        $stepupRequireLoa = null
    ) {
        parent::__construct(
            $entityId,
            $organizationEn,
            $organizationNl,
            $singleLogoutService,
            $certificates,
            $contactPersons,
            $descriptionEn,
            $descriptionNl,
            $displayNameEn,
            $displayNameNl,
            $keywordsEn,
            $keywordsNl,
            $logo,
            $nameEn,
            $nameNl,
            $nameIdFormat,
            $supportedNameIdFormats,
            /**
             * @deprecated: These coins are no longer used in EngineBlock and will be removed in release 6.2
             */
            $publishInEduGainDate,
            $publishInEdugain,
            $requestsMustBeSigned,
            $responseProcessingService,
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

    public function getDisplayName($preferredLocale = '')
    {
        $spName = '';
        if ($preferredLocale === 'nl') {
            $spName = $this->displayNameNl;
            if (empty($spName)) {
                $spName = $this->nameNl;
            }
        } else {
            $spName = $this->displayNameEn;
            if (empty($spName)) {
                $spName = $this->nameEn;
            }
        }
        if (empty($spName)) {
            $spName = $this->entityId;
        }
        return $spName;
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
