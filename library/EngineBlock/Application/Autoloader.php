<?php

class EngineBlock_Application_Autoloader
{
    const PSR0_CLASS_SEPARATOR = '_';
    const PHP_FILE_EXTENSION = '.php';

    /**
     * @var array
     */
    protected $_modules;

    /**
     * @var bool
     */
    protected $_mayLoadTestClasses = false;

    public function mayLoadTestClasses($may = true)
    {
        $this->_mayLoadTestClasses = $may;
        return $this;
    }

    /**
     * Try to auto-load a class.
     *
     * Detects all modules and autoloads any class that begin with 'ModuleName_' and autoloads all EngineBlock_ classes.
     *
     * @static
     * @param string $className
     * @return bool Whether auto-loading worked
     */
    public function load($className)
    {
        if (!isset($this->_modules)) {
            $this->_loadModules();
        }

        if ($this->_loadModuleClass($className)) {
            return true;
        }

        if ($this->_loadLibraryClass($className)) {
            return true;
        }

        if ($this->_mayLoadTestClasses) {
            if ($this->_loadTestClass($className)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Find all module directories.
     */
    protected function _loadModules()
    {
        $modules = array();

        $iterator = new DirectoryIterator(ENGINEBLOCK_FOLDER_MODULES);
        /** @var $item DirectoryIterator */
        foreach ($iterator as $item) {
            if ($item->isDir() && !$item->isDot()) {
                $modules[] = (string)$item;
            }
        }

        $this->_modules = $modules;
    }

    /**
     * @param string $className
     * @return bool
     */
    protected function _loadModuleClass($className)
    {
        // Performance optimization, it's better to stat all the module directories once
        // and do a quick check before even trying than to try for every EngineBlock_ class
        $classNamePrefix = substr($className, 0, strpos($className, self::PSR0_CLASS_SEPARATOR));
        $mightBeModuleClass = in_array($classNamePrefix, $this->_modules);
        if (!$mightBeModuleClass) {
            return false;
        }

        return $this->_tryPsr0Load($className, ENGINEBLOCK_FOLDER_MODULES);
    }

    /**
     * @param string $className
     * @return bool
     */
    protected function _loadLibraryClass($className)
    {
        return $this->_tryPsr0Load($className, ENGINEBLOCK_FOLDER_LIBRARY);
    }

    /**
     * @param $className
     * @return bool
     */
    protected function _loadTestClass($className)
    {
        $classNamePrefix = substr($className, 0, strpos($className, self::PSR0_CLASS_SEPARATOR));
        if ($classNamePrefix !== 'Test') {
            return false;
        }

        $classNameWithoutPrefix = substr($className, 5);
        return $this->_tryPsr0Load($classNameWithoutPrefix, ENGINEBLOCK_FOLDER_ROOT . '/tests/library/');
    }

    /**
     * @param string $className
     * @param string $pathBase
     * @return bool
     */
    protected function _tryPsr0Load($className, $pathBase)
    {
        $fileName = implode(DIRECTORY_SEPARATOR, explode(self::PSR0_CLASS_SEPARATOR, $className)) . self::PHP_FILE_EXTENSION;
        $filePath = $pathBase . $fileName;

        if (!file_exists($filePath)) {
            return false;
        }

        include $filePath;

        return true;
    }
}