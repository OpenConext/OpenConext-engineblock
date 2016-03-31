<?php

namespace OpenConext\EngineBlock\Metadata\Value;

use OpenConext\EngineBlock\Assert\Assertion;
use OpenConext\Value\Serializable;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods) Due to the various accessors, objects holds a lot of values.
 */
final class ServiceProviderConfiguration implements Serializable
{
    /**
     * @var bool
     */
    private $displayUnconnectedIdpsInWayf;

    /**
     * @var bool
     */
    private $isTrustedProxy;

    /**
     * @var bool
     */
    private $isTransparentIssuer;

    /**
     * @var bool
     */
    private $requiresConsent;

    /**
     * @var bool
     */
    private $denormalizationShouldBeSkipped;

    /**
     * @var bool
     */
    private $requiresPolicyEnforcementDecision;

    /**
     * @var bool
     */
    private $requiresAttributeAggregation;

    public function __construct(
        $displayUnconnectedIdpsInWayf,
        $isTrustedProxy,
        $isTransparentIssuer,
        $requiresConsent,
        $denormalizationShouldBeSkipped,
        $requiresPolicyEnforcementDecision,
        $requiresAttributeAggregation
    ) {
        Assertion::boolean($displayUnconnectedIdpsInWayf);
        Assertion::boolean($isTrustedProxy);
        Assertion::boolean($isTransparentIssuer);
        Assertion::boolean($requiresConsent);
        Assertion::boolean($denormalizationShouldBeSkipped);
        Assertion::boolean($requiresPolicyEnforcementDecision);
        Assertion::boolean($requiresAttributeAggregation);

        $this->displayUnconnectedIdpsInWayf      = $displayUnconnectedIdpsInWayf;
        $this->isTrustedProxy                    = $isTrustedProxy;
        $this->isTransparentIssuer               = $isTransparentIssuer;
        $this->requiresConsent                   = $requiresConsent;
        $this->denormalizationShouldBeSkipped    = $denormalizationShouldBeSkipped;
        $this->requiresPolicyEnforcementDecision = $requiresPolicyEnforcementDecision;
        $this->requiresAttributeAggregation      = $requiresAttributeAggregation;
    }

    /**
     * @return bool
     */
    public function displayUnconnectedIdpsInWayf()
    {
        return $this->displayUnconnectedIdpsInWayf;
    }

    /**
     * @return bool
     */
    public function isTrustedProxy()
    {
        return $this->isTrustedProxy;
    }

    /**
     * @return bool
     */
    public function isTransparentIssuer()
    {
        return $this->isTransparentIssuer;
    }

    /**
     * @return bool
     */
    public function requiresConsent()
    {
        return $this->requiresConsent;
    }

    /**
     * @return bool
     */
    public function denormalizationShouldBeSkipped()
    {
        return $this->denormalizationShouldBeSkipped;
    }

    /**
     * @return bool
     */
    public function requiresPolicyEnforcementDecision()
    {
        return $this->requiresPolicyEnforcementDecision;
    }

    /**
     * @return bool
     */
    public function requiresAttributeAggregation()
    {
        return $this->requiresAttributeAggregation;
    }

    /**
     * @param ServiceProviderConfiguration $other
     * @return bool
     */
    public function equals(ServiceProviderConfiguration $other)
    {
        return $this->displayUnconnectedIdpsInWayf === $other->displayUnconnectedIdpsInWayf
                && $this->isTrustedProxy === $other->isTrustedProxy
                && $this->isTransparentIssuer === $other->isTransparentIssuer
                && $this->requiresConsent === $other->requiresConsent
                && $this->denormalizationShouldBeSkipped === $other->denormalizationShouldBeSkipped
                && $this->requiresPolicyEnforcementDecision === $other->requiresPolicyEnforcementDecision
                && $this->requiresAttributeAggregation === $other->requiresAttributeAggregation;
    }

    public static function deserialize($data)
    {
        Assertion::isArray($data);
        Assertion::keysExist(
            $data,
            [
                'display_unconnect_idps_in_wayf',
                'is_trusted_proxy',
                'is_transparent_issuer',
                'requires_consent',
                'denormalization_should_be_skipped',
                'requires_policy_enforcement_decision',
                'requires_attribute_aggregation'
            ]
        );

        return new self(
            $data['display_unconnect_idps_in_wayf'],
            $data['is_trusted_proxy'],
            $data['is_transparent_issuer'],
            $data['requires_consent'],
            $data['denormalization_should_be_skipped'],
            $data['requires_policy_enforcement_decision'],
            $data['requires_attribute_aggregation']
        );
    }

    public function serialize()
    {
        return [
            'display_unconnect_idps_in_wayf'       => $this->displayUnconnectedIdpsInWayf,
            'is_trusted_proxy'                     => $this->isTrustedProxy,
            'is_transparent_issuer'                => $this->isTransparentIssuer,
            'requires_consent'                     => $this->requiresConsent,
            'denormalization_should_be_skipped'    => $this->denormalizationShouldBeSkipped,
            'requires_policy_enforcement_decision' => $this->requiresPolicyEnforcementDecision,
            'requires_attribute_aggregation'       => $this->requiresAttributeAggregation
        ];
    }

    public function __toString()
    {
        $booleanToString = function ($bool) {
            return $bool ? 'true' : 'false';
        };

        $template = 'ServiceProviderConfiguration(displayUnconnectedIdpsInWayf=%s, isTrustedProxy=%s, '
                    . 'isTransparentIssuer=%s, requiresConsent=%s, denormalizationShouldBeSkipped=%s '
                    . 'requiresPolicyEnforcementDecision=%s, requiresAttributeAggregation=%s)';

        return sprintf(
            $template,
            $booleanToString($this->displayUnconnectedIdpsInWayf),
            $booleanToString($this->isTrustedProxy),
            $booleanToString($this->isTransparentIssuer),
            $booleanToString($this->requiresConsent),
            $booleanToString($this->denormalizationShouldBeSkipped),
            $booleanToString($this->requiresPolicyEnforcementDecision),
            $booleanToString($this->requiresAttributeAggregation)
        );
    }
}
