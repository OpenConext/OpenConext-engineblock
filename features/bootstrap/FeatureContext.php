<?php

$rootDir = realpath(__DIR__ . '/../../');
require_once $rootDir . '/library/simplesamlphp/lib/_autoload.php';

use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;
use Behat\MinkExtension\Context\MinkContext;

// Require 3rd-party libraries here:

/**
 * Features context.
 */
class FeatureContext extends MinkContext
{
    /**
     * Initializes context.
     * Every scenario gets it's own context object.
     *
     * @param array $parameters context parameters (set them up through behat.yml)
     */
    public function __construct(array $parameters)
    {
        // Initialize your context here
    }

    /**
     * @Given /^An authn request is sent to Engineblock$/
     */
    public function anAuthnRequestIsSentToEngineblock()
    {
        $engineUrl = 'https://engine-test.demo.openconext.org';

        $destinationUrl = $engineUrl . '/authentication/idp/single-sign-on';
        $assertionConsumerServiceURL = 'https://engine-test.demo.openconext.org/dummy-sp';
        $issuerUrl = 'https://engine-test.demo.openconext.org/dummy-sp';
        $samlPAuthNRequest = $this->factorySamlPAuthNRequest($assertionConsumerServiceURL, $destinationUrl, $issuerUrl);

        $message = $this->encodeSamlMessage($samlPAuthNRequest);
        $engineRequestUrl = $destinationUrl . '?SAMLRequest=' . urlencode($message);

        $session = $this->getSession();
        $session->visit($engineRequestUrl);
    }

    /**
     * @param $samlMessage
     * @return string
     */
    private function encodeSamlMessage($samlMessage)
    {
        return base64_encode(gzdeflate($samlMessage));
    }

    /**
     * @param string $destinationUrl
     * @param string $assertionConsumerServiceURL
     * @param string $issuerUrl
     * @return string
     */
    private function factorySamlPAuthNRequest(
        $destinationUrl,
        $assertionConsumerServiceURL,
        $issuerUrl
    )
    {
        $samlpAuthNRequest = new SAML2_AuthnRequest();
        $samlpAuthNRequest->setDestination($destinationUrl);
        $samlpAuthNRequest->setAssertionConsumerServiceURL($assertionConsumerServiceURL);
        $samlpAuthNRequest->setIssuer($issuerUrl);
        $samlpAuthNRequest->setProtocolBinding('urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST');
        $samlpAuthNRequest->setNameIdPolicy(array(
            'Format' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:transient',
            'AllowCreate' => true
        ));

        $samlpAuthNRequestDomElement = $samlpAuthNRequest->toUnsignedXML();
        return $samlpAuthNRequestDomElement->ownerDocument->saveXML($samlpAuthNRequestDomElement);
    }
}