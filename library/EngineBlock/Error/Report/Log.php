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
        $log = $application->getLog();
        if ($log) {
            $log->err($exception->getMessage() . PHP_EOL . $exception->getTraceAsString());
        }
        else {
            // Catch-22, we don't have a log, so we can't report that we don't have a log as an error
            // We assume that if this is the case something serious is very wrong and we panic
            die("Panic! Unable to log errors, please contact your administrator and ask him to check the log file.");
        }
    }
}
