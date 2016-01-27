<?php
use OpenConext\Component\EngineBlockMetadata\Entity\ServiceProvider;

class EngineBlock_Exception_DissimilarServiceProviderWorkflowStates extends \Exception
{
    /**
     * @var ServiceProvider
     */
    private $trustedProxyServiceProvider;
    /**
     * @var string
     */
    private $requesterEntityId;

    /**
     * EngineBlock_Exception_DissimilarServiceProviderWorkflowStates constructor.
     *
     * @param ServiceProvider $trustedProxyServiceProvider
     * @param string          $requesterEntityId
     */
    public function __construct(
        ServiceProvider $trustedProxyServiceProvider,
        $requesterEntityId
    ) {
        $this->trustedProxyServiceProvider = $trustedProxyServiceProvider;
        $this->requesterEntityId           = $requesterEntityId;
    }
}
