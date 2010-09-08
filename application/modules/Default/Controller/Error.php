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

    public function testExceptionAction()
    {
        throw new Exception('Test exception');
    }

    public function testErrorAction()
    {
        $var();
    }
}
