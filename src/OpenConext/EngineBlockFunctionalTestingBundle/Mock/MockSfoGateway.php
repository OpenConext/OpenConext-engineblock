<?php
/**
 * Copyright 2019 SURFnet B.V.
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

use DateTime;
use LogicException;
use OpenConext\EngineBlockFunctionalTestingBundle\Fixtures\FunctionalTestingSfoGatewayMockConfiguration;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use RuntimeException;
use SAML2\Assertion;
use SAML2\AuthnRequest as SAML2AuthnRequest;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\Message;
use SAML2\Response;
use SAML2\XML\saml\SubjectConfirmation;
use SAML2\XML\saml\SubjectConfirmationData;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MockSfoGateway
{
    /**
     * @var DateTime
     */
    private $currentTime;

    /**
     * @var FunctionalTestingSfoGatewayMockConfiguration
     */
    private $gatewayConfiguration;

    /**
     * @param FunctionalTestingSfoGatewayMockConfiguration $gatewayConfiguration
     * @throws \Exception
     */
    public function __construct(
        FunctionalTestingSfoGatewayMockConfiguration $gatewayConfiguration
    ) {
        $this->gatewayConfiguration = $gatewayConfiguration;
        $this->currentTime = new DateTime();
    }

    /**
     * @param string $samlRequest
     * @param string $fullRequestUri
     * @return Response
     * @throws \Exception
     */
    public function handleSso($samlRequest, $fullRequestUri)
    {
        // parse the authnRequest
        $authnRequest = $this->parsePostRequest($samlRequest, $fullRequestUri);

        // get parameters from authnRequest
        $nameId = $authnRequest->getNameId()->value;
        $destination = $authnRequest->getDestination();
        $authnContextClassRef = $authnRequest->getRequestedAuthnContext()['AuthnContextClassRef'];
        $requesterId = current($authnRequest->getRequesterID());

        // handle failure
        if ($this->gatewayConfiguration->hasFailure()) {
            $status = $this->gatewayConfiguration->getStatus();
            $subStatus = $this->gatewayConfiguration->getSubStatus();
            $message = $this->gatewayConfiguration->getMessage();

            return $this->createFailureResponse($destination, $requesterId, $status, $subStatus, $message);
        }

        // handle success
        return $this->createSecondFactorOnlyResponse(
            $nameId,
            $destination,
            $authnContextClassRef,
            $requesterId
        );
    }

    /**
     * @param string $nameId
     * @param string $destination The ACS location
     * @param string|null $authnContextClassRef The loa level
     * @param string $requestId The requestId
     * @return Response
     */
    private function createSecondFactorOnlyResponse($nameId, $destination, $authnContextClassRef, $requestId)
    {
        return $this->createNewAuthnResponse(
            $this->createNewAssertion(
                $nameId,
                $authnContextClassRef,
                $destination,
                $requestId
            ),
            $destination,
            $requestId
        );
    }

    /**
     * @param string $samlRequest
     * @param string $fullRequestUri
     * @return SAML2AuthnRequest
     * @throws \Exception
     */
    private function parsePostRequest($samlRequest, $fullRequestUri)
    {
        // 1. Parse to xml object
        // additional security against XXE Processing vulnerability
        $previous = libxml_disable_entity_loader(true);
        $document = DOMDocumentFactory::fromString($samlRequest);
        libxml_disable_entity_loader($previous);

        // 2. Parse saml request
        $authnRequest = Message::fromXML($document->firstChild);

        if (!$authnRequest instanceof SAML2AuthnRequest) {
            throw new RuntimeException(sprintf(
                'The received request is not an AuthnRequest, "%s" received instead',
                substr(get_class($authnRequest), strrpos($authnRequest, '_') + 1)
            ));
        }

        // 3. Validate destination
        if (!$authnRequest->getDestination() === $fullRequestUri) {
            throw new BadRequestHttpException(sprintf(
                'Actual Destination "%s" does not match the AuthnRequest Destination "%s"',
                $fullRequestUri,
                $authnRequest->getDestination()
            ));
        }

        // 4. Validate issuer
        if (!$this->getServiceProvider()->entityId() !== $authnRequest->getIssuer()) {
            throw new BadRequestHttpException(sprintf(
                'Actual issuer "%s" does not match the AuthnRequest Issuer "%s"',
                $this->getServiceProvider()->entityId(),
                $authnRequest->getIssuer()
            ));
        }

        // 5. Validate key
        // Note: $authnRequest->validate throws an Exception when the signature does not match.
        $key = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, array('type' => 'public'));
        $key->loadKey($this->getServiceProvider()->publicKeyCertData());
        if (!$authnRequest->validate($key)) {
            throw new BadRequestHttpException(
                'Validation of the signature in the AuthnRequest failed'
            );
        }

        return $authnRequest;
    }

    /**
     * @param Response $response
     * @return string
     */
    public function parsePostResponse(Response $response)
    {
        return $response->toUnsignedXML()->ownerDocument->saveXML();
    }

    /**
     * @param string $destination The ACS location
     * @param string $requestId The requestId
     * @param string $status The response status (see \SAML2\Constants)
     * @param string|null $subStatus An optional substatus (see \SAML2\Constants)
     * @param string|null $message The textual message
     * @return Response
     */
    private function createFailureResponse($destination, $requestId, $status, $subStatus = null, $message = null)
    {
        $response = new Response();
        $response->setDestination($destination);
        $response->setIssuer($this->getIdentityProvider()->entityId());
        $response->setIssueInstant($this->getTimestamp());
        $response->setInResponseTo($requestId);


        if (!$this->isValidResponseStatus($status)) {
            throw new LogicException(sprintf('Trying to set invalid Response Status'));
        }

        if ($subStatus && !$this->isValidResponseSubStatus($subStatus)) {
            throw new LogicException(sprintf('Trying to set invalid Response SubStatus'));
        }

        $status = ['Code' => $status];
        if ($subStatus) {
            $status['SubCode'] = $subStatus;
        }
        if ($message) {
            $status['Message'] = $message;
        }

        $response->setStatus($status);

        return $response;
    }

    /**
     * @param Response $response
     * @return string
     */
    public function getResponseAsXML(Response $response)
    {
        return base64_encode($response->toUnsignedXML()->ownerDocument->saveXML());
    }

    /**
     * @param Assertion $newAssertion
     * @param string $destination The ACS location
     * @param string $requestId The requestId
     * @return Response
     */
    private function createNewAuthnResponse(Assertion $newAssertion, $destination, $requestId)
    {
        $response = new Response();
        $response->setAssertions([$newAssertion]);
        $response->setIssuer($this->getIdentityProvider()->entityId());
        $response->setIssueInstant($this->getTimestamp());
        $response->setDestination($destination);
        $response->setInResponseTo($requestId);

        return $response;
    }

    /**
     * @param string $nameId
     * @param string $authnContextClassRef
     * @param string $destination The ACS location
     * @param string $requestId The requestId
     * @return Assertion
     */
    private function createNewAssertion($nameId, $authnContextClassRef, $destination, $requestId)
    {
        $newAssertion = new Assertion();
        $newAssertion->setNotBefore($this->currentTime->getTimestamp());
        $newAssertion->setNotOnOrAfter($this->getTimestamp('PT5M'));
        $newAssertion->setIssuer($this->getIdentityProvider()->entityId());
        $newAssertion->setIssueInstant($this->getTimestamp());
        $this->signAssertion($newAssertion);
        $this->addSubjectConfirmationFor($newAssertion, $destination, $requestId);
        $newAssertion->setNameId([
            'Format' => Constants::NAMEID_UNSPECIFIED,
            'Value' => $nameId,
        ]);
        $newAssertion->setValidAudiences([$this->getServiceProvider()->entityId()]);
        $this->addAuthenticationStatementTo($newAssertion, $authnContextClassRef);

        return $newAssertion;
    }

    /**
     * @param Assertion $newAssertion
     * @param string $destination The ACS location
     * @param string $requestId The requestId
     */
    private function addSubjectConfirmationFor(Assertion $newAssertion, $destination, $requestId)
    {
        $confirmation         = new SubjectConfirmation();
        $confirmation->Method = Constants::CM_BEARER;

        $confirmationData                      = new SubjectConfirmationData();
        $confirmationData->InResponseTo        = $requestId;
        $confirmationData->Recipient           = $destination;
        $confirmationData->NotOnOrAfter        = $newAssertion->getNotOnOrAfter();

        $confirmation->SubjectConfirmationData = $confirmationData;

        $newAssertion->setSubjectConfirmation([$confirmation]);
    }

    /**
     * @param Assertion $assertion
     * @param $authnContextClassRef
     */
    private function addAuthenticationStatementTo(Assertion $assertion, $authnContextClassRef)
    {
        $assertion->setAuthnInstant($this->getTimestamp());
        $assertion->setAuthnContextClassRef($authnContextClassRef);
        $assertion->setAuthenticatingAuthority([$this->getIdentityProvider()->entityId()]);
    }

    /**
     * @param string $interval a \DateInterval compatible interval to skew the time with
     * @return int
     */
    private function getTimestamp($interval = null)
    {
        $time = clone $this->currentTime;

        if ($interval) {
            $time->add(new \DateInterval($interval));
        }

        return $time->getTimestamp();
    }

    /**
     * @param Assertion $assertion
     * @return Assertion
     */
    private function signAssertion(Assertion $assertion)
    {
        $assertion->setSignatureKey($this->loadPrivateKey());
        $assertion->setCertificates([$this->getPublicCertificate()]);

        return $assertion;
    }

    /**
     * @return XMLSecurityKey
     */
    private function loadPrivateKey()
    {
        $xmlSecurityKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, ['type' => 'private']);
        $xmlSecurityKey->loadKey($this->getIdentityProvider()->getPrivateKeyPem());

        return $xmlSecurityKey;
    }

    /**
     * @return string
     */
    private function getPublicCertificate()
    {
        return $this->getIdentityProvider()->publicKeyCertData();
    }

    private function isValidResponseStatus($status)
    {
        return in_array($status, [
            Constants::STATUS_SUCCESS,            // weeee!
            Constants::STATUS_REQUESTER,          // Something is wrong with the AuthnRequest
            Constants::STATUS_RESPONDER,          // Something went wrong with the Response
            Constants::STATUS_VERSION_MISMATCH,   // The version of the request message was incorrect
        ]);
    }

    private function isValidResponseSubStatus($subStatus)
    {
        return in_array($subStatus, [
            Constants::STATUS_AUTHN_FAILED,               // failed authentication
            Constants::STATUS_INVALID_ATTR,
            Constants::STATUS_INVALID_NAMEID_POLICY,
            Constants::STATUS_NO_AUTHN_CONTEXT,           // insufficient Loa or Loa cannot be met
            Constants::STATUS_NO_AVAILABLE_IDP,
            Constants::STATUS_NO_PASSIVE,
            Constants::STATUS_NO_SUPPORTED_IDP,
            Constants::STATUS_PARTIAL_LOGOUT,
            Constants::STATUS_PROXY_COUNT_EXCEEDED,
            Constants::STATUS_REQUEST_DENIED,
            Constants::STATUS_REQUEST_UNSUPPORTED,
            Constants::STATUS_REQUEST_VERSION_DEPRECATED,
            Constants::STATUS_REQUEST_VERSION_TOO_HIGH,
            Constants::STATUS_REQUEST_VERSION_TOO_LOW,
            Constants::STATUS_RESOURCE_NOT_RECOGNIZED,
            Constants::STATUS_TOO_MANY_RESPONSES,
            Constants::STATUS_UNKNOWN_ATTR_PROFILE,
            Constants::STATUS_UNKNOWN_PRINCIPAL,
            Constants::STATUS_UNSUPPORTED_BINDING,
        ]);
    }

    /**
     * @return MockIdentityProvider
     */
    private function getIdentityProvider()
    {
        $identityProvider = $this->gatewayConfiguration->getMockIdentityProvider();
        if (!$identityProvider) {
            throw new RuntimeException('No hosted IDP configured in gateway mock');
        }
        return $identityProvider;
    }

    /**
     * @return MockServiceProvider
     */
    private function getServiceProvider()
    {
        $serviceProvider = $this->gatewayConfiguration->getMockServiceProvider();
        if (!$serviceProvider) {
            throw new RuntimeException('No hosted IDP configured in gateway mock');
        }

        return $serviceProvider;
    }
}
