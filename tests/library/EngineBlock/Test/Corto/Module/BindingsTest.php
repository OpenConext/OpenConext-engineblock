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
        Phake::when($proxyServer)->getSigningCertificates()->thenReturn(new EngineBlock_X509_KeyPair(
            new EngineBlock_X509_Certificate(openssl_x509_read(file_get_contents(__DIR__ . '/../../X509/test.pem.crt'))),
            new EngineBlock_X509_PrivateKey(__DIR__ . '/../../X509/test.pem.key')
        ));
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
     * @todo this should be fixed by Boy
     */
//    public function testResponseVerifies($xmlFile, $certificateFile)
//    {
//        return $this->markTestSkipped('FIXME with SSP signature verification!');
//
//        $xml2array = new EngineBlock_Corto_XmlToArray();
//        $xml = file_get_contents($xmlFile);
//
//        $element = $xml2array->xml2array($xml);
//
//        $publicCertificate = file_get_contents($certificateFile);
//
//        $publicKey = openssl_pkey_get_public($publicCertificate);
//
//        if (isset($element['ds:Signature'])) {
//            $this->assertTrue(
//                $this->bindings->_verifySignatureXMLElement(
//                    $publicKey,
//                    $xml,
//                    $element
//                )
//            );
//        }
//
//        if (isset($element['saml:Assertion']['ds:Signature'])) {
//            $this->assertTrue(
//                $this->bindings->_verifySignatureXMLElement(
//                    $publicKey,
//                    $xml,
//                    $element['saml:Assertion']
//                )
//            );
//        }
//    }

    /**
     * Provides a list of paths to response xml files and certificate files
     * 
     * @return array
     */
    public function responseProvider()
    {
        $responseFiles = array();
        $certificateFiles = array();
        $responsesDir = TEST_RESOURCES_DIR . '/saml/responses';
        $defaultCertFile = $responsesDir . '/defaultCert';
        $responsesDirIterator = new DirectoryIterator($responsesDir);
        /** @var $responseFile DirectoryIterator */
        foreach($responsesDirIterator as $responseFile) {
            if ($responseFile->isFile() && !$responseFile->isDot()) {
                $extension = substr($responseFile->getFilename(), -3);
                $fileNameWithoutExtension = substr($responseFile->getFilename(), 0, -4);

                if ($extension == 'cer' || $extension == 'pem') {
                    $certificateFiles[$fileNameWithoutExtension] = $responseFile->getRealPath();
                } elseif ($extension == 'xml') {
                    $responseFiles[$fileNameWithoutExtension] = $responseFile->getRealPath();

                    // Set default certificate, can be overridden by adding a certificate with the same name as the response
                    if (!isset($certificateFiles[$fileNameWithoutExtension])) {
                        $certificateFiles[$fileNameWithoutExtension] = $defaultCertFile;
                    }
                }
            }
        }

        return array_merge_recursive($responseFiles, $certificateFiles);
    }
}
