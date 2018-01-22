<?php

use SAML2\Assertion;
use SAML2\AuthnRequest;
use SAML2\Constants;
use SAML2\Message;
use SAML2\Response;
use SAML2\XML\saml\SubjectConfirmation;
use SAML2\XML\saml\SubjectConfirmationData;

/**
 * NOTE: use for testing only!
 *
 * @todo write test
 */
class EngineBlock_Saml2_ResponseFactory
{
    /**
     * @param AuthnRequest $authnRequest
     * @param SimpleSAML_Configuration $idpConfig
     * @param $nameId
     * @param $issuer
     * @param array $attributes
     * @return Response
     */
    public function create(
        AuthnRequest $authnRequest,
        SimpleSAML_Configuration $idpConfig,
        $nameId,
        $issuer,
        array $attributes
    )
    {
        /* $returnAttributes contains the attributes we should return. Send them. */
        $assertion = new Assertion();
        $assertion->setIssuer($issuer);
        $assertion->setNameId(array(
            'Value' => $nameId,
            'Format' => Constants::NAMEID_UNSPECIFIED
        ));
        $assertion->setNotBefore(time());
        $assertion->setNotOnOrAfter(time() + 5*60);
        // Valid audiences is not required so disabled for now
        // $assertion->setValidAudiences(array($authnRequest->getIssuer()));
        $assertion->setAttributes($attributes);
        $assertion->setAttributeNameFormat(Constants::NAMEFORMAT_UNSPECIFIED);
        $assertion->setAuthnContextClassRef(Constants::AC_PASSWORD);

        $subjectConfirmation = new SubjectConfirmation();
        $subjectConfirmation->Method = Constants::CM_BEARER;
        $subjectConfirmation->SubjectConfirmationData = new SubjectConfirmationData();
        $subjectConfirmation->SubjectConfirmationData->NotOnOrAfter = time() + 5*60;
        $subjectConfirmation->SubjectConfirmationData->Recipient = $authnRequest->getAssertionConsumerServiceURL();
        $subjectConfirmation->SubjectConfirmationData->InResponseTo = $authnRequest->getId();
        $assertion->setSubjectConfirmation(array($subjectConfirmation));

        $response = new Response();
        $response->setRelayState($authnRequest->getRelayState());
        $response->setDestination($authnRequest->getAssertionConsumerServiceURL());
        $response->setIssuer($issuer);
        $response->setInResponseTo($authnRequest->getId());
        $response->setAssertions(array($assertion));

        $this->addSigns($response, $idpConfig);

        return $response;
    }

    /**
     * @param Response $response
     * @param SimpleSAML_Configuration $idpConfig
     */
    private function addSigns(Response $response, SimpleSAML_Configuration $idpConfig)
    {
        $assertions = $response->getAssertions();
        $className = EngineBlock_ApplicationSingleton::getInstance()->getDiContainer()->getMessageUtilClassName();

        // Special case the 'normal' message verification class name so we have IDE support.
        if ($className === 'sspmod_saml_Message') {
            sspmod_saml_Message::addSign(
                $idpConfig,
                SimpleSAML_Configuration::loadFromArray(array()),
                $assertions[0]
            );
            return;
        }

        $className::addSign(
            $idpConfig,
            SimpleSAML_Configuration::loadFromArray(array()),
            $assertions[0]
        );
    }

    /**
     * @param EngineBlock_Http_Request $httpRequest
     * @return Message
     */
    public function createFromHttpRequest(EngineBlock_Http_Request $httpRequest)
    {
        $parameter = $this->getParameterFromHttpRequest($httpRequest);
        $responseXml = $this->decodeParameter($parameter);

        $serializer = new EngineBlock_Saml2_MessageSerializer();
        return $serializer->deserialize($responseXml, Response::class);
    }

    /**
     * @param EngineBlock_Http_Request $httpRequest
     * @return string
     * @throws Exception
     */
    private function getParameterFromHttpRequest(EngineBlock_Http_Request $httpRequest)
    {
        $parameter = $httpRequest->getPostParameter('SAMLResponse');
        if (empty($parameter)) {
            throw new Exception('No SAMLResponse parameter');
        }

        return $parameter;
    }

    /**
     * @param string $parameter
     * @return string
     */
    private function decodeParameter($parameter)
    {
        return base64_decode($parameter);
    }
}
