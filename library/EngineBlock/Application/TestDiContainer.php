<?php

use OpenConext\EngineBlockBundle\Sfo\SfoEndpoint;

/**
 * Creates mocked versions of dependencies for unit testing
 */
class EngineBlock_Application_TestDiContainer extends EngineBlock_Application_DiContainer
{
    public function getXmlConverter()
    {
        return Phake::mock('EngineBlock_Corto_XmlToArray');
    }

    public function getFilterCommandFactory()
    {
        return Phake::mock('EngineBlock_Corto_Filter_Command_Factory');
    }

    public function getDatabaseConnectionFactory()
    {
        return Phake::mock('EngineBlock_Database_ConnectionFactory');
    }

    public function getConsentFactory()
    {
        $consentFactoryMock = Phake::mock('EngineBlock_Corto_Model_Consent_Factory');

        Phake::when($consentFactoryMock)
            ->create(Phake::anyParameters())
            ->thenReturn(Phake::mock('EngineBlock_Corto_Model_Consent'));

        return $consentFactoryMock;
    }

    /**
     * @return EngineBlock_Attributes_Metadata
     */
    public function getAttributeMetadata()
    {
        // returns a realistic representation of the attribute metadata
        $definitions = json_decode(file_get_contents(__DIR__ . '/../../../tests/resources/config/attributes-fixture.json'), true);
        return new EngineBlock_Attributes_Metadata($definitions, Phake::mock('\Psr\Log\LoggerInterface'));
    }

    /**
     * @return array
     */
    public function getEncryptionKeysConfiguration()
    {
        $basePath = $this->container->getParameter('kernel.project_dir');

        return [
            'default' => [
                'publicFile' => $basePath . '/tests/resources/key/engineblock.crt',
                'privateFile' => $basePath . '/tests/resources/key/engineblock.pem',
            ],
        ];
    }
}
