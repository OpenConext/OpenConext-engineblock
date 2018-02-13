<?php

namespace OpenConext\EngineBlock\Metadata\MetadataRepository\Filter;

use Mockery;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Metadata\Utils;
use PHPUnit_Framework_TestCase;
use Psr\Log\LoggerInterface;

/**
 * Class RemoveDisallowedIdentityProvidersFilter
 *
 * @package OpenConext\EngineBlock\Metadata\MetadataRepository\Filter
 */
class RemoveOtherWorkflowStatesTest extends PHPUnit_Framework_TestCase
{
    public function testRemoveOtherWorkflowState()
    {
        $prodSp = Utils::instantiate(
            'OpenConext\EngineBlock\Metadata\Entity\ServiceProvider',
            array('entityId' => 'https://prod.sp.example.edu', 'workflowState' => ServiceProvider::WORKFLOW_STATE_PROD)
        );
        $filter = new RemoveOtherWorkflowStatesFilter($prodSp, 'idp', 'sp');

        $prodIdp = Utils::instantiate(
            'OpenConext\EngineBlock\Metadata\Entity\ServiceProvider',
            array('entityId' => 'https://prod.idp.example.edu', 'workflowState' => ServiceProvider::WORKFLOW_STATE_PROD)
        );
        $this->assertNotNull($filter->filterRole($prodIdp));

        $testIdp = Utils::instantiate(
            'OpenConext\EngineBlock\Metadata\Entity\ServiceProvider',
            array('entityId' => 'https://test.idp.example.edu', 'workflowState' => ServiceProvider::WORKFLOW_STATE_PROD)
        );
        $this->assertNotNull($filter->filterRole($testIdp));

        $prodSp = Utils::instantiate(
            'OpenConext\EngineBlock\Metadata\Entity\ServiceProvider',
            array('entityId' => 'https://prod.sp.example.edu', 'workflowState' => ServiceProvider::WORKFLOW_STATE_PROD)
        );
        $this->assertNotNull($filter->filterRole($prodSp));

        $testSp = Utils::instantiate(
            'OpenConext\EngineBlock\Metadata\Entity\ServiceProvider',
            array('entityId' => 'https://test.sp.example.edu', 'workflowState' => ServiceProvider::WORKFLOW_STATE_TEST)
        );
        $this->assertNull($filter->filterRole($testSp));

        $buggyIdp = Utils::instantiate(
            'OpenConext\EngineBlock\Metadata\Entity\ServiceProvider',
            array('entityId' => 'https://buggy.idp.example.edu', 'workflowState' => '')
        );
        $this->assertNull($filter->filterRole($buggyIdp));
    }

    public function testLogging()
    {
        $mockLogger = Mockery::mock(LoggerInterface::class);
        $mockLogger
            ->shouldReceive('debug')
            ->with('Dissimilar workflow states (OpenConext\EngineBlock\Metadata\MetadataRepository\Filter\RemoveOtherWorkflowStatesFilter -> prodaccepted)');

        $prodSp = Utils::instantiate(
            'OpenConext\EngineBlock\Metadata\Entity\ServiceProvider',
            array('entityId' => 'https://prod.sp.example.edu', 'workflowState' => ServiceProvider::WORKFLOW_STATE_PROD)
        );
        $filter = new RemoveOtherWorkflowStatesFilter($prodSp, 'idp', 'sp');
        $buggyIdp = Utils::instantiate(
            'OpenConext\EngineBlock\Metadata\Entity\ServiceProvider',
            array('entityId' => 'https://buggy.idp.example.edu', 'workflowState' => '')
        );
        $this->assertNull($filter->filterRole($buggyIdp, $mockLogger));
    }
}
