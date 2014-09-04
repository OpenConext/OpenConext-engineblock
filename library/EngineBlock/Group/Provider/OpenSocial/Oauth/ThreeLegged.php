<?php

class EngineBlock_Group_Provider_OpenSocial_Oauth_ThreeLegged extends EngineBlock_Group_Provider_OpenSocial_Abstract
{
    /**
     * @var EngineBlock_Group_Provider_OpenSocial_Oauth_Helper_AccessToken
     */
    protected $_accessTokenHelper;

    public static function createFromConfigs(Zend_Config $config, $userId)
    {
        $databaseFactory = new EngineBlock_Database_ConnectionFactory();
        $databaseAdapter = $databaseFactory->create(
            EngineBlock_Database_ConnectionFactory::MODE_READ
        );

        $accessTokenHelper = new EngineBlock_Group_Provider_OpenSocial_Oauth_Helper_AccessToken(
            $config->id,
            $databaseAdapter,
            $userId
        );

        $authConfig = $config->auth->toArray();
        if (isset($authConfig['rsaPrivateKey'])) {
            if (empty($authConfig['rsaPrivateKey'])) {
                unset($authConfig['rsaPrivateKey']);
            }
            else {
                $authConfig['rsaPrivateKey'] = new Zend_Crypt_Rsa_Key_Private($authConfig['rsaPrivateKey']);
            }
        }
        
        if (isset($authConfig['rsaPublicKey'])) {
            if (empty($authConfig['rsaPublicKey'])) {
                unset($authConfig['rsaPublicKey']);
            }
            else {
                $authConfig['rsaPublicKey'] = new Zend_Crypt_Rsa_Key_Public($authConfig['rsaPublicKey']);
            }
        }

        // @todo get client from factory via DI container instead of instantiating it here, this would make testing and logging easier
        $httpClient = new Zend_Oauth_Client($authConfig, $config->url, $config);

        $accessToken = $accessTokenHelper->loadAccessToken();
        if ($accessToken) {
            $httpClient->setToken($accessToken);
        }

        $openSocialRestClient = new OpenSocial_Rest_Client($httpClient);

        $provider = new self(
            $config->id,
            $config->name,
            $openSocialRestClient
        );

        $provider->setUserId($userId);
        $provider->setAccessTokenHelper($accessTokenHelper);
        $provider->addPrecondition('EngineBlock_Group_Provider_Precondition_OpenSocial_Oauth_AccessTokenExists');

        $provider->configurePreconditions($config);
        $provider->configureGroupFilters($config);
        $provider->configureGroupMemberFilters($config);

        $decoratedProvider = $provider->configureDecoratorChain($config);

        return $decoratedProvider;
    }

    public function setAccessTokenHelper($helper)
    {
        $this->_accessTokenHelper = $helper;
        return $this;
    }

    public function setAccessToken($accessToken)
    {
        $this->_accessTokenHelper->storeAccessToken($accessToken);
        $this->_openSocialRestClient->getHttpClient()->setToken($accessToken);
    }

    /**
     * @return osapiAuth
     */
    public function getOpenSocialRestClient()
    {
        return $this->_openSocialRestClient;
    }


}