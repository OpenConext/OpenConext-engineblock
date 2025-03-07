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
use InvalidArgumentException;
use OpenConext\EngineBlock\Exception\InvalidDiscoveryException;
use OpenConext\EngineBlock\Metadata\Coins;
use OpenConext\EngineBlock\Metadata\ConsentSettings;
use OpenConext\EngineBlock\Metadata\Discovery;
use OpenConext\EngineBlock\Metadata\Logo;
use OpenConext\EngineBlock\Metadata\Mdui;
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
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
     * @ORM\Column(name="single_sign_on_services", type="array", length=65535)
     */
    public $singleSignOnServices = array();

    /**
     * @var ConsentSettings
     *
     * @ORM\Column(name="consent_settings", type="json_array", length=16777215)
     */
    private $consentSettings;

    /**
     * @var ShibMdScope[]
     *
     * @ORM\Column(name="shib_md_scopes", type="array", length=65535)
     */
    public $shibMdScopes = array();

    /**
     * @var array<int, Discovery>
     *
     * @ORM\Column(name="idp_discoveries", type="json")
     */
    private $discoveries;

    /**
     * WARNING: Please don't use this entity directly but use the dedicated factory instead.
     * @see \OpenConext\EngineBlock\Factory\Factory\IdentityProviderFactory
     */
    public function __construct(
        $entityId,
        ?Mdui $mdui = null,
        Organization $organizationEn = null,
        Organization $organizationNl = null,
        Organization $organizationPt = null,
        Service $singleLogoutService = null,
        bool $additionalLogging = false,
        array $certificates = array(),
        array $contactPersons = array(),
        string $descriptionEn = '',
        string $descriptionNl = '',
        string $descriptionPt = '',
        bool $disableScoping = false,
        string $displayNameEn = '',
        string $displayNameNl = '',
        string $displayNamePt = '',
        string $keywordsEn = '',
        string $keywordsNl = '',
        string $keywordsPt = '',
        Logo $logo = null,
        string $nameEn = '',
        string $nameNl = '',
        string $namePt = '',
        ?string $nameIdFormat = null,
        array $supportedNameIdFormats = array(
            Constants::NAMEID_TRANSIENT,
            Constants::NAMEID_PERSISTENT,
        ),
        bool $requestsMustBeSigned = false,
        string $signatureMethod = XMLSecurityKey::RSA_SHA256,
        string $workflowState = self::WORKFLOW_STATE_DEFAULT,
        string $manipulation = '',
        bool $enabledInWayf = true,
        string $guestQualifier = self::GUEST_QUALIFIER_ALL,
        bool $hidden = false,
        ?string $schacHomeOrganization = null,
        array $shibMdScopes = array(),
        array $singleSignOnServices = array(),
        ConsentSettings $consentSettings = null,
        StepupConnections $stepupConnections = null,
        MfaEntityCollection $mfaEntities = null,
        array $discoveries = [],
        ?string $defaultRAC = null
    ) {
        if (is_null($mdui)) {
            $mdui = Mdui::emptyMdui();
        }
        parent::__construct(
            $entityId,
            $mdui,
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
            $mfaEntities,
            $defaultRAC
        );

        $this->assertAllDiscoveries($discoveries);
        $this->discoveries = $discoveries;
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

    /**
     * @return array<int, Discovery>
     */
    public function getDiscoveries(): array
    {
        $this->ensureDiscoveriesDeserialized();
        return $this->discoveries;
    }

    /**
     * @param array<Discovery> $discoveries
     */
    public function setDiscoveries(array $discoveries)
    {
        $this->assertAllDiscoveries($discoveries);
        $this->discoveries = $discoveries;
    }

    private function ensureDiscoveriesDeserialized(): void
    {
        if (!is_array($this->discoveries)) {
            $this->discoveries = [];
            return;
        }

        foreach ($this->discoveries as $index => $discovery) {
            try {
                if (!$discovery instanceof Discovery) {
                    $logo = new Logo($discovery['logo']['url']);
                    $logo->width = $discovery['logo']['width'];
                    $logo->height = $discovery['logo']['height'];

                    $this->discoveries[$index] = Discovery::create(
                        $discovery['names'] ?? [],
                        $discovery['keywords'] ?? [],
                        $logo
                    );
                }
            } catch (InvalidDiscoveryException $e) {
                unset($this->discoveries[$index]);
            }
        }
    }

    private function assertAllDiscoveries(array $discoveries): void
    {
        foreach ($discoveries as $discovery) {
            if (!$discovery instanceof Discovery) {
                throw new InvalidArgumentException('Discovery must be instance of Discovery');
            }
        }
    }
}
