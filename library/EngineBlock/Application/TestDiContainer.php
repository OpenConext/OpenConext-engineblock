<?php
/**
 * Creates mocked versions of dependencies for unit testing
 */
class EngineBlock_Application_TestDiContainer extends EngineBlock_Application_DiContainer
{
    /**
     * Registers a mocked xml converter
     */
    protected function registerXmlConverter() {


        $this[self::XML_CONVERTER] = $this->share(function (EngineBlock_Application_DiContainer $container) {
            return Phake::mock('EngineBlock_Corto_XmlToArray');
        });


    }

    /**
     * Registers a factory which returns mocked consents
     */
    protected function registerConsentFactory() {
        $this[self::CONSENT_FACTORY] = $this->share(function (EngineBlock_Application_DiContainer $container) {
            $consentFactoryMock = Phake::mock('EngineBlock_Corto_Model_Consent_Factory');
            Phake::when($consentFactoryMock)
                ->create(Phake::anyParameters())
                ->thenReturn(Phake::mock('EngineBlock_Corto_Model_Consent'));
            return $consentFactoryMock;
        });
    }
}
