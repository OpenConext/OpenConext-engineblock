<?php
/**
 * Created by JetBrains PhpStorm.
 * User: boy
 * Date: 11/2/10
 * Time: 3:31 PM
 * To change this template use File | Settings | File Templates.
 */
 
class EngineBlock_Error_Report_Log implements EngineBlock_Error_Report_Interface
{
    public function __construct($config)
    {
    }

    public function report(Exception $exception)
    {
        $application = EngineBlock_ApplicationSingleton::getInstance();
        $application->getLog()->err($exception->getMessage() . PHP_EOL . $exception->getTraceAsString());
    }
}
