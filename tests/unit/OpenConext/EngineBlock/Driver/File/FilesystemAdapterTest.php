<?php

/**
 * Copyright 2010 SURFnet B.V.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace OpenConext\EngineBlock\Driver\File;

use Exception;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenConext\EngineBlock\Exception\InvalidArgumentException;
use OpenConext\EngineBlock\Exception\RuntimeException;
use PHPUnit\Framework\TestCase;

class FilesystemAdapterTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @test
     * @group EngineBlock
     * @group Driver
     *
     * @dataProvider \OpenConext\TestDataProvider::notString
     *
     * @param mixed $nonString
     */
    public function data_to_write_to_file_must_be_a_string($nonString)
    {
        $filesystemAdapter = new FilesystemAdapter(m::mock('\Symfony\Component\Filesystem\Filesystem'));

        $this->expectException(InvalidArgumentException::class);
        $filesystemAdapter->writeTo($nonString, '/some/path');
    }

    /**
     * @test
     * @group EngineBlock
     * @group Driver
     *
     * @dataProvider \OpenConext\TestDataProvider::notStringOrEmptyString
     *
     * @param mixed $nonStringOrEmtpyString
     */
    public function in_order_to_write_given_filepath_must_be_a_string($nonStringOrEmtpyString)
    {
        $filesystemAdapter = new FilesystemAdapter(m::mock('\Symfony\Component\Filesystem\Filesystem'));

        $this->expectException(InvalidArgumentException::class);
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
     */
    public function attempting_to_read_data_from_a_non_existent_file_fails()
    {
        $filesystemMock = m::mock('\Symfony\Component\Filesystem\Filesystem');
        $filesystemMock->shouldReceive('exists')->andReturn(false);

        $filesystemAdapter = new FilesystemAdapter($filesystemMock);

        $this->expectException(RuntimeException::class);
        $filesystemAdapter->readFrom('/does/not/exist');
    }

    /**
     * @test
     * @group EngineBlock
     * @group Driver
     */
    public function attempting_to_read_data_from_a_file_that_is_not_readable_fails()
    {
        $filesystemMock = m::mock('\Symfony\Component\Filesystem\Filesystem');
        $filesystemMock->shouldReceive('exists')->andReturn(true);

        $filesystemAdapter = new FilesystemAdapter($filesystemMock);

        $this->expectException(RuntimeException::class);
        $filesystemAdapter->readFrom('/this/is/not/readable');
    }
}
