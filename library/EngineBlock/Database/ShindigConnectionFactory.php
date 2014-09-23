<?php

class EngineBlock_Database_ShindigConnectionFactory extends EngineBlock_Database_ConnectionFactory
{
    protected function _getDatabaseSettings()
    {
        $configuration = $this->_getConfiguration();
        if (!isset($configuration->databaseShindig)) {
            throw new EngineBlock_Database_Exception("No shindig database settings?!");
        }
        return $configuration->databaseShindig;
    }
}