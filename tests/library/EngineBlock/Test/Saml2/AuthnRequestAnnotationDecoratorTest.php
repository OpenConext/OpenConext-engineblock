<?php

class EngineBlock_Test_Saml2_AuthnRequestAnnotationDecoratorTest extends PHPUnit_Framework_TestCase
{
    public function testToString()
    {
        $request = new SAML2_AuthnRequest();
        $request->setId('TEST123');
        $request->setIssueInstant(0);

        $annotatedRequest = new EngineBlock_Saml2_AuthnRequestAnnotationDecorator($request);
        $annotatedRequest->setDebug();

        $this->assertEquals(
            '{"sspMessage":"<?xml version=\"1.0\"?>\n<samlp:AuthnRequest xmlns:samlp=\"urn:oasis:names:tc:SAML:2.0:protocol\" xmlns:saml=\"urn:oasis:names:tc:SAML:2.0:assertion\" ID=\"TEST123\" Version=\"2.0\" IssueInstant=\"1970-01-01T00:00:00Z\"\/>\n","voContext":null,"keyId":null,"explicitVoContext":true,"wasSigned":false,"debug":true,"unsolicited":false,"transparent":false,"deliverByBinding":null}',
            $annotatedRequest->__toString()
        );
    }
}
