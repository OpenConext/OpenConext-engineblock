<?php
class EngineBlock_Application_DiContainer extends Pimple
{
    const XML_CONVERTER = 'xmlConverter';
    const CONSENT_FACTORY = 'consentFactory';
    const MAILER = 'mailer';
    const FILTER_COMMAND_FACTORY = 'filterCommandFactory';
    const DATABASE_CONNECTION_FACTORY = 'databaseConnectionFactory';
    const REDIS_CLIENT = 'redisClient';

    public function __construct()
    {
        $this->registerXmlConverter();
        $this->registerConsentFactory();
        $this->registerMailer();
        $this->registerFilterCommandFactory();
        $this->registerDatabaseConnectionFactory();
        $this->registerRedisClient();
    }

    protected function registerXmlConverter()
    {
        $this[self::XML_CONVERTER] = $this->share(function (EngineBlock_Application_DiContainer $container)
        {
            return new EngineBlock_Corto_XmlToArray();
        });
    }

    protected function registerConsentFactory()
    {
        $this[self::CONSENT_FACTORY] = $this->share(function (EngineBlock_Application_DiContainer $container)
        {
            return new EngineBlock_Corto_Model_Consent_Factory(
                $container[$container::FILTER_COMMAND_FACTORY],
                $container[$container::DATABASE_CONNECTION_FACTORY]
            );
        });
    }

    protected function registerMailer()
    {
        $this[self::MAILER] = $this->share(function (EngineBlock_Application_DiContainer $container)
        {
            return new EngineBlock_Mail_Mailer();
        });
    }

    protected function registerFilterCommandFactory()
    {
        $this[self::FILTER_COMMAND_FACTORY] = $this->share(function (EngineBlock_Application_DiContainer $container)
        {
            return new EngineBlock_Corto_Filter_Command_Factory();
        });
    }

    protected function registerDatabaseConnectionFactory()
    {
        $this[self::DATABASE_CONNECTION_FACTORY] = $this->share(function (EngineBlock_Application_DiContainer $container)
        {
            return new EngineBlock_Database_ConnectionFactory();
        });
    }

    /**
     * @return Redis
     */
    public function getRedisClient()
    {
        return $this[self::REDIS_CLIENT];
    }

    protected function registerRedisClient()
    {
        $this[self::REDIS_CLIENT] = $this->share(function (EngineBlock_Application_DiContainer $container)
        {
            $redisClient = new Redis();
            $redisClient->connect('127.0.0.1');

            return $redisClient;
        });
    }
}