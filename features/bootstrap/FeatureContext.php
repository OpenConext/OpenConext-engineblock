<?php

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
        $engineRequestUrl = $destinationUrl .'?SAMLRequest=' . urlencode($message);

        $session = $this->getSession();
        $session->visit($engineRequestUrl);
    }
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
        $dateTimeFormatted = gmdate('Y-m-d\TH:i:s\Z', time());

        $samlpAuthNRequest = <<<SAML
<samlp:AuthnRequest xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
                    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
                    ID="_5a62d5699e34038989f590149c0df34678126614da"
                    Version="2.0"
                    IssueInstant="$dateTimeFormatted"
                    Destination="$destinationUrl"
                    AssertionConsumerServiceURL="$assertionConsumerServiceURL"
                    ProtocolBinding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST"
                    >
    <saml:Issuer>$issuerUrl</saml:Issuer>
    <samlp:NameIDPolicy Format="urn:oasis:names:tc:SAML:2.0:nameid-format:transient"
                        AllowCreate="true"
                        />
</samlp:AuthnRequest>
SAML;

        return $samlpAuthNRequest;
    }