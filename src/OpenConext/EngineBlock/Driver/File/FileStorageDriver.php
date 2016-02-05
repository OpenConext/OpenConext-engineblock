<?php

namespace OpenConext\EngineBlock\Driver\File;

use OpenConext\EngineBlock\Assert\Assertion;
use OpenConext\EngineBlock\Driver\StorageDriver;

final class FileStorageDriver implements StorageDriver
{
    /**
     * @var FileHandler
     */
    private $fileAccessor;

    /**
     * @var string
     */
    private $filePath;

    /**
     * @param FileHandler $fileAccessor
     * @param string      $filePath
     */
    public function __construct(FileHandler $fileAccessor, $filePath)
    {
        Assertion::nonEmptyString($filePath, 'filePath');

        $this->fileAccessor = $fileAccessor;
        $this->filePath = $filePath;
    }

    public function save($data)
    {
        Assertion::string($data, 'Data to save must be a string, "%s" given');

        $this->fileAccessor->writeTo($data, $this->filePath);
    }

    public function load()
    {
        return $this->fileAccessor->readFrom($this->filePath);
    }
}
