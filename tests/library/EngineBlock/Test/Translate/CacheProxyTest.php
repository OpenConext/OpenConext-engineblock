<?php
class EngineBlock_Test_Translate_CacheProxyTest
    extends PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    private $files;

    private $expectedTranslateArray;

    public function setup()
    {
        $this->files = array(
            'en' => ENGINEBLOCK_FOLDER_ROOT . 'tests/resources/translate/en.php',
            'nl' => ENGINEBLOCK_FOLDER_ROOT . 'tests/resources/translate/nl.php'
        );
    }

    public function testMultipleFilesAreMergedAndLoaded()
    {
        $cacheProxy = new EngineBlock_Translate_CacheProxy($this->files);
        /** @var $translate Zend_Translate */
        $translate = $cacheProxy->load();

        $this->assertEquals('overschreven', $translate->getAdapter()->translate('override'));
    }

    public function testTranslateIsStoredInCache()
    {
        $applicationCacheMock = Phake::mock('Zend_Cache_Backend_Apc');
        $cacheProxy = new EngineBlock_Translate_CacheProxy($this->files, $applicationCacheMock);
        /** @var $translate Zend_Translate */
        $cacheProxy->load();

        Phake::verify($applicationCacheMock)->save(Phake::anyParameters());
    }

    public function testTranslateIsLoadedFromCache()
    {
        $translateMock = Phake::mock('Zend_Translate');
        $cachedData = array('foo' => 'cached');
        Phake::when($translateMock)->translate('onlyincache')->thenReturn('Only in cache');

        $applicationCacheMock = Phake::mock('Zend_Cache_Backend_Apc');
        Phake::when($applicationCacheMock)->test(Phake::anyParameters())->thenReturn(time());
        Phake::when($applicationCacheMock)->load(Phake::anyParameters())->thenReturn($translateMock);

        $cacheProxy = new EngineBlock_Translate_CacheProxy($this->files, $applicationCacheMock);
        /** @var $translate Zend_Translate */
        $translate = $cacheProxy->load();
        $this->assertInstanceOf('Zend_Translate', $translate);
        $this->assertEquals('Only in cache', $translate->translate('onlyincache'));
    }

    public function testTranslateIsNotLoadedFromCacheIfCacheIsTooOld()
    {
        $applicationCacheMock = Phake::mock('Zend_Cache_Backend_Apc');
        Phake::when($applicationCacheMock)->test(Phake::anyParameters())->thenReturn(1);

        $cacheProxy = new EngineBlock_Translate_CacheProxy($this->files, $applicationCacheMock);
        /** @var $translate Zend_Translate */
        $translate = $cacheProxy->load();
        $this->assertEquals('overschreven', $translate->getAdapter()->translate('override'));
    }

    public function testTranslateIsNotLoadedFromCacheIfResultIsInvalid()
    {
        $applicationCacheMock = Phake::mock('Zend_Cache_Backend_Apc');
        Phake::when($applicationCacheMock)->test(Phake::anyParameters())->thenReturn(time());
        Phake::when($applicationCacheMock)->load(Phake::anyParameters())->thenReturn('incorrectResult');

        $cacheProxy = new EngineBlock_Translate_CacheProxy($this->files, $applicationCacheMock);
        /** @var $translate Zend_Translate */
        $translate = $cacheProxy->load();
        $this->assertEquals('overschreven', $translate->getAdapter()->translate('override'));
    }
}
