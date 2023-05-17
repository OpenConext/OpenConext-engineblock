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
use SAML2\Constants;
use SAML2\Assertion;
use SAML2\Response;

class EngineBlock_Test_Corto_Filter_Command_VerifyShibMdScopingAllowsSubjectIdTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    const SUB_NAME   = Constants::ATTR_SUBJECT_ID;
    const SUB_SUFFIX = 'openconext.org';
    const SUB_VALUE  = 'invalid@' . self::SUB_SUFFIX;

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
        $this->logger  = new Logger('Test', [$this->handler]);

        $assertion = new Assertion();
        $assertion->setAttributes([self::SUB_NAME => [self::SUB_VALUE]]);

        $response = new Response();
        $response->setAssertions([$assertion]);

        $this->response = new EngineBlock_Saml2_ResponseAnnotationDecorator($response);
    }

    public function testNoConfiguredScopesLeadsToNoVerification()
    {
        $verifier = new EngineBlock_Corto_Filter_Command_VerifyShibMdScopingAllowsSubjectId($this->logger, false);
        $verifier->setResponse($this->response);
        $verifier->setIdentityProvider(new IdentityProvider('OpenConext'));

        $verifier->execute();

        $noScopeMessageLogged = $this->handler->hasNotice(
            'No shibmd:scope found in the IdP metadata, not verifying subject-id'
        );

        $this->assertTrue($noScopeMessageLogged, 'Logging that no shibmd:scope is configured is required');
    }

    public function testNoSubjectIdLeadsToNoVerification()
    {
        $scope          = new ShibMdScope();
        $scope->regexp  = false;
        $scope->allowed = self::SUB_SUFFIX;

        $identityProvider               = new IdentityProvider('OpenConext');
        $identityProvider->shibMdScopes = [$scope];

        // wipe the assertion attributes
        $this->response->getAssertion()->setAttributes([]);

        $verifier = new EngineBlock_Corto_Filter_Command_VerifyShibMdScopingAllowsSubjectId($this->logger, false);
        $verifier->setResponse($this->response);
        $verifier->setIdentityProvider($identityProvider);

        $verifier->execute();

        $noSubjectIdMessageLogged = $this->handler->hasNotice(
            'No subject-id found in response, not verifying'
        );

        $this->assertTrue(
            $noSubjectIdMessageLogged,
            'Logging that no subject-id is found is required'
        );
    }

    public function testSubjectIdWithoutAtSignIsRejected()
    {
        $scope          = new ShibMdScope();
        $scope->regexp  = false;
        $scope->allowed = self::SUB_SUFFIX;

        $identityProvider               = new IdentityProvider('OpenConext');
        $identityProvider->shibMdScopes = [$scope];

        // wipe the assertion attributes
        $this->response->getAssertion()->setAttributes([self::SUB_NAME => ['NoAtSign']]);

        $verifier = new EngineBlock_Corto_Filter_Command_VerifyShibMdScopingAllowsSubjectId($this->logger, false);
        $verifier->setResponse($this->response);
        $verifier->setIdentityProvider($identityProvider);

        $this->expectException(EngineBlock_Corto_Exception_InvalidAttributeValue::class);
        $this->expectExceptionMessage('Invalid subject-id, missing @');

        $verifier->execute();
    }

    public function testSubjectIdWithMultipleValuesRejected()
    {
        $scope          = new ShibMdScope();
        $scope->regexp  = false;
        $scope->allowed = self::SUB_SUFFIX;

        $identityProvider               = new IdentityProvider('OpenConext');
        $identityProvider->shibMdScopes = [$scope];

        $attributes = [self::SUB_NAME => ['something@' . self::SUB_SUFFIX, 'other@' . self::SUB_SUFFIX]];
        $this->response->getAssertion()->setAttributes($attributes);

        $verifier = new EngineBlock_Corto_Filter_Command_VerifyShibMdScopingAllowsSubjectId($this->logger, false);
        $verifier->setResponse($this->response);
        $verifier->setIdentityProvider($identityProvider);

        $this->expectException(EngineBlock_Corto_Exception_InvalidAttributeValue::class);
        $this->expectExceptionMessage('Only exactly one subject-id allowed');

        $verifier->execute();
    }

    public function testSubjectIdNotInScopeIsLoggedAsWarning()
    {
        $scope          = new ShibMdScope();
        $scope->regexp  = false;
        $scope->allowed = 'You shall not pass';

        $identityProvider               = new IdentityProvider('OpenConext');
        $identityProvider->shibMdScopes = [$scope];

        $verifier = new EngineBlock_Corto_Filter_Command_VerifyShibMdScopingAllowsSubjectId($this->logger, false);
        $verifier->setResponse($this->response);
        $verifier->setIdentityProvider($identityProvider);

        $verifier->execute();

        $invalidScopeIsLogged = $this->handler->hasWarningThatContains(
            'subjectId attribute value scope "' . self::SUB_SUFFIX . '" is not allowed by configured ShibMdScopes for IdP '
        );

        $this->assertTrue($invalidScopeIsLogged);
    }

    public function testSubjectIdThatMatchesLogsNoWarning()
    {
        $scope          = new ShibMdScope();
        $scope->regexp  = false;
        $scope->allowed = 'openconext.org';

        $identityProvider               = new IdentityProvider('OpenConext');
        $identityProvider->shibMdScopes = [$scope];

        $verifier = new EngineBlock_Corto_Filter_Command_VerifyShibMdScopingAllowsSubjectId($this->logger, false);
        $verifier->setResponse($this->response);
        $verifier->setIdentityProvider($identityProvider);

        $verifier->execute();

        $invalidScopeIsLogged = $this->handler->hasWarningThatContains(
            'subjectId attribute value scope "' . self::SUB_SUFFIX . '" is not allowed by configured ShibMdScopes for IdP '
        );

        $this->assertFalse($invalidScopeIsLogged);
    }

    public function testSubjectIdThatMatchesCaseInsensitivelyLogsNoWarning()
    {
        $scope          = new ShibMdScope();
        $scope->regexp  = false;
        $scope->allowed = 'openconext.ORG';

        $identityProvider               = new IdentityProvider('OpenConext');
        $identityProvider->shibMdScopes = [$scope];

        $verifier = new EngineBlock_Corto_Filter_Command_VerifyShibMdScopingAllowsSubjectId($this->logger, false);
        $verifier->setResponse($this->response);
        $verifier->setIdentityProvider($identityProvider);

        $verifier->execute();

        $invalidScopeIsLogged = $this->handler->hasWarningThatContains(
            'subjectId attribute value scope "' . self::SUB_SUFFIX . '" is not allowed by configured ShibMdScopes for IdP '
        );

        $this->assertFalse($invalidScopeIsLogged);
    }

    public function testSubjectIdThatMatchesRegexpLogsNoWarning()
    {
        $scope          = new ShibMdScope();
        $scope->regexp  = true;
        $scope->allowed = '.*conext\.org';

        $identityProvider               = new IdentityProvider('OpenConext');
        $identityProvider->shibMdScopes = [$scope];

        $verifier = new EngineBlock_Corto_Filter_Command_VerifyShibMdScopingAllowsSubjectId($this->logger, false);
        $verifier->setResponse($this->response);
        $verifier->setIdentityProvider($identityProvider);

        $verifier->execute();

        $invalidScopeIsLogged = $this->handler->hasWarningThatContains(
            'subjectId attribute value scope "' . self::SUB_SUFFIX . '" is not allowed by configured ShibMdScopes for IdP '
        );

        $this->assertFalse($invalidScopeIsLogged);
    }

    public function testSubjectIdNotInRegexpScopeIsLoggedAsWarning()
    {
        $scope          = new ShibMdScope();
        $scope->regexp  = true;
        $scope->allowed = '.*\.noconext\.org';

        $identityProvider               = new IdentityProvider('OpenConext');
        $identityProvider->shibMdScopes = [$scope];

        $verifier = new EngineBlock_Corto_Filter_Command_VerifyShibMdScopingAllowsSubjectId($this->logger, false);
        $verifier->setResponse($this->response);
        $verifier->setIdentityProvider($identityProvider);

        $verifier->execute();

        $invalidScopeIsLogged = $this->handler->hasWarningThatContains(
            'subjectId attribute value scope "' . self::SUB_SUFFIX . '" is not allowed by configured ShibMdScopes for IdP '
        );

        $this->assertTrue($invalidScopeIsLogged);
    }

    public function testSubjectIdNotInRegexpScopeThrowsException()
    {
        $this->expectException(EngineBlock_Corto_Exception_InvalidAttributeValue::class);
        $this->expectExceptionMessage('subjectId attribute value scope "openconext.org" is not allowed by configured ShibMdScopes for IdP "OpenConext"');

        $scope          = new ShibMdScope();
        $scope->regexp  = true;
        $scope->allowed = '.*\.noconext\.org';

        $identityProvider               = new IdentityProvider('OpenConext');
        $identityProvider->shibMdScopes = [$scope];

        $verifier = new EngineBlock_Corto_Filter_Command_VerifyShibMdScopingAllowsSubjectId($this->logger, true);
        $verifier->setResponse($this->response);
        $verifier->setIdentityProvider($identityProvider);

        $verifier->execute();
    }

}
