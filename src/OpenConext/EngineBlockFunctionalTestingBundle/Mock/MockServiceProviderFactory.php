<?php

namespace OpenConext\EngineBlockFunctionalTestingBundle\Mock;

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
        $descriptor = new \SAML2_XML_md_EntityDescriptor();
        $descriptor->entityID = $this->router->generate(
            'functional_testing_sp_metadata',
            array('spName' => $spName),
            RouterInterface::ABSOLUTE_URL
        );

        $acsService = new \SAML2_XML_md_IndexedEndpointType();
        $acsService->index = 0;
        $acsService->Binding  = \SAML2_Const::BINDING_HTTP_POST;
        $acsService->Location = $this->router->generate(
            'functional_testing_sp_acs',
            array('spName' => $spName),
            RouterInterface::ABSOLUTE_URL
        );

        $spSsoDescriptor = new \SAML2_XML_md_SPSSODescriptor();
        $spSsoDescriptor->protocolSupportEnumeration = array(\SAML2_Const::NS_SAMLP);
        $spSsoDescriptor->AssertionConsumerService[] = $acsService;

        $spSsoDescriptor->KeyDescriptor[] = $this->generateDefaultSigningKeyPair();

        $descriptor->RoleDescriptor[] = $spSsoDescriptor;

        $descriptor->Extensions['LoginRedirectUrl'] = $this->router->generate(
            'functional_testing_sp_login_redirect',
            array('spName' => $spName),
            RouterInterface::ABSOLUTE_URL
        );
        $descriptor->Extensions['LoginPostUrl'] = $this->router->generate(
            'functional_testing_sp_login_post',
            array('spName' => $spName),
            RouterInterface::ABSOLUTE_URL
        );
        return $descriptor;
    }

    private function generateDefaultAuthnRequest(MockServiceProvider $mockSp)
    {
        $request = new \SAML2_AuthnRequest();
        $request->setIssuer($mockSp->entityId());
        return $request;
    }
}
