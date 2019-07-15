<?php

use Monolog\Handler\TestHandler;
use Monolog\Logger;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\ShibMdScope;
use PHPUnit_Framework_TestCase as UnitTest;
use SAML2\Assertion;
use SAML2\Response;

class EngineBlock_Test_Corto_Filter_Command_ValidateAuthnContextClassRefTest extends UnitTest
{
    /**
     * @var TestHandler
     */
    private $handler;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var EngineBlock_Saml2_ResponseAnnotationDecorator
     */
    private $response;

    public function setUp()
    {
        $this->handler = new TestHandler();
        $this->logger  = new Logger('Test', array($this->handler));

        $assertion = new Assertion();
        $assertion->setAuthnContextClassRef('urn:oasis:names:tc:SAML:2.0:ac:classes:Password');

        $response = new Response();
        $response->setAssertions(array($assertion));

        $this->response = new EngineBlock_Saml2_ResponseAnnotationDecorator($response);
    }

    public function testNoConfiguredBlacklistRegexLeadsToNoValidation()
    {
        $verifier = new EngineBlock_Corto_Filter_Command_ValidateAuthnContextClassRef($this->logger, '');
        $verifier->setResponse($this->response);
        $verifier->setIdentityProvider(new IdentityProvider('OpenConext'));

        $verifier->execute();

        $notConfiguredMessageLogged = $this->handler->hasNotice(
            'No authn_context_class_ref_blacklist_regex found in the configuration, not validating AuthnContextClassRef'
        );

        $this->assertTrue($notConfiguredMessageLogged, 'Logging that no shibmd:scope is configured is required');
    }

    public function testNotMatchedBlacklistedRegexpPasses()
    {
        $verifier = new EngineBlock_Corto_Filter_Command_ValidateAuthnContextClassRef($this->logger, '/test/');
        $verifier->setResponse($this->response);
        $verifier->setIdentityProvider(new IdentityProvider('OpenConext'));

        $verifier->execute();
    }

    public function testMatchedBlacklistedRegexpThrowsException()
    {
        $this->expectException(EngineBlock_Corto_Exception_AuthnContextClassRefBlacklisted::class);
        $this->expectExceptionMessage('Assertion from IdP contains a blacklisted AuthnContextClassRef "urn:oasis:names:tc:SAML:2.0:ac:classes:Password"');

        $verifier = new EngineBlock_Corto_Filter_Command_ValidateAuthnContextClassRef($this->logger, '/urn:oasis:names:tc:SAML:2\.0:ac:classes:Password/');
        $verifier->setResponse($this->response);
        $verifier->setIdentityProvider(new IdentityProvider('OpenConext'));

        $verifier->execute();
    }
}
