<?php

class EngineBlock_Application_ErrorHandler
{
    protected $_application;
    protected $_exitHandlers = array();

    public function __construct(EngineBlock_ApplicationSingleton $application)
    {
        $this->_application = $application;
    }

    /**
     *
     * @todo this naively requires that you won't push error handlers while executing the exit handler
     *
     * @param $fn
     * @param $exitHandler
     */
    public function withExitHandler($fn, $exitHandler)
    {
        $this->pushExitHandler($exitHandler);
        $fn();
        $this->popExitHandler();
    }

    public function pushExitHandler($fn)
    {
        array_push($this->_exitHandlers, $fn);
        return $this;
    }

    public function popExitHandler()
    {
        array_pop($this->_exitHandlers);
        return $this;
    }

    public function exception(Exception $e)
    {
        foreach ($this->_exitHandlers as $exitHandler) {
            $exitHandler($e);
        }

        $this->_application->reportError($e);

        $message = 'A exceptional condition occurred, it has been logged and sent to the administrator.';
        if ($this->_application->getConfiguration()->debug) {
            $message .= PHP_EOL . '<br /><br /> ERROR: ' . PHP_EOL;
            $message .= '<br /><strong style="color: red"><pre>' . var_export($e, true) . '</pre></strong>';
        }
        die($message);
    }

    public function error($errorNumber, $errorMessage, $errorFile, $errorLine)
    {
        if (!(error_reporting() & $errorNumber)) {
            // This error code is not included in error_reporting
            // Execute PHP internal error handler
            return false;
        }

        $errorMessage = $errorMessage . " [$errorFile:$errorLine]";
        $exception = new EngineBlock_Exception($errorMessage, EngineBlock_Exception::CODE_ERROR);
        try {
            foreach ($this->_exitHandlers as $exitHandler) {
                $exitHandler(
                    $exception,
                    array(
                        'type'   => $errorNumber,
                        'message'=> $errorMessage,
                        'file'   => $errorFile,
                        'line'   => $errorLine,
                    )
                );
            }

            $this->_application->reportError(
                $exception
            );
        }
        catch (Exception $e) {
            // Unable to report an error, panic!
        }

        // Execute PHP internal error handler
        return false;
    }

    public function shutdown()
    {
        $lastError = error_get_last();
        if ($lastError['type'] !== E_ERROR && $lastError['type'] !== E_USER_ERROR) {
            // Not a fatal error, probably a normal shutdown
            return false;
        }

        $exception = new EngineBlock_Exception('PHP Fatal error', EngineBlock_Exception::CODE_ERROR);

        foreach ($this->_exitHandlers as $exitHandler) {
            $exitHandler($exception, $lastError);
        }

        // dump PHP error to log
        $log = $this->_application->getLogInstance();
        $log->attach($lastError, 'error');

        $this->_application->reportError(
            $exception
        );
        // Call destruct manually, see also "When will __destruct not be called in PHP"
        // http://stackoverflow.com/a/2385581/4512
        $log->__destruct();

        if (ini_get('display_errors')) {
            echo "<br />" . PHP_EOL;
        }
        $message = 'A very serious error occurred, it has been logged and sent to the administrator.';
        $message .= PHP_EOL . '<br /><br /> ERROR: ' . PHP_EOL;
        $message .= '<br /><strong style="color: red"><pre>' . var_export($lastError, true) . '</pre></strong>';
        die($message);
    }
}