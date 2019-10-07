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

use Doctrine\ORM\Mapping as ORM;
use OpenConext\EngineBlock\Metadata\Coins;
use OpenConext\EngineBlock\Metadata\ConsentSettings;
use OpenConext\EngineBlock\Metadata\Logo;
use OpenConext\EngineBlock\Metadata\MetadataRepository\Visitor\VisitorInterface;
use OpenConext\EngineBlock\Metadata\Organization;
use OpenConext\EngineBlock\Metadata\ShibMdScope;
use OpenConext\EngineBlock\Metadata\Service;
use OpenConext\EngineBlock\Metadata\StepupConnections;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use SAML2\Constants;

/**
 * Class IdentityProvider
 * @package OpenConext\EngineBlock\Metadata\Entity
 * @ORM\Entity
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 */
class IdentityProvider extends AbstractRole
{
    const GUEST_QUALIFIER_ALL = 'All';
    const GUEST_QUALIFIER_SOME = 'Some';
    const GUEST_QUALIFIER_NONE = 'None';

    /**
     * In all-caps to indicate that though the language doesn't allow it, this should be an array constant.
     *
     * @var string[]
     */
    public static $GUEST_QUALIFIERS = array(
        self::GUEST_QUALIFIER_ALL,
        self::GUEST_QUALIFIER_SOME,
        self::GUEST_QUALIFIER_NONE
    );

    /**
     * @var bool
     *
     * @ORM\Column(name="enabled_in_wayf", type="boolean")
     */
    public $enabledInWayf = true;

    /**
     * @var Service[]
     *
     * @ORM\Column(name="single_sign_on_services", type="array")
     */
    public $singleSignOnServices = array();

    /**
     * @var ConsentSettings
     *
     * @ORM\Column(name="consent_settings", type="json_array")
     */
    private $consentSettings;

    /**
     * @var ShibMdScope[]
     *
     * @ORM\Column(name="shib_md_scopes", type="array")
     */
    public $shibMdScopes = array();

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
     * @param string $manipulation
     * @param null $attributeReleasePolicy
     * @param bool $enabledInWayf
     * @param string $guestQualifier
     * @param bool $hidden
     * @param null $schacHomeOrganization
     * @param array $shibMdScopes
     * @param array $singleSignOnServices
     * @param ConsentSettings $consentSettings
     * @param StepupConnections|null $stepupConnections
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
        $manipulation = '',
        $attributeReleasePolicy = null,
        $enabledInWayf = true,
        $guestQualifier = self::GUEST_QUALIFIER_ALL,
        $hidden = false,
        $schacHomeOrganization = null,
        $shibMdScopes = array(),
        $singleSignOnServices = array(),
        ConsentSettings $consentSettings = null,
        StepupConnections $stepupConnections = null
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
        $this->enabledInWayf = $enabledInWayf;
        $this->shibMdScopes = $shibMdScopes;
        $this->singleSignOnServices = $singleSignOnServices;
        $this->consentSettings = $consentSettings;

        $this->coins = Coins::createForIdentityProvider(
            $guestQualifier,
            $schacHomeOrganization,
            $hidden,
            $stepupConnections,
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
        $visitor->visitIdentityProvider($this);
    }

    public function getDisplayName($preferredLocale = '')
    {
        $idpName = '';
        if ($preferredLocale === 'nl') {
            $idpName = $this->nameNl;
        }
        if (empty($idpName)) {
            $idpName = $this->nameEn;
        }
        if (empty($idpName)) {
            $idpName = $this->entityId;
        }
        return $idpName;
    }

    /**
     * @param ConsentSettings $settings
     * @return IdentityProvider
     */
    public function setConsentSettings(ConsentSettings $settings)
    {
        $this->consentSettings = $settings;

        return $this;
    }

    /**
     * @return ConsentSettings
     */
    public function getConsentSettings()
    {
        if (!$this->consentSettings instanceof ConsentSettings) {
            $this->setConsentSettings(
                new ConsentSettings(
                    (array)$this->consentSettings
                )
            );
        }

        return $this->consentSettings;
    }
}
