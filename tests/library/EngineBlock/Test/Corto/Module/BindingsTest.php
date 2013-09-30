<?php
/**
 * @todo test all other functionalities of Bindings, currently tests a small part of redirection
 */
class EngineBlock_Test_Corto_Module_BindingsTest extends PHPUnit_Framework_TestCase
{
    private $bindings;

    public function setup()
    {
        $proxyServer = Phake::mock('EngineBlock_Corto_ProxyServer');
        $this->bindings = new EngineBlock_Corto_Module_Bindings($proxyServer);
    }

    /**
     * @expectedException EngineBlock_Corto_Module_Bindings_UnsupportedBindingException
     */
    public function testResponseRedirectIsNotSupported()
    {
        $message = array(
            '__' => array(
                'ProtocolBinding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                'paramname' => 'SAMLResponse'
            )
        );
        $remoteEntity = array();
        $this->bindings->send($message, $remoteEntity);
    }

    /**
     * @param string $xmlFile
     *
     * @dataProvider responseFilePathsProvider
     */
    public function testInvalidNameId($xmlFile, $publiKey)
    {
        $server = Phake::mock('EngineBlock_Corto_ProxyServer');
        $bindings = new EngineBlock_Corto_Module_Bindings($server);
        $xml2array = new EngineBlock_Corto_XmlToArray();
        $xml = file_get_contents($xmlFile);

        $element = $xml2array->xml2array($xml);



        if (isset( $element['saml:Assertion']['ds:Signature']['ds:KeyInfo']['ds:X509Data'][0]['ds:X509Certificate'][0]['__v'])) {
            $publicKey = openssl_pkey_get_public($element['saml:Assertion']['ds:Signature']['ds:KeyInfo']['ds:X509Data'][0]['ds:X509Certificate'][0]['__v']);

            var_dump($publicKey);

            // @todo find out how some public key's work and others do not

            $this->assertTrue(
                $bindings->_verifySignatureXMLElement(
                    $publicKey,
                    $xml,
                    $element['saml:Assertion']
                )
            );
        }
    }

    /**
     * Provides a list of paths to response xml files
     * 
     * @return array
     */
    public function responseFilePathsProvider()
    {
        $responseFiles = array();
        $responsesDir = new DirectoryIterator(TEST_RESOURCES_DIR . '/saml/responses');
        /** @var $responseFile DirectoryIterator */
        foreach($responsesDir as $responseFile) {
            if ($responseFile->isFile() && !$responseFile->isDot()) {
                $responseFiles[] = array(
                    $responseFile->getPath() . '/' . $responseFile->getFilename(),
                    $responseFile->getFilename()
                );
            }
        }

        return $responseFiles;
    }
}