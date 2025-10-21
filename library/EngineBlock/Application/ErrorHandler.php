<?php

/**
 * Copyright 2010 SURFnet B.V.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

class EngineBlock_Application_ErrorHandler
{
    protected $_application;
    protected $_exitHandlers = array();

    /**
     * @var EngineBlock_Application_Error|null
     */
    private $lastErrorHandled;

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

    public function exception(Throwable $e)
    {
        foreach ($this->_exitHandlers as $exitHandler) {
            $exitHandler($e);
        }

        $this->_application->reportError($e);

        $message = 'An exceptional condition occurred. Contact support if this error persists.';
        die($message);
    }

    public function error($errorNumber, $errorMessage, $errorFile, $errorLine)
    {
        if (!(error_reporting() & $errorNumber)) {
            // This error code is not included in error_reporting
            // Execute PHP internal error handler
            return false;
        }

        $this->lastErrorHandled = null;
        $error = new EngineBlock_Application_Error($errorNumber, $errorMessage, $errorFile, $errorLine);

        $exception = new EngineBlock_Exception(
            sprintf('%s [%s:%d]', $error->getMessage(), $error->getFile(), $error->getLine()),
            EngineBlock_Exception::CODE_ERROR
        );
        try {
            foreach ($this->_exitHandlers as $exitHandler) {
                $exitHandler($exception, $error->toArray());
            }

            $this->_application->reportError($exception);
            $this->lastErrorHandled = $error;
        }
        catch (Exception $e) {
            // Unable to report an error, panic!
        }

        // Execute PHP internal error handler
        return false;
    }

    public function shutdown()
    {
        $lastError = EngineBlock_Application_Error::fromLast();
        if (!$lastError || $lastError->getType() !== E_ERROR && $lastError->getType() !== E_USER_ERROR) {
            // Not a fatal error, probably a normal shutdown
            return;
        }

        if (!$this->lastErrorHandled || !$lastError->equals($this->lastErrorHandled)) {
            $exception = new EngineBlock_Exception(
                sprintf('%s [%s:%d]', $lastError->getMessage(), $lastError->getFile(), $lastError->getLine()),
                EngineBlock_Exception::CODE_ERROR
            );

            foreach ($this->_exitHandlers as $exitHandler) {
                $exitHandler($exception, $lastError->toArray());
            }

            $this->_application->reportError($exception);
        }

        if (ini_get('display_errors')) {
            echo "<br />" . PHP_EOL;
        }
        $message = 'A very serious error occurred. Contact support if the error persists.';
        $message .= PHP_EOL . '<br /><br /> ERROR: ' . PHP_EOL . '<br />';
        $message .= '<strong style="color: red"><pre>' . var_export($lastError->toArray(), true) . '</pre></strong>';
        echo($message);
    }
}
