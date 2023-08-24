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

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use OpenConext\EngineBlock\Metadata\Coins;
use OpenConext\EngineBlock\Metadata\ContactPerson;
use OpenConext\EngineBlock\Metadata\Logo;
use OpenConext\EngineBlock\Metadata\Mdui;
use OpenConext\EngineBlock\Metadata\MetadataRepository\Visitor\VisitorInterface;
use OpenConext\EngineBlock\Metadata\Organization;
use OpenConext\EngineBlock\Metadata\Service;
use OpenConext\EngineBlock\Metadata\X509\X509Certificate;
use RuntimeException;
use SAML2\Constants;

/**
 * Abstract base class for configuration entities.
 *
 * @package OpenConext\EngineBlock\Metadata\Entity
 * @SuppressWarnings(PHPMD.TooManyFields)
 *
 * @ORM\Entity
 * @ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 * @ORM\Table(
 *      name="sso_provider_roles_eb5",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(
 *              name="idx_sso_provider_roles_entity_id_type",
 *              columns={"type", "entity_id"}
 *          )
 *      },
 *      indexes={
 *          @ORM\Index(
 *              name="idx_sso_provider_roles_type",
 *              columns={"type"}
 *          ),
 *          @ORM\Index(
 *              name="idx_sso_provider_roles_entity_id",
 *              columns={"entity_id"}
 *          ),
 *      }
 * )
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
 *  "sp"  = "OpenConext\EngineBlock\Metadata\Entity\ServiceProvider",
 *  "idp" = "OpenConext\EngineBlock\Metadata\Entity\IdentityProvider"
 * })
 *
 * @SuppressWarnings(PHPMD.UnusedPrivateField)
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 */
abstract class AbstractRole
{
    const WORKFLOW_STATE_PROD = 'prodaccepted';
    const WORKFLOW_STATE_TEST = 'testaccepted';
    const WORKFLOW_STATE_DEFAULT = self::WORKFLOW_STATE_PROD;

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    public $id;

    /**
     * @var string
     *
     * @ORM\Column(name="entity_id", type="string")
     */
    public $entityId;

    /**
     * @var string
     * @ORM\Column(name="name_nl", type="string")
     */
    public $nameNl;

    /**
     * @var string
     *
     * @ORM\Column(name="name_en", type="string")
     */
    public $nameEn;

    /**
     * @var string
     *
     * @ORM\Column(name="name_pt", type="string")
     */
    public $namePt;

    /**
     * @var string
     * @deprecated Will be removed in favour of using the Mdui value object, use the getter for this field instead
     * @ORM\Column(name="description_nl", type="string")
     */
    public $descriptionNl;

    /**
     * @var string
     * @deprecated Will be removed in favour of using the Mdui value object, use the getter for this field instead
     * @ORM\Column(name="description_en", type="string")
     */
    public $descriptionEn;

    /**
     * @var string
     * @deprecated Will be removed in favour of using the Mdui value object, use the getter for this field instead
     * @ORM\Column(name="description_pt", type="string")
     */
    public $descriptionPt;

    /**
     * @var string
     * @deprecated Will be removed in favour of using the Mdui value object, use the getter for this field instead
     * @ORM\Column(name="display_name_nl", type="string")
     */
    public $displayNameNl;

    /**
     * @var string
     * @deprecated Will be removed in favour of using the Mdui value object, use the getter for this field instead
     * @ORM\Column(name="display_name_en", type="string")
     */
    public $displayNameEn;

    /**
     * @var string
     * @deprecated Will be removed in favour of using the Mdui value object, use the getter for this field instead
     * @ORM\Column(name="display_name_pt", type="string")
     */
    public $displayNamePt;

    /**
     * @var Logo
     * @deprecated Will be removed in favour of using the Mdui value object, use the getter for this field instead
     * @ORM\Column(name="logo", type="object")
     */
    public $logo;

    /**
     * @var Organization
     *
     * @ORM\Column(name="organization_nl_name",type="object", nullable=true, length=65535)
     */
    public $organizationNl;

    /**
     * @var Organization
     *
     * @ORM\Column(name="organization_en_name",type="object", nullable=true, length=65535)
     */
    public $organizationEn;

    /**
     * @var Organization
     *
     * @ORM\Column(name="organization_pt_name",type="object", nullable=true, length=65535)
     */
    public $organizationPt;

    /**
     * @var string
     * @deprecated Will be removed in favour of using the Mdui value object, use the getter for this field instead
     * @ORM\Column(name="keywords_nl", type="string")
     */
    public $keywordsNl;

    /**
     * @var string
     * @deprecated Will be removed in favour of using the Mdui value object, use the getter for this field instead
     * @ORM\Column(name="keywords_en", type="string")
     */
    public $keywordsEn;

    /**
     * @var string
     * @deprecated Will be removed in favour of using the Mdui value object, use the getter for this field instead
     * @ORM\Column(name="keywords_pt", type="string")
     */
    public $keywordsPt;

    /**
     * @var X509Certificate[]
     *
     * @ORM\Column(name="certificates", type="array", length=65535)
     */
    public $certificates = array();

    /**
     * @var string
     *
     * @ORM\Column(name="workflow_state", type="string")
     */
    public $workflowState = self::WORKFLOW_STATE_DEFAULT;

    /**
     * @var ContactPerson[]
     *
     * @ORM\Column(name="contact_persons", type="array", length=65535)
     */
    public $contactPersons;

    /**
     * @var string
     *
     * @ORM\Column(name="name_id_format", type="string", nullable=true)
     */
    public $nameIdFormat;

    /**
     * @var string[]
     *
     * @ORM\Column(name="name_id_formats", type="array", length=65535)
     */
    public $supportedNameIdFormats;

    /**
     * @var Service
     *
     * @ORM\Column(name="single_logout_service", type="object", nullable=true, length=65535)
     */
    public $singleLogoutService;

    /**
     * @var bool
     *
     * @ORM\Column(name="requests_must_be_signed", type="boolean")
     */
    public $requestsMustBeSigned = false;

    /**
     * @var string
     *
     * @ORM\Column(name="manipulation", type="text", length=65535)
     */
    public $manipulation;

    /**
     * @var Coins
     *
     * @ORM\Column(name="coins", type="engineblock_metadata_coins")
     */
    protected $coins = array();

    /**
     * @var Mdui
     *
     * @ORM\Column(name="mdui", type="engineblock_metadata_mdui")
     */
    protected $mdui;

    public function __construct(
        $entityId,
        Mdui $mdui,
        Organization $organizationEn = null,
        Organization $organizationNl = null,
        Organization $organizationPt = null,
        Service $singleLogoutService = null,
        array $certificates = array(),
        array $contactPersons = array(),
        ?string $descriptionEn = '',
        ?string $descriptionNl = '',
        ?string $descriptionPt = '',
        ?string $displayNameEn = '',
        ?string $displayNameNl = '',
        ?string $displayNamePt = '',
        ?string $keywordsEn = '',
        ?string $keywordsNl = '',
        ?string $keywordsPt = '',
        ?Logo $logo = null,
        ?string $nameEn = '',
        ?string $nameNl = '',
        ?string $namePt = '',
        ?string $nameIdFormat = null,
        array $supportedNameIdFormats = array(
            Constants::NAMEID_TRANSIENT,
            Constants::NAMEID_PERSISTENT,
        ),
        bool $requestsMustBeSigned = false,
        string $workflowState = self::WORKFLOW_STATE_DEFAULT,
        string $manipulation = ''
    ) {
        $this->mdui = $mdui;
        $this->certificates = $certificates;
        $this->contactPersons = $contactPersons;
        $this->descriptionEn = $descriptionEn;
        $this->descriptionNl = $descriptionNl;
        $this->descriptionPt = $descriptionPt;
        $this->displayNameEn = $displayNameEn;
        $this->displayNameNl = $displayNameNl;
        $this->displayNamePt = $displayNamePt;
        $this->entityId = $entityId;
        $this->keywordsEn = $keywordsEn;
        $this->keywordsNl = $keywordsNl;
        $this->keywordsPt = $keywordsPt;
        $this->logo = $logo;
        $this->nameEn = $nameEn;
        $this->nameNl = $nameNl;
        $this->namePt = $namePt;
        $this->nameIdFormat = $nameIdFormat;
        $this->supportedNameIdFormats = $supportedNameIdFormats;
        $this->organizationEn = $organizationEn;
        $this->organizationNl = $organizationNl;
        $this->organizationPt = $organizationPt;
        $this->requestsMustBeSigned = $requestsMustBeSigned;
        $this->singleLogoutService = $singleLogoutService;
        $this->workflowState = $workflowState;
        $this->manipulation = $manipulation;
    }

    /**
     * @param VisitorInterface $visitor
     * @return null|AbstractRole
     */
    abstract public function accept(VisitorInterface $visitor);

    /**
     * @return string
     */
    public function getManipulation()
    {
        return $this->manipulation;
    }

    /**
     * @return $this
     */
    public function toggleWorkflowState()
    {
        if ($this->workflowState === static::WORKFLOW_STATE_PROD) {
            $this->workflowState = static::WORKFLOW_STATE_TEST;
            return $this;
        }

        if ($this->workflowState === static::WORKFLOW_STATE_TEST) {
            $this->workflowState = static::WORKFLOW_STATE_PROD;
            return $this;
        }

        throw new RuntimeException('Unknown workflow state');
    }

    public function getCoins(): Coins
    {
        return $this->coins;
    }

    public function getMdui(): Mdui
    {
        return $this->mdui;
    }
}
