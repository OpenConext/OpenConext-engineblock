<?php
class EngineBlock_Corto_Filter_Command_Factory
{
    /**
     * @param $name
     * @return EngineBlock_Corto_Filter_Command_Abstract
     * @throws EngineBlock_Exception
     */
    public function create($name) {
        $class = 'EngineBlock_Corto_Filter_Command_' . $name;

        if (!class_exists($class)) {
            throw new EngineBlock_Exception(sprintf('Filter command "%s" does not exist', $name));
        }

        return new $class();
    }
}
