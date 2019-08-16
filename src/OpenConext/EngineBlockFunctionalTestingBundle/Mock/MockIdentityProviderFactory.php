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

namespace OpenConext\EngineBlockFunctionalTestingBundle\Mock;

use OpenConext\EngineBlockFunctionalTestingBundle\Saml2\Response;
use SAML2\Compat\ContainerSingleton;
use SAML2\Constants;
use SAML2\XML\md\EntityDescriptor;
use SAML2\XML\md\IDPSSODescriptor;
use SAML2\XML\md\IndexedEndpointType;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class MockIdentityProviderFactory
 * @package OpenConext\EngineBlockFunctionalTestingBundle\Service
 */
class MockIdentityProviderFactory extends AbstractMockEntityFactory
{
    protected $router;

    /**
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @param $idpName
     * @return MockIdentityProvider
     */
    public function createNew($idpName)
    {
        $mockIdp = new MockIdentityProvider($idpName, $this->generateDefaultEntityMetadata($idpName));
        $mockIdp->signAssertions();
        $mockIdp->setResponse($this->generateDefaultResponse($mockIdp));
        return $mockIdp;
    }

    /**
     * @param string $idpName
     * @return EntityDescriptor
     */
    protected function generateDefaultEntityMetadata($idpName)
    {
        $entityMetadata = new EntityDescriptor();
        $entityMetadata->entityID = $this->router->generate(
            'functional_testing_idp_metadata',
            ['idpName' => $idpName],
            RouterInterface::ABSOLUTE_URL
        );

        $acsService = new IndexedEndpointType();
        $acsService->index = 0;
        $acsService->Binding  = Constants::BINDING_HTTP_REDIRECT;
        $acsService->Location = $this->router->generate(
            'functional_testing_idp_sso',
            ['idpName' => $idpName],
            RouterInterface::ABSOLUTE_URL
        );

        $idpSsoDescriptor = new IDPSSODescriptor();
        $idpSsoDescriptor->protocolSupportEnumeration = [Constants::NS_SAMLP];
        $idpSsoDescriptor->SingleSignOnService[] = $acsService;

        $idpSsoDescriptor->KeyDescriptor[] = $this->generateDefaultSigningKeyPair();

        $entityMetadata->RoleDescriptor[] = $idpSsoDescriptor;

        return $entityMetadata;
    }

    private function generateDefaultResponse(MockIdentityProvider $mockIdp)
    {
        $requestId = 'FIXME';
        $idpEntityId = $mockIdp->entityId();
        $responseId  = ContainerSingleton::getInstance()->generateId();
        $assertionId = ContainerSingleton::getInstance()->generateId();

        $now        = gmdate('Y-m-d\TH:i:s\Z');
        $tomorrow   = gmdate('Y-m-d\TH:i:s\Z', time() + (24 * 60 * 60));

        $uid = 'test' . time() . rand(10000, 99999);
        $schacHomeOrganization  = 'engine-test-stand.openconext.org';
        $nameId = 'ETS-MOCK-IDP-' . time();

        $document = new \DOMDocument();
        $document->loadXML(<<<RESPONSE
<samlp:Response
  xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
  xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
  ID="$responseId"
  IssueInstant="$now"
  InResponseTo="$requestId"
  Version="2.0">
    <saml:Issuer>$idpEntityId</saml:Issuer>
    <samlp:Status><samlp:StatusCode Value="urn:oasis:names:tc:SAML:2.0:status:Success" /></samlp:Status>
    <saml:Assertion IssueInstant="$now" Version="2.0" ID="$assertionId">
        <saml:Issuer>$idpEntityId</saml:Issuer>
        <saml:Subject>
            <saml:NameID>$nameId</saml:NameID>
            <saml:SubjectConfirmation Method="urn:oasis:names:tc:SAML:2.0:cm:bearer">
                <saml:SubjectConfirmationData
                  NotOnOrAfter="$tomorrow"
                  InResponseTo="$requestId" />
            </saml:SubjectConfirmation>
        </saml:Subject>
        <saml:AuthnStatement AuthnInstant="$now">
            <saml:AuthnContext>
                <saml:AuthnContextClassRef>
                    urn:oasis:names:tc:SAML:2.0:ac:classes:Password
                </saml:AuthnContextClassRef>
            </saml:AuthnContext>
        </saml:AuthnStatement>
        <saml:AttributeStatement>
            <saml:Attribute Name="urn:mace:dir:attribute-def:uid">
                <saml:AttributeValue>$uid</saml:AttributeValue>
            </saml:Attribute>
            <saml:Attribute Name="urn:mace:terena.org:attribute-def:schacHomeOrganization">
                <saml:AttributeValue>$schacHomeOrganization</saml:AttributeValue>
            </saml:Attribute>
        </saml:AttributeStatement>
    </saml:Assertion>
</samlp:Response>
RESPONSE
        );

        return new Response($document->firstChild);
    }
}
