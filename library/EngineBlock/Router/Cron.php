<?php

/**
 * Routes /cron/ urls
 */
class EngineBlock_Router_Cron extends EngineBlock_Router_Default
{
    const DEFAULT_MODULE_NAME = 'Cron';

    public function route($uri)
    {
        $urlParts = preg_split('/\//', $uri, 0, PREG_SPLIT_NO_EMPTY);

        // Only route /cron/ urls
        if ($urlParts[0] !== 'cron') {
            return false;
        }

        parent::route($uri);

        return ($this->_moduleName === $this->_defaultModuleName);
    }
}
