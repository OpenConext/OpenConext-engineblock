<?php
 
class EngineBlock_Controller_Abstract 
{
    protected $_moduleName;
    protected $_controllerName;
    protected $_viewData = array();
    protected $_noRender = false;

    public function __construct($moduleName, $controllerName)
    {
        $this->_moduleName     = $moduleName;
        $this->_controllerName = $controllerName;
    }

    public function __set($name, $value)
    {
        $this->_viewData[$name] = $value;
        return $value;
    }

    public function __get($name)
    {
        return $this->_viewData[$name];
    }

    public function hasAction($actionName)
    {
        $methodName = $this->_getMethodNameFromAction($actionName);
        return method_exists($this, $methodName);
    }

    public function handleAction($actionName, $arguments)
    {
        $methodName = $this->_getMethodNameFromAction($actionName);

        call_user_method_array($methodName, $this, $arguments);

        if ($this->_noRender) {
            return;
        }

        $renderedView = $this->renderView($actionName);
        EngineBlock_ApplicationSingleton::getInstance()->getHttpResponse()->setBody($renderedView);
    }

    protected function _getMethodNameFromAction($actionName)
    {
        $actionParts = explode('-', $actionName);
        $methodName = array_shift($actionParts);

        foreach ($actionParts as $actionPart)
        {
            $methodName .= ucfirst($actionPart);
        }

        return $methodName . 'Action';
    }

    protected function _getMethodNameFromActionName($actionName)
    {
        return $actionName;
    }

    public function setNoRender($noRender = true)
    {
        $this->_noRender = $noRender;
        return $this;
    }

    protected function renderView($actionName)
    {
        $moduleDir = dirname(__FILE__) . '/../../../application/modules/';
        $filePath = $moduleDir . $this->_moduleName . '/View/' . $this->_controllerName . '/' . $actionName . '.php';

        if (!file_exists($filePath)) {
            // @todo error out!
            die("View $filePath does not exist");
        }

        ob_start();
        
        extract($this->_viewData);
        require $filePath;

        $renderedView = ob_get_contents();
        ob_end_clean();

        return $renderedView;
    }
}
