<?php
class EngineBlock_Config_CacheProxyTest
    extends PHPUnit_Framework_TestCase
{
    public function testMultipleFilesAreMergedAndLoaded()
    {
        $files = array(
            ENGINEBLOCK_FOLDER_ROOT . 'tests/resources/config/config1.ini',
            ENGINEBLOCK_FOLDER_ROOT . 'tests/resources/config/config2.ini',
        );

        $cacheProxy = new EngineBlock_Config_CacheProxy($files);
        /** @var $config Zend_Config */
        $config = $cacheProxy->load();

        $expectedConfigArray = array(
            'foo1' => 'bar1',
            'foo2' => 'bar2',
            'override' => 'overwritten'
        );
        $this->assertEquals($expectedConfigArray, $config->toArray());
    }
}
