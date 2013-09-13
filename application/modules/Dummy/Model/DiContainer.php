<?php
class Dummy_Model_DiContainer extends Pimple
{
    const SIMPLESAMLPHP_CONFIG = 'simpleSamlPhpConfig';

    private static $instance;

    public function __construct()
    {
        $this->registerSimpleSamlPhpConfig();
    }

    /**
     * @return Dummy_Model_DiContainer
     */
    public static function getInstance()
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function getSimpleSamlPhpConfig()
    {
        return $this[self::SIMPLESAMLPHP_CONFIG];
    }

    /**
     * @note this might have to be split up between idp/sp sometime, for now config contains only keys which they share
     */
    protected function registerSimpleSamlPhpConfig()
    {
        $this[self::SIMPLESAMLPHP_CONFIG] = $this->share(function (Dummy_Model_DiContainer $container)
        {
            // Create a config containing the keys needed for signing
            $sspConfig = array();
            $keysPath = ENGINEBLOCK_FOLDER_APPLICATION . 'modules/Dummy/keys/';
            $sspConfig['privatekey'] = $keysPath . 'private_key.pem';
            $sspConfig['certData'] = file_get_contents($keysPath . 'certificate.crt');
            return new SimpleSAML_Configuration($sspConfig, null);
        });
    }
}