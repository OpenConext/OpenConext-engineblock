<?php

namespace OpenConext\EngineBlock\Metadata\Entity;

use Doctrine\ORM\Mapping as ORM;
use OpenConext\EngineBlock\Metadata\AttributeReleasePolicy;
use OpenConext\EngineBlock\Metadata\IndexedService;
use OpenConext\EngineBlock\Metadata\Logo;
use OpenConext\EngineBlock\Metadata\MetadataRepository\Visitor\VisitorInterface;
use OpenConext\EngineBlock\Metadata\Organization;
use OpenConext\EngineBlock\Metadata\RequestedAttribute;
use OpenConext\EngineBlock\Metadata\Service;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use SAML2\Constants;

/**
 * Class ServiceProvider
 * @package OpenConext\EngineBlock\Metadata\Entity
 * @ORM\Entity
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 */
class ServiceProvider extends AbstractRole
{
    /**
     * @var IndexedService[]
     *
     * @ORM\Column(name="assertion_consumer_services", type="array")
     */
    public $assertionConsumerServices;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_transparent_issuer", type="boolean")
     */
    public $isTransparentIssuer;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_trusted_proxy", type="boolean")
     */
    public $isTrustedProxy;

    /**
     * @var bool
     *
     * @ORM\Column(name="display_unconnected_idps_wayf", type="boolean")
     */
    public $displayUnconnectedIdpsWayf;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_consent_required", type="boolean")
     */
    public $isConsentRequired;

    /**
     * @var string
     *
     * @ORM\Column(name="terms_of_service_url", type="string")
     */
    public $termsOfServiceUrl;

    /**
     * @var bool
     *
     * @ORM\Column(name="skip_denormalization", type="boolean")
     */
    public $skipDenormalization;

    /**
     * @var string[]
     *
     * @ORM\Column(name="allowed_idp_entity_ids", type="array")
     */
    public $allowedIdpEntityIds;

    /**
     * @var bool
     *
     * @ORM\Column(name="policy_enforcement_decision_required", type="boolean")
     */
    public $policyEnforcementDecisionRequired;

    /**
     * @var bool
     *
     * @ORM\Column(name="attribute_aggregation_required", type="boolean")
     */
    public $attributeAggregationRequired;

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
     * @param array $assertionConsumerServices
     * @param bool $displayUnconnectedIdpsWayf
     * @param null $termsOfServiceUrl
     * @param bool $isConsentRequired
     * @param bool $isTransparentIssuer
     * @param bool $isTrustedProxy
     * @param null $requestedAttributes
     * @param bool $skipDenormalization
     * @param bool $policyEnforcementDecisionRequired
     * @param bool $attributeAggregationRequired
     * @param string $manipulation
     * @param AttributeReleasePolicy $attributeReleasePolicy
     * @param string|null $supportUrlEn
     * @param string|null $supportUrlNl
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
        $signatureMethod = XMLSecurityKey::RSA_SHA1,
        Service $responseProcessingService = null,
        $workflowState = self::WORKFLOW_STATE_DEFAULT,
        array $allowedIdpEntityIds = array(),
        array $assertionConsumerServices = array(),
        $displayUnconnectedIdpsWayf = false,
        $termsOfServiceUrl = null,
        $isConsentRequired = true,
        $isTransparentIssuer = false,
        $isTrustedProxy = false,
        $requestedAttributes = null,
        $skipDenormalization = false,
        $policyEnforcementDecisionRequired = false,
        $attributeAggregationRequired = false,
        $manipulation = '',
        AttributeReleasePolicy $attributeReleasePolicy = null,
        $supportUrlEn = null,
        $supportUrlNl = null
    ) {
        parent::__construct(
            $entityId,
            $organizationEn,
            $organizationNl,
            $singleLogoutService,
            $additionalLogging,
            $certificates,
            $contactPersons,
            $descriptionEn,
            $descriptionNl,
            $disableScoping,
            $displayNameEn,
            $displayNameNl,
            $keywordsEn,
            $keywordsNl,
            $logo,
            $nameEn,
            $nameNl,
            $nameIdFormat,
            $supportedNameIdFormats,
            $publishInEduGainDate,
            $publishInEdugain,
            $requestsMustBeSigned,
            $signatureMethod,
            $responseProcessingService,
            $workflowState,
            $manipulation,
            $attributeReleasePolicy
        );

        $this->allowedIdpEntityIds = $allowedIdpEntityIds;
        $this->assertionConsumerServices = $assertionConsumerServices;
        $this->displayUnconnectedIdpsWayf = $displayUnconnectedIdpsWayf;
        $this->termsOfServiceUrl = $termsOfServiceUrl;
        $this->isConsentRequired = $isConsentRequired;
        $this->isTransparentIssuer = $isTransparentIssuer;
        $this->isTrustedProxy = $isTrustedProxy;
        $this->requestedAttributes = $requestedAttributes;
        $this->skipDenormalization = $skipDenormalization;
        $this->policyEnforcementDecisionRequired = $policyEnforcementDecisionRequired;
        $this->attributeAggregationRequired = $attributeAggregationRequired;
        $this->supportUrlEn = $supportUrlEn;
        $this->supportUrlNl = $supportUrlNl;
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
}
