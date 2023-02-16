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
use OpenConext\EngineBlock\Metadata\MetadataRepository\Visitor\VisitorInterface;
use OpenConext\EngineBlock\Metadata\Organization;
use OpenConext\EngineBlock\Metadata\Service;
use OpenConext\EngineBlock\Metadata\X509\X509Certificate;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use RuntimeException;
use SAML2\Constants;

/**
 * This class has been added to temporary push to both sso_provider_roles_eb5
 * and sso_provider_roles_eb6
 *
 * TODO: Remove this code after sso_provider_roles_eb5 has been phased out
 *
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
 *  "sp"  = "OpenConext\EngineBlock\Metadata\Entity\ServiceProviderEb5",
 *  "idp" = "OpenConext\EngineBlock\Metadata\Entity\IdentityProviderEb5"
 * })
 *
 * @SuppressWarnings(PHPMD.UnusedPrivateField)
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 */
abstract class AbstractRoleEb5
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
     *
     * @ORM\Column(name="description_nl", type="string")
     */
    public $descriptionNl;

    /**
     * @var string
     *
     * @ORM\Column(name="description_en", type="string")
     */
    public $descriptionEn;

    /**
     * @var string
     *
     * @ORM\Column(name="description_pt", type="string")
     */
    public $descriptionPt;

    /**
     * @var string
     *
     * @ORM\Column(name="display_name_nl", type="string")
     */
    public $displayNameNl;

    /**
     * @var string
     *
     * @ORM\Column(name="display_name_en", type="string")
     */
    public $displayNameEn;

    /**
     * @var string
     *
     * @ORM\Column(name="display_name_pt", type="string")
     */
    public $displayNamePt;

    /**
     * @var Logo
     *
     * @ORM\Column(name="logo", type="object")
     */
    public $logo;

    /**
     * @var Organization
     *
     * @ORM\Column(name="organization_nl_name",type="object", nullable=true)
     */
    public $organizationNl;

    /**
     * @var Organization
     *
     * @ORM\Column(name="organization_en_name",type="object", nullable=true)
     */
    public $organizationEn;

    /**
     * @var Organization
     *
     * @ORM\Column(name="organization_pt_name",type="object", nullable=true)
     */
    public $organizationPt;

    /**
     * @var string
     *
     * @ORM\Column(name="keywords_nl", type="string")
     */
    public $keywordsNl;

    /**
     * @var string
     *
     * @ORM\Column(name="keywords_en", type="string")
     */
    public $keywordsEn;

    /**
     * @var string
     *
     * @ORM\Column(name="keywords_pt", type="string")
     */
    public $keywordsPt;

    /**
     * @var X509Certificate[]
     *
     * @ORM\Column(name="certificates", type="array")
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
     * @ORM\Column(name="contact_persons", type="array")
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
     * @ORM\Column(name="name_id_formats", type="array")
     */
    public $supportedNameIdFormats;

    /**
     * @var Service
     *
     * @ORM\Column(name="single_logout_service", type="object", nullable=true)
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
     * @ORM\Column(name="manipulation", type="text")
     */
    public $manipulation;

    /**
     * @var Coins
     *
     * @ORM\Column(name="coins", type="engineblock_metadata_coins")
     */
    protected $coins = array();

    /**
     * @param $entityId
     * @param Organization $organizationEn
     * @param Organization $organizationNl
     * @param Organization $organizationPt
     * @param Service $singleLogoutService
     * @param array $certificates
     * @param array $contactPersons
     * @param string $descriptionEn
     * @param string $descriptionNl
     * @param string $descriptionPt
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
     * @param string $workflowState
     * @param string $manipulation
     */
    public function __construct(
        $entityId,
        Organization $organizationEn = null,
        Organization $organizationNl = null,
        Organization $organizationPt = null,
        Service $singleLogoutService = null,
        array $certificates = array(),
        array $contactPersons = array(),
        $descriptionEn = '',
        $descriptionNl = '',
        $descriptionPt = '',
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
        $workflowState = self::WORKFLOW_STATE_DEFAULT,
        $manipulation = ''
    ) {
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

    /**
     * @return Coins
     */
    public function getCoins()
    {
        return $this->coins;
    }
}
