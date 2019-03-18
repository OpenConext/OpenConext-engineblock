<?php

use Monolog\Handler\TestHandler;
use Monolog\Logger;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\ShibMdScope;
use PHPUnit_Framework_TestCase as UnitTest;
use SAML2\Assertion;
use SAML2\Response;

class EngineBlock_Test_Corto_Filter_Command_VerifyShibMdScopingAllowsSchacHomeOrganisationTest extends UnitTest
{
    const URN_MACE  = 'urn:mace:terena.org:attribute-def:schacHomeOrganization';
    const URN_OID   = 'urn:oid:1.3.6.1.4.1.25178.1.2.9';
    const SHO_VALUE = 'OpenConext';

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
        $assertion->setAttributes(array(self::URN_OID => array(self::SHO_VALUE)));

        $response = new Response();
        $response->setAssertions(array($assertion));

        $this->response = new EngineBlock_Saml2_ResponseAnnotationDecorator($response);
    }

    public function testNoConfiguredScopesLeadsToNoVerification()
    {
        $verifier = new EngineBlock_Corto_Filter_Command_VerifyShibMdScopingAllowsSchacHomeOrganisation($this->logger, false);
        $verifier->setResponse($this->response);
        $verifier->setIdentityProvider(new IdentityProvider('OpenConext'));

        $verifier->execute();

        $noScopeMessageLogged = $this->handler->hasNotice(
            'No shibmd:scope found in the IdP metadata, not verifying schacHomeOrganization'
        );

        $this->assertTrue($noScopeMessageLogged, 'Logging that no shibmd:scope is configured is required');
    }

    public function testNoSchacHomeOrganizationLeadsToNoVerification()
    {
        $scope          = new ShibMdScope();
        $scope->regexp  = false;
        $scope->allowed = self::SHO_VALUE;

        $identityProvider               = new IdentityProvider(self::SHO_VALUE);
        $identityProvider->shibMdScopes = array($scope);

        // wipe the assertion attributes
        $this->response->getAssertion()->setAttributes(array());

        $verifier = new EngineBlock_Corto_Filter_Command_VerifyShibMdScopingAllowsSchacHomeOrganisation($this->logger, false);
        $verifier->setResponse($this->response);
        $verifier->setIdentityProvider($identityProvider);

        $verifier->execute();

        $noSchacHomeOrganizationMessageLogged = $this->handler->hasNotice(
            'No schacHomeOrganization found in response, not verifying'
        );

        $this->assertTrue(
            $noSchacHomeOrganizationMessageLogged,
            'Logging that no schacHomeOrganization is found is required'
        );
    }

    public function testSchacHomeOrganizationNotInScopeIsLoggedAsWarning()
    {
        $scope          = new ShibMdScope();
        $scope->regexp  = false;
        $scope->allowed = 'You shall not pass';

        $identityProvider               = new IdentityProvider(self::SHO_VALUE);
        $identityProvider->shibMdScopes = array($scope);

        $verifier = new EngineBlock_Corto_Filter_Command_VerifyShibMdScopingAllowsSchacHomeOrganisation($this->logger, false);
        $verifier->setResponse($this->response);
        $verifier->setIdentityProvider($identityProvider);

        $verifier->execute();

        $invalidScopeIsLogged = $this->handler->hasWarningThatContains(
            'schacHomeOrganization attribute value "' . self::SHO_VALUE . '" is not allowed by configured ShibMdScopes for IdP '
        );

        $this->assertTrue($invalidScopeIsLogged);
    }

    public function testSchacHomeOrganizationThatMatchesLogsNoWarning()
    {
        $scope          = new ShibMdScope();
        $scope->regexp  = false;
        $scope->allowed = 'OpenConext';

        $identityProvider               = new IdentityProvider(self::SHO_VALUE);
        $identityProvider->shibMdScopes = array($scope);

        $verifier = new EngineBlock_Corto_Filter_Command_VerifyShibMdScopingAllowsSchacHomeOrganisation($this->logger, false);
        $verifier->setResponse($this->response);
        $verifier->setIdentityProvider($identityProvider);

        $verifier->execute();

        $invalidScopeIsLogged = $this->handler->hasWarningThatContains(
            'schacHomeOrganization attribute value "' . self::SHO_VALUE . '" is not allowed by configured ShibMdScopes for IdP '
        );

        $this->assertFalse($invalidScopeIsLogged);
    }

    public function testSchacHomeOrganizationThatMatchesCaseInsensitivelyLogsNoWarning()
    {
        $scope          = new ShibMdScope();
        $scope->regexp  = false;
        $scope->allowed = 'opeNconexT';

        $identityProvider               = new IdentityProvider(self::SHO_VALUE);
        $identityProvider->shibMdScopes = array($scope);

        $verifier = new EngineBlock_Corto_Filter_Command_VerifyShibMdScopingAllowsSchacHomeOrganisation($this->logger, false);
        $verifier->setResponse($this->response);
        $verifier->setIdentityProvider($identityProvider);

        $verifier->execute();

        $invalidScopeIsLogged = $this->handler->hasWarningThatContains(
            'schacHomeOrganization attribute value "' . self::SHO_VALUE . '" is not allowed by configured ShibMdScopes for IdP '
        );

        $this->assertFalse($invalidScopeIsLogged);
    }

    public function testSchacHomeOrganizationThatMatchesRegexpLogsNoWarning()
    {
        $scope          = new ShibMdScope();
        $scope->regexp  = true;
        $scope->allowed = '.*Conext';

        $identityProvider               = new IdentityProvider(self::SHO_VALUE);
        $identityProvider->shibMdScopes = array($scope);

        $verifier = new EngineBlock_Corto_Filter_Command_VerifyShibMdScopingAllowsSchacHomeOrganisation($this->logger, false);
        $verifier->setResponse($this->response);
        $verifier->setIdentityProvider($identityProvider);

        $verifier->execute();

        $invalidScopeIsLogged = $this->handler->hasWarningThatContains(
            'schacHomeOrganization attribute value "' . self::SHO_VALUE . '" is not allowed by configured ShibMdScopes for IdP '
        );

        $this->assertFalse($invalidScopeIsLogged);
    }

    public function testSchacHomeOrganizationNotInRegexpScopeIsLoggedAsWarning()
    {
        $scope          = new ShibMdScope();
        $scope->regexp  = true;
        $scope->allowed = '.*\.noconext\.org';

        $identityProvider               = new IdentityProvider(self::SHO_VALUE);
        $identityProvider->shibMdScopes = array($scope);

        $verifier = new EngineBlock_Corto_Filter_Command_VerifyShibMdScopingAllowsSchacHomeOrganisation($this->logger, false);
        $verifier->setResponse($this->response);
        $verifier->setIdentityProvider($identityProvider);

        $verifier->execute();

        $invalidScopeIsLogged = $this->handler->hasWarningThatContains(
            'schacHomeOrganization attribute value "' . self::SHO_VALUE . '" is not allowed by configured ShibMdScopes for IdP '
        );

        $this->assertTrue($invalidScopeIsLogged);
    }


    public function testSchacHomeOrganizationNotInRegexpScopeThrowsException()
    {
        $this->expectException(EngineBlock_Corto_Exception_InvalidAttributeValue::class);
        $this->expectExceptionMessage('schacHomeOrganization attribute value "OpenConext" is not allowed by configured ShibMdScopes for IdP "OpenConext"');

        $scope          = new ShibMdScope();
        $scope->regexp  = true;
        $scope->allowed = '.*\.noconext\.org';

        $identityProvider               = new IdentityProvider(self::SHO_VALUE);
        $identityProvider->shibMdScopes = array($scope);

        $verifier = new EngineBlock_Corto_Filter_Command_VerifyShibMdScopingAllowsSchacHomeOrganisation($this->logger, true);
        $verifier->setResponse($this->response);
        $verifier->setIdentityProvider($identityProvider);

        $verifier->execute();
    }
}
