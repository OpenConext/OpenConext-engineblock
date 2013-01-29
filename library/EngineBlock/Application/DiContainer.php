<?php
class EngineBlock_Application_DiContainer extends Pimple
{
    const XML_CONVERTER = 'xmlConverter';
    const CONSENT_FACTORY = 'consentFactory';

    public function __construct() {
        $this->registerXmlConverter();
        $this->registerConsentFactory();
    }

    protected function registerXmlConverter() {
        return;

        $this[self::XML_CONVERTER] = $this->share(function (EngineBlock_Application_DiContainer $container) {
            return new EngineBlock_Corto_XmlToArray();
        });
    }

    protected function registerConsentFactory() {
        $this[self::CONSENT_FACTORY] = $this->share(function (EngineBlock_Application_DiContainer $container) {
            return new EngineBlock_Corto_Model_Consent_Factory();
        });
    }
}