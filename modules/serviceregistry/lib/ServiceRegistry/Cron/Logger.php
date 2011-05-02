<?php
/**
 *
 */

/**
 *
 */ 
class ServiceRegistry_Cron_Logger
{
    protected $_hasWarnings = false;
    protected $_hasErrors = false;
    protected $_summary = array();

    public function __construct()
    {
    }

    public function notice($message, $entityId = null)
    {
        $arguments = func_get_args();
        $message = array_shift($arguments);
        array_unshift($arguments, 'Notice');
        array_unshift($arguments, $message);
        call_user_func_array(array($this, 'log'), $arguments);
    }

    public function warn($message, $entityId = null)
    {
        $this->_hasWarnings = true;
        $arguments = func_get_args();
        $message = array_shift($arguments);
        array_unshift($arguments, 'Warning');
        array_unshift($arguments, $message);
        call_user_func_array(array($this, 'log'), $arguments);
    }

    public function error($message, $entityId = null)
    {
        $this->_hasErrors = true;
        $arguments = func_get_args();
        $message = array_shift($arguments);
        array_unshift($arguments, 'Error');
        array_unshift($arguments, $message);
        call_user_func_array(array($this, 'log'), $arguments);
    }

    public function log($message, $namespace1 = null, $namespace2 = null)
    {
        $arguments = func_get_args();
        $message = array_shift($arguments);

        $prefix = "";
        foreach ($arguments as $argument) {
            if (!is_null($argument)) {
                $prefix .= '[' . $argument . ']';
            }
        }
        $this->_summary[] = $prefix . $message;
    }

    public function hasWarnings()
    {
        return $this->_hasWarnings;
    }

    public function hasErrors()
    {
        return $this->_hasErrors;
    }

    public function getSummaryLines()
    {
        return $this->_summary;
    }
}
