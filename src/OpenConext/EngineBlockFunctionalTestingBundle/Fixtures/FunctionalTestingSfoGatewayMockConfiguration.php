<?php

namespace OpenConext\EngineBlockFunctionalTestingBundle\Fixtures;

use OpenConext\EngineBlockFunctionalTestingBundle\Fixtures\DataStore\AbstractDataStore;
use OpenConext\EngineBlockFunctionalTestingBundle\Mock\MockIdentityProvider;
use OpenConext\EngineBlockFunctionalTestingBundle\Mock\MockServiceProvider;
use RuntimeException;

final class FunctionalTestingSfoGatewayMockConfiguration
{
    private $data = [];

    /**
     * @var null|string
     */
    private $messageStatus = null;

    /**
     * @var null|string
     */
    private $messageSubStatus = null;

    /**
     * @var null|string
     */
    private $messageMessage = null;

    /**
     * @var AbstractDataStore
     */
    private $dataStore;

    public function __construct(AbstractDataStore $dataStore)
    {
        $this->dataStore            = $dataStore;

        $data = $dataStore->load();

        $this->data = (isset($data['data']) && is_array($data['data']) ? $data['data'] : []);
        $this->messageStatus = (isset($data['messageStatus']) ? $data['messageStatus'] : []);
        $this->messageSubStatus = (isset($data['messageSubStatus']) ? $data['messageSubStatus'] : []);
        $this->messageMessage = (isset($data['messageMessage']) ? $data['messageMessage'] : []);
    }

    /**
     * @return MockIdentityProvider|null
     */
    public function getIdentityProviderEntityId()
    {
        return $this->data['idp-entity-id'];
    }

    public function getIdentityProviderPublicKeyCertData()
    {
        return $this->data['idp-public-key'];
    }

    public function getIdentityProviderGetPrivateKeyPem()
    {
        return $this->data['idp-private-key'];
    }

    /**
     * @param MockIdentityProvider|null $mockIdentityProvider
     */
    public function setMockIdentityProvider($mockIdentityProvider)
    {
        $this->data['idp-entity-id'] = $mockIdentityProvider->entityId();
        $this->data['idp-public-key'] = $mockIdentityProvider->publicKeyCertData();
        $this->data['idp-private-key'] = $mockIdentityProvider->getPrivateKeyPem();
    }

    /**
     * @return MockIdentityProvider|null
     */
    public function getServiceProviderEntityId()
    {
        return $this->data['sp-entity-id'];
    }

    /**
     * @return MockIdentityProvider|null
     */
    public function getServiceProviderPublicKeyCertData()
    {
        return $this->data['sp-public-key'];
    }

    /**
     * @param MockServiceProvider|null $mockServiceProvider
     */
    public function setMockServiceProvider($mockServiceProvider)
    {
        $this->data['sp-entity-id'] = $mockServiceProvider->entityId();
        $this->data['sp-public-key'] = $mockServiceProvider->publicKeyCertData();
    }

    /**
     * @param $status
     * @param null $subStatus
     * @param array $message
     */
    public function setMessage($status, $subStatus = null, $message = null)
    {
        $this->messageStatus = $status;
        $this->messageSubStatus = $subStatus;
        $this->messageMessage = $message;
    }

    public function unsetMessage()
    {
        $this->messageStatus = null;
        $this->messageSubStatus = null;
        $this->messageMessage = null;
    }

    /**
     * @return string|null
     */
    public function getStatus()
    {
        return $this->messageStatus;
    }

    /**
     * @return string|null
     */
    public function getSubStatus()
    {
        return $this->messageSubStatus;
    }

    /**
     * @return string|null
     */
    public function getMessage()
    {
        return $this->messageMessage;
    }

    /**
     * @return bool
     */
    public function hasFailure()
    {
        return !empty($this->messageStatus);
    }

    public function save()
    {
        $data = [
            'data' => $this->data,
            'messageStatus' => $this->messageStatus,
            'messageSubStatus' => $this->messageSubStatus,
            'messageMessage' => $this->messageMessage,
        ];

        $this->dataStore->save($data);
    }
}
