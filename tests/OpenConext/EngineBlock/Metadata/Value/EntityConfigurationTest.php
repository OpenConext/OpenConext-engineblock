<?php

namespace OpenConext\EngineBlock\Metadata\Value;

use OpenConext\EngineBlock\Exception\InvalidArgumentException;
use PHPUnit_Framework_TestCase as UnitTest;

class EntityConfigurationTest extends UnitTest
{
    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     *
     * @dataProvider \OpenConext\TestDataProvider::notBoolean
     *
     * @param mixed $notBoolean
     */
    public function requires_additional_logging_must_be_a_boolean($notBoolean)
    {
        $this->expectException(InvalidArgumentException::class);

        new EntityConfiguration(
            new AttributeManipulationCode('echo $foo;'),
            WorkflowState::prodaccepted(),
            $notBoolean,
            true,
            true
        );
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     *
     * @dataProvider \OpenConext\TestDataProvider::notBoolean
     *
     * @param mixed $notBoolean
     */
    public function disabled_scoping_must_be_a_boolean($notBoolean)
    {
        $this->expectException(InvalidArgumentException::class);

        new EntityConfiguration(
            new AttributeManipulationCode('echo $foo;'),
            WorkflowState::prodaccepted(),
            true,
            $notBoolean,
            true
        );
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     *
     * @dataProvider \OpenConext\TestDataProvider::notBoolean
     *
     * @param mixed $notBoolean
     */
    public function requires_signed_requests_must_be_a_boolean($notBoolean)
    {
        $this->expectException(InvalidArgumentException::class);

        new EntityConfiguration(
            new AttributeManipulationCode('echo $foo;'),
            WorkflowState::prodaccepted(),
            true,
            true,
            $notBoolean
        );
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function attribute_manipulation_code_can_be_queried()
    {
        $attributeManipulationCode = new AttributeManipulationCode('echo $foo;');

        $samlEntityConfiguration = new EntityConfiguration(
            $attributeManipulationCode,
            WorkflowState::prodaccepted(),
            true,
            true,
            true
        );

        $this->assertEquals($attributeManipulationCode, $samlEntityConfiguration->getAttributeManipulationCode());
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function workflow_State_can_be_queried()
    {
        $workflowState = WorkflowState::testaccepted();

        $samlEntityConfiguration = new EntityConfiguration(
            new AttributeManipulationCode('echo $foo;'),
            $workflowState,
            true,
            true,
            true
        );

        $this->assertEquals($workflowState, $samlEntityConfiguration->getWorkflowState());
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function additional_logging_requirement_can_be_queried()
    {
        $requiresAdditionalLogging      = new EntityConfiguration(
            new AttributeManipulationCode('echo $foo;'),
            WorkflowState::prodaccepted(),
            true,
            true,
            true
        );
        $doesNotRequireAdditionaLogging = new EntityConfiguration(
            new AttributeManipulationCode('echo $foo;'),
            WorkflowState::prodaccepted(),
            false,
            true,
            true
        );

        $this->assertTrue($requiresAdditionalLogging->requiresAdditionalLogging());
        $this->assertFalse($doesNotRequireAdditionaLogging->requiresAdditionalLogging());
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function disabled_scoping_can_be_queried()
    {
        $disablesScoping = new EntityConfiguration(
            new AttributeManipulationCode('echo $foo;'),
            WorkflowState::prodaccepted(),
            true,
            true,
            true
        );
        $enablesScoping  = new EntityConfiguration(
            new AttributeManipulationCode('echo $foo;'),
            WorkflowState::prodaccepted(),
            true,
            false,
            true
        );

        $this->assertTrue($disablesScoping->isScopingDisabled());
        $this->assertFalse($enablesScoping->isScopingDisabled());
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function signed_request_requirement_can_be_queried()
    {
        $requiresSignedRequests       = new EntityConfiguration(
            new AttributeManipulationCode('echo $foo;'),
            WorkflowState::prodaccepted(),
            true,
            true,
            true
        );
        $doesNotRequireSignedRequests = new EntityConfiguration(
            new AttributeManipulationCode('echo $foo;'),
            WorkflowState::prodaccepted(),
            true,
            true,
            false
        );

        $this->assertTrue($requiresSignedRequests->requiresSignedRequests());
        $this->assertFalse($doesNotRequireSignedRequests->requiresSignedRequests());
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function equality_is_verified_on_all_configuration_values()
    {
        $defaultCode = new AttributeManipulationCode('echo $foo;');
        $defaultWorkflowState = WorkflowState::prodaccepted();

        $base                   = new EntityConfiguration($defaultCode, $defaultWorkflowState, true, true, true);
        $same                   = new EntityConfiguration($defaultCode, $defaultWorkflowState, true, true, true);
        $differentCode          = new EntityConfiguration(
            new AttributeManipulationCode('echo $bar;'),
            $defaultWorkflowState,
            true,
            true,
            true
        );
        $differentWorkflowState = new EntityConfiguration(
            $defaultCode,
            WorkflowState::testaccepted(),
            true,
            true,
            true
        );
        $noAdditionalLogging    = new EntityConfiguration($defaultCode, $defaultWorkflowState, false, true, true);
        $enabledScoping         = new EntityConfiguration($defaultCode, $defaultWorkflowState, true, false, true);
        $noSignedRequests       = new EntityConfiguration($defaultCode, $defaultWorkflowState, true, true, false);

        $this->assertTrue($base->equals($same));
        $this->assertFalse($base->equals($differentCode));
        $this->assertFalse($base->equals($differentWorkflowState));
        $this->assertFalse($base->equals($noAdditionalLogging));
        $this->assertFalse($base->equals($enabledScoping));
        $this->assertFalse($base->equals($noSignedRequests));
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function deserializing_serialized_saml_entity_configuration_yields_an_equal_value_object()
    {
        $original = new EntityConfiguration(
            new AttributeManipulationCode('echo $foo;'),
            WorkflowState::testaccepted(),
            true,
            true,
            true
        );

        $deserialized = EntityConfiguration::deserialize($original->serialize());

        $this->assertTrue($original->equals($deserialized));
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     *
     * @dataProvider \OpenConext\TestDataProvider::notArray
     *
     * @param mixed $notArray
     */
    public function deserialization_requires_data_to_be_an_array($notArray)
    {
        $this->expectException(InvalidArgumentException::class);

        EntityConfiguration::deserialize($notArray);
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     *
     * @dataProvider invalidDataProvider
     *
     * @param array $invalidData
     */
    public function deserialization_enforces_that_all_required_keys_are_present($invalidData)
    {
        $this->expectException(InvalidArgumentException::class);

        EntityConfiguration::deserialize($invalidData);
    }

    public function invalidDataProvider()
    {
        return [
            'no match'                       => [
                [
                    'foo'  => 'echo $foo;',
                    'bar'  => 'testaccepted',
                    'baz'  => true,
                    'quuz' => true,
                    'quux' => true
                ]
            ],
            'no attribute_manipulation_code' => [
                [
                    'workflow_state'              => 'testaccepted',
                    'requires_additional_logging' => true,
                    'disable_scoping'             => true,
                    'requires_signed_requests'    => true
                ]
            ],
            'no workflow_state'              => [
                [
                    'attribute_manipulation_code' => 'echo $foo;',
                    'requires_additional_logging' => true,
                    'disable_scoping'             => true,
                    'requires_signed_requests'    => true
                ]
            ],
            'no requires_additional_logging' => [
                [
                    'attribute_manipulation_code' => 'echo $foo;',
                    'workflow_state'              => 'testaccepted',
                    'disable_scoping'             => true,
                    'requires_signed_requests'    => true
                ]
            ],
            'no disable_scoping'             => [
                [
                    'attribute_manipulation_code' => 'echo $foo;',
                    'workflow_state'              => 'testaccepted',
                    'requires_additional_logging' => true,
                    'requires_signed_requests'    => true
                ]
            ],
            'no requires_signed_requests'    => [
                [
                    'attribute_manipulation_code' => 'echo $foo;',
                    'workflow_state'              => 'testaccepted',
                    'requires_additional_logging' => true,
                    'disable_scoping'             => true,
                ]
            ],
        ];
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function it_can_be_cast_to_string()
    {
        $original = new EntityConfiguration(
            new AttributeManipulationCode('echo $foo;'),
            WorkflowState::testaccepted(),
            true,
            true,
            true
        );

        $this->assertInternalType('string', (string) $original);
    }
}
