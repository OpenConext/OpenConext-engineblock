<?php

namespace OpenConext\EngineBlock\Driver\File;

use Exception;
use Mockery as m;
use PHPUnit_Framework_TestCase as UnitTest;

class FilesystemAdapterTest extends UnitTest
{
    /**
     * @test
     * @group EngineBlock
     * @group Driver
     *
     * @dataProvider \OpenConext\TestDataProvider::notString
     * @expectedException \OpenConext\EngineBlock\Exception\InvalidArgumentException
     *
     * @param mixed $nonString
     */
    public function data_to_write_to_file_must_be_a_string($nonString)
    {
        $filesystemAdapter = new FilesystemAdapter(m::mock('\Symfony\Component\Filesystem\Filesystem'));

        $filesystemAdapter->writeTo($nonString, '/some/path');
    }

    /**
     * @test
     * @group EngineBlock
     * @group Driver
     *
     * @dataProvider \OpenConext\TestDataProvider::notStringOrEmptyString
     * @expectedException \OpenConext\EngineBlock\Exception\InvalidArgumentException
     *
     * @param mixed $nonStringOrEmtpyString
     */
    public function in_order_to_write_given_filepath_must_be_a_string($nonStringOrEmtpyString)
    {
        $filesystemAdapter = new FilesystemAdapter(m::mock('\Symfony\Component\Filesystem\Filesystem'));

        $filesystemAdapter->writeTo('data-to-write', $nonStringOrEmtpyString);
    }

    /**
     * @test
     * @group EngineBlock
     * @group Driver
     */
    public function an_exception_thrown_by_filesystem_when_writing_is_converted_to_an_engineblock_exception()
    {
        $filesystemMock = m::mock('\Symfony\Component\Filesystem\Filesystem');
        $filesystemMock->shouldReceive('dumpFile')->andThrow('Symfony\Component\Filesystem\Exception\IOException');

        $filesystemAdapter = new FilesystemAdapter($filesystemMock);

        try {
            $filesystemAdapter->writeTo('data-to-write', '/some/path');
        } catch (Exception $exception) {
            $this->assertInstanceOf('\OpenConext\EngineBlock\Exception\RuntimeException', $exception);
            $this->assertInstanceOf('Symfony\Component\Filesystem\Exception\IOException', $exception->getPrevious());
        }
    }

    /**
     * @test
     * @group EngineBlock
     * @group Driver
     *
     * @expectedException \OpenConext\EngineBlock\Exception\RuntimeException
     */
    public function attempting_to_read_data_from_a_non_existent_file_fails()
    {
        $filesystemMock = m::mock('\Symfony\Component\Filesystem\Filesystem');
        $filesystemMock->shouldReceive('exists')->andReturn(false);

        $filesystemAdapter = new FilesystemAdapter($filesystemMock);

        $filesystemAdapter->readFrom('/does/not/exist');
    }

    /**
     * @test
     * @group EngineBlock
     * @group Driver
     *
     * @expectedException \OpenConext\EngineBlock\Exception\RuntimeException
     */
    public function attempting_to_read_data_from_a_file_that_is_not_readable_fails()
    {
        $filesystemMock = m::mock('\Symfony\Component\Filesystem\Filesystem');
        $filesystemMock->shouldReceive('exists')->andReturn(true);

        $filesystemAdapter = new FilesystemAdapter($filesystemMock);

        $filesystemAdapter->readFrom('/this/is/not/readable');
    }

    /**
     * @test
     * @group EngineBlock
     * @group Driver
     *
     * @expectedException \OpenConext\EngineBlock\Exception\RuntimeException
     */
    public function data_is_returned_without_modification()
    {
        $data = json_encode(array('foo' => array('bar' => 'quuz', 'baz' => 1.24)));

        $resource = fopen('php://memory', 'w');
        fwrite($resource, $data);

        $filesystemMock = m::mock('\Symfony\Component\Filesystem\Filesystem');
        $filesystemMock->shouldReceive('exists')->andReturn(true);

        $filesystemAdapter = new FilesystemAdapter($filesystemMock);
        $read = $filesystemAdapter->readFrom('php://memory');

        fclose($resource);

        $this->assertEquals($data, $read);
    }
}
