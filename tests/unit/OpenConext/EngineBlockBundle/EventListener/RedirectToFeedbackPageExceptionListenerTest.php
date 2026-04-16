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

namespace OpenConext\EngineBlockBundle\Tests\EventListener;

use EngineBlock_ApplicationSingleton;
use EngineBlock_Attributes_Manipulator_CustomException;
use EngineBlock_Corto_Exception_AuthnContextClassRefBlacklisted;
use EngineBlock_Corto_Exception_InvalidAcsLocation;
use EngineBlock_Corto_Exception_InvalidAttributeValue;
use EngineBlock_Corto_Exception_InvalidMfaAuthnContextClassRef;
use EngineBlock_Corto_Exception_InvalidStepupCalloutResponse;
use EngineBlock_Corto_Exception_InvalidStepupLoaLevel;
use EngineBlock_Corto_Exception_MissingRequiredFields;
use EngineBlock_Corto_Exception_PEPNoAccess;
use EngineBlock_Corto_Exception_ReceivedErrorStatusCode;
use EngineBlock_Corto_Exception_UnknownIdentityProviderSigningKey;
use EngineBlock_Corto_Exception_UnknownPreselectedIdp;
use EngineBlock_Corto_Exception_UserCancelledStepupCallout;
use EngineBlock_Corto_Module_Bindings_ClockIssueException;
use EngineBlock_Corto_Module_Bindings_SignatureVerificationException;
use EngineBlock_Corto_Module_Bindings_UnableToReceiveMessageException;
use EngineBlock_Corto_Module_Bindings_UnsolicitedAssertionException;
use EngineBlock_Corto_Module_Bindings_UnsupportedAcsLocationSchemeException;
use EngineBlock_Corto_Module_Bindings_UnsupportedBindingException;
use EngineBlock_Corto_Module_Bindings_UnsupportedSignatureMethodException;
use EngineBlock_Corto_Module_Bindings_VerificationException;
use EngineBlock_Corto_Module_Service_SingleSignOn_NoIdpsException;
use EngineBlock_Corto_Module_Services_SessionLostException;
use EngineBlock_Corto_Module_Services_SessionNotStartedException;
use EngineBlock_Exception_UnknownIdentityProvider;
use EngineBlock_Exception_UnknownRequesterIdInAuthnRequest;
use EngineBlock_Exception_UnknownServiceProvider;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenConext\EngineBlock\Exception\InvalidBindingException;
use OpenConext\EngineBlock\Exception\MissingParameterException;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlockBridge\ErrorReporter;
use OpenConext\EngineBlockBundle\EventListener\RedirectToFeedbackPageExceptionListener;
use OpenConext\EngineBlockBundle\Exception\AuthenticationSessionLimitExceededException;
use OpenConext\EngineBlockBundle\Exception\EntityCanNotBeFoundException;
use OpenConext\EngineBlockBundle\Exception\StuckInAuthenticationLoopException;
use OpenConext\EngineBlockBundle\Exception\UnknownKeyIdException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Throwable;

class RedirectToFeedbackPageExceptionListenerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private LoggerInterface $logger;
    private UrlGeneratorInterface $urlGenerator;
    private ErrorReporter $errorReporter;
    private EngineBlock_ApplicationSingleton $engineBlockSingleton;
    private RedirectToFeedbackPageExceptionListener $listener;

    protected function setUp(): void
    {
        $this->logger = Mockery::mock(LoggerInterface::class)->shouldIgnoreMissing();
        $this->urlGenerator = Mockery::mock(UrlGeneratorInterface::class);
        $this->errorReporter = Mockery::mock(ErrorReporter::class)->shouldIgnoreMissing();
        $this->engineBlockSingleton = Mockery::mock(EngineBlock_ApplicationSingleton::class);

        $this->listener = new RedirectToFeedbackPageExceptionListener(
            $this->engineBlockSingleton,
            $this->urlGenerator,
            $this->errorReporter,
            $this->logger
        );
    }

    #[Test]
    #[DataProvider('exceptionToRouteProvider')]
    public function it_redirects_to_the_correct_feedback_route(Throwable $exception, string $expectedRoute): void
    {
        $this->urlGenerator
            ->shouldReceive('generate')
            ->with($expectedRoute, [], UrlGeneratorInterface::ABSOLUTE_PATH)
            ->once()
            ->andReturn('/feedback');

        $event = $this->createEvent($exception);

        $this->listener->onKernelException($event);

        $this->assertInstanceOf(RedirectResponse::class, $event->getResponse());
    }

    public static function exceptionToRouteProvider(): array
    {
        return [
            'unable to receive message' => [
                new EngineBlock_Corto_Module_Bindings_UnableToReceiveMessageException('test'),
                'authentication_feedback_unable_to_receive_message',
            ],
            'unsolicited assertion (IdP-initiated SSO)' => [
                new EngineBlock_Corto_Module_Bindings_UnsolicitedAssertionException('test'),
                'authentication_feedback_unsolicited_response',
            ],
            'session lost' => [
                new EngineBlock_Corto_Module_Services_SessionLostException('test'),
                'authentication_feedback_session_lost',
            ],
            'session not started' => [
                new EngineBlock_Corto_Module_Services_SessionNotStartedException('test'),
                'authentication_feedback_session_not_started',
            ],
            'no identity providers' => [
                new EngineBlock_Corto_Module_Service_SingleSignOn_NoIdpsException('test'),
                'authentication_feedback_no_idps',
            ],
            'invalid ACS location' => [
                new EngineBlock_Corto_Exception_InvalidAcsLocation('test'),
                'authentication_feedback_invalid_acs_location',
            ],
            'missing required fields' => [
                new EngineBlock_Corto_Exception_MissingRequiredFields('test'),
                'authentication_feedback_missing_required_fields',
            ],
            'authn context class ref blacklisted' => [
                new EngineBlock_Corto_Exception_AuthnContextClassRefBlacklisted('test'),
                'authentication_authn_context_class_ref_blacklisted',
            ],
            'invalid MFA authn context class ref' => [
                new EngineBlock_Corto_Exception_InvalidMfaAuthnContextClassRef('test'),
                'authentication_invalid_mfa_authn_context_class_ref',
            ],
            'unsupported binding' => [
                new EngineBlock_Corto_Module_Bindings_UnsupportedBindingException('test'),
                'authentication_feedback_invalid_acs_binding',
            ],
            'unsupported ACS location URI scheme' => [
                new EngineBlock_Corto_Module_Bindings_UnsupportedAcsLocationSchemeException('test'),
                'authentication_feedback_unsupported_acs_location_uri_scheme',
            ],
            'received error status code' => [
                new EngineBlock_Corto_Exception_ReceivedErrorStatusCode('test'),
                'authentication_feedback_received_error_status_code',
            ],
            'signature verification failed' => [
                new EngineBlock_Corto_Module_Bindings_SignatureVerificationException('test'),
                'authentication_feedback_signature_verification_failed',
            ],
            'verification failed' => [
                new EngineBlock_Corto_Module_Bindings_VerificationException('test'),
                'authentication_feedback_verification_failed',
            ],
            'unknown identity provider signing key' => [
                new EngineBlock_Corto_Exception_UnknownIdentityProviderSigningKey('test', 'https://idp.example.org'),
                'authentication_feedback_unknown_signing_key',
            ],
            'unknown requester ID in authn request' => [
                new EngineBlock_Exception_UnknownRequesterIdInAuthnRequest(new ServiceProvider('https://sp.example.org')),
                'authentication_feedback_unknown_requesterid_in_authnrequest',
            ],
            'PEP no access' => [
                new EngineBlock_Corto_Exception_PEPNoAccess('test'),
                'authentication_feedback_pep_violation',
            ],
            'invalid attribute value' => [
                new EngineBlock_Corto_Exception_InvalidAttributeValue('test', 'urn:attribute', 'bad-value'),
                'authentication_feedback_invalid_attribute_value',
            ],
            'stuck in authentication loop' => [
                new StuckInAuthenticationLoopException('test'),
                'authentication_feedback_stuck_in_authentication_loop',
            ],
            'authentication session limit exceeded' => [
                new AuthenticationSessionLimitExceededException('test'),
                'authentication_feedback_authentication_limit_exceeded',
            ],
            'clock issue' => [
                new EngineBlock_Corto_Module_Bindings_ClockIssueException('test'),
                'authentication_feedback_response_clock_issue',
            ],
        ];
    }

    #[Test]
    #[DataProvider('stepupExceptionToRouteProvider')]
    public function it_redirects_stepup_exceptions_to_the_correct_feedback_route(
        Throwable $exception,
        string $expectedRoute
    ): void {
        $this->urlGenerator
            ->shouldReceive('generate')
            ->with($expectedRoute, [], UrlGeneratorInterface::ABSOLUTE_PATH)
            ->once()
            ->andReturn('/feedback');

        $event = $this->createEvent($exception);

        $this->listener->onKernelException($event);

        $this->assertInstanceOf(RedirectResponse::class, $event->getResponse());
    }

    public static function stepupExceptionToRouteProvider(): array
    {
        $receivedError = new EngineBlock_Corto_Exception_ReceivedErrorStatusCode('Received error status code');
        $receivedError->setFeedbackStatusCode('urn:oasis:names:tc:SAML:2.0:status:Responder');
        $receivedError->setFeedbackStatusMessage('Authentication failed');

        return [
            'user cancelled stepup callout' => [
                new EngineBlock_Corto_Exception_UserCancelledStepupCallout('test', $receivedError),
                'authentication_feedback_stepup_callout_user_cancelled',
            ],
            'stepup unmet LOA' => [
                new EngineBlock_Corto_Exception_InvalidStepupLoaLevel('test', $receivedError),
                'authentication_feedback_stepup_callout_unmet_loa',
            ],
            'stepup unknown callout response' => [
                new EngineBlock_Corto_Exception_InvalidStepupCalloutResponse('test', $receivedError),
                'authentication_feedback_stepup_callout_unknown',
            ],
        ];
    }

    #[Test]
    public function it_includes_the_signature_method_as_a_route_param(): void
    {
        $exception = new EngineBlock_Corto_Module_Bindings_UnsupportedSignatureMethodException('rsa-sha1');

        $this->urlGenerator
            ->shouldReceive('generate')
            ->with(
                'authentication_feedback_unsupported_signature_method',
                ['signature-method' => 'rsa-sha1'],
                UrlGeneratorInterface::ABSOLUTE_PATH
            )
            ->once()
            ->andReturn('/feedback');

        $event = $this->createEvent($exception);

        $this->listener->onKernelException($event);

        $this->assertInstanceOf(RedirectResponse::class, $event->getResponse());
    }

    #[Test]
    public function it_includes_entity_id_as_a_route_param_for_unknown_service_provider(): void
    {
        $exception = new EngineBlock_Exception_UnknownServiceProvider('test', 'https://sp.example.org');

        $this->urlGenerator
            ->shouldReceive('generate')
            ->with(
                'authentication_feedback_unknown_service_provider',
                ['entity-id' => 'https://sp.example.org'],
                UrlGeneratorInterface::ABSOLUTE_PATH
            )
            ->once()
            ->andReturn('/feedback');

        $event = $this->createEvent($exception);

        $this->listener->onKernelException($event);

        $this->assertInstanceOf(RedirectResponse::class, $event->getResponse());
    }

    #[Test]
    public function it_includes_entity_id_and_destination_as_route_params_for_unknown_identity_provider(): void
    {
        $exception = new EngineBlock_Exception_UnknownIdentityProvider('test', 'https://idp.example.org', 'https://engine.example.org/sso');

        $this->urlGenerator
            ->shouldReceive('generate')
            ->with(
                'authentication_feedback_unknown_identity_provider',
                ['entity-id' => 'https://idp.example.org', 'destination' => 'https://engine.example.org/sso'],
                UrlGeneratorInterface::ABSOLUTE_PATH
            )
            ->once()
            ->andReturn('/feedback');

        $event = $this->createEvent($exception);

        $this->listener->onKernelException($event);

        $this->assertInstanceOf(RedirectResponse::class, $event->getResponse());
    }

    #[Test]
    public function it_includes_key_id_as_a_route_param_for_unknown_key_id(): void
    {
        $exception = new UnknownKeyIdException('my-key-id');

        $this->urlGenerator
            ->shouldReceive('generate')
            ->with(
                'authentication_feedback_unknown_keyid',
                ['keyid' => 'my-key-id'],
                UrlGeneratorInterface::ABSOLUTE_PATH
            )
            ->once()
            ->andReturn('/feedback');

        $event = $this->createEvent($exception);

        $this->listener->onKernelException($event);

        $this->assertInstanceOf(RedirectResponse::class, $event->getResponse());
    }

    #[Test]
    public function it_includes_the_idp_hash_as_a_route_param_for_unknown_preselected_idp(): void
    {
        $exception = new EngineBlock_Corto_Exception_UnknownPreselectedIdp('test', 'abc123hash');

        $this->urlGenerator
            ->shouldReceive('generate')
            ->with(
                'authentication_feedback_unknown_preselected_idp',
                ['idp-hash' => 'abc123hash'],
                UrlGeneratorInterface::ABSOLUTE_PATH
            )
            ->once()
            ->andReturn('/feedback');

        $event = $this->createEvent($exception);

        $this->listener->onKernelException($event);

        $this->assertInstanceOf(RedirectResponse::class, $event->getResponse());
    }

    #[Test]
    public function it_stores_feedback_in_session_for_custom_attribute_manipulator_exception(): void
    {
        $exception = new EngineBlock_Attributes_Manipulator_CustomException('Custom feedback message');

        $this->urlGenerator
            ->shouldReceive('generate')
            ->with('authentication_feedback_custom', [], UrlGeneratorInterface::ABSOLUTE_PATH)
            ->once()
            ->andReturn('/feedback');

        $event = $this->createEventWithSession($exception);

        $this->listener->onKernelException($event);

        $this->assertInstanceOf(RedirectResponse::class, $event->getResponse());
        $this->assertSame(
            $exception->getFeedback(),
            $event->getRequest()->getSession()->get('feedback_custom')
        );
    }

    #[Test]
    public function it_stores_message_in_session_for_invalid_binding_exception(): void
    {
        $exception = new InvalidBindingException('No binding found');

        $this->urlGenerator
            ->shouldReceive('generate')
            ->with('authentication_feedback_no_authentication_request_received', [], UrlGeneratorInterface::ABSOLUTE_PATH)
            ->once()
            ->andReturn('/feedback');

        $event = $this->createEventWithSession($exception);

        $this->listener->onKernelException($event);

        $this->assertInstanceOf(RedirectResponse::class, $event->getResponse());
        $this->assertSame('No binding found', $event->getRequest()->getSession()->get('feedback_custom'));
    }

    #[Test]
    public function it_stores_message_in_session_for_missing_parameter_exception(): void
    {
        $exception = new MissingParameterException('Required parameter missing');

        $this->urlGenerator
            ->shouldReceive('generate')
            ->with('authentication_feedback_no_authentication_request_received', [], UrlGeneratorInterface::ABSOLUTE_PATH)
            ->once()
            ->andReturn('/feedback');

        $event = $this->createEventWithSession($exception);

        $this->listener->onKernelException($event);

        $this->assertInstanceOf(RedirectResponse::class, $event->getResponse());
        $this->assertSame('Required parameter missing', $event->getRequest()->getSession()->get('feedback_custom'));
    }

    #[Test]
    public function it_stores_message_in_session_for_entity_not_found_exception(): void
    {
        $exception = new EntityCanNotBeFoundException('Entity not found');

        $this->urlGenerator
            ->shouldReceive('generate')
            ->with('authentication_feedback_metadata_entity_not_found', [], UrlGeneratorInterface::ABSOLUTE_PATH)
            ->once()
            ->andReturn('/feedback');

        $event = $this->createEventWithSession($exception);

        $this->listener->onKernelException($event);

        $this->assertInstanceOf(RedirectResponse::class, $event->getResponse());
        $this->assertSame('Entity not found', $event->getRequest()->getSession()->get('feedback_custom'));
    }

    #[Test]
    public function it_does_not_set_a_response_for_unrecognized_exceptions(): void
    {
        $exception = new RuntimeException('Some unhandled exception');
        $event = $this->createEvent($exception);

        $this->listener->onKernelException($event);

        $this->assertNull($event->getResponse());
    }

    private function createEvent(Throwable $exception): ExceptionEvent
    {
        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = Request::create('/');
        return new ExceptionEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $exception);
    }

    private function createEventWithSession(Throwable $exception): ExceptionEvent
    {
        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = Request::create('/');
        $request->setSession(new Session(new MockArraySessionStorage()));
        return new ExceptionEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $exception);
    }
}
