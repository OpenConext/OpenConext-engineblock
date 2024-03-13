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

use DateInterval;
use DateTime;
use LogicException;
use OpenConext\EngineBlockFunctionalTestingBundle\Fixtures\FunctionalTestingStepupGatewayMockConfiguration;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use RuntimeException;
use SAML2\Assertion;
use SAML2\AuthnRequest as SAML2AuthnRequest;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\Message;
use SAML2\Response;
use SAML2\XML\saml\Issuer;
use SAML2\XML\saml\NameID;
use SAML2\XML\saml\SubjectConfirmation;
use SAML2\XML\saml\SubjectConfirmationData;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MockStepupGateway
{

    const PARAMETER_REQUEST = 'SAMLRequest';
    const PARAMETER_RELAY_STATE = 'RelayState';
    const PARAMETER_SIGNATURE = 'Signature';
    const PARAMETER_SIGNATURE_ALGORITHM = 'SigAlg';

    /**
     * @var DateTime
     */
    private $currentTime;

    /**
     * @var FunctionalTestingStepupGatewayMockConfiguration
     */
    private $gatewayConfiguration;

    /**
     * @var string
     */
    private $sfoRolloverEntityId;

    /**
     * @param FunctionalTestingStepupGatewayMockConfiguration $gatewayConfiguration
     * @throws \Exception
     */
    public function __construct(
        FunctionalTestingStepupGatewayMockConfiguration $gatewayConfiguration,
        $sfoRolloverEntityId
    ) {
        $this->gatewayConfiguration = $gatewayConfiguration;
        $this->currentTime = new DateTime();
        $this->sfoRolloverEntityId = $sfoRolloverEntityId;
    }

    /**
     * @param Request $request
     * @param string $fullRequestUri
     * @param bool $updateAudience [default] false
     * @return Response
     */
    public function handleSsoSuccess(Request $request, $fullRequestUri, $updateAudience = false)
    {
        // parse the authnRequest
        $authnRequest = $this->parseRequest($request, $fullRequestUri);

        // get parameters from authnRequest
        $nameId = $authnRequest->getNameId()->getValue();
        $destination = $authnRequest->getAssertionConsumerServiceURL();
        $authnContextClassRef = current($authnRequest->getRequestedAuthnContext()['AuthnContextClassRef']);
        $requestId = $authnRequest->getId();

        // handle success
        return $this->createSecondFactorOnlyResponse(
            $nameId,
            $destination,
            $authnContextClassRef,
            $requestId,
            $updateAudience
        );
    }

    /**
     * @param Request $request
     * @param string $fullRequestUri
     * @return Response
     */
    public function handleSsoSuccessLoa2(Request $request, $fullRequestUri)
    {
        // parse the authnRequest
        $authnRequest = $this->parseRequest($request, $fullRequestUri);

        // get parameters from authnRequest
        $nameId = $authnRequest->getNameId()->getValue();
        $destination = $authnRequest->getAssertionConsumerServiceURL();
        $authnContextClassRef = 'https://gateway.tld/authentication/loa2';
        $requestId = $authnRequest->getId();

        // handle success
        return $this->createSecondFactorOnlyResponse(
            $nameId,
            $destination,
            $authnContextClassRef,
            $requestId
        );
    }

    /**
     * @param Request $request
     * @param string $fullRequestUri
     * @param string $status
     * @param string $subStatus
     * @param string $message
     * @return Response
     */
    public function handleSsoFailure(Request $request, $fullRequestUri, $status, $subStatus, $message = '')
    {
        // parse the authnRequest
        $authnRequest = $this->parseRequest($request, $fullRequestUri);

        // get parameters from authnRequest
        $destination = $authnRequest->getAssertionConsumerServiceURL();
        $requestId = $authnRequest->getId();

        return $this->createFailureResponse($destination, $requestId, $status, $subStatus, $message);
    }


    /**
     * @param string $nameId
     * @param string $destination The ACS location
     * @param string|null $authnContextClassRef The loa level
     * @param string $requestId The requestId
     * @param bool $updateAudience [default] false
     * @return Response
     */
    private function createSecondFactorOnlyResponse($nameId, $destination, $authnContextClassRef, $requestId, $updateAudience = false)
    {
        return $this->createNewAuthnResponse(
            $this->createNewAssertion(
                $nameId,
                $authnContextClassRef,
                $destination,
                $requestId,
                $updateAudience
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
    private function parseRequest(Request $request, $fullRequestUri)
    {
        // the GET parameter is already urldecoded by Symfony, so we should not do it again.
        $requestData = $request->get(self::PARAMETER_REQUEST);
        $samlRequest = base64_decode($requestData, true);
        if ($samlRequest === false) {
            throw new BadRequestHttpException('Failed decoding the request, did not receive a valid base64 string');
        }

        // Catch any errors gzinflate triggers
        $errorNo = $errorMessage = null;
        set_error_handler(function ($number, $message) use (&$errorNo, &$errorMessage) {
            $errorNo      = $number;
            $errorMessage = $message;
        });
        $samlRequest = gzinflate($samlRequest);
        restore_error_handler();

        if ($samlRequest === false) {
            throw new BadRequestHttpException(sprintf(
                'Failed inflating the request; error "%d": "%s"',
                $errorNo,
                $errorMessage
            ));
        }

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
        if (!$this->gatewayConfiguration->getServiceProviderEntityId() === $authnRequest->getIssuer()) {
            throw new BadRequestHttpException(sprintf(
                'Actual issuer "%s" does not match the AuthnRequest Issuer "%s"',
                $this->gatewayConfiguration->getServiceProviderEntityId(),
                $authnRequest->getIssuer()->getValue()
            ));
        }

        // 5. Validate key
        // Note: $authnRequest->validate throws an Exception when the signature does not match.
        $key = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, array('type' => 'public'));
        $key->loadKey($this->gatewayConfiguration->getIdentityProviderPublicKeyCertData());

        // The query string to validate needs to be urlencoded again because Symfony has already decoded this for us
        $query = self::PARAMETER_REQUEST . '=' . urlencode($requestData);
        $query .= '&' . self::PARAMETER_SIGNATURE_ALGORITHM . '=' . urlencode($request->get(self::PARAMETER_SIGNATURE_ALGORITHM));

        $signature = base64_decode($request->get(self::PARAMETER_SIGNATURE));

        if (!$key->verifySignature($query, $signature)) {
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
        $issuer = new Issuer();
        $issuer->setValue($this->gatewayConfiguration->getIdentityProviderEntityId());
        $response->setIssuer($issuer);
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
     * @param Assertion $newAssertion
     * @param string $destination The ACS location
     * @param string $requestId The requestId
     * @return Response
     */
    private function createNewAuthnResponse(Assertion $newAssertion, $destination, $requestId)
    {
        $response = new Response();
        $response->setAssertions([$newAssertion]);
        $issuer = new Issuer();
        $issuer->setValue($this->gatewayConfiguration->getIdentityProviderEntityId());
        $response->setIssuer($issuer);
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
     * @param bool $updateAudience [default] false
     * @return Assertion
     */
    private function createNewAssertion($nameId, $authnContextClassRef, $destination, $requestId, $updateAudience = false)
    {
        $newAssertion = new Assertion();
        $newAssertion->setNotBefore($this->currentTime->getTimestamp());
        $newAssertion->setNotOnOrAfter($this->getTimestamp('PT5M'));
        $issuer = new Issuer();
        $issuer->setValue($this->gatewayConfiguration->getIdentityProviderEntityId());
        $newAssertion->setIssuer($issuer);
        $newAssertion->setIssueInstant($this->getTimestamp());
        $this->signAssertion($newAssertion);
        $this->addSubjectConfirmationFor($newAssertion, $destination, $requestId);
        $newNameId = new NameID();
        $newNameId->setValue($nameId);
        $newNameId->setFormat(Constants::NAMEID_UNSPECIFIED);
        $newAssertion->setNameId($newNameId);
        $audiences = [$this->gatewayConfiguration->getServiceProviderEntityId()];
        // If the entity id being updated, then set that new EntityId as the audience for this assertion
        if ($updateAudience) {
            $audiences = [$this->sfoRolloverEntityId];
        }
        $newAssertion->setValidAudiences($audiences);
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
        $confirmation = new SubjectConfirmation();
        $confirmation->setMethod(Constants::CM_BEARER);

        $confirmationData = new SubjectConfirmationData();
        $confirmationData->setInResponseTo($requestId);
        $confirmationData->setRecipient($destination);
        $confirmationData->setNotOnOrAfter($newAssertion->getNotOnOrAfter());

        $confirmation->setSubjectConfirmationData($confirmationData);

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
        $assertion->setAuthenticatingAuthority([$this->gatewayConfiguration->getIdentityProviderEntityId()]);
    }

    /**
     * @param string $interval a DateInterval compatible interval to skew the time with
     * @return int
     */
    private function getTimestamp($interval = null)
    {
        $time = clone $this->currentTime;

        if ($interval) {
            $time->add(new DateInterval($interval));
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
        $xmlSecurityKey->loadKey($this->gatewayConfiguration->getIdentityProviderGetPrivateKeyPem());

        return $xmlSecurityKey;
    }

    /**
     * @return string
     */
    private function getPublicCertificate()
    {
        return $this->gatewayConfiguration->getIdentityProviderPublicKeyCertData();
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
}
