<?php

namespace OpenConext\EngineBlockFunctionalTestingBundle\Fixtures;

use OpenConext\EngineBlockFunctionalTestingBundle\Fixtures\DataStore\AbstractDataStore;
use OpenConext\EngineBlockFunctionalTestingBundle\Mock\MockIdentityProvider;
use OpenConext\EngineBlockFunctionalTestingBundle\Mock\MockServiceProvider;
use RuntimeException;

final class FunctionalTestingSfoGatewayMockConfiguration
{
    /**
     * @var MockIdentityProvider|null
     */
    private $mockIdentityProvider = null;

    /**
     * @var MockServiceProvider|null
     */
    private $mockServiceProvider = null;

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
        $this->mockIdentityProvider = (isset($data['idp']) ? $data['idp'] : null);
        $this->mockServiceProvider = (isset($data['sp']) ? $data['sp'] : null);

        $this->messageStatus = (isset($data['messageStatus']) ? $data['messageStatus'] : []);
        $this->messageSubStatus = (isset($data['messageSubStatus']) ? $data['messageSubStatus'] : []);
        $this->messageMessage = (isset($data['messageMessage']) ? $data['messageMessage'] : []);
    }

    /**
     * @return MockIdentityProvider|null
     */
    public function getMockIdentityProvider()
    {
        return $this->mockIdentityProvider;
    }

    /**
     * @param MockIdentityProvider|null $mockIdentityProvider
     */
    public function setMockIdentityProvider($mockIdentityProvider)
    {
        $this->mockIdentityProvider = $mockIdentityProvider;
    }

    /**
     * @return MockServiceProvider|null
     */
    public function getMockServiceProvider()
    {
        return $this->mockServiceProvider;
    }

    /**
     * @param MockServiceProvider|null $mockServiceProvider
     */
    public function setMockServiceProvider($mockServiceProvider)
    {
        $this->mockServiceProvider = $mockServiceProvider;
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
        return !is_null($this->messageStatus);
    }

    public function save()
    {
        $data = [
            'idp' => $this->mockIdentityProvider,
            'sp' => $this->mockServiceProvider,
            'messageStatus' => $this->messageStatus,
            'messageSubStatus' => $this->messageSubStatus,
            'messageMessage' => $this->messageMessage,
        ];

        $this->dataStore->save($data);
    }
}
