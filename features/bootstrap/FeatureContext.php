<?php

use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

// Require 3rd-party libraries here:

/**
 * Features context.
 */
class FeatureContext extends BehatContext
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
     * @Given /^I request Engine to log me in$/
     */
    public function iRequestEngineToLogMeIn()
    {
        $engineUrl = 'https://engine.demo.openconext.org';

        $destinationUrl = $engineUrl . '/authentication/idp/single-sign-on';
        $assertionConsumerServiceURL = 'https://profile.demo.openconext.org/simplesaml/module.php/saml/sp/saml2-acs.php/default-sp';
        $issuerUrl = 'https://profile.demo.openconext.org/simplesaml/module.php/saml/sp/metadata.php/default-sp';
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
     * @return \Behat\Mink\Session
     */
    private function getSession()
    {
        $client = new \Behat\Mink\Driver\Goutte\Client();
        $driver = new \Behat\Mink\Driver\GoutteDriver($client);
        $client->setClient(new \Guzzle\Http\Client('', array(
            'ssl.certificate_authority' => false
        )));

        // init session:
        $session = new \Behat\Mink\Session($driver);

        // start session:
        $session->start();

        return $session;
    }

    private function factorySamlPAuthNRequest(
        $destinationUrl,
        $assertionConsumerServiceURL,
        $issuerUrl
    )
    {
        $samlpAuthNRequest = <<<SAML
<samlp:AuthnRequest xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
                    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
                    ID="_5a62d5699e34038989f590149c0df34678126614da"
                    Version="2.0"
                    IssueInstant="2013-08-03T02:52:40Z"
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