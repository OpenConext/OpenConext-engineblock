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

use OpenConext\EngineBlock\Assert\Assertion;
use OpenConext\EngineBlock\Metadata\TransparentMfaEntity;
use Psr\Log\LoggerInterface;

/**
 * The validator will block any incoming IdP assertion were the configured AuthnContextClassRef for a specific SP/IdP combination
 * doesn't match and will throw an error. As the configured AuthnContextClassRef MUST be set in the AuthnContextClassRef element
 * OR MUST be set as value in the http://schemas.microsoft.com/claims/authnmethodsreferences attribute.
 **/
class EngineBlock_Corto_Filter_Command_ValidateMfaAuthnContextClassRef extends EngineBlock_Corto_Filter_Command_Abstract
{
    const MICROSOFT_AUTHN_METHODS_REFERENCES_ATTRIBUTE = 'http://schemas.microsoft.com/claims/authnmethodsreferences';

    /**
     * @throws EngineBlock_Corto_Exception_AuthnContextClassRefBlacklisted
     */
    public function execute()
    {
        $mfaEntity = $this->_identityProvider->getCoins()->mfaEntities()->findByEntityId($this->_serviceProvider->entityId);
        // SP's configured to pass auth context transparently are not checked for an expected (configured) class ref.
        if (!$mfaEntity || $mfaEntity instanceof TransparentMfaEntity) {
            return;
        }

        $values = $this->getAuthnMethods($this->_response);

        if (!in_array($mfaEntity->level(), $values)) {
            throw new EngineBlock_Corto_Exception_InvalidMfaAuthnContextClassRef(
                sprintf(
                    'Assertion from MFA IdP "%s" for SP "%s" does not contain the requested AuthnContextClassRef "%s"',
                    $this->_identityProvider->entityId,
                    $this->_serviceProvider->entityId,
                    $mfaEntity->level()
                )
            );
        }
    }

    private function getAuthnMethods(EngineBlock_Saml2_ResponseAnnotationDecorator $response)
    {
        $values = [];
        $values[] = $response->getAssertion()->getAuthnContextClassRef();
        $attributes = $response->getAssertion()->getAttributes();

        if (isset($attributes[self::MICROSOFT_AUTHN_METHODS_REFERENCES_ATTRIBUTE])) {
            $values = array_merge($values, $attributes[self::MICROSOFT_AUTHN_METHODS_REFERENCES_ATTRIBUTE]);
        }

        return $values;
    }
}
