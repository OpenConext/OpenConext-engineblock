<?php
 
class Default_Controller_Error extends EngineBlock_Controller_Abstract
{
    public function displayAction($exception)
    {
        $application = EngineBlock_ApplicationSingleton::getInstance();
        if ($application->getConfigurationValue('debug', false)) {
            $this->exception = $exception;
        }
        if (!isset($application->getConfiguration()->error)) {
            return true;
        }
        $error = $application->getConfiguration()->error;
        if (!isset($error->reports)) {
            return true;
        }

        $reporter = $this->_getErrorReporter($error->reports);
        $reporter->report($exception);
    }

    protected function _getErrorReporter(Zend_Config $reports)
    {
        return new EngineBlock_Error_Reporter($reports);
    }
}
