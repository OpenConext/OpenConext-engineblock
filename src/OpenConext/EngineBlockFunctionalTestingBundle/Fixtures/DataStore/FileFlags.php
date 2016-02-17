<?php

namespace OpenConext\EngineBlockFunctionalTestingBundle\Fixtures\DataStore;

class FileFlags
{
    protected $dir;

    public function __construct($dir)
    {
        $this->dir = $dir;
    }

    /**
     * @SuppressWarnings(PHPMD.ShortMethodName)
     */
    public function on($name, $value)
    {
        $this->verifyDir();

        file_put_contents($this->dir . DIRECTORY_SEPARATOR . $name, $value);
    }

    public function off($name)
    {
        $this->verifyDir();

        $file = $this->dir . DIRECTORY_SEPARATOR . $name;
        if (!file_exists($file)) {
            return;
        }

        unlink($file);
    }

    protected function verifyDir()
    {
        if (is_writeable($this->dir)) {
            return;
        }

        if (!file_exists($this->dir)) {
            $madeDir = mkdir($this->dir, 0755, true);
            if (!$madeDir) {
                throw new \RuntimeException('Unable to create directory: ' . $this->dir);
            }
        }
    }
}
