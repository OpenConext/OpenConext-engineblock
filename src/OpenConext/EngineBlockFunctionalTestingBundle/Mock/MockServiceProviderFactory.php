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

namespace OpenConext\EngineBlockFunctionalTestingBundle\Mock;

use SAML2\AuthnRequest;
use SAML2\Constants;
use SAML2\XML\md\EntityDescriptor;
use SAML2\XML\md\IndexedEndpointType;
use SAML2\XML\md\SPSSODescriptor;
use SAML2\XML\saml\Issuer;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class MockServiceProviderFactory
 * @package OpenConext\EngineBlockFunctionalTestingBundle\Service
 */
class MockServiceProviderFactory extends AbstractMockEntityFactory
{
    protected $router;

    /**
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function createNew($spName)
    {
        $descriptor = $this->generateDefaultEntityMetadata($spName);

        $mockSp = new MockServiceProvider($spName, $descriptor);

        $mockSp->setAuthnRequest($this->generateDefaultAuthnRequest($mockSp));

        return $mockSp;
    }

    protected function generateDefaultEntityMetadata($spName)
    {
        $descriptor = new EntityDescriptor();
        $descriptor->setEntityID(
            $this->router->generate(
                'functional_testing_sp_metadata',
                ['spName' => $spName],
                RouterInterface::ABSOLUTE_URL
            )
        );

        $acsService = new IndexedEndpointType();
        $acsService->setIndex(0);
        $acsService->setBinding(Constants::BINDING_HTTP_POST);
        $acsService->setLocation(
            $this->router->generate(
                'functional_testing_sp_acs',
                ['spName' => $spName],
                RouterInterface::ABSOLUTE_URL
            )
        );
        $spSsoDescriptor = new SPSSODescriptor();
        $spSsoDescriptor->setProtocolSupportEnumeration([Constants::NS_SAMLP]);
        $spSsoDescriptor->setAssertionConsumerService([$acsService]);

        $spSsoDescriptor->setKeyDescriptor([$this->generateDefaultSigningKeyPair()]);

        $descriptor->setRoleDescriptor([$spSsoDescriptor]);

        $extensions = [
            'LoginRedirectUrl' => $this->router->generate(
                'functional_testing_sp_login_redirect',
                ['spName' => $spName],
                RouterInterface::ABSOLUTE_URL
            ),
            'LoginPostUrl' => $this->router->generate(
                'functional_testing_sp_login_post',
                ['spName' => $spName],
                RouterInterface::ABSOLUTE_URL
            ),
        ];

        $descriptor->setExtensions($extensions);
        return $descriptor;
    }

    private function generateDefaultAuthnRequest(MockServiceProvider $mockSp)
    {
        $request = new AuthnRequest();
        $issuer = new Issuer();
        $issuer->setValue($mockSp->entityId());
        $request->setIssuer($issuer);
        return $request;
    }
}
