<?php

namespace OpenConext\EngineBlock\Metadata\Value;

use OpenConext\EngineBlock\Exception\InvalidArgumentException;
use PHPUnit_Framework_TestCase as UnitTest;

class WorkflowStateTest extends UnitTest
{
    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function workflow_state_must_be_either_prodaccepted_or_testaccepted()
    {
        $this->expectException(InvalidArgumentException::class);

        new WorkflowState('accaccepted');
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     *
     * @dataProvider typeAndFactoryMethodProvider
     *
     * @param string $workflowState
     * @param string $factoryMethod
     */
    public function a_workflow_state_created_with_a_valid_type_equals_its_factory_created_version(
        $workflowState,
        $factoryMethod
    ) {
        $workflowStateByState   = new WorkflowState($workflowState);
        $workflowStateByFactory = WorkflowState::$factoryMethod();

        $workflowStateByState->equals($workflowStateByFactory);
    }

    public function typeAndFactoryMethodProvider()
    {
        return [
            'prodaccepted' => [WorkflowState::STATE_PRODACCEPTED, 'prodaccepted'],
            'testaccepted' => [WorkflowState::STATE_TESTACCEPTED, 'testaccepted'],
        ];
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function only_the_same_workflow_state_is_considered_equal()
    {
        $prodaccepted = WorkflowState::prodaccepted();
        $testaccepted = WorkflowState::testaccepted();

        $this->assertTrue($prodaccepted->equals(WorkflowState::prodaccepted()));
        $this->assertFalse($prodaccepted->equals($testaccepted));
        $this->assertTrue($testaccepted->equals($testaccepted));
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function the_workflow_state_can_be_retrieved()
    {
        $state = WorkflowState::testaccepted();

        $this->assertEquals($state->getWorkflowState(), WorkflowState::STATE_TESTACCEPTED);
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function deserializing_a_serialized_workflow_state_yields_an_equal_value_object()
    {
        $original = new WorkflowState(WorkflowState::STATE_PRODACCEPTED);

        $deserialized = WorkflowState::deserialize($original->serialize());

        $this->assertTrue($original->equals($deserialized));
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function a_workflow_state_can_be_cast_to_string()
    {
        $workflowState = WorkflowState::prodaccepted();

        $this->assertInternalType('string', (string) $workflowState);
    }
}
