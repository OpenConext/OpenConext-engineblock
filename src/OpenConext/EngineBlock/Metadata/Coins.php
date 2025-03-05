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

namespace OpenConext\EngineBlock\Metadata;

use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use RobRichards\XMLSecLibs\XMLSecurityKey;

/**
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
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
        $stepupAllowNoToken,
        $stepupRequireLoa,
        $disableScoping,
        $additionalLogging,
        $signatureMethod,
        $stepupForceAuthn,
        $collabEnabled
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
            'disableScoping' => $disableScoping,
            'additionalLogging' => $additionalLogging,
            'signatureMethod' => $signatureMethod,
            'stepupAllowNoToken' => $stepupAllowNoToken,
            'stepupRequireLoa' => $stepupRequireLoa,
            'stepupForceAuthn' => $stepupForceAuthn,
            'collabEnabled' => $collabEnabled,
        ]);
    }

    public static function createForIdentityProvider(
        $guestQualifier,
        $schacHomeOrganization,
        $hidden,
        $stepupConnections,
        $disableScoping,
        $additionalLogging,
        $signatureMethod,
        $mfaEntities,
        $defaultRAC
    ) {
        return new self([
            'guestQualifier' => $guestQualifier,
            'schacHomeOrganization' => $schacHomeOrganization,
            'hidden' => $hidden,
            'disableScoping' => $disableScoping,
            'additionalLogging' => $additionalLogging,
            'signatureMethod' => $signatureMethod,
            'stepupConnections' => $stepupConnections,
            'mfaEntities' => $mfaEntities,
            'defaultRAC' => $defaultRAC,
        ]);
    }

    private function __construct(array $values)
    {
        $this->values = [];
        foreach ($values as $key => $value) {
            if (!is_null($value)) {
                $this->values[$key] = $value;
            }
        }
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

        if (isset($data['stepupConnections'])) {
            $data['stepupConnections'] = new StepupConnections($data['stepupConnections']);
        }

        if (isset($data['mfaEntities'])) {
            $data['mfaEntities'] = MfaEntityCollection::fromCoin($data['mfaEntities']);
        }

        return new self($data);
    }

    // SP
    public function isConsentRequired()
    {
        return $this->getValue('isConsentRequired', true);
    }

    public function isTransparentIssuer()
    {
        return $this->getValue('isTransparentIssuer', false);
    }

    public function isTrustedProxy()
    {
        return $this->getValue('isTrustedProxy', false);
    }

    public function displayUnconnectedIdpsWayf()
    {
        return $this->getValue('displayUnconnectedIdpsWayf', false);
    }

    public function termsOfServiceUrl()
    {
        return $this->getValue('termsOfServiceUrl');
    }

    public function skipDenormalization()
    {
        return $this->getValue('skipDenormalization', false);
    }

    public function policyEnforcementDecisionRequired()
    {
        return $this->getValue('policyEnforcementDecisionRequired', false);
    }

    public function requesteridRequired()
    {
        return $this->getValue('requesteridRequired', false);
    }

    public function signResponse()
    {
        return $this->getValue('signResponse', false);
    }

    public function stepupAllowNoToken()
    {
        return $this->getValue('stepupAllowNoToken', false);
    }

    public function stepupRequireLoa()
    {
        return $this->getValue('stepupRequireLoa', '');
    }

    /**
     * Should the Stepup authentication request (to the Stepup Gateway)
     * have the ForceAuthn attribute in the AuthnRequest?
     */
    public function isStepupForceAuthn()
    {
        return $this->getValue('stepupForceAuthn', false);
    }

    // IDP
    public function defaultRAC()
    {
        return $this->getValue('defaultRAC');
    }

    public function guestQualifier()
    {
        return $this->getValue('guestQualifier', IdentityProvider::GUEST_QUALIFIER_ALL);
    }

    public function schacHomeOrganization()
    {
        return $this->getValue('schacHomeOrganization');
    }

    public function hidden()
    {
        return $this->getValue('hidden', false);
    }

    /**
     * @return StepupConnections
     */
    public function stepupConnections()
    {
        return $this->getValue('stepupConnections', new StepupConnections());
    }

    // Abstract
    public function disableScoping()
    {
        return $this->getValue('disableScoping', false);
    }

    public function additionalLogging()
    {
        return $this->getValue('additionalLogging', false);
    }

    public function signatureMethod()
    {
        return $this->getValue('signatureMethod', XMLSecurityKey::RSA_SHA256);
    }

    public function collabEnabled()
    {
        return $this->getValue('collabEnabled', false);
    }

    public function mfaEntities(): MfaEntityCollection
    {
        return $this->getValue('mfaEntities', MfaEntityCollection::fromCoin([]));
    }

    private function getValue($key, $default = null)
    {
        if (!array_key_exists($key, $this->values)) {
            return $default;
        }
        return $this->values[$key];
    }
}
