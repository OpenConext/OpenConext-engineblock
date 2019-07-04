<?php

namespace OpenConext\EngineBlock\Metadata;

/**
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 */
class Coins
{
    private $values = [];

    public static function createForServiceProvider(
        $isConsentRequired,
        $isTransparentIssuer,
        $isTrustedProxy,
        $displayUnconnectedIdpsWayf,
        $termsOfServiceUrl,
        $skipDenormalization,
        $policyEnforcementDecisionRequired,
        $requesteridRequired,
        $signResponse,
        $publishInEdugain,
        $disableScoping,
        $additionalLogging,
        $signatureMethod
    ) {
        return new self([
            'isConsentRequired' => $isConsentRequired,
            'isTransparentIssuer' => $isTransparentIssuer,
            'isTrustedProxy' => $isTrustedProxy,
            'displayUnconnectedIdpsWayf' => $displayUnconnectedIdpsWayf,
            'termsOfServiceUrl' => $termsOfServiceUrl,
            'skipDenormalization' => $skipDenormalization,
            'policyEnforcementDecisionRequired' => $policyEnforcementDecisionRequired,
            'requesteridRequired' => $requesteridRequired,
            'signResponse' => $signResponse,
            'publishInEdugain' => $publishInEdugain,
            'disableScoping' => $disableScoping,
            'additionalLogging' => $additionalLogging,
            'signatureMethod' => $signatureMethod,
        ]);
    }

    public static function createForIdentityProvider(
        $guestQualifier,
        $schacHomeOrganization,
        $hidden,
        $publishInEdugain,
        $disableScoping,
        $additionalLogging,
        $signatureMethod
    ) {
        return new self([
            'guestQualifier' => $guestQualifier,
            'schacHomeOrganization' => $schacHomeOrganization,
            'hidden' => $hidden,
            'publishInEdugain' => $publishInEdugain,
            'disableScoping' => $disableScoping,
            'additionalLogging' => $additionalLogging,
            'signatureMethod' => $signatureMethod,
        ]);
    }

    public function __construct(array $values)
    {
        $this->values = $values;
    }

    /**
     * @return false|string
     */
    public function toJson()
    {
        return json_encode($this->values);
    }

    /**
     * @param string $data
     * @return Coins
     */
    public static function fromJson($data)
    {
        $data = json_decode($data, true);

        return new self($data);
    }
}
