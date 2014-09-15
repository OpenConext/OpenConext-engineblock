<?php

class EngineBlock_Group_Provider_OpenSocial_HttpBasic 
    extends EngineBlock_Group_Provider_OpenSocial_Abstract
{
    public static function createFromConfigs(Zend_Config $config, $userId)
    {
        // @todo get client from factory via DI container instead of instantiating it here, this would make testing and logging easier
        $httpClient = new Zend_Http_Client($config->url);
        $httpClient->setAuth(
            $config->user,
            $config->password
        );

        $openSocialRestClient = new OpenSocial_Rest_Client($httpClient);
        $provider = new self($config->id, $config->name, $openSocialRestClient);

        $provider->setUserId($userId);
        $provider->configurePreconditions($config);
        $provider->configureGroupFilters($config);
        $provider->configureGroupMemberFilters($config);

        $decoratedProvider = $provider->configureDecoratorChain($config);

        return $decoratedProvider;
    }
}
