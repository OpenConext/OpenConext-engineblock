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

use OpenConext\EngineBlock\Assert\Assertion;
use OpenConext\EngineBlock\Exception\RuntimeException;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

final class FilesystemAdapter implements FileHandler
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function writeTo($data, $filePath)
    {
        Assertion::string($data, 'Can only write string data to file, "%s" given');
        Assertion::nonEmptyString($filePath, 'filePath');

        try {
            $this->filesystem->dumpFile($filePath, $data);
        } catch (IOException $exception) {
            $newMessage = sprintf('Could not write data to file "%s": "%s"', $filePath, $exception->getMessage());
            throw new RuntimeException($newMessage, null, $exception);
        }
    }

    public function readFrom($filePath)
    {
        Assertion::nonEmptyString($filePath, 'filePath');

        if (!$this->filesystem->exists($filePath)) {
            throw new RuntimeException(sprintf('Cannot read from file "%s" as it does not exist', $filePath));
        }

        if (!is_readable($filePath)) {
            throw new RuntimeException(sprintf('Cannot read from file "%s" as it is not readable', $filePath));
        }

        $data = file_get_contents($filePath);
        if ($data === false) {
            throw new RuntimeException(sprintf('Could not read data from file "%s"', $filePath));
        }

        return $data;
    }

    public function remove($filePath)
    {
        try {
            $this->filesystem->remove($filePath);
        } catch (IOException $exception) {
            $newMessage = sprintf('Could not remove file "%s", "%s"', $filePath, $exception->getMessage());
            throw new RuntimeException($newMessage, null, $exception);
        }
    }
}
