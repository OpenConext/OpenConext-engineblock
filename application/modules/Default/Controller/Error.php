<?php
 
class Default_Controller_Error extends EngineBlock_Controller_Abstract
{
    public function displayAction($exception)
    {
        $application = EngineBlock_ApplicationSingleton::getInstance();
        if ($application->getConfigurationValue('debug', false)) {
            $this->exception = $exception;
        }
    }
}
