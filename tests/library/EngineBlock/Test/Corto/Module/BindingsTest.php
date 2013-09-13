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
}
