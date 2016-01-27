<?php

namespace OpenConext\EngineBlockBridge\Tests;

use OpenConext\EngineBlockBridge\Configuration\EngineBlockConfiguration;
use OpenConext\EngineBlock\Exception\InvalidArgumentException;
use PHPUnit_Framework_TestCase as TestCase;
use stdClass;

class EngineBlockConfigurationTest extends TestCase
{
    /**
     * @test
     * @group Configuration
     */
    public function configuration_can_be_created_from_multidimensional_array()
    {
        $configuredValues = array('path' => array('sub_path' => 'value'));

        $expectedConfiguration = new EngineBlockConfiguration(
            array(
                'path' => new EngineBlockConfiguration(
                    array(
                        'sub_path' => 'value'
                    )
                )
            )
        );

        $actualConfiguration = new EngineBlockConfiguration($configuredValues);

        $this->assertEquals($expectedConfiguration, $actualConfiguration);
    }

    /**
     * @test
     * @group Configuration
     *
     * @dataProvider nonOrEmptyStringProvider
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid argument type
     *
     * @param $path
     */
    public function non_string_or_non_empty_string_path_cannot_be_used_for_querying($path)
    {
        $configuration = new EngineBlockConfiguration(array());
        $configuration->get($path);
    }

    /**
     * @test
     * @group Configuration
     */
    public function default_value_is_retrieved_when_configuration_path_cannot_be_found()
    {
        $defaultValue  = 'some_default_value';
        $configuration = new EngineBlockConfiguration(array());

        $retrievedValue = $configuration->get('not_configured_key', $defaultValue);

        $this->assertSame($defaultValue, $retrievedValue);
    }

    /**
     * @test
     * @group Configuration
     */
    public function default_value_is_retrieved_when_nested_configuration_path_cannot_be_found()
    {
        $defaultValue  = 'some_default_value';
        $configuration = new EngineBlockConfiguration(array('path' => array('sub_path' => 'not_retrieved_value')));

        $retrievedValue = $configuration->get('path.not_configured_key', $defaultValue);

        $this->assertSame($defaultValue, $retrievedValue);
    }

    /**
     * @test
     * @group Configuration
     *
     * @dataProvider scalarOrNullProvider
     *
     * @param $configuredValue
     */
    public function configured_scalars_or_null_can_be_retrieved_by_path($configuredValue)
    {
        $path           = 'path_to_value';
        $configuration  = new EngineBlockConfiguration(array($path => $configuredValue));
        $retrievedValue = $configuration->get($path);

        $this->assertSame($configuredValue, $retrievedValue);
    }

    /**
     * @test
     * @group Configuration
     */
    public function sub_configuration_can_be_retrieved_by_path()
    {
        $configuredSubConfig = array('path_to_value' => 'a_configured_value');
        $subConfigPath       = 'sub_config_path';
        $configuration       = new EngineBlockConfiguration(array($subConfigPath => $configuredSubConfig));

        $retrievedSubConfig = $configuration->get($subConfigPath);

        $this->assertInstanceOf(
            '\OpenConext\EngineBlockBridge\Configuration\EngineBlockConfiguration',
            $retrievedSubConfig
        );
    }

    /**
     * @test
     * @group Configuration
     */
    public function nested_value_can_be_retrieved_by_path()
    {
        $configuredValue = 'some_configured_value';
        $nestedConfig    = array(
            'path' => array(
                'sub_path' => $configuredValue
            )
        );
        $configuration   = new EngineBlockConfiguration($nestedConfig);

        $retrievedValue = $configuration->get('path.sub_path');

        $this->assertEquals($configuredValue, $retrievedValue);
    }

    public function nonOrEmptyStringProvider()
    {
        return array(
            'integer'      => array(1),
            'float'        => array(1.1),
            'boolean'      => array(true),
            'array'        => array(array()),
            'object'       => array(new stdClass()),
            'null'         => array(null),
            'empty string' => array('')
        );
    }

    public function scalarOrNullProvider()
    {
        return array(
            'integer' => array(1),
            'float'   => array(1.1),
            'boolean' => array(true),
            'string'  => array('foo'),
            'null'    => array(null)
        );
    }
}
