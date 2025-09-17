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

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenConext\EngineBlock\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class FileStorageDriverTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @test
     * @group EngineBlock
     * @group Driver
     *
     * @dataProvider \OpenConext\TestDataProvider::notStringOrEmptyString
     *
     * @param mixed $notStringOrEmptyString
     */
    public function filepath_must_be_a_non_empty_string($notStringOrEmptyString)
    {
        $this->expectException(InvalidArgumentException::class);

        new FileStorageDriver(m::mock(\OpenConext\EngineBlock\Driver\File\FileHandler::class), $notStringOrEmptyString);
    }

    /**
     * @test
     * @group EngineBlock
     * @group Driver
     *
     * @dataProvider \OpenConext\TestDataProvider::notString
     *
     * @param mixed $notString
     */
    public function data_to_save_must_be_a_string($notString)
    {
        $this->expectException(InvalidArgumentException::class);

        $storage = new FileStorageDriver(m::mock(\OpenConext\EngineBlock\Driver\File\FileHandler::class), '/some/path');

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

        $fileHandlerMock = m::mock(\OpenConext\EngineBlock\Driver\File\FileHandler::class);
        $fileHandlerMock->shouldReceive('writeTo')->withArgs([$data, $filePath]);

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

        $fileHandlerMock = m::mock(\OpenConext\EngineBlock\Driver\File\FileHandler::class);
        $fileHandlerMock->shouldReceive('readFrom')->withArgs([$filePath])->andReturn($data);

        $storage = new FileStorageDriver($fileHandlerMock, $filePath);
        $read = $storage->load();

        $this->assertEquals($data, $read);
    }
}
