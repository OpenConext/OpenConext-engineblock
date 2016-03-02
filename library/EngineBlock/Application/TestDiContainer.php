<?php
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

    public function getMailer()
    {
        return Phake::mock('EngineBlock_Mail_Mailer');
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
}
