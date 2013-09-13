<?php
class EngineBlock_Test_Config_CacheProxyTest
    extends PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    private $files;

    private $expectedConfigArray;

    public function setup()
    {
        $this->files = array(
            ENGINEBLOCK_FOLDER_ROOT . 'tests/resources/config/config1.ini',
            ENGINEBLOCK_FOLDER_ROOT . 'tests/resources/config/config2.ini'
        );

        $this->expectedConfigArray = array(
            'foo1' => 'bar1',
            'foo2' => 'bar2',
            'override' => 'overwritten'
        );
    }

    public function testMultipleFilesAreMergedAndLoaded()
    {
        $cacheProxy = new EngineBlock_Config_CacheProxy($this->files);
        /** @var $config Zend_Config */
        $config = $cacheProxy->load();


        $this->assertEquals($this->expectedConfigArray, $config->toArray());
    }

    public function testConfigIsStoredInCache()
    {
        $applicationCacheMock = Phake::mock('Zend_Cache_Backend_Apc');
        $cacheProxy = new EngineBlock_Config_CacheProxy($this->files, $applicationCacheMock);
        /** @var $config Zend_Config */
        $cacheProxy->load();

        Phake::verify($applicationCacheMock)->save(Phake::anyParameters());
    }

    public function testConfigIsLoadedFromCache()
    {
        $configMock = Phake::mock('EngineBlock_Config_Ini');
        $cachedData = array('foo' => 'cached');
        Phake::when($configMock)->toArray()->thenReturn($cachedData);

        $applicationCacheMock = Phake::mock('Zend_Cache_Backend_Apc');
        Phake::when($applicationCacheMock)->test(Phake::anyParameters())->thenReturn(time());
        Phake::when($applicationCacheMock)->load(Phake::anyParameters())->thenReturn($configMock);

        $cacheProxy = new EngineBlock_Config_CacheProxy($this->files, $applicationCacheMock);
        /** @var $config Zend_Config */
        $config = $cacheProxy->load();
        $this->assertInstanceOf('EngineBlock_Config_Ini', $config);
        $this->assertEquals($cachedData, $config->toArray());
    }

    public function testConfigIsNotLoadedFromCacheIfCacheIsTooOld()
    {
        $applicationCacheMock = Phake::mock('Zend_Cache_Backend_Apc');
        Phake::when($applicationCacheMock)->test(Phake::anyParameters())->thenReturn(1);

        $cacheProxy = new EngineBlock_Config_CacheProxy($this->files, $applicationCacheMock);
        /** @var $config Zend_Config */
        $config = $cacheProxy->load();
        $this->assertEquals($this->expectedConfigArray, $config->toArray());
    }

    public function testConfigIsNotLoadedFromCacheIfResultIsInvalid()
    {
        $applicationCacheMock = Phake::mock('Zend_Cache_Backend_Apc');
        Phake::when($applicationCacheMock)->test(Phake::anyParameters())->thenReturn(time());
        Phake::when($applicationCacheMock)->load(Phake::anyParameters())->thenReturn('incorrectResult');

        $cacheProxy = new EngineBlock_Config_CacheProxy($this->files, $applicationCacheMock);
        /** @var $config Zend_Config */
        $config = $cacheProxy->load();
        $this->assertEquals($this->expectedConfigArray, $config->toArray());
    }
}
