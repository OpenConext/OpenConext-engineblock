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

namespace OpenConext\EngineBlockFunctionalTestingBundle\Fixtures\DataStore;

use League\Flysystem\FilesystemException;
use League\Flysystem\Local\LocalFilesystemAdapter as Local;
use League\Flysystem\Filesystem;
use RuntimeException;

abstract class AbstractDataStore
{
    protected $filePath;

    /**
     * @var Filesystem
     */
    private $fileSystem;

    public function __construct($filePath)
    {
        $directory = dirname($filePath);
        $this->filePath = basename($filePath);
        $adapter = new Local($directory);
        $this->fileSystem = new Filesystem($adapter);

        if (!$this->fileSystem->fileExists($this->filePath)) {
            $this->save([]);
            $this->ensureWwwCanWrite($filePath);
        }
    }

    /**
     * @throws FilesystemException
     */
    public function load($default = [])
    {
        if (!$this->fileSystem->fileExists($this->filePath)) {
            return $default;
        }

        $fileContents = $this->fileSystem->read($this->filePath);

        if (isset($fileContents) && $fileContents === false) {
            throw new RuntimeException(sprintf('Unable to load data from: "%s"', $this->filePath));
        }

        if (empty($fileContents)) {
            return $default;
        }

        $data = $this->decode($fileContents);
        if ($data === false) {
            throw new RuntimeException(sprintf('Unable to decode data from: "%s"', $this->filePath));
        }
        return $data;
    }

    /**
     * @throws FilesystemException
     */
    public function save($data)
    {
        $this->fileSystem->write($this->filePath, $this->encode($data));

        // Because the serialization of IdP / SP mocks is destructive, use the restored data after saving.
        // If this data is not used, the destructed object remains in memory.
        $fileContents = $this->fileSystem->read($this->filePath);

        return $this->decode($fileContents);
    }

    abstract protected function encode($data);

    abstract protected function decode($data);

    /**
     * The db is created during the behat process, which runs as root.
     * But requests from the webserver should also be able to update the state.
    */
    private function ensureWwwCanWrite(string $absolutePath): void
    {
        chown($absolutePath, 'www-data');
        chgrp($absolutePath, 'www-data');
        chmod($absolutePath, 0664);
    }
}
