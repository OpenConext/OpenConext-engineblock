<?php

/**
 * Implementation of the Group Directory interface.
 * 
 * @author ivo
 */
class EngineBlock_Groups_Directory 
{
    /**
     * Get an instance of a group provider interface
     * @param String $groupProviderIdentifier the identifier that indicates which group provider to instantiate
     * @return EngineBlock_Groups_Abstract
     */
    public static function createGroupsClient($groupProviderIdentifier="default")
    {
        $classname = self::getClassname($groupProviderIdentifier);
        
        return new $classname(self::getConfig($groupProviderIdentifier));
    }
    
    public static function getClassname($groupProviderIdentifier)
    {
        // @todo: depending on the groupidentifier, we need to find out if we're dealing with grouper or
        // some other future group provider interface.
        return "EngineBlock_Groups_Grouper";
    }
    
    public static function getConfig($groupProviderIdentifier)
    {
        // @todo: determine config based on the groupProviderIdentifier instead of relying
        // on the default.
        $application = EngineBlock_ApplicationSingleton::getInstance();
        return $application->getConfiguration()->grouper;
    }
}