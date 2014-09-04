<?php

class Default_Controller_Error extends EngineBlock_Controller_Abstract
{
    public function displayAction($exception)
    {
        $this->_getResponse()->setStatus(500, 'Internal Server Error');

        $application = EngineBlock_ApplicationSingleton::getInstance();
        if ($application->getConfigurationValue('debug', false)) {
            $this->exception = $exception;
        }
    }

    public function notFoundAction()
    {
        $this->_getResponse()->setStatus(404, 'Not Found');
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
