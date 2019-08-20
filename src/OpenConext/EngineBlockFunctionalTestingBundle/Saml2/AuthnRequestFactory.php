<?php

/**
 * Copyright 2014 SURFnet B.V.
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

namespace OpenConext\EngineBlockFunctionalTestingBundle\Saml2;

use OpenConext\EngineBlockFunctionalTestingBundle\Mock\MockServiceProvider;
use OpenConext\EngineBlockFunctionalTestingBundle\Service\EngineBlock;
use RobRichards\XMLSecLibs\XMLSecurityKey;

/**
 * Class AuthnRequestFactory
 * @package OpenConext\EngineBlockFunctionalTestingBundle\Saml2
 */
class AuthnRequestFactory
{
    /**
     * @param MockServiceProvider $mockSp
     * @param EngineBlock $engineBlock
     * @return AuthnRequest
     * @throws \Exception
     */
    public function createForRequestFromTo(MockServiceProvider $mockSp, EngineBlock $engineBlock)
    {
        $request = $mockSp->getAuthnRequest();

        // Set / override the Destination
        $transparentIdp = $mockSp->getTransparentIdp();
        if (!empty($transparentIdp)) {
            $destination = $engineBlock->transparentSsoLocation($transparentIdp);
        } else {
            $destination = $engineBlock->singleSignOnLocation();
        }
        $request->setDestination($destination);

        if ($mockSp->mustSignAuthnRequests()) {
            $key = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, ['type' => 'private']);
            $key->loadKey($mockSp->getPrivateKeyPem());
            $request->setSignatureKey($key);
        }

        return $request;
    }
}
