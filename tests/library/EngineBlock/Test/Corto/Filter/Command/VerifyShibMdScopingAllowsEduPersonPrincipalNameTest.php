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

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\ShibMdScope;
use PHPUnit\Framework\TestCase;
use SAML2\Assertion;
use SAML2\Response;

class EngineBlock_Test_Corto_Filter_Command_VerifyShibMdScopingAllowsEduPersonPrincipalNameTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    const URN_MACE    = 'urn:mace:dir:attribute-def:eduPersonPrincipalName';
    const URN_OID     = 'urn:oid:1.3.6.1.4.1.5923.1.1.1.6';
    const EPPN_SUFFIX = 'openconext.org';
    const EPPN_VALUE  = 'invalid@' . self::EPPN_SUFFIX;

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

    public function setUp(): void
    {
        $this->handler = new TestHandler();
        $this->logger  = new Logger('Test', array($this->handler));

        $assertion = new Assertion();
        $assertion->setAttributes(array(self::URN_OID => array(self::EPPN_VALUE)));

        $response = new Response();
        $response->setAssertions(array($assertion));

        $this->response = new EngineBlock_Saml2_ResponseAnnotationDecorator($response);
    }

    public function testNoConfiguredScopesLeadsToNoVerification()
    {
        $verifier = new EngineBlock_Corto_Filter_Command_VerifyShibMdScopingAllowsEduPersonPrincipalName($this->logger, false);
        $verifier->setResponse($this->response);
        $verifier->setIdentityProvider(new IdentityProvider('OpenConext'));

        $verifier->execute();

        $noScopeMessageLogged = $this->handler->hasNotice(
            'No shibmd:scope found in the IdP metadata, not verifying eduPersonPrincipalName'
        );

        $this->assertTrue($noScopeMessageLogged, 'Logging that no shibmd:scope is configured is required');
    }

    public function testNoEduPersonPrincipalNameLeadsToNoVerification()
    {
        $scope          = new ShibMdScope();
        $scope->regexp  = false;
        $scope->allowed = self::EPPN_SUFFIX;

        $identityProvider               = new IdentityProvider('OpenConext');
        $identityProvider->shibMdScopes = array($scope);

        // wipe the assertion attributes
        $this->response->getAssertion()->setAttributes(array());

        $verifier = new EngineBlock_Corto_Filter_Command_VerifyShibMdScopingAllowsEduPersonPrincipalName($this->logger, false);
        $verifier->setResponse($this->response);
        $verifier->setIdentityProvider($identityProvider);

        $verifier->execute();

        $noEduPersonPrincipalNameMessageLogged = $this->handler->hasNotice(
            'No eduPersonPrincipalName found in response, not verifying'
        );

        $this->assertTrue(
            $noEduPersonPrincipalNameMessageLogged,
            'Logging that no eduPersonPrincipalName is found is required'
        );
    }

    public function testEduPersonPrincipalNameWithoutAtSignIsLoggedAsWarning()
    {
        $scope          = new ShibMdScope();
        $scope->regexp  = false;
        $scope->allowed = self::EPPN_SUFFIX;

        $identityProvider               = new IdentityProvider('OpenConext');
        $identityProvider->shibMdScopes = array($scope);

        // wipe the assertion attributes
        $this->response->getAssertion()->setAttributes(array(self::URN_OID => array('NoAtSign')));

        $verifier = new EngineBlock_Corto_Filter_Command_VerifyShibMdScopingAllowsEduPersonPrincipalName($this->logger, false);
        $verifier->setResponse($this->response);
        $verifier->setIdentityProvider($identityProvider);

        $verifier->execute();

        $noAtSignMessageLogged = $this->handler->hasWarning(
            'Value of attribute eduPersonPrincipalName does not contain "@", not verifying'
        );

        $this->assertTrue($noAtSignMessageLogged);
    }

    public function testEduPersonPrincipalNameNotInScopeIsLoggedAsWarning()
    {
        $scope          = new ShibMdScope();
        $scope->regexp  = false;
        $scope->allowed = 'You shall not pass';

        $identityProvider               = new IdentityProvider('OpenConext');
        $identityProvider->shibMdScopes = array($scope);

        $verifier = new EngineBlock_Corto_Filter_Command_VerifyShibMdScopingAllowsEduPersonPrincipalName($this->logger, false);
        $verifier->setResponse($this->response);
        $verifier->setIdentityProvider($identityProvider);

        $verifier->execute();

        $invalidScopeIsLogged = $this->handler->hasWarningThatContains(
            'eduPersonPrincipalName attribute value "' . self::EPPN_SUFFIX . '" is not allowed by configured ShibMdScopes for IdP '
        );

        $this->assertTrue($invalidScopeIsLogged);
    }

    public function testEduPersonPrincipalNameThatMatchesLogsNoWarning()
    {
        $scope          = new ShibMdScope();
        $scope->regexp  = false;
        $scope->allowed = 'openconext.org';

        $identityProvider               = new IdentityProvider('OpenConext');
        $identityProvider->shibMdScopes = array($scope);

        $verifier = new EngineBlock_Corto_Filter_Command_VerifyShibMdScopingAllowsEduPersonPrincipalName($this->logger, false);
        $verifier->setResponse($this->response);
        $verifier->setIdentityProvider($identityProvider);

        $verifier->execute();

        $invalidScopeIsLogged = $this->handler->hasWarningThatContains(
            'eduPersonPrincipalName attribute value "' . self::EPPN_SUFFIX . '" is not allowed by configured ShibMdScopes for IdP '
        );

        $this->assertFalse($invalidScopeIsLogged);
    }

    public function testEduPersonPrincipalNameThatMatchesCaseInsensitivelyLogsNoWarning()
    {
        $scope          = new ShibMdScope();
        $scope->regexp  = false;
        $scope->allowed = 'openconext.ORG';

        $identityProvider               = new IdentityProvider('OpenConext');
        $identityProvider->shibMdScopes = array($scope);

        $verifier = new EngineBlock_Corto_Filter_Command_VerifyShibMdScopingAllowsEduPersonPrincipalName($this->logger, false);
        $verifier->setResponse($this->response);
        $verifier->setIdentityProvider($identityProvider);

        $verifier->execute();

        $invalidScopeIsLogged = $this->handler->hasWarningThatContains(
            'eduPersonPrincipalName attribute value "' . self::EPPN_SUFFIX . '" is not allowed by configured ShibMdScopes for IdP '
        );

        $this->assertFalse($invalidScopeIsLogged);
    }

    public function testEduPersonPrincipalNameThatMatchesRegexpLogsNoWarning()
    {
        $scope          = new ShibMdScope();
        $scope->regexp  = true;
        $scope->allowed = '.*conext\.org';

        $identityProvider               = new IdentityProvider('OpenConext');
        $identityProvider->shibMdScopes = array($scope);

        $verifier = new EngineBlock_Corto_Filter_Command_VerifyShibMdScopingAllowsEduPersonPrincipalName($this->logger, false);
        $verifier->setResponse($this->response);
        $verifier->setIdentityProvider($identityProvider);

        $verifier->execute();

        $invalidScopeIsLogged = $this->handler->hasWarningThatContains(
            'eduPersonPrincipalName attribute value "' . self::EPPN_SUFFIX . '" is not allowed by configured ShibMdScopes for IdP '
        );

        $this->assertFalse($invalidScopeIsLogged);
    }

    public function testEduPersonPrincipalNameNotInRegexpScopeIsLoggedAsWarning()
    {
        $scope          = new ShibMdScope();
        $scope->regexp  = true;
        $scope->allowed = '.*\.noconext\.org';

        $identityProvider               = new IdentityProvider('OpenConext');
        $identityProvider->shibMdScopes = array($scope);

        $verifier = new EngineBlock_Corto_Filter_Command_VerifyShibMdScopingAllowsEduPersonPrincipalName($this->logger, false);
        $verifier->setResponse($this->response);
        $verifier->setIdentityProvider($identityProvider);

        $verifier->execute();

        $invalidScopeIsLogged = $this->handler->hasWarningThatContains(
            'eduPersonPrincipalName attribute value "' . self::EPPN_SUFFIX . '" is not allowed by configured ShibMdScopes for IdP '
        );

        $this->assertTrue($invalidScopeIsLogged);
    }

    public function testEduPersonPrincipalNameNotInRegexpScopeThrowsException()
    {
        $this->expectException(EngineBlock_Corto_Exception_InvalidAttributeValue::class);
        $this->expectExceptionMessage('eduPersonPrincipalName attribute value "openconext.org" is not allowed by configured ShibMdScopes for IdP "OpenConext"');

        $scope          = new ShibMdScope();
        $scope->regexp  = true;
        $scope->allowed = '.*\.noconext\.org';

        $identityProvider               = new IdentityProvider('OpenConext');
        $identityProvider->shibMdScopes = array($scope);

        $verifier = new EngineBlock_Corto_Filter_Command_VerifyShibMdScopingAllowsEduPersonPrincipalName($this->logger, true);
        $verifier->setResponse($this->response);
        $verifier->setIdentityProvider($identityProvider);

        $verifier->execute();
    }

}
