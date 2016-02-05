<?php

namespace OpenConext\EngineBlockBridge\Configuration;

use PHPUnit_Framework_TestCase as TestCase;

class EngineBlockIniFileLoaderTest extends TestCase
{
    /**
     * @test
     * @group EngineBlockBridge
     */
    public function ignore_sections()
    {
        $fileLoader = new EngineBlockIniFileLoader();
        $result = $fileLoader->load(array(__DIR__ . '/fixtures/default_configuration.ini'));
        $this->assertArrayNotHasKey('base', $result);
    }

    /**
     * @test
     * @group EngineBlockBridge
     */
    public function load_and_parse_an_ini_file()
    {
        $fileLoader = new EngineBlockIniFileLoader();

        $expectedResult = array(
            'keep_boolean'                 => '',
            'overwrite_boolean'            => '',
            'keep_string'                  => 'the_same',
            'overwrite_string'             => 'some_string',
            'escape_percent'               => '50%%',
            'an_array'                     => array(0, 1),
            'overwrite_config_with_string' => 'x',
            'overwrite_string_with_config' => array('should_this_exist' => '1'),
            'nested'                       => array(
                'keep'      => array('path'   => 'foo'),
                'overwrite' => array('path'   => 'change_this'),
                'escaped'   => array('string' => '%%escape%%.%%this%%')
            ),
        );

        $result = $fileLoader->load(array(__DIR__ . '/fixtures/default_configuration.ini'));

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @test
     * @group EngineBlockBridge
     */
    public function load_and_parse_multiple_ini_files_where_last_defined_has_precedence_over_first_defined()
    {
        $fileLoader = new EngineBlockIniFileLoader();

        $expectedResult = array(
            'keep_boolean'                 => '',
            'overwrite_boolean'            => '1',
            'keep_string'                  => 'the_same',
            'overwrite_string'             => 'a_more_specific_string',
            'escape_percent'               => '50%%',
            'an_array'                     => array(1, 2, 3),
            'overwrite_config_with_string' => 'x',
            'overwrite_string_with_config' => array('should_this_exist' => '1'),
            'nested'                       => array(
                'keep'      => array('path'   => 'foo'),
                'overwrite' => array('path'   => 'changed'),
                'escaped'   => array('string' => '%%escape%%.%%this%%'),
                'added'     => array('path'   => 'new')
            ),
        );

        $result = $fileLoader->load(
            array(
                __DIR__ . '/fixtures/default_configuration.ini',
                __DIR__ . '/fixtures/specific_configuration.ini'
            )
        );

        $this->assertEquals($expectedResult, $result);
    }
}
