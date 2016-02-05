<?php

namespace OpenConext\EngineBlock\Driver\File;

use Mockery as m;
use PHPUnit_Framework_TestCase as UnitTest;

class FileStorageDriverTest extends UnitTest
{
    /**
     * @test
     * @group EngineBlock
     * @group Driver
     *
     * @dataProvider \OpenConext\TestDataProvider::notStringOrEmptyString
     * @expectedException \OpenConext\EngineBlock\Exception\InvalidArgumentException
     *
     * @param mixed $notStringOrEmptyString
     */
    public function filepath_must_be_a_non_empty_string($notStringOrEmptyString)
    {
        new FileStorageDriver(m::mock('OpenConext\EngineBlock\Driver\File\FileHandler'), $notStringOrEmptyString);
    }

    /**
     * @test
     * @group EngineBlock
     * @group Driver
     *
     * @dataProvider \OpenConext\TestDataProvider::notString
     * @expectedException \OpenConext\EngineBlock\Exception\InvalidArgumentException
     *
     * @param mixed $notString
     */
    public function data_to_save_must_be_a_string($notString)
    {
        $storage = new FileStorageDriver(m::mock('OpenConext\EngineBlock\Driver\File\FileHandler'), '/some/path');

        $storage->save($notString);
    }

    /**
     * @test
     * @group EngineBlock
     * @group Driver
     */
    public function data_is_written_unmodified_to_file()
    {
        $data     = 'FooBarBaz';
        $filePath = '/some/file/path';

        $fileHandlerMock = m::mock('OpenConext\EngineBlock\Driver\File\FileHandler');
        $fileHandlerMock->shouldReceive('writeTo')->withArgs(array($data, $filePath));

        $storage = new FileStorageDriver($fileHandlerMock, $filePath);
        $storage->save($data);
    }

    /**
     * @test
     * @group EngineBlock
     * @group Driver
     */
    public function data_is_read_from_file_and_returned_unmodified()
    {
        $data = 'FooBarBaz';
        $filePath = '/some/file/path';

        $fileHandlerMock = m::mock('OpenConext\EngineBlock\Driver\File\FileHandler');
        $fileHandlerMock->shouldReceive('readFrom')->withArgs(array($filePath))->andReturn($data);

        $storage = new FileStorageDriver($fileHandlerMock, $filePath);
        $read = $storage->load();

        $this->assertEquals($data, $read);
    }
}
