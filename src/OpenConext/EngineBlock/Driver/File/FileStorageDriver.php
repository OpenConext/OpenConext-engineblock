<?php

/**
 * Copyright 2014 SURFnet B.V.
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

use OpenConext\EngineBlock\Assert\Assertion;
use OpenConext\EngineBlock\Driver\StorageDriver;

final class FileStorageDriver implements StorageDriver
{
    /**
     * @var FileHandler
     */
    private $fileHandler;

    /**
     * @var string
     */
    private $filePath;

    /**
     * @param FileHandler $fileHandler
     * @param string      $filePath
     */
    public function __construct(FileHandler $fileHandler, $filePath)
    {
        Assertion::nonEmptyString($filePath, 'filePath');

        $this->fileHandler = $fileHandler;
        $this->filePath    = $filePath;
    }

    public function save($data)
    {
        Assertion::string($data, 'Data to save must be a string, "%s" given');

        $this->fileHandler->writeTo($data, $this->filePath);
    }

    public function load()
    {
        return $this->fileHandler->readFrom($this->filePath);
    }
}
