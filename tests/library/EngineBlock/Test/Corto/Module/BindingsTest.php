<?php
/**
 * @todo test all other functionalities of Bindings, currently tests a small part of redirection
 */
class EngineBlock_Test_Corto_Module_BindingsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var EngineBlock_Corto_Module_Bindings
     */
    private $bindings;

    public function setup()
    {
        $proxyServer = Phake::mock('EngineBlock_Corto_ProxyServer');
        $log = Phake::mock('EngineBlock_Log');
        Phake::when($proxyServer)->getSessionLog()->thenReturn($log);
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
     * @param string $certificateFile
     *
     * @dataProvider responseFilePathsProvider
     */
    public function testInvalidNameId($xmlFile, $certificateFile)
    {
        $xml2array = new EngineBlock_Corto_XmlToArray();
        $xml = file_get_contents($xmlFile);

        $element = $xml2array->xml2array($xml);

        $publicKey = (file_get_contents($certificateFile));

        if (isset($element['ds:Signature'])) {
            $this->assertTrue(
                $this->bindings->_verifySignatureXMLElement(
                    $publicKey,
                    $xml,
                    $element
                )
            );
        }

        if (isset($element['saml:Assertion']['ds:Signature'])) {
            $this->assertTrue(
                $this->bindings->_verifySignatureXMLElement(
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
                $extension = substr($responseFile->getFilename(), -3);
                $fileNameWithoutExtension = substr($responseFile->getFilename(), 0, -4);
                if ($extension == 'pem') {
                    $responseFiles[$fileNameWithoutExtension]['certificateFile'] = $responseFile->getRealPath();
                } elseif ($extension == 'xml') {
                    $responseFiles[$fileNameWithoutExtension]['responseXmlFile'] = $responseFile->getRealPath();
                }
            }
        }

        return $responseFiles;
    }
}