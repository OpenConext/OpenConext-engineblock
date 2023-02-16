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
use OpenConext\EngineBlock\Metadata\Factory\IdentityProviderEntityInterface;
use OpenConext\EngineBlock\Metadata\Logo;
use OpenConext\EngineBlock\Metadata\MetadataRepository\Visitor\VisitorInterface;
use OpenConext\EngineBlock\Metadata\MfaEntityCollection;
use OpenConext\EngineBlock\Metadata\Organization;
use OpenConext\EngineBlock\Metadata\ShibMdScope;
use OpenConext\EngineBlock\Metadata\Service;
use OpenConext\EngineBlock\Metadata\StepupConnections;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use SAML2\Constants;

/**
 * @package OpenConext\EngineBlock\Metadata\Entity
 * @ORM\Entity
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 *
 * WARNING: Please don't use this entity directly but use the dedicated factory instead.
 * @see \OpenConext\EngineBlock\Factory\Factory\IdentityProviderFactory
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
     * @ORM\Column(name="single_sign_on_services", type="engineblock_service_array")
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
     * @ORM\Column(name="shib_md_scopes", type="engineblock_shib_md_scope_array")
     */
    public $shibMdScopes = array();

    /**
     * WARNING: Please don't use this entity directly but use the dedicated factory instead.
     * @see \OpenConext\EngineBlock\Factory\Factory\IdentityProviderFactory
     *
     * @param string $entityId
     * @param Organization $organizationEn
     * @param Organization $organizationNl
     * @param Organization $organizationPt
     * @param Service $singleLogoutService
     * @param bool $additionalLogging
     * @param array $certificates
     * @param array $contactPersons
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
     * @param string $manipulation
     * @param bool $enabledInWayf
     * @param string $guestQualifier
     * @param bool $hidden
     * @param null $schacHomeOrganization
     * @param array $shibMdScopes
     * @param array $singleSignOnServices
     * @param ConsentSettings $consentSettings
     * @param StepupConnections|null $stepupConnections
     * @param MfaEntityCollection|null $mfaEntities
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
        $manipulation = '',
        $enabledInWayf = true,
        $guestQualifier = self::GUEST_QUALIFIER_ALL,
        $hidden = false,
        $schacHomeOrganization = null,
        $shibMdScopes = array(),
        $singleSignOnServices = array(),
        ConsentSettings $consentSettings = null,
        StepupConnections $stepupConnections = null,
        MfaEntityCollection $mfaEntities = null
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
            $signatureMethod,
            $mfaEntities
        );
    }

    /**
     * {@inheritdoc}
     */
    public function accept(VisitorInterface $visitor)
    {
        $visitor->visitIdentityProvider($this);
    }

    /**
     * @param string $preferredLocale
     * @return string
     */
    public function getDisplayName($preferredLocale = '')
    {
        $idpName = '';
        if ($preferredLocale === 'nl') {
            $idpName = $this->nameNl;
        } elseif ($preferredLocale === 'en') {
            $idpName = $this->nameEn;
        } elseif ($preferredLocale === 'pt') {
            $idpName = $this->namePt;
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
