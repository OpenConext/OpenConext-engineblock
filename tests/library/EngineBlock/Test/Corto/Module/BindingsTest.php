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
        $response = new EngineBlock_Saml2_ResponseAnnotationDecorator(new SAML2_Response());
        $response->setDeliverByBinding(SAML2_Const::BINDING_HTTP_REDIRECT);

        $remoteEntity = array();
        $this->bindings->send($response, $remoteEntity);
    }

    /**
     * @param string $xmlFile
     * @param string $certificateFile
     *
     * @dataProvider responseProvider
     */
    public function testResponseVerifies($xmlFile, $certificateFile)
    {
        return $this->markTestSkipped('FIXME with SSP signature verification!');

        $xml2array = new EngineBlock_Corto_XmlToArray();
        $xml = file_get_contents($xmlFile);

        $element = $xml2array->xml2array($xml);

        $publicCertificate = file_get_contents($certificateFile);

        $publicKey = openssl_pkey_get_public($publicCertificate);

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
     * Provides a list of paths to response xml files and certificate files
     * 
     * @return array
     */
    public function responseProvider()
    {
        $responseFiles = array();
        $certificateFiles = array();
        $responsesDir = new DirectoryIterator(TEST_RESOURCES_DIR . '/saml/responses');
        /** @var $responseFile DirectoryIterator */
        foreach($responsesDir as $responseFile) {
            if ($responseFile->isFile() && !$responseFile->isDot()) {
                $extension = substr($responseFile->getFilename(), -3);
                $fileNameWithoutExtension = substr($responseFile->getFilename(), 0, -4);

                if ($extension == 'cer') {
                    $certificateFiles[$fileNameWithoutExtension] = $responseFile->getRealPath();
                } elseif ($extension == 'xml') {
                    $responseFiles[$fileNameWithoutExtension] = $responseFile->getRealPath();
                }
            }
        }

        return array_merge_recursive($responseFiles, $certificateFiles);
    }
}
